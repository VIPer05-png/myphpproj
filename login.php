<?php
session_start();

$conn = new mysqli("localhost", "root", "", "cyber_dashboard");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            $_SESSION['user'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header("Location: dashboard.php");
            exit();

        } else {
            $error = "Invalid password!";
        }

    } else {
        $error = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<title>Login - The Cyberhut</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<!-- Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<link href="style.css" rel="stylesheet">
<script src="theme.js"></script>
<script src="https://accounts.google.com/gsi/client" async defer></script>

</head>

<body>

<!-- ANIMATED BACKGROUND -->
<div class="bg-animated">
    <div class="bg-orb-1"></div>
    <div class="bg-orb-2"></div>
</div>

<div class="login-container">
    <div class="card login-card p-4">

        <div class="text-center mb-3">
            <img src="logo.png" alt="Cyberhut Logo" style="width: 60px; height: 60px; filter: drop-shadow(0 0 15px rgba(6,182,212,0.5)); border-radius: 12px;">
        </div>
        <h3 class="text-center text-white mb-4 fw-bold">Login to <span style="background: linear-gradient(90deg, #06b6d4, #3b82f6); background-clip: text; -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Cyberhut</span></h3>

        <?php if($error): ?>
            <div class="alert text-center">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- 🔥 autocomplete OFF fixes default values -->
        <form method="POST" autocomplete="off">

            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Enter username" required autocomplete="off">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Enter password" required autocomplete="new-password">
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2">Sign In</button>

            <div class="d-flex align-items-center my-4">
                <hr class="flex-grow-1 border-secondary opacity-50">
                <span class="mx-3 text-muted fw-bold" style="font-size: 0.85rem;">OR LOGIN VIA GOOGLE</span>
                <hr class="flex-grow-1 border-secondary opacity-50">
            </div>

            <!-- Google GSI Initialization -->
            <div id="g_id_onload"
                 data-client_id="YOUR_GOOGLE_CLIENT_ID"
                 data-context="signin"
                 data-ux_mode="popup"
                 data-login_uri="http://localhost/php-practice/cyberNew/google_auth.php"
                 data-auto_prompt="false">
            </div>

            <!-- Render Google Sign-in Button -->
            <div class="g_id_signin"
                 data-type="standard"
                 data-shape="rectangular"
                 data-theme="filled_black"
                 data-text="signin_with"
                 data-size="large"
                 data-logo_alignment="left"
                 style="display: flex; justify-content: center;">
            </div>

        </form>

        <p class="text-center text-white mt-4 mb-2">
            Don't have an account? 
            <a href="register.php">Register here</a>
        </p>

        <div class="text-center mt-2">
            <a href="index.php" class="text-muted"><i class="bi bi-arrow-left me-1"></i>Back to Home</a>
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
</script>
</body>
</html>
