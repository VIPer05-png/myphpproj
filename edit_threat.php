<?php
session_start();

// 🔐 RBAC: Only Admin can edit
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit();
}

require_once 'includes/db.php';

// 🔒 Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = $_GET['id'];

// Fetch existing data
$stmt = $conn->prepare("SELECT * FROM threats WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$threat = $result->fetch_assoc();

// Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = $_POST['title'];
    $type = $_POST['type'];
    $severity = $_POST['severity'];
    $location = $_POST['location'];
    $description = $_POST['description'] ?? '';
    $latitude = $_POST['latitude'] ?? $threat['latitude'];
    $longitude = $_POST['longitude'] ?? $threat['longitude'];
    $coordinates_verified = isset($_POST['coordinates_verified']) ? 1 : 0;

    // Validate coordinates
    if ($latitude !== null && $longitude !== null) {
        $latitude = floatval($latitude);
        $longitude = floatval($longitude);
        
        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            $_SESSION['toast_msg'] = "Invalid coordinates! Latitude must be -90 to 90, Longitude must be -180 to 180.";
            $_SESSION['toast_type'] = "danger";
            header("Location: edit_threat.php?id=" . $id);
            exit();
        }
    }

    $stmt = $conn->prepare("UPDATE threats SET title=?, type=?, severity=?, location=?, latitude=?, longitude=?, coordinates_verified=?, description=? WHERE id=?");
    $stmt->bind_param("ssssddiis", $title, $type, $severity, $location, $latitude, $longitude, $coordinates_verified, $description, $id);

    if ($stmt->execute()) {
        $_SESSION['toast_msg'] = "Threat parameters successfully updated with verified coordinates.";
        $_SESSION['toast_type'] = "info";
        header("Location: dashboard.php");
        exit();
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="form-wrapper">
    <div class="card custom-card p-4 shadow" data-aos="zoom-in" data-aos-duration="800">

        <h3 class="mb-4 text-center">
            <i class="bi bi-pencil-square text-info me-2"></i>Modify Threat Intel
        </h3>

        <form method="POST">
            
            <div class="mb-3">
                <label class="form-label">Threat Nomenclature</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-hash"></i></span>
                    <input type="text" name="title" value="<?= htmlspecialchars($threat['title']) ?>" class="form-control" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Attack Vector / Type</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-diagram-3"></i></span>
                    <input type="text" name="type" value="<?= htmlspecialchars($threat['type']) ?>" class="form-control" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Severity Index</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-exclamation-triangle"></i></span>
                        <select name="severity" class="form-select">
                            <option value="Low" <?= $threat['severity']=='Low'?'selected':'' ?>>Low Priority</option>
                            <option value="Medium" <?= $threat['severity']=='Medium'?'selected':'' ?>>Medium</option>
                            <option value="High" <?= $threat['severity']=='High'?'selected':'' ?>>High Critical</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Origin / Target Location</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                        <input type="text" name="location" id="location" value="<?= htmlspecialchars($threat['location']) ?>" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Threat Description</label>
                <textarea name="description" class="form-control" placeholder="Add detailed description of the threat..." rows="4" style="resize: vertical; background: var(--bg-input); border: 1px solid var(--c-primary); border-radius: 8px; color: #e5e7eb;"><?= htmlspecialchars($threat['description'] ?? '') ?></textarea>
            </div>

            <!-- Precise Geographic Coordinates Verification -->
            <div class="mb-4 p-3 rounded" style="background: rgba(6, 182, 212, 0.1); border: 1px solid rgba(6, 182, 212, 0.3);">
                <label class="mb-2 fw-bold text-info"><i class="bi bi-pin-map me-1"></i>Verify & Update Coordinates</label>
                <p class="text-muted small mb-3">Review and adjust the threat location coordinates for accuracy.</p>
                
                <div style="height: 350px; border-radius: 8px; overflow: hidden; margin-bottom: 12px; border: 1px solid rgba(6, 182, 212, 0.3);" id="coordMap"></div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Latitude (-90 to 90)</label>
                        <input type="number" name="latitude" id="latitude" class="form-control" step="0.00001" min="-90" max="90" placeholder="e.g., 40.7128" value="<?= htmlspecialchars($threat['latitude'] ?? '') ?>">
                        <small class="text-muted d-block mt-1">Positive = North, Negative = South</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Longitude (-180 to 180)</label>
                        <input type="number" name="longitude" id="longitude" class="form-control" step="0.00001" min="-180" max="180" placeholder="e.g., -74.0060" value="<?= htmlspecialchars($threat['longitude'] ?? '') ?>">
                        <small class="text-muted d-block mt-1">Positive = East, Negative = West</small>
                    </div>
                </div>

                <div class="form-check">
                    <input type="checkbox" name="coordinates_verified" id="coordinates_verified" class="form-check-input" value="1" <?= ($threat['coordinates_verified'] ? 'checked' : '') ?>>
                    <label class="form-check-label text-info" for="coordinates_verified">
                        <i class="bi bi-check-circle me-1"></i>Mark coordinates as verified and accurate
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 mt-2">
                <i class="bi bi-save me-1"></i> Update Intel Profile
            </button>
            <a href="dashboard.php" class="btn btn-outline-secondary text-white border-secondary w-100 py-2 mt-3">Cancel</a>

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

  // Load existing coordinates if available
  const existingLat = parseFloat(document.getElementById('latitude').value);
  const existingLng = parseFloat(document.getElementById('longitude').value);
  
  if (!isNaN(existingLat) && !isNaN(existingLng)) {
      currentMarker = L.marker([existingLat, existingLng], {
          icon: L.icon({
              iconUrl: 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgZmlsbD0iIzA2YjZkNCI+PHBhdGggZD0iTTEyIDJDNi40OCAyIDIgNi40OCAyIDEyczQuNDggMTAgMTAgMTAgMTAtNC40OCAxMC0xMFMxNy41MiAyIDEyIDJ6bTAgMThjLTQuNDIgMC04LTMuNTgtOC04czMuNTgtOCA4LTggOCAzLjU4IDggOC0zLjU4IDgtOCA4eiIvPjwvc3ZnPg==',
              iconSize: [24, 24],
              iconAnchor: [12, 24],
              popupAnchor: [0, -24]
          })
      }).addTo(coordMap).bindPopup(`<strong>Current:</strong><br/>Lat: ${existingLat.toFixed(8)}<br/>Lng: ${existingLng.toFixed(8)}`).openPopup();
      coordMap.setView([existingLat, existingLng], 8);
  }

  // Handle map clicks to set coordinates
  coordMap.on('click', function(e) {
      const lat = e.latlng.lat.toFixed(8);
      const lng = e.latlng.lng.toFixed(8);
      
      document.getElementById('latitude').value = lat;
      document.getElementById('longitude').value = lng;
      
      if (currentMarker) {
          coordMap.removeLayer(currentMarker);
      }
      
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
          if (currentMarker) {
              coordMap.removeLayer(currentMarker);
          }
          
          currentMarker = L.marker([lat, lng], {
              icon: L.icon({
                  iconUrl: 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgZmlsbD0iIzA2YjZkNCI+PHBhdGggZD0iTTEyIDJDNi40OCAyIDIgNi40OCAyIDEyczQuNDggMTAgMTAgMTAgMTAtNC40OCAxMC0xMFMxNy41MiAyIDEyIDJ6bTAgMThjLTQuNDIgMC04LTMuNTgtOC04czMuNTgtOCA4LTggOCAzLjU4IDggOC0zLjU4IDgtOCA4eiIvPjwvc3ZnPg==',
                  iconSize: [24, 24],
                  iconAnchor: [12, 24],
                  popupAnchor: [0, -24]
              })
          }).addTo(coordMap).bindPopup(`<strong>Coordinates:</strong><br/>Lat: ${lat.toFixed(8)}<br/>Lng: ${lng.toFixed(8)}`).openPopup();
          
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
