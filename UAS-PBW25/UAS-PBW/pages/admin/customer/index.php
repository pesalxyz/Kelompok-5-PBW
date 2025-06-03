<?php
include '../../../koneksi.php';

// Ambil customer yang punya order dengan status 'disewa' atau 'dikembalikan'
$query = "SELECT u.nama AS nama_customer, u.email, v.vehicle_name AS nama_mobil, o.status
          FROM orders o
          JOIN users u ON o.user_id = u.id
          JOIN vehicles v ON o.vehicle_id = v.vehicle_id
          WHERE o.status IN ('disewa', 'dikembalikan')
          ORDER BY u.nama";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kelola Data Customer - RentEase Admin</title>
    <link rel="stylesheet" href="../../assets/css/admin-dashboard.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background-color: #dc2626;
            color: white;
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar .logo {
            font-size: 20px;
            font-weight: bold;
        }

        .navbar ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            gap: 20px;
        }

        .navbar a {
            color: #ffffff;
            text-decoration: none;
            font-weight: 500;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            background-color: white;
            padding: 32px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .title {
            font-size: 28px;
            margin-bottom: 24px;
            color: #1f2937;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        th, td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background-color: #f9fafb;
            color: #374151;
            font-size: 14px;
            font-weight: 600;
        }

        td {
            font-size: 14px;
            color: #4b5563;
        }

        .status-disewa {
            color: #10b981; /* green */
            font-weight: 600;
        }

        .status-dikembalikan {
            color: #3b82f6; /* blue */
            font-weight: 600;
        }

        tr:hover {
            background-color: #f3f4f6;
        }
    </style>
</head>
<body>
    <header class="navbar">
        <div class="logo">RentEase</div>
        <nav>
            <ul>
                <li><a href="../dashboard.php">Dashboard</a></li>
            </ul>
        </nav>
    </header>

    <main class="container">
        <h1 class="title">Kelola Data Customer</h1>

        <table>
            <thead>
                <tr>
                    <th>Nama Customer</th>
                    <th>Email</th>
                    <th>Mobil Disewa</th>
                    <th>Status Sewa</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nama_customer']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['nama_mobil']) ?></td>
                        <td class="status-<?= strtolower($row['status']) ?>">
                            <?= ucfirst($row['status']) ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
