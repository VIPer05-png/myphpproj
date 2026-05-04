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

    $stmt = $conn->prepare("DELETE FROM threats WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['toast_msg'] = "Threat submission rejected and removed.";
        $_SESSION['toast_type'] = "warning";
    } else {
        $_SESSION['toast_msg'] = "Error rejecting threat.";
        $_SESSION['toast_type'] = "danger";
    }

    $stmt->close();
    $conn->close();
}

header("Location: ../dashboard.php");
exit();
?>
