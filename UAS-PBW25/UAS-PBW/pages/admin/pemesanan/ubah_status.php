<?php
include __DIR__ . '/../../../koneksi.php';

$order_id = $_GET['id'];
$status_baru = $_GET['status'];

$allowed_status = ['disewa', 'dikembalikan', 'dibatalkan'];

if (!in_array($status_baru, $allowed_status)) {
    echo "Status tidak valid.";
    exit;
}

// Mulai transaction supaya aman
mysqli_begin_transaction($conn);

try {
    $query_order = "UPDATE orders SET status=? WHERE order_id=?";
    $stmt_order = mysqli_prepare($conn, $query_order);
    mysqli_stmt_bind_param($stmt_order, "si", $status_baru, $order_id);
    mysqli_stmt_execute($stmt_order);

    // Update status payments sesuai rules
    if ($status_baru === 'disewa') {
        $status_payment = 'verified';
    } elseif ($status_baru === 'dibatalkan') {
        $status_payment = 'rejected';
    } else {
        $status_payment = null;
    }

    if ($status_payment !== null) {
        $query_payment = "UPDATE payments SET status=? WHERE order_id=?";
        $stmt_payment = mysqli_prepare($conn, $query_payment);
        mysqli_stmt_bind_param($stmt_payment, "si", $status_payment, $order_id);
        mysqli_stmt_execute($stmt_payment);
    }

    mysqli_commit($conn);

    header('Location: index.php');
    exit();

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo "Gagal mengubah status: " . $e->getMessage();
}
