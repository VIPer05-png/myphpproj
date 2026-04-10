<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "cyber_dashboard");

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

    $stmt = $conn->prepare("UPDATE threats SET title=?, type=?, severity=?, location=? WHERE id=?");
    $stmt->bind_param("ssssi", $title, $type, $severity, $location, $id);

    if ($stmt->execute()) {
        header("Location: dashboard.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Threat</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: linear-gradient(135deg, #1e3c72, #2a5298); color:white; }
.card { border-radius:15px; }
</style>
</head>

<body>

<div class="container mt-5">
<div class="card p-4 shadow">

<h3 class="mb-4 text-center">Edit Threat</h3>

<form method="POST">

<div class="mb-3">
<label>Title</label>
<input type="text" name="title" value="<?= $threat['title'] ?>" class="form-control">
</div>

<div class="mb-3">
<label>Type</label>
<input type="text" name="type" value="<?= $threat['type'] ?>" class="form-control">
</div>

<div class="mb-3">
<label>Severity</label>
<select name="severity" class="form-select">
<option <?= $threat['severity']=='Low'?'selected':'' ?>>Low</option>
<option <?= $threat['severity']=='Medium'?'selected':'' ?>>Medium</option>
<option <?= $threat['severity']=='High'?'selected':'' ?>>High</option>
</select>
</div>

<div class="mb-3">
<label>Location</label>
<input type="text" name="location" value="<?= $threat['location'] ?>" class="form-control">
</div>

<button class="btn btn-primary w-100">Update Threat</button>

</form>

</div>
</div>

</body>
</html>