<?php
include '../../koneksi.php';

// Ambil total kendaraan
$jml_mobil = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(vehicle_id) AS total FROM vehicles"))['total'];

// Ambil total customer
$jml_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) AS total FROM users"))['total'];

// Ambil total pemesanan
$jml_order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(order_id) AS total FROM orders"))['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - RentEase</title>
    <link rel="stylesheet" href="../../assets/css/admin-dashboard.css">
</head>
<body>
   <header class="navbar">
    <div class="logo">RentEase</div>
    <nav>
        <ul>
            <li><a href="../explore.php">Explore Cars</a></li>
            <li><a href="#">Dashboard</a></li>
        </ul>
    </nav>
    <div class="admin-box">Admin</div>
</header>


    <main class="container">
        <h1 class="title">Welcome to Admin Dashboard</h1>
        <div class="stats">
            <div class="card">
                <h3>Total Mobil</h3>
                <p><?= $jml_mobil ?></p>
            </div>
            <div class="card">
                <h3>Total Customer</h3>
                <p><?= $jml_user ?></p>
            </div>
            <div class="card">
                <h3>Total Pemesanan</h3>
                <p><?= $jml_order ?></p>
            </div>
        </div>

        <div class="actions">
            <a href="mobil/index.php" class="btn">Kelola Mobil</a>
            <a href="pemesanan/index.php" class="btn">Kelola Pemesanan</a>
            <a href="customer/index.php" class="btn">Kelola Customer</a>
        </div>
    </main>
</body>
</html>
