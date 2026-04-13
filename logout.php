<?php
session_start();

// If confirmed → destroy session
if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    session_destroy();
    header("Location: login.php"); // 🔁 Better redirect
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Logout</title>

<script>
function confirmLogout() {
    let confirmAction = confirm("Are you sure you want to logout?");

    if (confirmAction) {
        window.location.href = "logout.php?confirm=yes";
    } else {
        window.location.href = "dashboard.php"; // 🔁 Better UX
    }
}
</script>

</head>

<body onload="confirmLogout()">
</body>
</html>