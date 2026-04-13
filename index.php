
<?php
session_start();
$conn = new mysqli("localhost","root","","cyber_dashboard");

$result = $conn->query("SELECT * FROM threats ORDER BY created_at DESC LIMIT 5");

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
<nav class="navbar navbar-expand-lg navbar-dark px-4 fixed-top">
    <a class="navbar-brand fw-bold fs-4 d-flex align-items-center" href="#">
    <img src="logo.png" alt="Logo" width="30" height="30" class="me-2" style="border-radius:6px; box-shadow: 0 0 10px rgba(6, 182, 212, 0.5);">
    The Cyberhut
</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">

            <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>

            <?php if(isset($_SESSION['user'])): ?>
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>

                <?php if($_SESSION['role']=='admin'): ?>
                <li class="nav-item"><a class="nav-link" href="add_threat.php">Add Threat</a></li>
                <?php endif; ?>

                <li class="nav-item"><a class="nav-link text-warning fw-500 ms-3">Hi, <?= $_SESSION['user']; ?></a></li>
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

<!-- HERO -->
<div class="hero mt-5 pt-4 container">
    <div class="row w-100 align-items-center">
        <!-- Left Typography -->
        <div class="col-lg-6 text-center text-lg-start z-1" data-aos="fade-right" data-aos-duration="1000">
            <div class="mb-4 d-inline-flex align-items-center bg-dark bg-opacity-50 border border-info rounded-pill px-3 py-2" style="backdrop-filter: blur(5px);">
                <span class="spinner-grow spinner-grow-sm text-info me-2" style="width: 0.5rem; height: 0.5rem;"></span>
                <span class="text-info fw-bold text-uppercase" style="font-size: 0.75rem; letter-spacing: 2px;">Global Intel Network Active</span>
            </div>

            <img src="logo.png" alt="Cyberhut Logo" class="mb-4 d-block d-lg-none mx-auto" style="width: 80px; height: 80px; filter: drop-shadow(0 0 20px rgba(6,182,212,0.6)); border-radius: 20%;">

            <h1 class="display-3 fw-bolder mb-3" style="line-height: 1.1;">
                Securing the <br>
                <span style="background: linear-gradient(90deg, #06b6d4, #3b82f6); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Cyberhut</span> Network
            </h1>
            
            <p class="lead text-muted mx-auto mx-lg-0 mb-5" style="max-width: 600px; font-size: 1.15rem;">
                Empowering organizations to monitor, analyze, and proactively manage global cyber threats with real-time analytics and neural visualization.
            </p>

            <div class="d-flex justify-content-center justify-content-lg-start gap-3">
                <?php if(!isset($_SESSION['user'])): ?>
                    <a href="login.php" class="btn btn-primary px-4 py-3 rounded-pill fw-bold" style="box-shadow: 0 8px 20px rgba(6, 182, 212, 0.4);">
                        <i class="bi bi-terminal me-2"></i>Access Terminal
                    </a>
                    <a href="register.php" class="btn btn-outline-light px-4 py-3 rounded-pill fw-bold" style="background: rgba(255,255,255,0.05); backdrop-filter: blur(10px);">
                        Create Clearance
                    </a>
                <?php else: ?>
                    <a href="dashboard.php" class="btn btn-primary px-4 py-3 rounded-pill fw-bold" style="box-shadow: 0 8px 20px rgba(6, 182, 212, 0.4);">
                        <i class="bi bi-speedometer2 me-2"></i>Go to Dashboard
                    </a>
                    <a href="#live-threats-container" class="btn btn-outline-light px-4 py-3 rounded-pill fw-bold" style="background: rgba(255,255,255,0.05); backdrop-filter: blur(10px);">
                        Live Intel Feed
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right 3D Globe MVP -->
        <div class="col-lg-6 mt-5 mt-lg-0 z-0 position-relative d-flex justify-content-center" data-aos="fade-left" data-aos-duration="1200">
            <!-- Pulsing orb underneath globe for extra effect -->
            <div class="position-absolute top-50 start-50 translate-middle rounded-circle" style="width: 400px; height: 400px; background: radial-gradient(circle, rgba(6, 182, 212, 0.2) 0%, transparent 70%); filter: blur(30px); z-index: -1;"></div>
            <div id="globeViz" style="width: 100%; max-width: 600px; height: 500px; cursor: move;"></div>
        </div>
    </div>
</div>

<!-- DATA SECTION -->
<div class="container section mb-5 pb-5" data-aos="fade-up" data-aos-delay="200">
    <h2 class="text-center mb-5 fw-bold text-white">Recent Intel<span class="text-info">.</span></h2>

    <div class="table-wrapper">
        <div class="table-responsive">
            <table class="table table-custom text-center">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Threat Designation</th>
                        <th>Vector Type</th>
                        <th>Severity Level</th>
                        <th>Origin Location</th>
                    </tr>
                </thead>
                <tbody>

                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="text-muted">#<?= $row['id'] ?></td>
                        <td class="fw-500 text-white"><?= $row['title'] ?></td>
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
                    </tr>
                <?php endwhile; ?>

                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- LIVE THREAT FEEDS -->
<div class="container section mb-5 pb-5">
    <h2 class="text-center mb-5 fw-bold text-white" data-aos="fade-down">Live Global Intel Feed<span class="text-danger">.</span></h2>
    
    <div class="row g-4" id="live-threats-container">
        <!-- JS will populate these -->
        <div class="col-12 text-center text-muted py-5" id="loading-threats">
            <div class="spinner-border text-info" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Connecting to global intel networks...</p>
        </div>
    </div>
</div>

<!-- FOOTER -->
<footer class="text-center mt-auto">
    <p class="mb-0">© 2026 CyberDash Intelligence Systems. All rights secured.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
  AOS.init({
    duration: 1000,
    once: true,
  });

  // Fetch Live Threats Function
  async function fetchLiveThreats() {
    const container = document.getElementById('live-threats-container');
    const loading = document.getElementById('loading-threats');
    
    try {
        const response = await fetch('https://api.rss2json.com/v1/api.json?rss_url=https://feeds.feedburner.com/TheHackersNews');
        const data = await response.json();
        
        if (data.status === 'ok') {
            loading.style.display = 'none';
            const items = data.items.slice(0, 3);
            
            items.forEach((item, index) => {
                const dateObj = new Date(item.pubDate);
                const dateStr = dateObj.toLocaleDateString() + ' ' + dateObj.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                
                let shortDesc = item.description.replace(/<[^>]*>?/gm, ''); 
                if (shortDesc.length > 120) shortDesc = shortDesc.substring(0, 120) + '...';
                
                const delay = index * 150;
                
                const cardHTML = `
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="${delay}">
                        <div class="card card-custom p-4 h-100 d-flex flex-column text-start" style="border: 1px solid rgba(239, 68, 68, 0.2);">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill px-3 py-1 d-flex align-items-center" style="gap: 5px;"><span class="spinner-grow spinner-grow-sm" style="width: 0.6rem; height: 0.6rem;"></span> LIVE ALERT</span>
                                <small class="text-muted"><i class="bi bi-clock me-1"></i>${dateStr}</small>
                            </div>
                            <h5 class="fw-bold mb-3 text-white">${item.title}</h5>
                            <p class="text-muted flex-grow-1" style="font-size: 0.9rem;">${shortDesc}</p>
                            <a href="${item.link}" target="_blank" class="btn btn-outline-danger btn-sm mt-3 fw-bold w-100" style="transition: 0.3s; background: rgba(239, 68, 68, 0.05);">
                                View Intel Report <i class="bi bi-arrow-up-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                `;
                container.innerHTML += cardHTML;
            });
        }
    } catch (e) {
        loading.innerHTML = '<p class="text-danger">Failed to establish safe connection with global intel networks.</p>';
    }
  }

  // Execute on page load
  document.addEventListener('DOMContentLoaded', fetchLiveThreats);
</script>
<!-- Globe.gl Dependencies -->
<script src="//unpkg.com/three"></script>
<script src="//unpkg.com/globe.gl"></script>

<script>
  // Initialize Cyber Globe
  document.addEventListener('DOMContentLoaded', () => {
    const vizContainer = document.getElementById('globeViz');
    if(vizContainer) {
        const globeWidth = vizContainer.clientWidth || 500;
        const globeHeight = vizContainer.clientHeight || 500;

        const world = Globe()
          (vizContainer)
          .width(globeWidth)
          .height(globeHeight)
          .globeImageUrl('//unpkg.com/three-globe/example/img/earth-night.jpg')
          .bumpImageUrl('//unpkg.com/three-globe/example/img/earth-topology.png')
          .backgroundColor('rgba(0,0,0,0)') // Transparent bg
          .atmosphereColor('#06b6d4')
          .atmosphereAltitude(0.25);
          
        world.controls().autoRotate = true;
        world.controls().autoRotateSpeed = 1.2;
        world.controls().enableZoom = false;

        // Generate Random Threat Vector Arcs
        const N = 25;
        const arcsData = [...Array(N).keys()].map(() => ({
          startLat: (Math.random() - 0.5) * 180,
          startLng: (Math.random() - 0.5) * 360,
          endLat: (Math.random() - 0.5) * 180,
          endLng: (Math.random() - 0.5) * 360,
          color: [['#ef4444', '#f43f5e', '#06b6d4', '#10b981'][Math.floor(Math.random() * 4)], 'rgba(255,255,255,0)']
        }));

        world.arcsData(arcsData)
          .arcColor('color')
          .arcDashLength(() => 0.5 + Math.random())
          .arcDashGap(() => 0.5 + Math.random())
          .arcDashAnimateTime(() => Math.random() * 4000 + 1000);
          
        // Resize listener
        window.addEventListener('resize', () => {
            world.width(vizContainer.clientWidth);
        });
    }
  });
</script>

</body>
</html>
```