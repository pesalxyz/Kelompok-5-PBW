<?php
include '../../../koneksi.php';

$query = "
SELECT 
    o.order_id,
    o.vehicle_id,
    v.vehicle_name AS nama_mobil,
    u.nama AS nama_user,
    o.rental_start AS tanggal_mulai,
    o.rental_end AS tanggal_selesai,
    o.status AS status_order,
    
    p.payment_id,
    p.transaction_id,
    p.payment_proof,
    p.amount,
    p.payment_method,
    p.status AS status_pembayaran,
    p.verified_by,
    p.verified_at,
    p.created_at AS tanggal_pembayaran,
    p.updated_at AS pembayaran_diperbarui
FROM orders o
JOIN vehicles v ON o.vehicle_id = v.vehicle_id
JOIN users u ON o.user_id = u.id
LEFT JOIN payments p ON o.order_id = p.order_id
ORDER BY o.rental_start DESC;
";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Kelola Pemesanan - RentEase Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f9fafb;
        }

        .navbar {
            background-color: #dc2626; /* Merah */
            color: white;
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        .navbar a:hover {
            text-decoration: underline;
        }

        .container {
            padding: 32px;
        }

        .title {
            font-size: 28px;
            margin-bottom: 20px;
            color: #111827;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        th,
        td {
            border: 1px solid #e5e7eb;
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #f3f4f6;
            color: #111827;
        }

        td[class^="status-"] {
            font-weight: bold;
        }

        .status-pending {
            color: orange;
        }

        .status-disewa {
            color: green;
        }

        .status-dikembalikan {
            color: blue;
        }

        .status-dibatalkan {
            color: red;
        }

        .btn-status {
            padding: 6px 12px;
            margin: 2px;
            font-size: 14px;
            font-weight: 600;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-blue {
            background-color: #3b82f6;
        }

        .btn-blue:hover {
            background-color: #2563eb;
        }

        .btn-red {
            background-color: #ef4444;
        }

        .btn-red:hover {
            background-color: #dc2626;
        }

        .btn-green {
            background-color: #10b981;
        }

        .btn-green:hover {
            background-color: #059669;
        }

        .btn-orange {
            background-color: #f97316;
        }

        .btn-orange:hover {
            background-color: #ea580c;
        }

        .flex {
            display: flex;
            gap: 6px;
            justify-content: center;
            flex-wrap: wrap;
        }
    </style>
</head>

<body>
    <header class="navbar">
        <div class="logo">RentEase</div>
        <nav>
            <a href="../dashboard.php">Dashboard</a>
        </nav>
    </header>

    <main class="container">
        <h1 class="title">Kelola Pemesanan</h1>

        <table>
            <thead>
                <tr>
                    <th>ID Pesanan</th>
                    <th>Nama Mobil</th>
                    <th>Customer</th>
                    <th>Tanggal Mulai</th>
                    <th>Tanggal Selesai</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $row['order_id'] ?></td>
                        <td><?= $row['nama_mobil'] ?></td>
                        <td><?= $row['nama_user'] ?></td>
                        <td><?= $row['tanggal_mulai'] ?></td>
                        <td><?= $row['tanggal_selesai'] ?></td>
                        <td class="status-<?= strtolower($row['status_order']) ?>">
                            <?= ucfirst($row['status_order']) ?>
                        </td>
                        <td>
                            <div class="flex">
                                <?php
                                $orderId = htmlspecialchars($row['order_id']);
                                $status = $row['status_order'];

                                if ($status === 'pending'): ?>
                                    <a href="ubah_status.php?id=<?= $orderId ?>&status=disewa" class="btn-status btn-green">Konfirmasi</a>
                                <?php elseif ($status === 'disewa'): ?>
                                    <a href="ubah_status.php?id=<?= $orderId ?>&status=dikembalikan" class="btn-status btn-blue">Selesai</a>
                                <?php endif; ?>

                                <?php if ($status !== 'dibatalkan' && $status !== 'dikembalikan'): ?>
                                    <a href="ubah_status.php?id=<?= $orderId ?>&status=dibatalkan" class="btn-status btn-orange">Batalkan</a>
                                <?php endif; ?>

                                <a href="hapus_order.php?id=<?= $orderId ?>" class="btn-status btn-red"
                                    onclick="return confirm('Yakin ingin menghapus order ini?');">Hapus</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>
</body>

</html>
