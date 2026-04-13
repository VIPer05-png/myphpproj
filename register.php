<?php
session_start();

$conn = new mysqli("localhost", "root", "", "cyber_dashboard");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);

    if (!empty($username) && !empty($password) && !empty($email)) {

        $check = $conn->prepare("SELECT id FROM users WHERE username=?");
        $check->bind_param("s", $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "Username already exists!";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users(username,password,email,role) VALUES(?,?,?,'user')");
            $stmt->bind_param("sss", $username, $hashed, $email);

            if ($stmt->execute()) {
                $_SESSION['registered'] = true;
                header("Location: login.php");
                exit();
            } else {
                $message = "Registration failed!";
            }
        }

    } else {
        $message = "All fields are required!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<title>Register - The Cyberhut</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<!-- Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<link href="style.css" rel="stylesheet">
<script src="theme.js"></script>

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
        <h3 class="text-center text-white mb-4 fw-bold">Join <span style="background: linear-gradient(90deg, #06b6d4, #3b82f6); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Cyberhut</span></h3>

        <?php if($message): ?>
            <div class="alert text-center">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">

            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Choose a username" required autocomplete="off">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="Enter email" required autocomplete="off">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Create a password" required autocomplete="new-password">
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2">Initialize Account</button>

        </form>

        <p class="text-center text-white mt-4 mb-2">
            Already have an account? 
            <a href="login.php">Sign In here</a>
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
