<?php
session_start();

// 🔐 RBAC: Only Admin Allowed
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "cyber_dashboard");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $type = $_POST['type'];
    $severity = $_POST['severity'];
    $location = $_POST['location'];
    $description = $_POST['description'] ?? '';

    $stmt = $conn->prepare("INSERT INTO threats(title,type,severity,location,description) VALUES(?,?,?,?,?)");
    $stmt->bind_param("sssss", $title, $type, $severity, $location, $description);

    if ($stmt->execute()) {
        $_SESSION['toast_msg'] = "Threat initialized and logged successfully.";
        $_SESSION['toast_type'] = "success";
        header("Location: dashboard.php");
        exit();
    }
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
            <input type="text" name="location" class="form-control" placeholder="e.g., Datacenter 4">
        </div>

        <div class="mb-4">
            <label class="mb-1"><i class="bi bi-file-text me-1 text-info"></i>Threat Description</label>
            <textarea name="description" class="form-control" placeholder="Add detailed description of the threat..." rows="4" style="resize: vertical; background: var(--bg-input); border: 1px solid var(--c-primary); border-radius: 8px; color: #e5e7eb;"></textarea>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2">Add Threat to Intel</button>

        </form>

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
</script>
</body>
</html>
