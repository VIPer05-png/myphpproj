<?php
session_start();

// 🔐 RBAC: Only Admin can delete
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header("Location: dashboard.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "cyber_dashboard");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 🔐 Validate ID
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM threats WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['toast_msg'] = "Threat record successfully eliminated.";
        $_SESSION['toast_type'] = "danger";
    }
}

// Redirect back
header("Location: dashboard.php");
exit();
?>