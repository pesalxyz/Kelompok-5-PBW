<?php
include '../../../koneksi.php';

$limit = 5;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(vehicle_id) AS total FROM vehicles"))['total'];
$total_pages = ceil($total_data / $limit);
$vehicles = mysqli_query($conn, "SELECT * FROM vehicles ORDER BY vehicle_id ASC LIMIT $limit OFFSET $offset");

$jml_mobil = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(vehicle_id) AS total FROM vehicles"))['total'];
$jml_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(id) AS total FROM users"))['total'];
$jml_order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(order_id) AS total FROM orders"))['total'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Mobil - RentEase</title>
    <link rel="stylesheet" href="../../../assets/css/mobile-index.css">
</head>

<body>
    <!-- Navbar -->
    <header class="navbar">
        <div class="container">
            <a href="index.php" class="logo">RentEase</a>
            <nav>
                <ul>
                    <li><a href="../../explore.php">Explore Cars</a></li>
                    <li><a href="../dashboard.php">Dashboard</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <h1 class="title">Data Mobil</h1>

            <div class="table-section">
                <div class="table-header">
                    <a href="./tambah.php" class="btn-add">+ Tambah Mobil</a>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Gambar</th>
                            <th>Nama Kendaraan</th>
                            <th>Tipe</th>
                            <th>Harga/Hari</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; ?>
                        <?php if (mysqli_num_rows($vehicles) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($vehicles)): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <img src="../uploads/<?= $row['image'] ?>" width="80" height="60" alt="<?= $row['vehicle_name'] ?>">
                                    </td>
                                    <td><?= $row['vehicle_name'] ?></td>
                                    <td><?= $row['vehicle_type'] ?></td>
                                    <td>Rp<?= number_format($row['price_per_day'], 0, ',', '.') ?></td>
                                    <td>
                                        <span class="status-badge <?= $row['status'] == 'available' ? 'status-available' : 'status-rented' ?>">
                                            <?= ucfirst($row['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="./update.php?id=<?= $row['vehicle_id'] ?>" class="btn-edit">Edit</a>
                                            <a href="./hapus.php?id=<?= $row['vehicle_id'] ?>" onclick="return confirm('Yakin ingin menghapus kendaraan ini?')" class="btn-delete">Hapus</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: #666;">
                                    Belum ada data kendaraan
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>

</html>
