<?php
include '../../../koneksi.php';

$id = $_GET['id'];
// Hapus gambar dulu (optional)
$get = mysqli_fetch_assoc(mysqli_query($conn, "SELECT image FROM vehicles WHERE vehicle_id = $id"));
if ($get['image'] && file_exists("../../uploads/" . $get['image'])) {
    unlink("../../uploads/" . $get['image']);
}

// Hapus dari database
mysqli_query($conn, "DELETE FROM vehicles WHERE vehicle_id = $id");
header("Location: index.php");
?>
