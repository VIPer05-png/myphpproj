<?php
session_start();

// 🔐 RBAC: Only Admin can edit
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "cyber_dashboard");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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

    $stmt = $conn->prepare("UPDATE threats SET title=?, type=?, severity=?, location=?, description=? WHERE id=?");
    $stmt->bind_param("sssssi", $title, $type, $severity, $location, $description, $id);

    if ($stmt->execute()) {
        $_SESSION['toast_msg'] = "Threat parameters successfully updated.";
        $_SESSION['toast_type'] = "info";
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
                        <input type="text" name="location" value="<?= htmlspecialchars($threat['location']) ?>" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Threat Description</label>
                <textarea name="description" class="form-control" placeholder="Add detailed description of the threat..." rows="4" style="resize: vertical; background: var(--bg-input); border: 1px solid var(--c-primary); border-radius: 8px; color: #e5e7eb;"><?= htmlspecialchars($threat['description'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 mt-2">
                <i class="bi bi-save me-1"></i> Update Intel Profile
            </button>
            <a href="dashboard.php" class="btn btn-outline-secondary text-white border-secondary w-100 py-2 mt-3">Cancel</a>

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
