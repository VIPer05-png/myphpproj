<?php
session_start();

// 🔐 RBAC: Only Admin Allowed
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    
    require_once '../includes/db.php';

    $stmt = $conn->prepare("UPDATE threats SET status='approved', coordinates_verified=1 WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['toast_msg'] = "Threat verification successful. It is now active on the dashboard.";
        $_SESSION['toast_type'] = "success";
    } else {
        $_SESSION['toast_msg'] = "Error verifying threat.";
        $_SESSION['toast_type'] = "danger";
    }

    $stmt->close();
    $conn->close();
}

header("Location: ../dashboard.php");
exit();
?>
