<?php
session_start();
require_once __DIR__ . '/../../../koneksi.php';

if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "ID order tidak ditemukan.";
    header("Location: index.php"); // Ganti dengan halaman daftar order kamu
    exit;
}

$order_id = $_GET['id'];

// Validasi sederhana: pastikan $order_id adalah angka
if (!ctype_digit($order_id)) {
    $_SESSION['error_message'] = "ID order tidak valid.";
    header("Location: index.php");
    exit;
}

try {
    // Hapus data order berdasarkan order_id
    $stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
    $stmt->execute([$order_id]);

    // (Optional) Hapus juga data terkait di payments, jika ada
    $stmt2 = $conn->prepare("DELETE FROM payments WHERE order_id = ?");
    $stmt2->execute([$order_id]);

    $_SESSION['success_message'] = "Order berhasil dihapus.";
} catch (Exception $e) {
    $_SESSION['error_message'] = "Gagal menghapus order: " . $e->getMessage();
}

header("Location: index.php");
exit;
