<?php
session_start();

// 🔐 RBAC: Must be logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $type = $_POST['type'];
    $severity = $_POST['severity'];
    $location = $_POST['location'];
    $description = $_POST['description'] ?? '';
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;
    
    // Validate coordinates
    if ($latitude !== null && $longitude !== null) {
        $latitude = floatval($latitude);
        $longitude = floatval($longitude);
        
        // Verify coordinates are within valid range
        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            $_SESSION['toast_msg'] = "Invalid coordinates! Latitude must be -90 to 90, Longitude must be -180 to 180.";
            $_SESSION['toast_type'] = "danger";
            header("Location: add_threat.php");
            exit();
        }
    } else {
        // If coordinates not provided, set to default
        $latitude = 20;
        $longitude = 0;
    }
    
    // Admins get immediate approval, users go to pending
    $status = ($_SESSION['role'] === 'admin') ? 'approved' : 'pending';
    $coordinates_verified = ($_SESSION['role'] === 'admin') ? 1 : 0;

    $stmt = $conn->prepare("INSERT INTO threats(title,type,severity,location,latitude,longitude,coordinates_verified,description,status) VALUES(?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("ssssddiis", $title, $type, $severity, $location, $latitude, $longitude, $coordinates_verified, $description, $status);

    if ($stmt->execute()) {
        if ($status === 'approved') {
            $_SESSION['toast_msg'] = "Threat initialized and logged successfully with precise coordinates.";
        } else {
            $_SESSION['toast_msg'] = "Threat logged and is awaiting Admin verification of coordinates.";
        }
        $_SESSION['toast_type'] = "success";
        header("Location: dashboard.php");
        exit();
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="form-wrapper">
    <div class="card custom-card p-5" data-aos="zoom-in" data-aos-duration="600">

        <h3 class="mb-4 text-center fw-bold text-white">Log New <span style="background: linear-gradient(90deg, #06b6d4, #3b82f6); background-clip: text; -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Intel</span></h3>

        <form method="POST" autocomplete="off">

        <div class="mb-3">
            <label class="mb-1"><i class="bi bi-tag me-1 text-info"></i>Designation Title</label>
            <input type="text" name="title" class="form-control" placeholder="e.g., Ransomware breach" required>
        </div>

        <div class="mb-3">
            <label class="mb-1"><i class="bi bi-diagram-3 me-1 text-info"></i>Vector Type</label>
            <input type="text" name="type" class="form-control" placeholder="e.g., Malware" required>
        </div>

        <div class="mb-3">
            <label class="mb-1"><i class="bi bi-exclamation-triangle me-1 text-info"></i>Severity Level</label>
            <select name="severity" class="form-select">
                <option value="Low">Low Priority</option>
                <option value="Medium">Medium Severity</option>
                <option value="High">High Alert</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="mb-1"><i class="bi bi-geo-alt me-1 text-info"></i>Origin Location</label>
            <input type="text" name="location" id="location" class="form-control" placeholder="e.g., Datacenter 4" required>
        </div>

        <!-- Precise Geographic Coordinates Section -->
        <div class="mb-4 p-3 rounded" style="background: rgba(6, 182, 212, 0.1); border: 1px solid rgba(6, 182, 212, 0.3);">
            <label class="mb-2 fw-bold text-info"><i class="bi bi-pin-map me-1"></i>Precise Coordinates (Optional)</label>
            <p class="text-muted small mb-3">Click on the map below to select threat location coordinates, or enter them manually for maximum accuracy.</p>
            
            <div style="height: 350px; border-radius: 8px; overflow: hidden; margin-bottom: 12px; border: 1px solid rgba(6, 182, 212, 0.3);" id="coordMap"></div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small text-muted">Latitude (-90 to 90)</label>
                    <input type="number" name="latitude" id="latitude" class="form-control" step="0.00001" min="-90" max="90" placeholder="e.g., 40.7128" value="">
                    <small class="text-muted d-block mt-1">Positive = North, Negative = South</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label small text-muted">Longitude (-180 to 180)</label>
                    <input type="number" name="longitude" id="longitude" class="form-control" step="0.00001" min="-180" max="180" placeholder="e.g., -74.0060" value="">
                    <small class="text-muted d-block mt-1">Positive = East, Negative = West</small>
                </div>
            </div>
            
            <small class="text-info d-block mt-2 mt-2">
                <i class="bi bi-info-circle me-1"></i>Admin will verify these coordinates before publishing
            </small>
        </div>

        <div class="mb-4">
            <label class="mb-1"><i class="bi bi-file-text me-1 text-info"></i>Threat Description</label>
            <textarea name="description" class="form-control" placeholder="Add detailed description of the threat..." rows="4" style="resize: vertical; background: var(--bg-input); border: 1px solid var(--c-primary); border-radius: 8px; color: #e5e7eb;"></textarea>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2">
            <?= ($_SESSION['role'] === 'admin') ? 'Add Threat to Intel' : 'Submit Threat for Verification' ?>
        </button>

        </form>

    </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
  AOS.init({
    duration: 1000,
    once: true
  });

  // Interactive Map Picker for Coordinates
  let currentMarker = null;
  const bounds = [
      [-90, -180],
      [90, 180]
  ];
  const coordMap = L.map('coordMap', { 
      zoomControl: true,
      maxBounds: bounds,
      maxBoundsViscosity: 1.0,
      minZoom: 1
  }).setView([20, 0], 2);
  
  L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
      attribution: '&copy; OpenStreetMap &copy; CARTO',
      subdomains: 'abcd',
      maxZoom: 19,
      noWrap: true
  }).addTo(coordMap);

  // Handle map clicks to set coordinates
  coordMap.on('click', function(e) {
      const lat = e.latlng.lat.toFixed(8);
      const lng = e.latlng.lng.toFixed(8);
      
      // Update input fields
      document.getElementById('latitude').value = lat;
      document.getElementById('longitude').value = lng;
      
      // Remove old marker
      if (currentMarker) {
          coordMap.removeLayer(currentMarker);
      }
      
      // Add new marker
      currentMarker = L.marker([lat, lng], {
          icon: L.icon({
              iconUrl: 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgZmlsbD0iIzA2YjZkNCI+PHBhdGggZD0iTTEyIDJDNi40OCAyIDIgNi40OCAyIDEyczQuNDggMTAgMTAgMTAgMTAtNC40OCAxMC0xMFMxNy41MiAyIDEyIDJ6bTAgMThjLTQuNDIgMC04LTMuNTgtOC04czMuNTgtOCA4LTggOCAzLjU4IDggOC0zLjU4IDgtOCA4eiIvPjwvc3ZnPg==',
              iconSize: [24, 24],
              iconAnchor: [12, 24],
              popupAnchor: [0, -24]
          })
      }).addTo(coordMap).bindPopup(`<strong>Selected:</strong><br/>Lat: ${lat}<br/>Lng: ${lng}`).openPopup();
      
      // Reverse geocoding to automatically fill the location name
      fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
          .then(response => response.json())
          .then(data => {
              if (data && data.display_name) {
                  const parts = data.display_name.split(', ');
                  const shortName = parts.slice(0, Math.min(3, parts.length)).join(', ');
                  document.getElementById('location').value = shortName;
              }
          })
          .catch(err => console.log('Reverse geocoding error:', err));
  });

  // Allow manual input to update map
  document.getElementById('latitude').addEventListener('change', updateMapFromInputs);
  document.getElementById('longitude').addEventListener('change', updateMapFromInputs);

  function updateMapFromInputs() {
      const lat = parseFloat(document.getElementById('latitude').value);
      const lng = parseFloat(document.getElementById('longitude').value);
      
      if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
          // Remove old marker
          if (currentMarker) {
              coordMap.removeLayer(currentMarker);
          }
          
          // Add new marker
          currentMarker = L.marker([lat, lng], {
              icon: L.icon({
                  iconUrl: 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgZmlsbD0iIzA2YjZkNCI+PHBhdGggZD0iTTEyIDJDNi40OCAyIDIgNi40OCAyIDEyczQuNDggMTAgMTAgMTAgMTAtNC40OCAxMC0xMFMxNy41MiAyIDEyIDJ6bTAgMThjLTQuNDIgMC04LTMuNTgtOC04czMuNTgtOCA4LTggOCAzLjU4IDggOC0zLjU4IDgtOCA4eiIvPjwvc3ZnPg==',
                  iconSize: [24, 24],
                  iconAnchor: [12, 24],
                  popupAnchor: [0, -24]
              })
          }).addTo(coordMap).bindPopup(`<strong>Coordinates:</strong><br/>Lat: ${lat.toFixed(8)}<br/>Lng: ${lng.toFixed(8)}`).openPopup();
          
          // Pan map to coordinates
          coordMap.setView([lat, lng], 8);
          
          // Reverse geocoding to automatically fill the location name
          fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
              .then(response => response.json())
              .then(data => {
                  if (data && data.display_name) {
                      const parts = data.display_name.split(', ');
                      const shortName = parts.slice(0, Math.min(3, parts.length)).join(', ');
                      document.getElementById('location').value = shortName;
                  }
              })
              .catch(err => console.log('Reverse geocoding error:', err));
      }
  }
</script>

</body>
</html>
