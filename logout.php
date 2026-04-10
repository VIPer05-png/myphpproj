<?php
session_start();
if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    session_destroy(); 
    header("Location: index.php"); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    <script type="text/javascript">
        function confirmLogout() {
            var confirmAction = confirm("Are you sure you want to logout?");
            if (confirmAction) {
                window.location.href = "logout.php?confirm=yes";
            } else {
                window.location.href = "index.php"; // or any other page
            }
        }
    </script>
</head>
<body onload="confirmLogout()">
</body>
</html>
