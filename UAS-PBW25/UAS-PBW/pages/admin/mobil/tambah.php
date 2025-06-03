<?php
include '../../../koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['vehicle_name'];
    $tipe = $_POST['vehicle_type'];
    $harga = $_POST['price_per_day'];
    $status = $_POST['status'];

    $gambar = $_FILES['image']['name'];
    $tmp = $_FILES['image']['tmp_name'];
    $path = "../uploads/" . $gambar;

    if (move_uploaded_file($tmp, $path)) {
        $sql = "INSERT INTO vehicles (vehicle_name, vehicle_type, price_per_day, status, image)
                VALUES ('$nama', '$tipe', '$harga', '$status', '$gambar')";
        mysqli_query($conn, $sql);
        header("Location: index.php");
        exit;
    } else {
        echo "Upload gambar gagal!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Mobil</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 30px;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        form {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        input[type="text"],
        input[type="number"],
        select,
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-bottom: 20px;
            font-size: 14px;
        }

        button {
            background-color: #d32f2f;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: #b71c1c;
        }

        a.back-link {
            display: inline-block;
            text-align: center;
            margin-top: 20px;
            color: #d32f2f;
            text-decoration: none;
            font-weight: 500;
        }

        a.back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<h2>Tambah Mobil</h2>
<form action="" method="post" enctype="multipart/form-data">
    <label>Nama Mobil:</label>
    <input type="text" name="vehicle_name" required>

    <label>Tipe:</label>
    <select name="vehicle_type" required>
        <option value="car">Car</option>
        <option value="motorcycle">Motorcycle</option>
        <option value="truck">Truck</option>
    </select>

    <label>Harga per Hari:</label>
    <input type="number" name="price_per_day" required>

    <label>Status:</label>
    <select name="status" required>
        <option value="available">Available</option>
        <option value="rented">Rented</option>
    </select>

    <label>Gambar:</label>
    <input type="file" name="image" accept="image/*" required>

    <button type="submit">Tambah</button>
</form>

<a class="back-link" href="index.php">← Kembali ke daftar mobil</a>

</body>
</html>
