<?php
session_start();

if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}


require_once __DIR__ . '/../koneksi.php';

$mobilId = $_GET['id'];


if (!isset($mobilId)) {
    header('Location: explore.php');
}


$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId); // "i" untuk integer
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();


if (!$user) {
    session_destroy();
    header("Location: ../login.php"); // lebih tepat ke login
    exit;
}

$car_id = $mobilId;

if ($_POST) {

    // Ambil car_id lebih awal
    $car_id = isset($_GET['id']) ? (int) $_GET['id'] : 1;

    // Ambil data kendaraan
    $cars = [];
    $query = "SELECT vehicle_id, vehicle_name, vehicle_type, price_per_day, status, created_at, image FROM vehicles";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $cars[$row['vehicle_id']] = $row;
        }
    }

    $selected_car = $cars[$car_id] ?? reset($cars); // fallback ke pertama jika tidak ditemukan

    // Hitung lama sewa
    $pickup_date = new DateTime($_POST['pickup_date']);
    $return_date = new DateTime($_POST['return_date']);
    $rental_days = $pickup_date->diff($return_date)->days;
    if ($rental_days == 0)
        $rental_days = 1;

    // Harga dasar
    $total_price = $selected_car['price_per_day'] * $rental_days;

    // Layanan tambahan
    $additional_services = $_POST['additional_services'] ?? [];
    foreach ($additional_services as $service) {
        switch ($service) {
            case 'additional_driver':
                $total_price += 200000;
                break;
            case 'child_seat':
                $total_price += 50000 * $rental_days;
                break;
            case 'gps':
                $total_price += 25000 * $rental_days;
                break;
            case 'full_insurance':
                $total_price += 100000 * $rental_days;
                break;
        }
    }

    // Biaya lokasi
    if (strpos($_POST['pickup_location'], 'airport') !== false)
        $total_price += 50000;
    if ($_POST['pickup_location'] === 'custom')
        $total_price += 100000;
    if (strpos($_POST['return_location'], 'airport') !== false)
        $total_price += 50000;
    if ($_POST['return_location'] === 'custom')
        $total_price += 100000;

    // Promo code discount
    $promo_discount = 0;
    if (isset($_POST['promo_code']) && $_POST['promo_code'] === 'RENTFIRST15') {
        $promo_discount = $total_price * 0.15; // 15% discount
        $total_price -= $promo_discount;
    }

    // Gabungkan waktu
    $rental_start = $_POST['pickup_date'] . ' ' . $_POST['pickup_time'];
    $rental_end = $_POST['return_date'] . ' ' . $_POST['return_time'];

    // Siapkan insert
    $stmt = $conn->prepare("INSERT INTO orders (
        user_id, vehicle_id, package_id, rental_start, rental_end, total_price,
        status, payment_status, pickup_location, return_location, special_requests
    ) VALUES (?, ?, ?, ?, ?, ?, 'pending', 'unpaid', ?, ?, ?)");

    $package_id = 1; // default
    $user_id = $_SESSION['user_id'] ?? 0;

    $stmt->bind_param(
        "iiissdsss",
        $user_id,
        $car_id,
        $package_id,
        $rental_start,
        $rental_end,
        $total_price,
        $_POST['pickup_location'],
        $_POST['return_location'],
        $_POST['notes']
    );

    if ($stmt->execute()) {
        $booking_id = $conn->insert_id;

        $_SESSION['booking_data'] = [
            'booking_id' => $booking_id,
            'car_name' => $selected_car['vehicle_name'],
            'total_price' => $total_price,
            'rental_days' => $rental_days,
            'pickup_date' => $_POST['pickup_date'],
            'return_date' => $_POST['return_date'],
            'pickup_location' => $_POST['pickup_location'],
            'return_location' => $_POST['return_location'],
            'customer_name' => $_POST['fullname'],
            'customer_email' => $_POST['email'],
            'customer_phone' => $_POST['phone']
        ];

        header('Location: payment.php');
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Misalnya $car_id didapat dari URL: ?car_id=2
$cars = [];
$query = "SELECT vehicle_id, vehicle_name, vehicle_type, price_per_day, status, created_at, image FROM vehicles";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Masukkan data ke array dengan key = vehicle_id
        $cars[$row['vehicle_id']] = $row;
    }
}

// Misalnya $car_id didapat dari URL: ?car_id=2
$car_id = isset($_GET['id']) ? (int) $_GET['id'] : 1;

$selected_car = $cars[$car_id] ?? $cars[1];




// Calculate minimum dates
$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemesanan - RentEase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .breadcrumb-custom {
            padding: 1rem;
            background-color: #f9fafb;
            margin-bottom: 1rem;
        }

        .order-container {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 1.5rem;
            padding: 1rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .order-summary {
            position: sticky;
            top: 100px;
            height: fit-content;
        }

        @media (max-width: 768px) {
            .order-container {
                grid-template-columns: 1fr;
            }

            .billing-details {
                padding: 1rem;
            }
        }

        .service-item {
            transition: all 0.3s ease;
            padding: 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            margin-bottom: 0.5rem;
        }

        .service-item:hover {
            background-color: #f9fafb;
        }

        .service-item input:checked+.service-content {
            background-color: #fef2f2;
            border-color: #dc2626;
        }

        .car-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 0.5rem;
        }

        .promo-section {
            background: linear-gradient(135deg, #fef2f2 0%, #fff5f5 100%);
            border: 2px dashed #dc2626;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .promo-input {
            position: relative;
        }

        .promo-success {
            background-color: #dcfce7;
            border-color: #16a34a;
            color: #15803d;
        }

        .promo-error {
            background-color: #fef2f2;
            border-color: #dc2626;
            color: #dc2626;
        }

        .promo-btn {
            transition: all 0.3s ease;
        }

        .promo-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(220, 38, 38, 0.2);
        }
    </style>
</head>

<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-red-600 text-white py-4 shadow-md sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <!-- Logo -->
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                    <span class="text-red-600 font-bold text-xl">R</span>
                </div>
                <span class="text-2xl font-bold">RentEase</span>
            </div>

            <!-- Navbar -->
            <nav class="hidden md:flex space-x-8">
                <a href="beranda.php"
                    class="text-red-100 font-medium hover:text-white transition duration-300">Beranda</a>
                <a href="explore.php" class="text-red-100 font-medium hover:text-white transition duration-300">Explore Cars</a>
                <a href="about_us.html"
                    class="text-red-100 font-medium hover:text-white transition duration-300">About Us</a>
            </nav>

            <!-- Notification and Profile -->
            <div class="flex items-center space-x-4">
                <!-- Notification -->
                <div class="relative">
                    <button id="notificationBtn"
                        class="p-2 hover:bg-red-700 rounded-full transition duration-300 relative">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                            </path>
                        </svg>
                        <span class="absolute top-0 right-0 w-2 h-2 bg-yellow-400 rounded-full"></span>
                    </button>

                    <!-- Notification Dropdown -->
                    <div id="notificationDropdown"
                        class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg z-50">
                        <div class="p-4 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800">Notifikasi</h3>
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition duration-300">
                                <p class="text-sm font-medium text-gray-900">Selesaikan pemesanan Anda untuk mendapatkan
                                    konfirmasi.</p>
                                <p class="text-xs text-gray-500 mt-1">Baru saja</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Button -->
                <a href="profile.php" class="inline-block">
                    <button
                        class="bg-white text-red-600 rounded-lg px-6 py-2 flex items-center font-medium transition duration-300 hover:bg-gray-100">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Profile
                    </button>
                </a>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <div class="breadcrumb-custom">
        <div class="container mx-auto">
            <nav class="text-sm text-gray-600">
                <a href="beranda.php" class="hover:text-red-600">Beranda</a>
                <span class="mx-2">></span>
                <a href="explore.php" class="hover:text-red-600">Jelajahi Mobil</a>
                <span class="mx-2">></span>
                <strong class="text-gray-900">Pemesanan</strong>
            </nav>
        </div>
    </div>

    <?php if (isset($error_message)): ?>
        <div class="container mx-auto mb-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <?php echo $error_message; ?>
            </div>
        </div>
    <?php endif; ?>

    <main class="order-container">
        <!-- Billing Details -->
        <section class="billing-details bg-white p-6 rounded-lg shadow">
            <div class="flex items-center mb-6">
                <svg class="w-6 h-6 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                <h3 class="text-xl font-bold text-gray-800">Informasi Pemesanan</h3>
            </div>

            <form method="POST" action="" class="space-y-6" id="bookingForm">
                <input type="hidden" name="car_id" value="<?php echo $car_id; ?>">

                <!-- Informasi Pribadi -->
                <div class="border-b pb-6">
                    <h4 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                        <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Informasi Pribadi
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="fullname" class="block text-gray-600 mb-2 font-medium">Nama Lengkap *</label>
                            <input value="<?= $user['nama']; ?>" type="text" id="fullname" name="fullname"
                                placeholder="Masukkan nama lengkap Anda"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-200"
                                required>
                        </div>

                        <div>
                            <label for="email" class="block text-gray-600 mb-2 font-medium">Email *</label>
                            <input value="<?= $user['email']; ?>" type="email" id="email" name="email"
                                placeholder="contoh@email.com"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-200"
                                required>
                        </div>

                        <div>
                            <label for="phone" class="block text-gray-600 mb-2 font-medium">Nomor Telepon *</label>
                            <input value="<?= $user['no_hp']; ?>" type="tel" id="phone" name="phone"
                                placeholder="081234567890"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-200"
                                required>
                        </div>

                        <div>
                            <label for="address" class="block text-gray-600 mb-2 font-medium">Alamat</label>
                            <textarea id="address" name="address" rows="3" placeholder="Masukkan alamat lengkap Anda"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-200"><?= $user['alamat']; ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Informasi Peminjaman -->
                <div class="border-b pb-6">
                    <h4 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                        <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3a4 4 0 118 0v4m-4 6v6m-6-8h12a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6a2 2 0 012-2z">
                            </path>
                        </svg>
                        Detail Peminjaman
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="pickup_date" class="block text-gray-600 mb-2 font-medium">Tanggal Pengambilan
                                *</label>
                            <input type="date" id="pickup_date" name="pickup_date" min="<?php echo $today; ?>"
                                value="<?php echo $today; ?>"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-200"
                                required>
                        </div>

                        <div>
                            <label for="pickup_time" class="block text-gray-600 mb-2 font-medium">Waktu Pengambilan
                                *</label>
                            <input type="time" id="pickup_time" name="pickup_time" value="10:00"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-200"
                                required>
                        </div>

                        <div>
                            <label for="return_date" class="block text-gray-600 mb-2 font-medium">Tanggal Pengembalian
                                *</label>
                            <input type="date" id="return_date" name="return_date" min="<?php echo $tomorrow; ?>"
                                value="<?php echo $tomorrow; ?>"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-200"
                                required>
                        </div>

                        <div>
                            <label for="return_time" class="block text-gray-600 mb-2 font-medium">Waktu Pengembalian
                                *</label>
                            <input type="time" id="return_time" name="return_time" value="10:00"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-200"
                                required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="pickup_location" class="block text-gray-600 mb-2 font-medium">Lokasi Pengambilan
                                *</label>
                            <select id="pickup_location" name="pickup_location"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-200"
                                required>
                                <option value="">Pilih lokasi pengambilan</option>
                                <option value="office_jakarta" selected>Kantor RentEase Jakarta</option>
                                <option value="office_bandung">Kantor RentEase Bandung</option>
                                <option value="office_surabaya">Kantor RentEase Surabaya</option>
                                <option value="airport_jakarta">Bandara Soekarno-Hatta (+Rp50.000)</option>
                                <option value="airport_bali">Bandara Ngurah Rai (+Rp50.000)</option>
                                <option value="custom">Lokasi Lainnya (+Rp100.000)</option>
                            </select>
                        </div>

                        <div>
                            <label for="return_location" class="block text-gray-600 mb-2 font-medium">Lokasi
                                Pengembalian *</label>
                            <select id="return_location" name="return_location"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-200"
                                required>
                                <option value="">Pilih lokasi pengembalian</option>
                                <option value="office_jakarta" selected>Kantor RentEase Jakarta</option>
                                <option value="office_bandung">Kantor RentEase Bandung</option>
                                <option value="office_surabaya">Kantor RentEase Surabaya</option>
                                <option value="airport_jakarta">Bandara Soekarno-Hatta (+Rp50.000)</option>
                                <option value="airport_bali">Bandara Ngurah Rai (+Rp50.000)</option>
                                <option value="custom">Lokasi Lainnya (+Rp100.000)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Informasi Pengemudi -->
                <div class="border-b pb-6">
                    <h4 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                        <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2">
                            </path>
                        </svg>
                        Informasi Pengemudi
                    </h4>

                    <div class="mb-4">
                        <label
                            class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" id="self_drive" name="self_drive" class="mr-3 h-4 w-4 text-red-600"
                                checked>
                            <span class="text-gray-700 font-medium">Saya yang akan mengemudikan sendiri</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="license_number" class="block text-gray-600 mb-2 font-medium">Nomor SIM *</label>
                            <input type="text" id="license_number" name="license_number"
                                placeholder="Masukkan nomor SIM Anda"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-200"
                                required>
                        </div>

                        <div>
                            <label for="license_expiry" class="block text-gray-600 mb-2 font-medium">Tanggal Kadaluarsa
                                SIM *</label>
                            <input type="date" id="license_expiry" name="license_expiry" min="<?php echo $today; ?>"
                                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-200"
                                required>
                        </div>
                    </div>
                </div>

                <!-- Layanan Tambahan -->
                <div class="border-b pb-6">
                    <h4 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                        <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4">
                            </path>
                        </svg>
                        Layanan Tambahan
                    </h4>

                    <div class="space-y-3">
                        <label class="service-item flex items-center cursor-pointer">
                            <input type="checkbox" name="additional_services[]" value="additional_driver"
                                class="mr-3 h-4 w-4 text-red-600">
                            <div class="service-content flex-1 flex justify-between items-center">
                                <span class="text-gray-700">Pengemudi Tambahan</span>
                                <span class="text-red-600 font-semibold">+Rp200.000</span>
                            </div>
                        </label>

                        <label class="service-item flex items-center cursor-pointer">
                            <input type="checkbox" name="additional_services[]" value="child_seat"
                                class="mr-3 h-4 w-4 text-red-600">
                            <div class="service-content flex-1 flex justify-between items-center">
                                <span class="text-gray-700">Kursi Anak</span>
                                <span class="text-red-600 font-semibold">+Rp50.000/hari</span>
                            </div>
                        </label>

                        <label class="service-item flex items-center cursor-pointer">
                            <input type="checkbox" name="additional_services[]" value="gps"
                                class="mr-3 h-4 w-4 text-red-600">
                            <div class="service-content flex-1 flex justify-between items-center">
                                <span class="text-gray-700">GPS</span>
                                <span class="text-red-600 font-semibold">+Rp25.000/hari</span>
                            </div>
                        </label>

                        <label class="service-item flex items-center cursor-pointer">
                            <input type="checkbox" name="additional_services[]" value="full_insurance"
                                class="mr-3 h-4 w-4 text-red-600">
                            <div class="service-content flex-1 flex justify-between items-center">
                                <span class="text-gray-700">Asuransi All-Risk Premium</span>
                                <span class="text-red-600 font-semibold">+Rp100.000/hari</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Promo Code Section -->
                <div class="promo-section">
                    <h4 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                        <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                            </path>
                        </svg>
                        Kode Promo
                    </h4>
                    
                    <div class="bg-white rounded-lg p-4 border border-red-200">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-gray-700 font-medium">Punya kode promo?</span>
                            <span class="text-sm text-red-600 font-semibold bg-red-50 px-2 py-1 rounded">Hemat hingga 15%!</span>
                        </div>
                        
                        <div class="promo-input flex gap-2">
                            <input type="text" id="promo_code" name="promo_code" placeholder="Masukkan kode promo"
                                class="flex-1 p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-200 uppercase"
                                maxlength="20">
                            <button type="button" id="apply_promo" class="promo-btn bg-red-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-700 transition duration-300">
                                Gunakan
                            </button>
                        </div>
                        
                        <div id="promo_message" class="mt-3 p-3 rounded-lg hidden">
                            <div class="flex items-center">
                                <svg id="promo_icon" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span id="promo_text" class="font-medium"></span>
                            </div>
                        </div>
                        
                        <div class="mt-3 text-sm text-gray-500">
                            <p>💡 <strong>Tips:</strong> Pengguna pertama kali bisa mendapat diskon spesial!</p>
                        </div>
                    </div>
                </div>

                <!-- Catatan -->
                <div class="mb-6">
                    <label for="notes" class="block text-gray-600 mb-2 font-medium">Catatan Tambahan</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Catatan atau permintaan khusus (opsional)"
                        class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500 focus:ring-2 focus:ring-red-200"></textarea>
                </div>

                <!-- Submit Button -->
                <div class="mt-8">
                    <button type="submit"
                        class="w-full bg-red-600 text-white py-4 px-6 rounded-lg font-semibold text-lg hover:bg-red-700 transition duration-300 flex items-center justify-center shadow-lg">
                        LANJUTKAN KE PEMBAYARAN
                        <svg class="w-6 h-6 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </section>

        <!-- Order Summary -->
        <aside class="order-summary bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                    </path>
                </svg>
                Ringkasan Pesanan
            </h3>

            <div class="mb-6">
                <img src="/pages/admin/uploads/<?php echo $selected_car['image']; ?>"
                    alt="<?php echo $selected_car['vehicle_name']; ?>" class="car-image mb-3">
                <h4 class="font-bold text-gray-900 text-lg"><?php echo $selected_car['vehicle_name']; ?></h4>
                <p class="text-red-600 font-semibold text-lg">Rp
                    <?php echo number_format($selected_car['price_per_day'], 0, ',', '.'); ?> / hari
                </p>
            </div>

            <!-- Price Breakdown -->
            <div class="border-t pt-4">
                <div class="space-y-2 mb-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Harga sewa </span>
                        <span id="base-price">Rp
                            <?php echo number_format($selected_car['price_per_day'], 0, ',', '.'); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Layanan tambahan</span>
                        <span id="additional-price">Rp 0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Biaya lokasi</span>
                        <span id="location-price">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-green-600" id="promo-discount-row" style="display: none;">
                        <span>Diskon promo</span>
                        <span id="promo-discount">-Rp 0</span>
                    </div>
                </div>

                <div class="border-t pt-3">
                    <div class="flex justify-between text-lg font-bold">
                        <span>Total</span>
                        <span class="text-red-600" id="total-price">Rp
                            <?php echo number_format($selected_car['price_per_day'], 0, ',', '.'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Trust Indicators -->
            <div class="mt-6 pt-4 border-t">
                <div class="space-y-3 text-sm text-gray-600">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Tanpa biaya tersembunyi
                    </div>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Asuransi dasar termasuk
                    </div>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Pembatalan gratis 24 jam
                    </div>
                </div>
            </div>
        </aside>
    </main>

    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="container mx-auto text-center">
            <div class="flex items-center justify-center space-x-3 mb-4">
                <div class="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold">R</span>
                </div>
                <span class="text-xl font-bold">RentEase</span>
            </div>
            <p class="text-gray-400 mb-4">Solusi terpercaya untuk kebutuhan sewa mobil Anda</p>
            <div class="flex justify-center space-x-6">
                <a href="#" class="text-gray-400 hover:text-white transition duration-300">Syarat & Ketentuan</a>
                <a href="#" class="text-gray-400 hover:text-white transition duration-300">Kebijakan Privasi</a>
                <a href="#" class="text-gray-400 hover:text-white transition duration-300">Hubungi Kami</a>
            </div>
            <p class="text-gray-500 mt-4">&copy; 2024 RentEase. All rights reserved.</p>
        </div>
    </footer>

    <script>
        document.getElementById('notificationBtn').addEventListener('click', function () {
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.classList.toggle('hidden');
        });

        document.addEventListener('click', function (event) {
            const notificationBtn = document.getElementById('notificationBtn');
            const dropdown = document.getElementById('notificationDropdown');

            if (!notificationBtn.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Price calculation
        const basePrice = <?php echo $selected_car['price_per_day']; ?>;
        let additionalPrice = 0;
        let locationPrice = 0;
        let totalDays = 1;
        let promoDiscount = 0;
        let promoApplied = false;

        function calculateDays() {
            const pickupDate = new Date(document.getElementById('pickup_date').value);
            const returnDate = new Date(document.getElementById('return_date').value);
            const diffTime = Math.abs(returnDate - pickupDate);
            totalDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) || 1;
            updatePriceDisplay();
        }

        function calculateAdditionalServices() {
            additionalPrice = 0;
            const services = document.querySelectorAll('input[name="additional_services[]"]:checked');

            services.forEach(service => {
                switch (service.value) {
                    case 'additional_driver':
                        additionalPrice += 200000;
                        break;
                    case 'child_seat':
                        additionalPrice += 50000 * totalDays;
                        break;
                    case 'gps':
                        additionalPrice += 25000 * totalDays;
                        break;
                    case 'full_insurance':
                        additionalPrice += 100000 * totalDays;
                        break;
                }
            });
            updatePriceDisplay();
        }

        function calculateLocationPrice() {
            locationPrice = 0;
            const pickupLocation = document.getElementById('pickup_location').value;
            const returnLocation = document.getElementById('return_location').value;

            if (pickupLocation.includes('airport')) locationPrice += 50000;
            if (pickupLocation === 'custom') locationPrice += 100000;
            if (returnLocation.includes('airport')) locationPrice += 50000;
            if (returnLocation === 'custom') locationPrice += 100000;

            updatePriceDisplay();
        }

        function calculatePromoDiscount() {
            if (promoApplied) {
                const subtotal = (basePrice * totalDays) + additionalPrice + locationPrice;
                promoDiscount = subtotal * 0.15; // 15% discount
            } else {
                promoDiscount = 0;
            }
            updatePriceDisplay();
        }

        function updatePriceDisplay() {
            const basePriceTotal = basePrice * totalDays;
            const subtotal = basePriceTotal + additionalPrice + locationPrice;
            const total = subtotal - promoDiscount;

            document.getElementById('base-price').textContent = 'Rp ' + basePriceTotal.toLocaleString('id-ID');
            document.getElementById('additional-price').textContent = 'Rp ' + additionalPrice.toLocaleString('id-ID');
            document.getElementById('location-price').textContent = 'Rp ' + locationPrice.toLocaleString('id-ID');
            document.getElementById('total-price').textContent = 'Rp ' + total.toLocaleString('id-ID');

            // Show/hide promo discount row
            const promoRow = document.getElementById('promo-discount-row');
            if (promoApplied && promoDiscount > 0) {
                document.getElementById('promo-discount').textContent = '-Rp ' + promoDiscount.toLocaleString('id-ID');
                promoRow.style.display = 'flex';
            } else {
                promoRow.style.display = 'none';
            }

            // Recalculate promo discount if needed
            if (promoApplied) {
                calculatePromoDiscount();
            }
        }

        // Promo code functionality
        function applyPromoCode() {
            const promoCode = document.getElementById('promo_code').value.trim().toUpperCase();
            const promoMessage = document.getElementById('promo_message');
            const promoText = document.getElementById('promo_text');
            const promoIcon = document.getElementById('promo_icon');
            const applyBtn = document.getElementById('apply_promo');

            if (promoCode === 'RENTFIRST15') {
                // Valid promo code
                promoApplied = true;
                calculatePromoDiscount();
                
                promoMessage.className = 'mt-3 p-3 rounded-lg promo-success';
                promoText.textContent = 'Kode promo berhasil diterapkan! Anda mendapat diskon 15%';
                promoIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>';
                promoMessage.classList.remove('hidden');
                
                applyBtn.textContent = 'Diterapkan';
                applyBtn.disabled = true;
                applyBtn.classList.add('bg-green-600', 'hover:bg-green-600');
                applyBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
                
                document.getElementById('promo_code').disabled = true;
                document.getElementById('promo_code').classList.add('bg-gray-100');
                
            } else if (promoCode === '') {
                // Empty promo code
                promoMessage.classList.add('hidden');
                
            } else {
                // Invalid promo code
                promoApplied = false;
                promoDiscount = 0;
                updatePriceDisplay();
                
                promoMessage.className = 'mt-3 p-3 rounded-lg promo-error';
                promoText.textContent = 'Kode promo tidak valid atau sudah kadaluarsa';
                promoIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>';
                promoMessage.classList.remove('hidden');
                
                // Hide error message after 3 seconds
                setTimeout(() => {
                    promoMessage.classList.add('hidden');
                }, 3000);
            }
        }

        // Event listeners
        document.getElementById('pickup_date').addEventListener('change', calculateDays);
        document.getElementById('return_date').addEventListener('change', calculateDays);
        document.getElementById('pickup_location').addEventListener('change', calculateLocationPrice);
        document.getElementById('return_location').addEventListener('change', calculateLocationPrice);

        document.querySelectorAll('input[name="additional_services[]"]').forEach(checkbox => {
            checkbox.addEventListener('change', calculateAdditionalServices);
        });

        document.getElementById('apply_promo').addEventListener('click', applyPromoCode);

        document.getElementById('promo_code').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyPromoCode();
            }
        });

        // Auto-uppercase promo code input
        document.getElementById('promo_code').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });

        // Form validation
        document.getElementById('bookingForm').addEventListener('submit', function (e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('border-red-500');
                    isValid = false;
                } else {
                    field.classList.remove('border-red-500');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi.');
            }
        });

        // Initialize calculations
        calculateDays();
        calculateLocationPrice();
        calculateAdditionalServices();
    </script>
</body>

</html>