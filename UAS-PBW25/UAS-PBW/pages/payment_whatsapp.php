<?php
// Start session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

// Check if WhatsApp data exists
if (!isset($_SESSION['whatsapp_data'])) {
    header("Location: order.php");
    exit;
}

$whatsapp_data = $_SESSION['whatsapp_data'];

$admin_whatsapp = "6285782607447"; // Format: 62 + nomor tanpa 0 di depan

// Prepare WhatsApp message
$message = "🚗 *KONFIRMASI PEMBAYARAN RENTEASE*\n\n";
$message .= "📋 *Detail Pesanan:*\n";
$message .= "• Nomor Pesanan: " . $whatsapp_data['payment_reference'] . "\n";
$message .= "• Kendaraan: " . $whatsapp_data['car_name'] . "\n";
$message .= "• Total Pembayaran: Rp " . number_format($whatsapp_data['total_price'], 0, ',', '.') . "\n";
$message .= "• Nama Pemesan: " . $whatsapp_data['customer_name'] . "\n\n";
$message .= "💳 *Informasi Pembayaran:*\n";
$message .= "• ID Transaksi: " . $whatsapp_data['transaction_id'] . "\n";
$message .= "• Metode: QRIS\n\n";
$message .= "📎 *Mohon kirim bukti pembayaran (screenshot) untuk verifikasi.*\n\n";
$message .= "Terima kasih! 🙏";

$encoded_message = urlencode($message);
$whatsapp_url = "https://wa.me/{$admin_whatsapp}?text={$encoded_message}";
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi WhatsApp - RentEase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .whatsapp-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 1rem;
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
            background: #10b981;
            z-index: -1;
        }

        .step.completed .step-number {
            background: #10b981;
            color: white;
        }

        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #10b981;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            font-weight: bold;
        }

        .whatsapp-btn {
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            transition: all 0.3s ease;
        }

        .whatsapp-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37, 211, 102, 0.3);
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        .message-preview {
            background: #f0f2f5;
            border-radius: 10px;
            padding: 15px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            position: relative;
        }

        .message-preview::before {
            content: '';
            position: absolute;
            top: 0;
            left: -10px;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 10px 10px 0 0;
            border-color: #f0f2f5 transparent transparent transparent;
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
                <a href="beranda.php" class="bg-white text-red-600 rounded-lg px-4 py-2 font-medium hover:bg-gray-100">
                    Beranda
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
                <div class="step completed">
                    <div class="step-number">2</div>
                    <span class="text-sm">Pembayaran</span>
                </div>
                <div class="step completed">
                    <div class="step-number">3</div>
                    <span class="text-sm">Konfirmasi</span>
                </div>
            </div>
        </div>
    </div>

    <main class="whatsapp-container py-8">
        <!-- WhatsApp Confirmation -->
        <div class="bg-white p-8 rounded-lg shadow text-center mb-8">
            <div class="pulse-animation mb-6">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488" />
                    </svg>
                </div>
            </div>

            <h1 class="text-3xl font-bold text-gray-800 mb-4">Lanjutkan ke WhatsApp</h1>
            <p class="text-gray-600 mb-6">Data pembayaran Anda telah tersimpan. Sekarang kirim bukti pembayaran ke admin
                via WhatsApp untuk verifikasi.</p>

            <!-- Order Summary -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
                <h3 class="font-bold text-gray-800 mb-3">📋 Ringkasan Pesanan</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span>Nomor Pesanan:</span>
                        <span class="font-semibold"><?php echo $whatsapp_data['payment_reference']; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Kendaraan:</span>
                        <span class="font-semibold"><?php echo $whatsapp_data['car_name']; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>ID Transaksi:</span>
                        <span class="font-semibold"><?php echo $whatsapp_data['transaction_id']; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Total:</span>
                        <span class="font-bold text-red-600">Rp
                            <?php echo number_format($whatsapp_data['total_price'], 0, ',', '.'); ?></span>
                    </div>
                </div>
            </div>

            <!-- WhatsApp Message Preview -->
            <div class="text-left mb-6">
                <h4 class="font-bold text-gray-700 mb-3">📱 Pesan yang akan dikirim:</h4>
                <div class="message-preview text-sm">
                    <?php echo nl2br(htmlspecialchars($message)); ?>
                </div>
            </div>

            <!-- WhatsApp Button -->
            <a href="<?php echo $whatsapp_url; ?>" target="_blank"
                class="whatsapp-btn inline-flex items-center px-8 py-4 rounded-lg text-white font-semibold text-lg mb-4">
                <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488" />
                </svg>
                BUKA WHATSAPP ADMIN
            </a>

            <p class="text-sm text-gray-500 mb-6">Atau salin nomor admin:
                <strong><?php echo $admin_whatsapp; ?></strong>
            </p>

            <!-- Instructions -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-left">
                <h4 class="font-bold text-blue-800 mb-2">📝 Instruksi:</h4>
                <ol class="list-decimal list-inside text-sm text-blue-700 space-y-1">
                    <li>Klik tombol "BUKA WHATSAPP ADMIN" di atas</li>
                    <li>Pesan otomatis akan terbuka di WhatsApp</li>
                    <li>Kirim pesan tersebut ke admin</li>
                    <li>Lampirkan screenshot bukti pembayaran</li>
                    <li>Tunggu konfirmasi dari admin (maks. 24 jam)</li>
                </ol>
            </div>

            <div class="mt-6">
                <a href="beranda.php"
                    class="bg-gray-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-700 transition duration-300">
                    Kembali ke Beranda
                </a>
            </div>
        </div>

        <!-- Contact Info -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                    </path>
                </svg>
                Informasi Kontak
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <h4 class="font-semibold text-gray-700 mb-2">WhatsApp Admin:</h4>
                    <p class="text-sm text-gray-600">+<?php echo $admin_whatsapp; ?></p>
                    <p class="text-xs text-gray-500">Jam operasional: 08:00 - 22:00</p>
                </div>

                <div>
                    <h4 class="font-semibold text-gray-700 mb-2">Customer Service:</h4>
                    <p class="text-sm text-gray-600">0800-1234-5678</p>
                    <p class="text-xs text-gray-500">Layanan 24/7</p>
                </div>
            </div>

            <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-sm text-yellow-700">
                    <strong>Penting:</strong> Pastikan Anda mengirim bukti pembayaran yang jelas dan sesuai dengan ID
                    transaksi yang telah dimasukkan.
                </p>
            </div>
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
        // Auto-clear WhatsApp data after 30 minutes to prevent reuse
        setTimeout(function () {
            fetch('clear_whatsapp_session.php', { method: 'POST' });
        }, 30 * 60 * 1000); // 30 minutes

        // Track WhatsApp button click
        document.querySelector('.whatsapp-btn').addEventListener('click', function () {
            // You can add analytics tracking here
            console.log('WhatsApp button clicked');

            // Optional: Show success message after a delay
            setTimeout(function () {
                const successDiv = document.createElement('div');
                successDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                successDiv.textContent = 'WhatsApp terbuka! Jangan lupa kirim bukti pembayaran.';
                document.body.appendChild(successDiv);

                setTimeout(function () {
                    successDiv.remove();
                }, 5000);
            }, 2000);
        });

        // Copy admin number functionality
        function copyAdminNumber() {
            const adminNumber = '<?php echo $admin_whatsapp; ?>';
            navigator.clipboard.writeText(adminNumber).then(function () {
                alert('Nomor admin berhasil disalin!');
            });
        }

        // Add copy functionality to admin number
        const adminNumberText = document.querySelector('strong');
        if (adminNumberText) {
            adminNumberText.style.cursor = 'pointer';
            adminNumberText.title = 'Klik untuk menyalin nomor';
            adminNumberText.addEventListener('click', copyAdminNumber);
        }
    </script>
</body>

</html>

<?php
// Clear WhatsApp data after displaying (optional security measure)
// Uncomment the line below if you want to clear data immediately after display
// unset($_SESSION['whatsapp_data']);
?>