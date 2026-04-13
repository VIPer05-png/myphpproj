<?php
session_start();

// Enforce Role
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied: You must be an administrator to generate exports.");
}

$conn = new mysqli("localhost", "root", "", "cyber_dashboard");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}// Set Headers for CSV force-download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="Cyberhut_Threat_Intel_' . date('Y-m-d') . '.csv"');
header('Cache-Control: no-cache, no-store, must-revalidate');

$output = fopen('php://output', 'w');

// Header Row
fputcsv($output, array('Threat ID', 'Title', 'Attack Vector Type', 'Severity', 'Target Location', 'Description', 'Logged Datetime'));

// Fetch all rows
$result = $conn->query("SELECT id, title, type, severity, location, description, created_at FROM threats ORDER BY created_at DESC");

while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>
