<?php
// Start session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    // Redirect to login page if not logged in
    header("Location: index.php");
    exit;
}


require_once __DIR__ . "/../koneksi.php";


$userId = $_SESSION['user_id'];


$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId); // "i" untuk integer, ganti dengan "s" jika string
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

// If user not found
if (!$user) {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - RentEase</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
            <nav class="hidden md:flex space-x-8 text-xl">
                <a href="beranda.php" class="text-white font-medium hover:underline">Beranda</a>
                <a href="explore.php" class="text-white font-medium hover:underline">Explore Cars</a>
                <a href="about_us.php" class="text-white font-medium hover:underline">About us</a>
            </nav>

            <!-- Profile Button -->
            <div class="flex items-center space-x-4">
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

    <!-- Profile Content -->
    <main class="container mx-auto mt-8 px-4">
        <div class="bg-white rounded-lg shadow-md overflow-hidden max-w-4xl mx-auto">
            <div class="bg-red-600 p-6 text-white">
                <h1 class="text-2xl font-bold">User Profile</h1>
            </div>

            <div class="p-6">
                <div class="flex flex-col md:flex-row">
                    <!-- Profile Image -->
                    <div class="md:w-1/3 flex justify-center mb-6 md:mb-0">
                        <div class="w-32 h-32 bg-gray-300 rounded-full flex items-center justify-center overflow-hidden">
                            <?php if (!empty($user['foto'])): ?>
                                <img src="../uploads/<?= htmlspecialchars($user['foto']) ?>" alt="Profile" class="w-32 h-32 object-cover">
                            <?php else: ?>
                                <span class="text-gray-700 font-bold text-4xl">
                                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Profile Details -->
                    <div class="md:w-2/3">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h3 class="text-gray-500 text-sm">Username</h3>
                                <p class="font-medium text-lg"><?php echo htmlspecialchars($user['username']); ?></p>
                            </div>

                            <div>
                                <h3 class="text-gray-500 text-sm">Email</h3>
                                <p class="font-medium text-lg">
                                    <?php echo htmlspecialchars($user['email'] ?? 'Not provided'); ?>
                                </p>
                            </div>

                            <div>
                                <h3 class="text-gray-500 text-sm">Full Name</h3>
                                <p class="font-medium text-lg">
                                    <?php echo htmlspecialchars($user['nama'] ?? 'Not provided'); ?>
                                </p>
                            </div>

                            <div>
                                <h3 class="text-gray-500 text-sm">Phone Number</h3>
                                <p class="font-medium text-lg">
                                    <?php echo htmlspecialchars($user['no_hp'] ?? 'Not provided'); ?>
                                </p>
                            </div>
                        </div>

                        <!-- Edit Profile Button -->
                        <div class="mt-6">
                            <a href="edit_profile.php"
                                class="bg-red-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-red-700 transition">
                                Edit Profile
                            </a>

                            <a href="../logout.php"
                            onclick="return confirm('Yakin ingin logout?')"
                            class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-medium hover:bg-gray-300 transition ml-2">
                            Logout
                            </a>

                        </div>
                    </div>
                </div>
            </div>


                <?php
                // Ambil riwayat order berdasarkan user_id
                $orderStmt = $conn->prepare("SELECT `order_id`, `vehicle_id`, `rental_start`, `rental_end`, `status`, `payment_status` FROM orders WHERE user_id = ? ORDER BY rental_start DESC");
                $orderStmt->bind_param("i", $userId); // user_id tipe integer
                $orderStmt->execute();
                $result = $orderStmt->get_result();
                ?>

                <div class="border-t border-gray-200 p-6">
                    <h2 class="text-xl font-bold mb-4">Rental History</h2>

                    <?php if ($result->num_rows > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Vehicle ID</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Rental Start</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Rental End</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Payment</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php while ($order = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?= htmlspecialchars($order['vehicle_id']) ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?= htmlspecialchars($order['rental_start']) ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?= htmlspecialchars($order['rental_end']) ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php
                                                $status = strtolower($order['status']);
                                                if ($status === 'completed') {
                                                    echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completed</span>';
                                                } elseif ($status === 'active') {
                                                    echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Active</span>';
                                                } elseif ($status === 'cancelled') {
                                                    echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Cancelled</span>';
                                                } else {
                                                    echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">' . htmlspecialchars($order['status']) . '</span>';
                                                }
                                                ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php
                                                $payment = strtolower($order['status']);
                                                if ($payment === 'verified') {
                                                    echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-50 text-green-700">Paid</span>';
                                                } elseif ($payment === 'pending') {
                                                    echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-50 text-yellow-700">Pending</span>';
                                                } else {
                                                    echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-700">' . htmlspecialchars($order['status']) . '</span>';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500">No rental history found.</p>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between">
                <div class="mb-6 md:mb-0">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                            <span class="text-red-600 font-bold text-xl">R</span>
                        </div>
                        <span class="text-2xl font-bold">RentEase</span>
                    </div>
                    <p class="text-gray-400">The best car rental service in town.</p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-8">
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Company</h3>
                        <ul class="text-gray-400 text-sm space-y-2">
                            <li><a href="#" class="hover:underline">About Us</a></li>
                            <li><a href="#" class="hover:underline">Careers</a></li>
                            <li><a href="#" class="hover:underline">Contact</a></li>
                            <li><a href="#" class="hover:underline">Blog</a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold mb-4">Support</h3>
                        <ul class="text-gray-400 text-sm space-y-2">
                            <li><a href="#" class="hover:underline">Help Center</a></li>
                            <li><a href="#" class="hover:underline">Terms of Service</a></li>
                            <li><a href="#" class="hover:underline">Privacy Policy</a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold mb-4">Follow Us</h3>
                        <div class="flex space-x-4">
                            <a href="#" class="text-gray-400 hover:text-white transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 10h4v12H8zM16 10h4v12h-4z" />
                                </svg>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-white transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 10h4v12H8zM16 10h4v12h-4z" />
                                </svg>
                            </a>
                            <a href="#" class="text-gray-400 hover:text-white transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 10h4v12H8zM16 10h4v12h-4z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-700 mt-8 pt-4 text-center text-gray-400 text-sm">
                &copy; 2023 RentEase. All rights reserved.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"
        integrity="sha384-oBqDVmMz4fnFO9gybB+2z5G5h5h5f5l5O5l5O5l5O5l5O5l5O5l5O5l5O5l5"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"
        integrity="sha384-1CmrxMRARb6aN8gx7C3pL3v3X3X3X3X3X3X3X3X3X3X3X3X3X3X3X3"
        crossorigin="anonymous"></script>
</body>

</html>