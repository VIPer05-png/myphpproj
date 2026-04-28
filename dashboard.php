<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$toast_msg = $_SESSION['toast_msg'] ?? null;
$toast_type = $_SESSION['toast_type'] ?? 'info';
unset($_SESSION['toast_msg'], $_SESSION['toast_type']);

$conn = new mysqli("localhost", "root", "", "cyber_dashboard");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sorting logic
$sort = $_GET['sort'] ?? 'date_desc';
switch($sort) {
    case 'date_asc': $order_by = 'created_at ASC'; break;
    case 'id_desc': $order_by = 'id DESC'; break;
    case 'id_asc': $order_by = 'id ASC'; break;
    case 'date_desc': default: $order_by = 'created_at DESC'; break;
}

// Fetch approved threats and prepare analytic data
$result = $conn->query("SELECT * FROM threats WHERE status = 'approved' ORDER BY $order_by");
$threats = [];
$severity_counts = ['High' => 0, 'Medium' => 0, 'Low' => 0];
$type_counts = [];

// Fetch pending threats for Admin
$pending_threats = [];
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $pending_res = $conn->query("SELECT * FROM threats WHERE status = 'pending' ORDER BY created_at DESC");
    if ($pending_res) {
        while($p_row = $pending_res->fetch_assoc()) {
            $pending_threats[] = $p_row;
        }
    }
}

while($row = $result->fetch_assoc()) {
    $threats[] = $row;
    
    // Severity metric
    $sev = ucfirst(strtolower($row['severity']));
    if(isset($severity_counts[$sev])) {
        $severity_counts[$sev]++;
    } else {
        $severity_counts['Medium']++;
    }
    
    // Type metric
    $type = $row['type'];
    if(!isset($type_counts[$type])) {
        $type_counts[$type] = 0;
    }
    $type_counts[$type]++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>The Cyberhut</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Fonts: Outfit for dynamic headings, Poppins for body -->
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>

<link href="style.css" rel="stylesheet">
<script src="theme.js"></script>
</head>

<body class="d-flex flex-column min-vh-100">

<!-- ANIMATED BACKGROUND -->
<div class="bg-animated">
    <div class="bg-orb-1"></div>
    <div class="bg-orb-2"></div>
</div>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark px-4 fixed-top mb-5">
    <a class="navbar-brand fw-bold fs-4 d-flex align-items-center" href="index.php">
    <img src="logo.png" alt="Logo" width="30" height="30" class="me-2" style="border-radius:6px; box-shadow: 0 0 10px rgba(6, 182, 212, 0.5);">
    The Cyberhut
</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">

            <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>

            <?php if(isset($_SESSION['user'])): ?>
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>

                <li class="nav-item"><a class="nav-link" href="add_threat.php">Add Threat</a></li>

                <li class="nav-item"><a class="nav-link text-warning fw-500 ms-3">Hi, <?= htmlspecialchars($_SESSION['user']); ?></a></li>
                <li class="nav-item"><a class="nav-link text-danger fw-500 ms-2" href="logout.php">Logout</a></li>

            <?php else: ?>
                <li class="nav-item ms-lg-3"><a class="btn btn-outline-info btn-sm mt-1 mb-1 ms-lg-2" href="login.php">Login</a></li>
                <li class="nav-item"><a class="btn btn-info btn-sm mt-1 mb-1 ms-lg-2 text-dark fw-bold" href="register.php">Register</a></li>
            <?php endif; ?>

            <li class="nav-item dropdown ms-lg-3">
                <a class="nav-link dropdown-toggle text-info" href="#" id="themeDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-palette"></i> Theme
                </a>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end" aria-labelledby="themeDropdown">
                    <li><a class="dropdown-item" href="#" onclick="setTheme('default')"><span style="color:#06b6d4;">●</span> Cyberspace</a></li>
                    <li><a class="dropdown-item" href="#" onclick="setTheme('matrix')"><span style="color:#10b981;">●</span> Matrix Green</a></li>
                    <li><a class="dropdown-item" href="#" onclick="setTheme('critical')"><span style="color:#f43f5e;">●</span> Critical Red</a></li>
                </ul>
            </li>

        </ul>
    </div>
</nav>

<!-- MAIN -->
<div class="container mt-5 pt-5 pb-5">

    <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
        <h2 class="text-white fw-bold">Threat <span class="text-info">Dashboard</span></h2>

        <div class="d-flex gap-2 align-items-center flex-wrap">
            <!-- Filter Search -->
            <div class="input-group" style="width: auto;">
                <span class="input-group-text bg-dark text-muted border-secondary"><i class="bi bi-search"></i></span>
                <input type="text" id="threatSearch" class="form-control bg-dark text-white border-secondary" placeholder="Filter intel...">
            </div>
            
            <!-- Sort Filter -->
            <form method="GET" class="d-flex m-0" id="sortForm">
                <select name="sort" class="form-select bg-dark text-white border-secondary text-muted" style="width: auto; min-width: 140px;" onchange="document.getElementById('sortForm').submit();">
                    <option value="date_desc" <?= $sort === 'date_desc' ? 'selected' : '' ?>>Date (Newest)</option>
                    <option value="date_asc" <?= $sort === 'date_asc' ? 'selected' : '' ?>>Date (Oldest)</option>
                    <option value="id_desc" <?= $sort === 'id_desc' ? 'selected' : '' ?>>ID (Desc)</option>
                    <option value="id_asc" <?= $sort === 'id_asc' ? 'selected' : '' ?>>ID (Asc)</option>
                </select>
            </form>

            <!-- 🔐 RBAC: Only Admin -->
            <?php if(isset($_SESSION['role']) && $_SESSION['role']=='admin'): ?>
            <a href="export_threats.php" class="btn btn-outline-success text-nowrap">
                <i class="bi bi-cloud-download me-1"></i> Export CSV
            </a>
            <a href="add_threat.php" class="btn btn-outline-info text-nowrap">
                <i class="bi bi-plus-circle me-1"></i> Add Threat
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Admin Pending Approvals -->
    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin' && count($pending_threats) > 0): ?>
    <div class="table-wrapper border-warning mb-5" data-aos="fade-down" style="box-shadow: 0 0 15px rgba(245, 158, 11, 0.2);">
        <h5 class="text-warning mb-3 fw-bold"><i class="bi bi-shield-exclamation me-2"></i>Pending Verification (<?= count($pending_threats) ?>)</h5>
        <div class="table-responsive">
            <table class="table table-custom text-center align-middle mb-0">
                <thead class="table-dark text-warning">
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Severity</th>
                        <th>Location</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($pending_threats as $pt): ?>
                    <tr style="background: rgba(245, 158, 11, 0.05);">
                        <td class="text-white fw-bold"><?= htmlspecialchars($pt['title']) ?></td>
                        <td><?= htmlspecialchars($pt['type']) ?></td>
                        <td>
                            <?php
                            $sev = strtolower($pt['severity']); 
                            $sevClass = "severity-medium";
                            if($sev == 'high') $sevClass = "severity-high";
                            if($sev == 'low') $sevClass = "severity-low";
                            ?>
                            <span class="severity-pill <?= $sevClass ?>"><?= $pt['severity'] ?></span>
                        </td>
                        <td class="text-muted"><?= htmlspecialchars($pt['location']) ?></td>
                        <td>
                            <a href="approve_threat.php?id=<?= $pt['id'] ?>" class="btn btn-success btn-sm rounded-circle me-1" title="Approve">
                                <i class="bi bi-check-lg"></i>
                            </a>
                            <a href="reject_threat.php?id=<?= $pt['id'] ?>" class="btn btn-danger btn-sm rounded-circle" title="Reject" onclick="return confirm('Reject and discard this threat?')">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Analytics Charts -->
    <div class="row mb-4" data-aos="fade-up" data-aos-delay="100">
        <div class="col-md-4">
            <div class="card-custom p-4 text-center h-100 d-flex flex-column justify-content-center">
                <h6 class="text-white mb-3 text-uppercase fw-bold" style="letter-spacing:1px; font-size:0.8rem;">Severity Distribution</h6>
                <div style="max-height: 200px; margin: 0 auto; position:relative; width: 100%;">
                    <canvas id="sevChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-8 mt-4 mt-md-0">
            <div class="card-custom p-4 text-center h-100 d-flex flex-column justify-content-center">
                <h6 class="text-white mb-3 text-uppercase fw-bold" style="letter-spacing:1px; font-size:0.8rem;">Attack Vectors</h6>
                <div style="max-height: 200px; margin: 0 auto; position:relative; width: 100%;">
                    <canvas id="typeChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Threat Geolocation Map -->
    <div class="card-custom p-0 mb-4 overflow-hidden position-relative" data-aos="fade-up" data-aos-delay="150" style="height: 400px; z-index: 1;">
        <div id="threatMap" style="width: 100%; height: 100%;"></div>
        <div class="position-absolute top-0 start-0 m-3 p-2 rounded" style="background: rgba(5,11,20,0.8); backdrop-filter: blur(5px); border: 1px solid rgba(6,182,212,0.3); z-index: 1000; pointer-events: none;">
            <h6 class="text-info mb-0 fw-bold m-0" style="font-size: 0.85rem; letter-spacing: 1px;"><i class="bi bi-radar me-2"></i>LIVE INTEL MAP</h6>
        </div>
    </div>

    <div class="table-wrapper" data-aos="fade-up" data-aos-delay="200">

        <div class="table-responsive">
            <table class="table table-custom text-center align-middle">

                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Severity</th>
                        <th>Location</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (count($threats) > 0): ?>
                    <?php foreach($threats as $row): ?>

                        <tr>

                            <td class="text-muted">#<?= $row['id'] ?></td>
                            <td class="position-relative threat-title-cell">
                                <span class="fw-500 text-white threat-title-text" data-description="<?= htmlspecialchars($row['description'] ?? 'No description available', ENT_QUOTES) ?>" style="cursor: help; position: relative;">
                                    <?= htmlspecialchars($row['title']) ?>
                                    <i class="bi bi-info-circle ms-1" style="font-size: 0.85rem; opacity: 0.6;"></i>
                                </span>
                                <div class="description-tooltip" style="display: none;"></div>
                            </td>
                            <td><span class="badge bg-secondary bg-opacity-25 text-light border border-secondary"><?= $row['type'] ?></span></td>

                            <td>
                                <?php
                                $sev = strtolower($row['severity']); 
                                $sevClass = "severity-medium";
                                if($sev == 'high') $sevClass = "severity-high";
                                if($sev == 'low') $sevClass = "severity-low";
                                ?>
                                <span class="severity-pill <?= $sevClass ?>"><?= $row['severity'] ?></span>
                            </td>

                            <td class="text-muted"><i class="bi bi-geo-alt me-1"></i><?= $row['location'] ?></td>
                            <td class="text-muted"><?= $row['created_at'] ?></td>

                            <td>
                                <!-- 🔐 RBAC: Only Admin -->
                                <?php if($_SESSION['role']=='admin'): ?>
                                    <a href="edit_threat.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm rounded-circle me-1" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <a href="delete_threat.php?id=<?= $row['id'] ?>" 
                                       class="btn btn-danger btn-sm rounded-circle"
                                       title="Delete"
                                       onclick="return confirm('Are you sure you want to delete this threat record?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-secondary"><i class="bi bi-eye-slash me-1"></i>View Only</span>
                                <?php endif; ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-muted py-4">No threats found in the system.</td>
                    </tr>
                <?php endif; ?>

                </tbody>

            </table>
        </div>

    </div>

</div>

<!-- FOOTER -->
<footer class="text-center mt-auto py-3">
    <p class="mb-0 text-muted" style="font-size: 0.9rem;">&copy; 2026 CyberDash Intelligence Systems. All rights secured.</p>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
  AOS.init({
    duration: 1000,
    once: true
  });

  // Filter Table Logic
  document.getElementById('threatSearch').addEventListener('keyup', function() {
      const q = this.value.toLowerCase();
      const rows = document.querySelectorAll('tbody tr');
      rows.forEach(row => {
          const text = row.innerText.toLowerCase();
          row.style.display = text.includes(q) ? '' : 'none';
      });
  });
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const typeLabels = <?= json_encode(array_keys($type_counts)) ?>;
  const typeData = <?= json_encode(array_values($type_counts)) ?>;

  Chart.defaults.color = '#94a3b8';
  Chart.defaults.font.family = "'Poppins', sans-serif";

  // Severity Chart (Doughnut)
  new Chart(document.getElementById('sevChart'), {
      type: 'doughnut',
      data: {
          labels: ['High', 'Medium', 'Low'],
          datasets: [{
              data: [
                  <?= $severity_counts['High'] ?? 0 ?>, 
                  <?= $severity_counts['Medium'] ?? 0 ?>, 
                  <?= $severity_counts['Low'] ?? 0 ?>
              ],
              backgroundColor: ['rgba(239, 68, 68, 0.8)', 'rgba(245, 158, 11, 0.8)', 'rgba(16, 185, 129, 0.8)'],
              borderColor: 'transparent',
              hoverOffset: 4
          }]
      },
      options: {
          plugins: { legend: { position: 'right' } },
          maintainAspectRatio: false
      }
  });

  // Vector Chart (Bar)
  new Chart(document.getElementById('typeChart'), {
      type: 'bar',
      data: {
          labels: typeLabels,
          datasets: [{
              label: 'Count',
              data: typeData,
              backgroundColor: 'rgba(6, 182, 212, 0.6)',
              borderColor: '#06b6d4',
              borderWidth: 1,
              borderRadius: 4
          }]
      },
      options: {
          plugins: { legend: { display: false } },
          scales: {
              y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { stepSize: 1 } },
              x: { grid: { display: false } }
          },
          maintainAspectRatio: false
      }
  });

  // Threat Title Tooltip on Hover
  document.querySelectorAll('.threat-title-text').forEach(element => {
      const tooltip = element.nextElementSibling;
      const description = element.getAttribute('data-description');
      
      element.addEventListener('mouseenter', function(e) {
          if (description) {
              tooltip.textContent = description;
              tooltip.style.display = 'block';
              tooltip.style.position = 'absolute';
              tooltip.style.top = '110%';
              tooltip.style.left = '50%';
              tooltip.style.transform = 'translateX(-50%)';
              tooltip.style.background = '#092552';
              tooltip.style.backdropFilter = 'none';
              tooltip.style.border = 'none';
              tooltip.style.borderRadius = '8px';
              tooltip.style.padding = '12px 16px';
              tooltip.style.maxWidth = '350px';
              tooltip.style.color = '#ffffff';
              tooltip.style.fontSize = '0.9rem';
              tooltip.style.lineHeight = '1.4';
              tooltip.style.whiteSpace = 'normal';
              tooltip.style.zIndex = '1000';
              tooltip.style.boxShadow = '0 8px 32px rgba(59, 130, 246, 0.3)';
              tooltip.style.wordWrap = 'break-word';
          }
      });
      
      element.addEventListener('mouseleave', function(e) {
          tooltip.style.display = 'none';
      });
  });

  // --- LEAFLET MAP LOGIC ---
  const mapData = <?= json_encode($threats) ?>;
  const map = L.map('threatMap', { zoomControl: false }).setView([20, 0], 2);
  
  L.control.zoom({ position: 'bottomright' }).addTo(map);

  L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
      attribution: '&copy; OpenStreetMap &copy; CARTO',
      subdomains: 'abcd',
      maxZoom: 19
  }).addTo(map);

  // Instant coordinate lookup for accurate positioning
  const GEO_DB = {
      'russia': [55.7558, 37.6173], 'moscow': [55.7558, 37.6173],
      'china': [39.9042, 116.4074], 'beijing': [39.9042, 116.4074], 'shanghai': [31.2304, 121.4737],
      'north korea': [39.0392, 125.7625], 'pyongyang': [39.0392, 125.7625],
      'south korea': [37.5665, 126.9780], 'seoul': [37.5665, 126.9780],
      'usa': [38.9072, -77.0369], 'united states': [38.9072, -77.0369], 'new york': [40.7128, -74.0060],
      'washington': [38.9072, -77.0369], 'los angeles': [34.0522, -118.2437], 'san francisco': [37.7749, -122.4194],
      'india': [28.6139, 77.2090], 'new delhi': [28.6139, 77.2090], 'mumbai': [19.0760, 72.8777],
      'bangalore': [12.9716, 77.5946], 'hyderabad': [17.3850, 78.4867],
      'uk': [51.5074, -0.1278], 'united kingdom': [51.5074, -0.1278], 'london': [51.5074, -0.1278],
      'germany': [52.5200, 13.4050], 'berlin': [52.5200, 13.4050], 'munich': [48.1351, 11.5820],
      'france': [48.8566, 2.3522], 'paris': [48.8566, 2.3522],
      'japan': [35.6762, 139.6503], 'tokyo': [35.6762, 139.6503], 'osaka': [34.6937, 135.5023],
      'brazil': [-15.7975, -47.8919], 'sao paulo': [-23.5505, -46.6333],
      'canada': [45.4215, -75.6972], 'toronto': [43.6532, -79.3832], 'vancouver': [49.2827, -123.1207],
      'australia': [-33.8688, 151.2093], 'sydney': [-33.8688, 151.2093], 'melbourne': [-37.8136, 144.9631],
      'iran': [35.6892, 51.3890], 'tehran': [35.6892, 51.3890],
      'south africa': [-33.9249, 18.4241], 'johannesburg': [-26.2041, 28.0473], 'cape town': [-33.9249, 18.4241],
      'mexico': [19.4326, -99.1332], 'mexico city': [19.4326, -99.1332],
      'italy': [41.9028, 12.4964], 'rome': [41.9028, 12.4964],
      'spain': [40.4168, -3.7038], 'madrid': [40.4168, -3.7038],
      'turkey': [41.0082, 28.9784], 'istanbul': [41.0082, 28.9784],
      'ukraine': [50.4501, 30.5234], 'kyiv': [50.4501, 30.5234],
      'israel': [31.7683, 35.2137], 'tel aviv': [32.0853, 34.7818],
      'singapore': [1.3521, 103.8198],
      'pakistan': [33.6844, 73.0479], 'islamabad': [33.6844, 73.0479], 'karachi': [24.8607, 67.0011],
      'indonesia': [-6.2088, 106.8456], 'jakarta': [-6.2088, 106.8456],
      'nigeria': [9.0579, 7.4951], 'lagos': [6.5244, 3.3792],
      'egypt': [30.0444, 31.2357], 'cairo': [30.0444, 31.2357],
      'vietnam': [21.0278, 105.8342], 'hanoi': [21.0278, 105.8342],
      'thailand': [13.7563, 100.5018], 'bangkok': [13.7563, 100.5018],
      'netherlands': [52.3676, 4.9041], 'amsterdam': [52.3676, 4.9041],
      'switzerland': [46.9480, 7.4474], 'sweden': [59.3293, 18.0686],
      'poland': [52.2297, 21.0122], 'romania': [44.4268, 26.1025]
  };

  // Fast lookup with API fallback
  function getCoords(locationText) {
      const key = locationText.toLowerCase().trim();
      if (GEO_DB[key]) return Promise.resolve(GEO_DB[key]);
      // Fallback to Nominatim API
      return fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(locationText)}&limit=1`)
          .then(r => r.json())
          .then(data => data.length > 0 ? [parseFloat(data[0].lat), parseFloat(data[0].lon)] : null)
          .catch(() => null);
  }

  // Plot all threats instantly
  async function plotThreats() {
      const geoCache = {};
      let apiQueue = [];

      for (const threat of mapData) {
          if (!threat.location || threat.location.trim() === '') continue;
          const locName = threat.location.trim();
          const key = locName.toLowerCase();

          // Try instant lookup first
          if (GEO_DB[key]) {
              addMarker(GEO_DB[key], threat, locName);
          } else if (geoCache[key]) {
              addMarker(geoCache[key], threat, locName);
          } else {
              apiQueue.push({ threat, locName, key });
          }
      }

      // Process any unknown locations via API (with delay)
      for (const item of apiQueue) {
          if (geoCache[item.key]) {
              addMarker(geoCache[item.key], item.threat, item.locName);
              continue;
          }
          await new Promise(r => setTimeout(r, 1100));
          const coords = await getCoords(item.locName);
          if (coords) {
              geoCache[item.key] = coords;
              addMarker(coords, item.threat, item.locName);
          }
      }
  }

  function addMarker(coords, threat, locName) {
      const sevColor = threat.severity.toLowerCase() === 'high' ? '#ef4444'
                     : threat.severity.toLowerCase() === 'medium' ? '#f59e0b' : '#10b981';
      const dotSize = threat.severity.toLowerCase() === 'high' ? 14 : (threat.severity.toLowerCase() === 'medium' ? 11 : 9);

      const marker = L.marker(coords, { icon: L.divIcon({
          className: 'custom-pulse-icon',
          html: `<div class="radar-pulse" style="width:${dotSize}px;height:${dotSize}px;background:${sevColor};box-shadow:0 0 20px ${sevColor}, 0 0 40px ${sevColor};"></div>`,
          iconSize: [20, 20],
          iconAnchor: [10, 10]
      }) }).addTo(map);

      marker.bindPopup(`
          <div style="min-width:180px;">
              <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;">
                  <span style="width:8px;height:8px;border-radius:50%;background:${sevColor};display:inline-block;"></span>
                  <strong style="color:${sevColor};font-size:0.85rem;">${threat.severity.toUpperCase()} SEVERITY</strong>
              </div>
              <b style="font-size:1rem;">${threat.title}</b><br/>
              <span style="color:#94a3b8;font-size:0.8rem;">${threat.type} — ${locName}</span>
          </div>
      `);
  }

  plotThreats();
</script>

<?php if(isset($toast_msg) && $toast_msg): ?>
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1055;">
    <div id="actionToast" class="toast align-items-center text-bg-<?= isset($toast_type) ? $toast_type : 'success' ?> border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fw-bold">
                <?= htmlspecialchars($toast_msg) ?>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var toastEl = document.getElementById('actionToast');
        var toast = new bootstrap.Toast(toastEl, { delay: 4000 });
        toast.show();
    });
</script>
<?php endif; ?>

</body>
</html>
