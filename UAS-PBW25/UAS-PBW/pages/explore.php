<?php
session_start();
require_once __DIR__ . "/../koneksi.php";

// Ambil username dari session
$username = "Profile";

if (isset($_SESSION['user_id'])) {
    $id = $_SESSION['user_id'];
    $query = "SELECT username FROM users WHERE id = $id";
    $result = mysqli_query($conn, $query) or die("Query error: " . mysqli_error($conn));

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $username = $row['username'];
    }
}

try {
    // CARI MOBIL TERSEDIA
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $searchQuery = "WHERE v.status = 'available'";

    if (!empty($search)) {
        $search = mysqli_real_escape_string($conn, $search);
        $searchQuery .= " AND (v.vehicle_name LIKE '%$search%' OR v.vehicle_type LIKE '%$search%')";
    }

    $vehicles = mysqli_query($conn, "SELECT * FROM vehicles v $searchQuery ORDER BY vehicle_id DESC");

    $rekomendasiQuery = "
        SELECT v.*, COUNT(o.order_id) AS total_disewa
        FROM vehicles v
        JOIN orders o ON v.vehicle_id = o.vehicle_id
        WHERE o.status IN ('disewa', 'dikembalikan')
        GROUP BY v.vehicle_id
        ORDER BY total_disewa DESC
        LIMIT 5
    ";
    $rekomendasi = mysqli_query($conn, $rekomendasiQuery);

} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}
?>



<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentEase - Jelajahi Mobil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/explore.css">
    <style>
        .gradient-header {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        }

        .card {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1);
        }

        .car-card {
            background: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .car-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .car-specs {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }

        .car-spec-item {
            display: flex;
            align-items: center;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .car-spec-item svg {
            margin-right: 0.5rem;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(220, 38, 38, 0.3);
        }

        .promotion-gradient {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
        }

        .nav-link:hover {
            color: white !important;
        }

        .animate-fade-in {
            animation: fadeIn 0.6s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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
            <?php $currentPage = basename($_SERVER['PHP_SELF']); ?>     
            <nav class="hidden md:flex space-x-8 text-xl">
                <a href="beranda.php" class="text-white font-medium <?= $currentPage == 'beranda.php' ? 'underline' : 'hover:underline' ?>">Beranda</a>
                <a href="explore.php" class="text-white font-medium <?= $currentPage == 'explore.php' ? 'underline' : 'hover:underline' ?>">Explore Cars</a>
                <a href="about_us.php" class="text-white font-medium <?= $currentPage == 'about_us.php' ? 'underline' : 'hover:underline' ?>">About us</a>
            </nav>

            <!-- Profile -->
            <div class="flex items-center space-x-4">
                <!-- Profile Button -->
                <a href="profile.php" class="inline-block">
                    <button
                        class="bg-white text-red-600 rounded-lg px-6 py-2 flex items-center font-medium transition duration-300 hover:bg-gray-100">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>                        
                        <?php echo htmlspecialchars($username); ?>
                    </button>
                </a>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-6 py-10">
        <!-- Hero Section -->
        <div class="card p-8 mb-8 animate-fade-in">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">Temukan Mobil Impian Anda</h1>
                    <p class="text-gray-600 text-lg">Jelajahi koleksi kami dan temukan kendaraan yang tepat untuk
                        kebutuhan Anda.</p>
                </div>
            </div>
        </div>

        <!-- Search Section -->
        <div class="card p-6 mb-8 animate-fade-in">
            <form method="GET" action="">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <input type="text" name="search" id="searchBar" value="<?= htmlspecialchars($search) ?>"
                            placeholder="🔍 Search by Car, Model, or Brand" class="search-input">
                    </div>
                    <div class="flex space-x-4">
                        <button type="submit" class="btn-primary md:w-auto">
                            Search
                        </button>
                        <?php if (!empty($search)): ?>
                            <a href="explore.php"
                                class="bg-gray-500 text-white px-6 py-3 rounded-lg font-medium hover:bg-gray-600 transition duration-300">
                                Clear
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>


        <div class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">
                    <?= !empty($search) ? "Search Results for \"$search\"" : "Available Cars" ?>
                </h2>
                <span class="text-gray-500">
                    <?= mysqli_num_rows($vehicles) ?> cars found
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="carList">
                <?php if (mysqli_num_rows($vehicles) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($vehicles)): ?>
                        <div class="car-card">
                            <div class="h-48 mb-4 rounded-lg overflow-hidden">
                                <img src="admin/uploads/<?= $row['image'] ?>" alt="<?= $row['vehicle_name'] ?>"
                                    class="w-full h-full object-cover"
                                    onerror="this.src='https://via.placeholder.com/400x300?text=Gambar+Tidak+Tersedia'">
                            </div>

                            <div class="flex justify-between items-start mb-3">
                                <h3 class="text-xl font-bold text-gray-900"><?= $row['vehicle_name'] ?></h3>
                                <span
                                    class="status-badge <?= $row['status'] == 'available' ? 'status-available' : 'status-rented' ?>">
                                    <?= $row['status'] == 'available' ? 'Tersedia' : 'Disewa' ?>
                                </span>
                            </div>

                            <div class="car-specs mb-4">
                                <div class="car-spec-item">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                        </path>
                                    </svg>
                                    <?= ucfirst($row['vehicle_type']) ?>
                                </div>
                                <div class="car-spec-item">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                        </path>
                                    </svg>
                                    Premium
                                </div>
                                <div class="car-spec-item">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                    Irit Bahan Bakar
                                </div>
                                <div class="car-spec-item">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Transmisi Otomatis
                                </div>
                            </div>

                            <div class="flex justify-between items-center mt-4">
                                <p class="text-lg">
                                    <span class="text-lg font-bold text-red-600">Rp
                                        <?= number_format($row['price_per_day']) ?></span> / Hari
                                </p>


                                <?php if ($row['status'] == 'available'): ?>
                                    <a href="order.php?id=<?= $row['vehicle_id']; ?>">
                                        <button class="btn-primary w-auto">Sewa Sekarang</button>
                                    </a>
                                <?php else: ?>
                                    <button class="bg-gray-400 text-white px-6 py-3 rounded-lg font-medium cursor-not-allowed"
                                        disabled>
                                        Tidak Tersedia
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-full text-center py-12">
                        <div class="text-gray-400 mb-4">
                            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-600 mb-2">No cars found</h3>
                        <p class="text-gray-500 mb-4">
                            <?= !empty($search) ? "Try searching with different keywords" : "No vehicles available at the moment" ?>
                        </p>
                        <?php if (!empty($search)): ?>
                            <a href="explore.php" class="btn-primary inline-block">
                                View All Cars
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Car List Section -->


        <?php if (mysqli_num_rows($rekomendasi) > 0): ?>
            <div class="mb-12">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Rekomendasi Mobil</h2>
                    <span class="text-gray-500">
                        <?= mysqli_num_rows($rekomendasi) ?> mobil populer
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php while ($row = mysqli_fetch_assoc($rekomendasi)): ?>
                        <div class="car-card">
                            <div class="h-48 mb-4 rounded-lg overflow-hidden">
                                <img src="admin/uploads/<?= $row['image'] ?>" alt="<?= $row['vehicle_name'] ?>"
                                    class="w-full h-full object-cover"
                                    onerror="this.src='https://via.placeholder.com/400x300?text=Gambar+Tidak+Tersedia'">
                            </div>

                            <div class="flex justify-between items-start mb-3">
                                <h3 class="text-xl font-bold text-gray-900"><?= $row['vehicle_name'] ?></h3>
                                <span class="status-badge status-available">
                                    <?= $row['total_disewa']; ?> Kali Disewa
                                </span>
                            </div>

                            <div class="car-specs mb-4">
                                <div class="car-spec-item">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                        </path>
                                    </svg>
                                    <?= ucfirst($row['vehicle_type']) ?>
                                </div>
                                <div class="car-spec-item">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                        </path>
                                    </svg>
                                    Premium
                                </div>
                                <div class="car-spec-item">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                    Irit Bahan Bakar
                                </div>
                                <div class="car-spec-item">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Transmisi Otomatis
                                </div>
                            </div>

                            <div class="flex justify-between items-center mt-4">
                                <p class="text-lg">
                                    <span class="text-lg font-bold text-red-600">Rp
                                        <?= number_format($row['price_per_day']) ?></span> / Hari
                                </p>

                                <a href="order.php?id=<?= $row['vehicle_id']; ?>">
                                    <button class="btn-primary w-auto">Sewa Sekarang</button>
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>



        <!-- Promotion Banner -->
        <div class="promotion-gradient rounded-lg p-8 mb-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold mb-2">Penawaran Khusus Akhir Pekan!</h2>
                    <p class="text-red-100 mb-4">Dapatkan diskon 15% untuk sewa akhir pekan dengan kode</p>
                    <div class="inline-flex items-center bg-white rounded-lg px-4 py-2">
                        <span class="text-red-600 font-bold mr-2">WEEKEND15</span>
                        <button class="text-red-600 hover:text-red-700 transition duration-300"
                            onclick="copyPromoCode()">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                                </path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="mt-6 md:mt-0">
                    <button
                        class="bg-white text-red-600 px-8 py-3 rounded-lg font-medium hover:bg-red-50 transition duration-300"
                        onclick="claimOffer()">
                        Klaim Penawaran
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
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
        // Notification dropdown toggle
        document.getElementById('notificationBtn').addEventListener('click', function () {
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.classList.toggle('hidden');
        });

        // Close notification dropdown when clicking outside
        document.addEventListener('click', function (event) {
            const notificationBtn = document.getElementById('notificationBtn');
            const dropdown = document.getElementById('notificationDropdown');

            if (!notificationBtn.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Search functionality
        function searchCars() {
            const searchTerm = document.getElementById('searchBar').value.toLowerCase();
            const carCards = document.querySelectorAll('[data-car-name]');

            carCards.forEach(card => {
                const carName = card.getAttribute('data-car-name');
                if (carName.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Search on Enter key
        document.getElementById('searchBar').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                searchCars();
            }
        });

        // Copy promo code
        function copyPromoCode() {
            navigator.clipboard.writeText('WEEKEND15').then(function () {
                alert('Kode promo berhasil disalin!');
            });
        }

        // Claim offer
        function claimOffer() {
            alert('Penawaran akan diterapkan di halaman pemesanan!');
            window.location.href = 'order.php';
        }

        // Add loading animation for car cards
        window.addEventListener('load', function () {
            const carCards = document.querySelectorAll('.car-card');
            carCards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    card.style.transition = 'all 0.6s ease';

                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 100);
                }, index * 200);
            });
        });
    </script>
</body>

</html>