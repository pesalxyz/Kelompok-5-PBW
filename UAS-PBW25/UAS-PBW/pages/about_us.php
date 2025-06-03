<?php
session_start();

include __DIR__ . "/../koneksi.php";

// Cek apakah user sudah login
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>About Us - RentEase</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
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

      <!-- Profile Button -->
      <a href="profile.php" class="inline-block">
        <button class=" flex items-center bg-white text-red-600 px-4 py-2 rounded-lg font-medium hover:bg-gray-100 transition">
        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
        xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
        </svg>  
        <?php echo htmlspecialchars($username); ?>
        </button>
      </a>
    </div>
  </header>

  <!-- Hero Section -->
  <section class="about-hero bg-red-600 py-12 text-center text-white">
    <h1 class="text-4xl font-bold">Tentang Kami</h1>
  </section>

  <!-- About Section -->
  <section class="about bg-white py-16">
    <div class="container mx-auto px-4">
      <p class="text-lg text-gray-700 leading-relaxed">
        <strong>RentEase</strong> Service adalah layanan rental mobil profesional yang menyediakan berbagai jenis kendaraan untuk kebutuhan harian, perjalanan bisnis, wisata keluarga, hingga kebutuhan khusus lainnya. Dengan armada yang terawat dan pelayanan yang ramah, kami berkomitmen untuk memberikan pengalaman berkendara yang nyaman, aman, dan efisien bagi setiap pelanggan di seluruh Indonesia.
      </p>
    </div>
  </section>

  <!-- Vision Section -->
  <section class="vision bg-gray-50 py-16">
    <div class="container mx-auto px-4 text-center">
      <h2 class="text-2xl font-semibold text-gray-800 mb-4">Visi</h2>
      <p class="text-lg text-gray-600 leading-relaxed max-w-2xl mx-auto">
        Menjadi penyedia layanan rental mobil terdepan di Indonesia yang mengutamakan kualitas pelayanan, kenyamanan pelanggan, dan keandalan armada.
      </p>
    </div>
  </section>

  <!-- Mission Section -->
  <section class="mission bg-white py-16">
    <div class="container mx-auto px-4 text-center">
      <h2 class="text-2xl font-semibold text-gray-800 mb-4">Misi</h2>
      <ol class="list-decimal text-left mx-auto max-w-2xl text-gray-600 space-y-4">
        <li>Menyediakan armada kendaraan yang bersih, terawat, dan siap pakai.</li>
        <li>Memberikan pelayanan yang cepat, ramah, dan profesional.</li>
        <li>Membangun kepercayaan pelanggan melalui transparansi harga dan sistem sewa yang mudah.</li>
        <li>Mengembangkan layanan berbasis teknologi untuk kemudahan reservasi dan monitoring.</li>
        <li>Menjaga kepuasan pelanggan sebagai prioritas utama dalam setiap layanan yang kami berikan.</li>
      </ol>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-red-600 text-white py-4 text-center">
    <p>&copy; 2025 RentCar Service. All rights reserved.</p>
  </footer>
</body>
</html>
