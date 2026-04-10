<?php
session_start();

$conn = new mysqli("localhost", "root", "", "cyber_dashboard");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

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

            $stmt = $conn->prepare("INSERT INTO users(username,password,email) VALUES(?,?,?)");
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
<title>Register - Cyber Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">

<!-- Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #1e3c72, #2a5298);
}

/* Same container as login */
.auth-container {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* Same card as login */
.auth-card {
    width: 100%;
    max-width: 400px;
    border-radius: 15px;
    backdrop-filter: blur(10px);
}

/* Same button style */
.btn-custom {
    border-radius: 8px;
    padding: 10px;
    font-weight: 500;
}
</style>

</head>

<body>

<div class="auth-container">
    <div class="card shadow-lg auth-card p-4">

        <h3 class="text-center mb-4">Register</h3>

        <?php if($message): ?>
            <div class="alert alert-danger text-center">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Enter username" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="Enter email" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>
            </div>

            <!-- SAME BUTTON STYLE AS LOGIN -->
            <button type="submit" class="btn btn-primary w-100 btn-custom">Register</button>

        </form>

        <p class="text-center mt-3">
            Already have an account? 
            <a href="login.php">Login</a>
        </p>

        <div class="text-center mt-2">
            <a href="index.php" class="text-muted">← Back to Home</a>
        </div>

    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>