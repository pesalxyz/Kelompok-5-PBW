<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "rent_car_baru";
$dbname = "rent_car_baru";
$username = "root";
$password = "";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>