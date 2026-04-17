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
}// Sorting logic
$sort = $_GET['sort'] ?? 'date_desc';
switch($sort) {
    case 'date_asc': $order_by = 'created_at ASC'; break;
    case 'id_desc': $order_by = 'id DESC'; break;
    case 'id_asc': $order_by = 'id ASC'; break;
    case 'date_desc': default: $order_by = 'created_at DESC'; break;
}

// Fetch threats and prepare analytic data
$result = $conn->query("SELECT * FROM threats ORDER BY $order_by");
$threats = [];
$severity_counts = ['High' => 0, 'Medium' => 0, 'Low' => 0];
$type_counts = [];

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

                <?php if($_SESSION['role']=='admin'): ?>
                <li class="nav-item"><a class="nav-link" href="add_threat.php">Add Threat</a></li>
                <?php endif; ?>

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

<!-- Bootstrap JS -->
<!-- FOOTER -->
<footer class="text-center mt-auto py-3">
    <p class="mb-0 text-muted" style="font-size: 0.9rem;">&copy; 2026 CyberDash Intelligence Systems. All rights secured.</p>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
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
              tooltip.style.bottom = '130%';
              tooltip.style.left = '50%';
              tooltip.style.transform = 'translateX(-50%)';
              tooltip.style.background = 'rgba(6, 182, 212, 0.15)';
              tooltip.style.backdropFilter = 'blur(10px)';
              tooltip.style.border = '1px solid rgba(6, 182, 212, 0.3)';
              tooltip.style.borderRadius = '8px';
              tooltip.style.padding = '12px 16px';
              tooltip.style.maxWidth = '350px';
              tooltip.style.color = '#e5e7eb';
              tooltip.style.fontSize = '0.9rem';
              tooltip.style.lineHeight = '1.4';
              tooltip.style.whiteSpace = 'normal';
              tooltip.style.zIndex = '1000';
              tooltip.style.boxShadow = '0 8px 32px rgba(6, 182, 212, 0.15)';
              tooltip.style.wordWrap = 'break-word';
          }
      });
      
      element.addEventListener('mouseleave', function(e) {
          tooltip.style.display = 'none';
      });
  });
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
