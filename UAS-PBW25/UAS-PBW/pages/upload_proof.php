<?php
require_once __DIR__ . "/../koneksi.php";
header('Content-Type: application/json');

// Validasi metode request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Validasi input
if (!isset($_POST['payment_id']) || !isset($_FILES['payment_proof'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'payment_id dan payment_proof wajib diisi']);
    exit;
}

$payment_id = $_POST['payment_id'];
$file = $_FILES['payment_proof'];

// Validasi upload file
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Upload gagal']);
    exit;
}

// Validasi ekstensi
$allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowed_extensions)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Format file tidak valid']);
    exit;
}

$upload_dir = __DIR__ . '/../uploads';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$new_filename = uniqid('proof_') . '.' . $ext;
$upload_path = $upload_dir . $new_filename;

if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan file']);
    exit;
}

$stmt = $conn->prepare("UPDATE payments SET payment_proof = ?, updated_at = NOW() WHERE payment_id = ?");
$stmt->bind_param("si", $new_filename, $payment_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Bukti pembayaran berhasil diperbarui', 'file' => $new_filename]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal update database']);
}
