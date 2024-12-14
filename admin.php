<?php
// Admin authentication check
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #20232a;
            color: #ffffff;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #282c34;
            padding-top: 20px;
        }
        .sidebar a {
            display: block;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: #61dafb;
            color: black;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .navbar {
            background-color: #61dafb;
            color: white;
        }
        .navbar a {
            color: white;
        }
        .card {
            background-color: #282c34;
            color: white;
        }
        .card a {
            color: #61dafb;
            text-decoration: none;
        }
        .card a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
   <?php include 'sidebar.php';?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container-fluid">
                <span class="navbar-brand">Welcome, Admin</span>
                <div class="d-flex">
                    <a href="#" class="me-3">View Site</a>
                    <a href="#" class="me-3">Change Password</a>
                    <a href="logout.php" class="btn btn-danger btn-sm">Log Out</a>
                </div>
            </div>
        </nav>

        <!-- Dashboard Content -->
        <div class="mt-4">
            <h2>Recent Actions</h2>
            <div class="card p-3">

            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
