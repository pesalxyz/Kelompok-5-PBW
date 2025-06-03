<?php
// Start session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

// Check if booking data exists
if (!isset($_SESSION['booking_data'])) {
    // Debug: Check what's in session
    error_log("Session data: " . print_r($_SESSION, true));
    error_log("No booking data found, redirecting to order.php");

    // Add error message to session
    $_SESSION['error_message'] = "Data pemesanan tidak ditemukan. Silakan ulangi proses pemesanan.";
    header("Location: order.php");
    exit;
}

require_once __DIR__ . "/../koneksi.php";


$booking_data = $_SESSION['booking_data'];


if ($_POST && isset($_POST['confirm_payment'])) {
    $transaction_id = trim($_POST['transaction_id']);
    error_log("Payment form submitted with transaction_id: " . $transaction_id);

    if (empty($transaction_id)) {
        $error_message = "ID Transaksi wajib diisi.";
        error_log("Error: Empty transaction ID");
    } else {
        try {
            // Default value for payment proof
            $payment_proof_filename = 'whatsapp_submission';

            // Jika ada file diupload
            if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['payment_proof'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

                if (in_array($ext, $allowed_ext)) {
                    $upload_dir = __DIR__ . '/../uploads/';
                    if (!is_dir($upload_dir))
                        mkdir($upload_dir, 0777, true);

                    $payment_proof_filename = uniqid('proof_') . '.' . $ext;
                    $upload_path = $upload_dir . $payment_proof_filename;

                    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                        error_log("Upload gagal: gagal memindahkan file.");
                        $payment_proof_filename = 'whatsapp_submission'; // fallback
                    }
                } else {
                    error_log("Upload gagal: ekstensi tidak diizinkan.");
                }
            }


            $stmt = $conn->prepare("UPDATE orders SET payment_status = 'pending_verification', payment_method = 'QRIS', updated_at = NOW() WHERE order_id = ?");
            $update_result = $stmt->execute([$booking_data['booking_id']]);
            error_log("Order update result: " . ($update_result ? 'success' : 'failed'));

            try {
                $stmt = $conn->prepare("INSERT INTO payments (order_id, transaction_id, payment_proof, amount, payment_method, status, created_at) VALUES (?, ?, ?, ?, 'QRIS', 'pending_verification', NOW())");
                $payment_result = $stmt->execute([
                    $booking_data['booking_id'],
                    $transaction_id,
                    $payment_proof_filename,
                    $booking_data['total_price']
                ]);
                error_log("Payment insert result: " . ($payment_result ? 'success' : 'failed'));
            } catch (Exception $e) {
                error_log("Payment table insert failed: " . $e->getMessage());
            }

            $_SESSION['whatsapp_data'] = [
                'booking_id' => $booking_data['booking_id'],
                'transaction_id' => $transaction_id,
                'car_name' => $booking_data['car_name'],
                'total_price' => $booking_data['total_price'],
                'customer_name' => $booking_data['customer_name'],
                'payment_reference' => "RENT" . str_pad($booking_data['booking_id'], 6, "0", STR_PAD_LEFT)
            ];

            unset($_SESSION['booking_data']);
            $_SESSION['success_message'] = "Data pembayaran tersimpan! Silakan lanjutkan ke WhatsApp.";

            header("Location: payment_whatsapp.php");
            exit();
        } catch (Exception $e) {
            $error_message = "Error database: " . $e->getMessage();
            error_log("Database error: " . $e->getMessage());
        }
    }
}


// Generate payment reference
$payment_reference = "RENT" . str_pad($booking_data['booking_id'], 6, "0", STR_PAD_LEFT);
$payment_deadline = date('Y-m-d H:i:s', strtotime('+24 hours'));
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - RentEase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .payment-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 1rem;
        }

        .qr-code {
            max-width: 250px;
            height: 250px;
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
        }

        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }

        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 15px;
            right: -50%;
            width: 100%;
            height: 2px;
            background: #e5e7eb;
            z-index: -1;
        }

        .step.active .step-number {
            background: #dc2626;
            color: white;
        }

        .step.completed .step-number {
            background: #10b981;
            color: white;
        }

        .step.completed:not(:last-child)::after {
            background: #10b981;
        }

        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e5e7eb;
            color: #6b7280;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            font-weight: bold;
        }

        .payment-timer {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-left: 4px solid #dc2626;
        }

        .qris-info {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border-left: 4px solid #3b82f6;
        }
    </style>
</head>

<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-red-600 text-white py-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                    <span class="text-red-600 font-bold text-xl">R</span>
                </div>
                <span class="text-2xl font-bold">RentEase</span>
            </div>

            <div class="flex items-center space-x-4">
                <a href="profile.php" class="bg-white text-red-600 rounded-lg px-4 py-2 font-medium hover:bg-gray-100">
                    Profil
                </a>
            </div>
        </div>
    </header>

    <!-- Step Indicator -->
    <div class="bg-white py-6 shadow-sm">
        <div class="container mx-auto">
            <div class="step-indicator max-w-md mx-auto">
                <div class="step completed">
                    <div class="step-number">1</div>
                    <span class="text-sm">Pemesanan</span>
                </div>
                <div class="step active">
                    <div class="step-number">2</div>
                    <span class="text-sm">Pembayaran</span>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <span class="text-sm">Konfirmasi</span>
                </div>
            </div>
        </div>
    </div>

    <main class="payment-container py-8">
        <!-- Payment Timer -->
        <div class="payment-timer p-4 rounded-lg mb-6">
            <div class="flex items-center">
                <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h3 class="text-lg font-bold text-red-800">Selesaikan Pembayaran Dalam</h3>
                    <p class="text-red-600" id="countdown">23:59:59</p>
                    <p class="text-sm text-red-600">Batas waktu:
                        <?php echo date('d M Y H:i', strtotime($payment_deadline)); ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Payment Method -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <svg class="w-6 h-6 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                        </path>
                    </svg>
                    Pembayaran QRIS
                </h2>

                <!-- QR Code Section -->
                <div class="text-center mb-6">
                    <div class="qr-code mb-4">
                        <img src="/assets/qr.jpg" alt="QR Code" class="w-full h-full object-contain">
                    </div>
                    <p class="text-sm text-gray-600 mb-2">Scan QR Code dengan aplikasi pembayaran digital Anda</p>
                    <p class="text-lg font-bold text-red-600">Rp
                        <?php echo number_format($booking_data['total_price'], 0, ',', '.'); ?>
                    </p>
                </div>

                <!-- Payment Instructions -->
                <div class="qris-info p-4 rounded-lg mb-6">
                    <h4 class="font-bold text-blue-800 mb-3">Cara Pembayaran:</h4>
                    <ol class="list-decimal list-inside space-y-2 text-sm text-blue-700">
                        <li>Buka aplikasi e-wallet atau mobile banking Anda</li>
                        <li>Pilih menu "Scan QR" atau "Bayar dengan QR"</li>
                        <li>Arahkan kamera ke QR Code di atas</li>
                        <li>Periksa detail pembayaran dan konfirmasi</li>
                        <li>Setelah berhasil, upload bukti pembayaran di bawah</li>
                    </ol>
                </div>

                <!-- Payment Confirmation Form -->
                <?php if (isset($error_message)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4" enctype="multipart/form-data">
                    <div>
                        <label for="transaction_id" class="block text-gray-700 font-medium mb-2">ID Transaksi / Nomor
                            Referensi *</label>
                        <input type="text" id="transaction_id" name="transaction_id"
                            placeholder="Masukkan ID transaksi dari aplikasi pembayaran"
                            class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500"
                            required>
                        <p class="text-xs text-gray-500 mt-1">ID transaksi biasanya berupa kombinasi angka/huruf yang
                            muncul setelah pembayaran berhasil</p>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="font-bold text-blue-800 mb-2 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Langkah Selanjutnya
                        </h4>
                        <ol class="list-decimal list-inside text-sm text-blue-700 space-y-1">
                            <li>Klik tombol "LANJUTKAN KE WHATSAPP" di bawah</li>
                            <li>Anda akan diarahkan ke chat WhatsApp admin</li>
                            <li>Kirim screenshot bukti pembayaran ke admin</li>
                            <li>Admin akan memverifikasi pembayaran Anda</li>
                        </ol>
                    </div>



                    <button type="submit" name="confirm_payment"
                        class="w-full bg-red-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-red-700 transition duration-300 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                        LANJUTKAN KE WHATSAPP
                    </button>
                </form>

                <!-- Alternative Payment Info -->
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <h4 class="font-bold text-gray-800 mb-2">Metode Pembayaran yang Didukung:</h4>
                    <div class="grid grid-cols-3 gap-2 text-sm text-gray-600">
                        <div class="text-center">• GoPay</div>
                        <div class="text-center">• OVO</div>
                        <div class="text-center">• DANA</div>
                        <div class="text-center">• ShopeePay</div>
                        <div class="text-center">• LinkAja</div>
                        <div class="text-center">• Bank Digital</div>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                    <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                        </path>
                    </svg>
                    Ringkasan Pesanan
                </h3>

                <!-- Booking Details -->
                <div class="space-y-4 mb-6">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Nomor Pesanan</span>
                        <span class="font-semibold"><?php echo $payment_reference; ?></span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-600">Kendaraan</span>
                        <span class="font-semibold"><?php echo $booking_data['car_name']; ?></span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-600">Durasi Sewa</span>
                        <span class="font-semibold"><?php echo $booking_data['rental_days']; ?> hari</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-600">Tanggal Ambil</span>
                        <span
                            class="font-semibold"><?php echo date('d M Y', strtotime($booking_data['pickup_date'])); ?></span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-600">Tanggal Kembali</span>
                        <span
                            class="font-semibold"><?php echo date('d M Y', strtotime($booking_data['return_date'])); ?></span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-600">Lokasi Ambil</span>
                        <span
                            class="font-semibold text-right text-sm"><?php echo $booking_data['pickup_location']; ?></span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-600">Lokasi Kembali</span>
                        <span
                            class="font-semibold text-right text-sm"><?php echo $booking_data['return_location']; ?></span>
                    </div>
                </div>

                <!-- Customer Details -->
                <div class="border-t pt-4 mb-6">
                    <h4 class="font-semibold text-gray-700 mb-3">Detail Pemesan</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Nama</span>
                            <span><?php echo $booking_data['customer_name']; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Email</span>
                            <span><?php echo $booking_data['customer_email']; ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Telepon</span>
                            <span><?php echo $booking_data['customer_phone']; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Price Breakdown -->
                <div class="border-t pt-4">
                    <div class="flex justify-between text-lg font-bold text-red-600">
                        <span>Total Pembayaran</span>
                        <span>Rp <?php echo number_format($booking_data['total_price'], 0, ',', '.'); ?></span>
                    </div>
                </div>

                <!-- Payment Security -->
                <div class="mt-6 p-4 bg-green-50 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-green-600 mr-2 mt-0.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                            </path>
                        </svg>
                        <div>
                            <h4 class="font-semibold text-green-800">Pembayaran Aman</h4>
                            <p class="text-sm text-green-700">Transaksi Anda dilindungi dengan enkripsi SSL dan sistem
                                keamanan berlapis</p>
                        </div>
                    </div>
                </div>

                <!-- Help Section -->
                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <h4 class="font-semibold text-blue-800 mb-2">Butuh Bantuan?</h4>
                    <div class="space-y-1 text-sm text-blue-700">
                        <p>📞 Customer Service: 0800-1234-5678</p>
                        <p>💬 WhatsApp: +62 812-3456-7890</p>
                        <p>📧 Email: support@rentease.com</p>
                        <p class="text-xs text-blue-600 mt-2">Layanan 24/7 untuk membantu Anda</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Important Notes -->
        <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <h4 class="font-bold text-yellow-800 mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z">
                    </path>
                </svg>
                Penting untuk Diperhatikan
            </h4>
            <ul class="list-disc list-inside space-y-1 text-sm text-yellow-700">
                <li>Selesaikan pembayaran dalam waktu yang ditentukan untuk menghindari pembatalan otomatis</li>
                <li>Pastikan nominal pembayaran sesuai dengan total yang tertera</li>
                <li>Simpan bukti pembayaran dan ID transaksi untuk referensi</li>
                <li>Verifikasi pembayaran akan dilakukan dalam 1x24 jam setelah konfirmasi</li>
                <li>Hubungi customer service jika mengalami kendala dalam pembayaran</li>
            </ul>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="container mx-auto text-center">
            <div class="flex items-center justify-center space-x-3 mb-4">
                <div class="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold">R</span>
                </div>
                <span class="text-xl font-bold">RentEase</span>
            </div>
            <p class="text-gray-400 mb-4">Solusi terpercaya untuk kebutuhan sewa mobil Anda</p>
            <p class="text-gray-500">&copy; 2024 RentEase. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Countdown timer
        function startCountdown() {
            const deadline = new Date('<?php echo $payment_deadline; ?>').getTime();

            const timer = setInterval(function () {
                const now = new Date().getTime();
                const timeLeft = deadline - now;

                if (timeLeft > 0) {
                    const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

                    document.getElementById('countdown').textContent =
                        String(hours).padStart(2, '0') + ':' +
                        String(minutes).padStart(2, '0') + ':' +
                        String(seconds).padStart(2, '0');
                } else {
                    clearInterval(timer);
                    document.getElementById('countdown').textContent = 'EXPIRED';
                    document.getElementById('countdown').style.color = '#dc2626';

                    // Disable form
                    const form = document.querySelector('form');
                    const inputs = form.querySelectorAll('input, button');
                    inputs.forEach(input => input.disabled = true);

                    // Show expiry message
                    const expiredDiv = document.createElement('div');
                    expiredDiv.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mt-4';
                    expiredDiv.textContent = 'Waktu pembayaran telah habis. Silakan buat pesanan baru.';
                    form.parentNode.insertBefore(expiredDiv, form);
                }
            }, 1000);
        }

        // File upload validation
        document.getElementById('payment_proof').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const maxSize = 5 * 1024 * 1024; // 5MB
                if (file.size > maxSize) {
                    alert('Ukuran file terlalu besar. Maksimal 5MB.');
                    e.target.value = '';
                    return;
                }

                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Format file tidak didukung. Gunakan JPG, PNG, atau PDF.');
                    e.target.value = '';
                    return;
                }
            }
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function (e) {
            const transactionId = document.getElementById('transaction_id').value.trim();
            const paymentProof = document.getElementById('payment_proof').files[0];

            if (!transactionId) {
                alert('Silakan masukkan ID transaksi.');
                e.preventDefault();
                return;
            }

            if (!paymentProof) {
                alert('Silakan upload bukti pembayaran.');
                e.preventDefault();
                return;
            }

            // Show loading
            const submitBtn = e.target.querySelector('button[type="submit"]');
            submitBtn.textContent = 'MEMPROSES...';
            submitBtn.disabled = true;
        });

        // Start countdown when page loads
        startCountdown();

        // Auto-refresh QR code every 5 minutes (if needed)
        setInterval(function () {
            // You can implement QR code refresh logic here if needed
        }, 300000);
    </script>


    <script>
        document.getElementById('paymentProof').addEventListener('change', function () {
            if (this.files.length > 0) {
                document.getElementById('uploadForm').submit();
            }
        });
    </script>


</body>

</html>