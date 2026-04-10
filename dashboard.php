<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "cyber_dashboard");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch threats
$result = $conn->query("SELECT * FROM threats ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<title>Dashboard</title>
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
    color: white;
}

/* Table container */
.table-container {
    background: white;
    color: black;
    border-radius: 10px;
    padding: 20px;
}

/* Navbar */
.navbar {
    background: rgba(0,0,0,0.85);
}
</style>

</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark px-4">
    <a class="navbar-brand fw-bold" href="index.php">Cyber Dashboard</a>

    <div class="ms-auto">
        <span class="text-warning me-3">Welcome, <?php echo $_SESSION['user']; ?></span>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
</nav>

<!-- MAIN -->
<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Threat Dashboard</h2>
        <a href="add_threat.php" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Add Threat
        </a>
    </div>

    <div class="table-container shadow">

        <div class="table-responsive">
            <table class="table table-bordered table-hover text-center">

                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Severity</th>
                        <th>Location</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= $row['title'] ?></td>
                            <td><?= $row['type'] ?></td>

                            <td>
                                <?php
                                if ($row['severity'] == 'High') {
                                    echo "<span class='badge bg-danger'>High</span>";
                                } elseif ($row['severity'] == 'Medium') {
                                    echo "<span class='badge bg-warning text-dark'>Medium</span>";
                                } else {
                                    echo "<span class='badge bg-success'>Low</span>";
                                }
                                ?>
                            </td>

                            <td><?= $row['location'] ?></td>
                            <td><?= $row['created_at'] ?></td>

                            <td>
                                <a href="edit_threat.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <a href="delete_threat.php?id=<?= $row['id'] ?>" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Are you sure?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No threats found</td>
                    </tr>
                <?php endif; ?>

                </tbody>

            </table>
        </div>

    </div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>