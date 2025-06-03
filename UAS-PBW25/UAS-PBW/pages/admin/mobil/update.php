<?php
include '../../../koneksi.php';

$id = $_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM vehicles WHERE vehicle_id = '$id'");
$data = mysqli_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['vehicle_name'];
    $tipe = $_POST['vehicle_type'];
    $harga = $_POST['price_per_day'];
    $status = $_POST['status'];

    if ($_FILES['image']['name'] != '') {
        $gambar = $_FILES['image']['name'];
        $tmp = $_FILES['image']['tmp_name'];
        $path = "../uploads/" . $gambar;

        if (move_uploaded_file($tmp, $path)) {
            $sql = "UPDATE vehicles SET vehicle_name = '$nama', vehicle_type = '$tipe', price_per_day = '$harga', status = '$status', image = '$gambar' WHERE vehicle_id = '$id'";
        } else {
            echo "Upload gambar gagal!";
            exit;
        }
    } else {
        $sql = "UPDATE vehicles SET vehicle_name = '$nama', vehicle_type = '$tipe', price_per_day = '$harga', status = '$status' WHERE vehicle_id = '$id'";
    }

    if (mysqli_query($conn, $sql)) {
        header("Location: index.php");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Update Mobil</title>
    <link rel="stylesheet" href="../../../assets/css/mobile-update.css">
</head>

<body>
    <header class="navbar">
        <div class="container">
            <a href="../../../index.php" class="logo">RentEase</a>
            <nav>
                <ul>
                    <li><a href="../../explore.php">Explore Cars</a></li>
                    <li><a href="../dashboard.php">Dashboard</a></li>
                    <li><a href="../../logout.php" class="btn-logout">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">

            <div class="form-header">
                <h2>Update Mobil</h2>
            </div>
            <div class="form-section">
                <div class="form-body">
                    <form action="" method="post" enctype="multipart/form-data">

                        <div class="form-group">
                            <label>Nama Kendaraan:</label>
                            <input type="text" name="vehicle_name" value="<?= $data['vehicle_name'] ?>" required>
                        </div>

                        <div class="form-gropup">
                            <label>Tipe:</label>
                            <select name="vehicle_type" required>
                                <option value="car" <?= $data['vehicle_type'] == 'car' ? 'selected' : '' ?>>Car</option>
                                <option value="motorcycle" <?= $data['vehicle_type'] == 'motorcycle' ? 'selected' : '' ?>>
                                    Motorcycle</option>
                                <option value="truck" <?= $data['vehicle_type'] == 'truck' ? 'selected' : '' ?>>Truck
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Harga per Hari:</label>
                            <input type="number" name="price_per_day" value="<?= $data['price_per_day'] ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Status:</label>
                            <select name="status" required>
                                <option value="available" <?= $data['status'] == 'available' ? 'selected' : '' ?>>Available
                                </option>
                                <option value="rented" <?= $data['status'] == 'rented' ? 'selected' : '' ?>>Rented</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Gambar Saat Ini:</label>
                            <div class="current-image">
                                <img src="../uploads/<?= $data['image'] ?>" width="150" alt="Current Image">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Ganti Gambar (Opsional):</label>
                            <div class="image-info">Biarkan kosong jika tidak ingin mengubah gambar</div>
                            <input type="file" name="image" accept="image/*">
                        </div>
                        <button type="submit" class="btn-submit">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </main>



    <a class="back-link" href="index.php">← Kembali ke daftar mobil</a>

</body>

</html>