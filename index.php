<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Cyber Threat Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">

<!-- Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>

body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #1e3c72, #2a5298);
    color: #fff;
}

/* Navbar */
.navbar {
    background: rgba(0,0,0,0.85);
}

/* Hero */
.hero {
    padding: 100px 20px;
    text-align: center;
}

.hero h1 {
    font-weight: 600;
    text-shadow: 2px 2px 10px rgba(0,0,0,0.6);
}

.hero p {
    color: #ddd;
}

/* Buttons */
.btn-custom {
    padding: 12px 25px;
    font-weight: 500;
}

/* Cards */
.card-custom {
    background: rgba(255,255,255,0.15);
    border: none;
    backdrop-filter: blur(5px);
    transition: 0.3s;
}

.card-custom:hover {
    transform: translateY(-5px);
    box-shadow: 0 0 20px rgba(255,255,255,0.3);
}

/* Section spacing */
.section {
    padding: 60px 0;
}

/* Table preview */
.table-custom {
    background: white;
    color: black;
    border-radius: 10px;
    overflow: hidden;
}

/* Footer */
footer {
    background: #000;
    padding: 20px;
    text-align: center;
}

</style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark px-4">
    <a class="navbar-brand fw-bold" href="#">Cyber Dashboard</a>

    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#menu">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="menu">
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>

            <?php if(isset($_SESSION['user'])): ?>
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="add_threat.php">Add Threat</a></li>
                <li class="nav-item"><a class="nav-link" href="analytics.php">Analytics</a></li>
                <li class="nav-item"><a class="nav-link text-warning">Hi, <?php echo $_SESSION['user']; ?></a></li>
                <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
            <?php else: ?>
                <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<!-- HERO -->
<div class="hero">
    <h1 class="display-4">National Cyber Threat Dashboard</h1>
    <p class="lead">Monitor, analyze and manage cyber threats efficiently</p>

    <?php if(!isset($_SESSION['user'])): ?>
        <a href="login.php" class="btn btn-primary btn-custom m-2">Get Started</a>
        <a href="register.php" class="btn btn-light btn-custom m-2">Create Account</a>
    <?php else: ?>
        <a href="dashboard.php" class="btn btn-success btn-custom m-2">Go to Dashboard</a>
    <?php endif; ?>
</div>

<!-- FEATURES -->
<div class="container section">
    <div class="row text-center g-4">

        <div class="col-md-4">
            <div class="card card-custom p-4">
                <i class="bi bi-shield-lock fs-1"></i>
                <h4 class="mt-3">Threat Monitoring</h4>
                <p>Track cyber threats across multiple regions.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-custom p-4">
                <i class="bi bi-bar-chart fs-1"></i>
                <h4 class="mt-3">Analytics</h4>
                <p>Visualize threat severity and patterns.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-custom p-4">
                <i class="bi bi-person-check fs-1"></i>
                <h4 class="mt-3">Secure Access</h4>
                <p>Authentication-based access system.</p>
            </div>
        </div>

    </div>
</div>

<!-- DATA SECTION (IMPORTANT FOR YOU) -->
<div class="container section">
    <h3 class="text-center mb-4">Recent Threat Data (Preview)</h3>

    <div class="table-responsive">
        <table class="table table-custom table-bordered text-center">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Severity</th>
                    <th>Location</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>Phishing Attack</td>
                    <td>Email</td>
                    <td class="text-danger fw-bold">High</td>
                    <td>Delhi</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Malware</td>
                    <td>Trojan</td>
                    <td class="text-warning fw-bold">Medium</td>
                    <td>Mumbai</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ACTION SECTION -->
<div class="container text-center section">
    <h3>Quick Actions</h3>

    <a href="add_threat.php" class="btn btn-outline-light m-2">Add Threat</a>
    <a href="dashboard.php" class="btn btn-outline-light m-2">View Dashboard</a>
    <a href="analytics.php" class="btn btn-outline-light m-2">View Analytics</a>
</div>

<!-- FOOTER -->
<footer>
    <p>© 2026 Cyber Threat Dashboard | MCA Project</p>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>