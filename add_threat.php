<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "cyber_dashboard");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $type = $_POST['type'];
    $severity = $_POST['severity'];
    $location = $_POST['location'];

    $stmt = $conn->prepare("INSERT INTO threats(title,type,severity,location) VALUES(?,?,?,?)");
    $stmt->bind_param("ssss", $title, $type, $severity, $location);

    if ($stmt->execute()) {
        header("Location: dashboard.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Threat</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: linear-gradient(135deg, #1e3c72, #2a5298); color:white; }
.card { border-radius:15px; }
</style>
</head>

<body>

<div class="container mt-5">
<div class="card p-4 shadow">

<h3 class="mb-4 text-center">Add New Threat</h3>

<form method="POST">

<div class="mb-3">
<label>Title</label>
<input type="text" name="title" class="form-control" required>
</div>

<div class="mb-3">
<label>Type</label>
<input type="text" name="type" class="form-control" required>
</div>

<div class="mb-3">
<label>Severity</label>
<select name="severity" class="form-select">
<option>Low</option>
<option>Medium</option>
<option>High</option>
</select>
</div>

<div class="mb-3">
<label>Location</label>
<input type="text" name="location" class="form-control">
</div>

<button class="btn btn-success w-100">Add Threat</button>

</form>

</div>
</div>

</body>
</html>