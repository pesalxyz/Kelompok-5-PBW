<?php

require_once __DIR__ . "/koneksi.php";
session_start();
// Check if user is logged in
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

// Get car ID from URL
$car_id = isset($_GET['car_id']) ? intval($_GET['car_id']) : 0;

// If no car ID is provided, redirect to beranda
if ($car_id <= 0) {
    header("Location: beranda.php");
    exit;
}

// Connect to database
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get user data
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : (isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 0);
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

// Select from the appropriate table
if ($isAdmin) {
    $table = 'admin';
    $id_field = 'admin_id';
} else {
    $table = 'users';
    $id_field = 'user_id';
}

$stmt = $conn->prepare("SELECT * FROM {$table} WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// If user not found, redirect to login
if (!$user) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Simple mock data for car details
$cars = [
    1 => [
        'id' => 1,
        'name' => 'Toyota Avanza',
        'image' => 'https://via.placeholder.com/600x350?text=Toyota+Avanza',
        'seats' => 7,
        'transmission' => 'Automatic',
        'features' => ['AC', 'Free WiFi', 'Bluetooth', 'USB Port'],
        'price' => 350000,
        'description' => 'A spacious 7-seater MPV perfect for family trips. Comfortable seating with modern amenities makes it ideal for both city driving and longer journeys.'
    ],
    2 => [
        'id' => 2,
        'name' => 'Honda Civic',
        'image' => 'https://via.placeholder.com/600x350?text=Honda+Civic',
        'seats' => 5,
        'transmission' => 'Automatic',
        'features' => ['AC', 'GPS', 'Leather Seats', 'Bluetooth'],
        'price' => 450000,
        'description' => 'The sleek and stylish Honda Civic combines fuel efficiency with a sporty driving experience. Perfect for business trips or weekend getaways.'
    ],
    3 => [
        'id' => 3,
        'name' => 'Mitsubishi Pajero',
        'image' => 'https://via.placeholder.com/600x350?text=Mitsubishi+Pajero',
        'seats' => 7,
        'transmission' => 'Automatic',
        'features' => ['AC', '4WD', 'Roof Rack', 'Leather Seats'],
        'price' => 700000,
        'description' => 'A powerful 4WD SUV built for adventure. Tackle any terrain with confidence in this comfortable and capable vehicle.'
    ],
    4 => [
        'id' => 4,
        'name' => 'Daihatsu Xenia',
        'image' => 'https://via.placeholder.com/600x350?text=Daihatsu+Xenia',
        'seats' => 7,
        'transmission' => 'Manual',
        'features' => ['AC', 'Bluetooth', 'USB Port', 'Touchscreen Display'],
        'price' => 300000,
        'description' => 'An affordable and practical MPV with seating for seven. Perfect for family outings and daily commutes, with good fuel economy.'
    ]
];

// Get the selected car
$selectedCar = isset($cars[$car_id]) ? $cars[$car_id] : null;

// If car not found, redirect to beranda
if (!$selectedCar) {
    header("Location: beranda.php");
    exit;
}

// Process rental form submission
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pickup_date = trim($_POST['pickup_date'] ?? '');
    $return_date = trim($_POST['return_date'] ?? '');
    $pickup_location = trim($_POST['pickup_location'] ?? '');

    // Validation
    if (empty($pickup_date) || empty($return_date) || empty($pickup_location)) {
        $error = "All fields are required";
    } else {
        // Convert dates for validation
        $pickup_timestamp = strtotime($pickup_date);
        $return_timestamp = strtotime($return_date);
        $today_timestamp = strtotime('today');

        if ($pickup_timestamp < $today_timestamp) {
            $error = "Pickup date cannot be in the past";
        } else if ($return_timestamp <= $pickup_timestamp) {
            $error = "Return date must be after pickup date";
        } else {
            // Calculate rental days and total price
            $days = ceil(($return_timestamp - $pickup_timestamp) / (60 * 60 * 24));
            $total_price = $selectedCar['price'] * $days;

            // In a real app, we would insert the booking into a database here
            // For this example, we'll just show a success message
            $success = "Your booking for the {$selectedCar['name']} has been confirmed!<br>
                        Pickup: " . date('d F Y', $pickup_timestamp) . "<br>
                        Return: " . date('d F Y', $return_timestamp) . "<br>
                        Duration: $days days<br>
                        Total: Rp. " . number_format($total_price, 0, ',', '.') . "<br>
                        <br>
                        <a href='beranda.php' class='btn'>Back to Dashboard</a>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent <?php echo htmlspecialchars($selectedCar['name']); ?> - RentCar Service</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Additional styles specific to rent.php */
        .container {
            height: auto;
            display: block;
            background: none;
            padding: 20px;
        }

        .car-details {
            display: flex;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-top: 30px;
            overflow: hidden;
        }

        .car-image {
            flex: 0 0 50%;
        }

        .car-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .car-info {
            flex: 0 0 50%;
            padding: 30px;
        }

        .car-title {
            color: #333;
            font-size: 28px;
            margin-bottom: 15px;
        }

        .car-price {
            color: #e63946;
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .car-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .car-features {
            margin-bottom: 20px;
        }

        .car-feature {
            display: inline-block;
            background-color: #f8f8f8;
            padding: 5px 10px;
            border-radius: 20px;
            margin: 0 5px 5px 0;
            font-size: 14px;
            color: #333;
        }

        .car-specs {
            display: flex;
            margin-bottom: 20px;
        }

        .car-spec {
            flex: 1;
            text-align: center;
            border-right: 1px solid #eee;
            padding: 10px;
        }

        .car-spec:last-child {
            border-right: none;
        }

        .car-spec-value {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .car-spec-label {
            font-size: 14px;
            color: #666;
        }

        .rental-form {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-top: 30px;
            padding: 30px;
        }

        .form-title {
            color: #333;
            font-size: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .btn {
            display: inline-block;
            background-color: #e63946;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
        }

        .btn:hover {
            background-color: #d62828;
        }

        .btn-secondary {
            background-color: #666;
            margin-right: 10px;
        }

        .btn-secondary:hover {
            background-color: #555;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        @media (max-width: 768px) {
            .car-details {
                flex-direction: column;
            }

            .car-image,
            .car-info {
                flex: 0 0 100%;
            }
        }
    </style>
</head>

<body>
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <img src="kunci-mobil.png" alt="RentCar Logo"
                        onerror="this.src='https://via.placeholder.com/40x40?text=RC'">
                    <h1>RentCar Service</h1>
                </div>

                <div class="user-menu">
                    <div class="user-info">
                        <img src="<?php echo !empty($user['foto']) ? $user['foto'] : 'uploads/default-avatar.png'; ?>"
                            alt="User Avatar" class="user-avatar" onerror="this.src='uploads/default-avatar.png'">
                        <span class="user-name">
                            <?php echo htmlspecialchars($user['nama']); ?>
                            <?php if ($isAdmin): ?>
                                <span class="admin-badge">Admin</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <form action="logout.php" method="POST">
                        <button type="submit" class="logout-btn">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php else: ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="car-details">
                <div class="car-image">
                    <img src="<?php echo htmlspecialchars($selectedCar['image']); ?>"
                        alt="<?php echo htmlspecialchars($selectedCar['name']); ?>">
                </div>

                <div class="car-info">
                    <h2 class="car-title"><?php echo htmlspecialchars($selectedCar['name']); ?></h2>
                    <p class="car-price">Rp. <?php echo number_format($selectedCar['price'], 0, ',', '.'); ?> / day</p>

                    <div class="car-specs">
                        <div class="car-spec">
                            <div class="car-spec-value"><?php echo $selectedCar['seats']; ?></div>
                            <div class="car-spec-label">Seats</div>
                        </div>

                        <div class="car-spec">
                            <div class="car-spec-value"><?php echo $selectedCar['transmission']; ?></div>
                            <div class="car-spec-label">Transmission</div>
                        </div>
                    </div>

                    <p class="car-description"><?php echo htmlspecialchars($selectedCar['description']); ?></p>

                    <div class="car-features">
                        <?php foreach ($selectedCar['features'] as $feature): ?>
                            <span class="car-feature"><?php echo htmlspecialchars($feature); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="rental-form">
                <h3 class="form-title">Book Your Rental</h3>

                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                    <div class="form-group">
                        <label for="pickup_date">Pickup Date:</label>
                        <input type="date" id="pickup_date" name="pickup_date" class="form-control"
                            min="<?php echo date('Y-m-d'); ?>" required
                            value="<?php echo isset($_POST['pickup_date']) ? htmlspecialchars($_POST['pickup_date']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="return_date">Return Date:</label>
                        <input type="date" id="return_date" name="return_date" class="form-control"
                            min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required
                            value="<?php echo isset($_POST['return_date']) ? htmlspecialchars($_POST['return_date']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="pickup_location">Pickup Location:</label>
                        <select id="pickup_location" name="pickup_location" class="form-control" required>
                            <option value="" disabled <?php echo !isset($_POST['pickup_location']) ? 'selected' : ''; ?>>
                                Select pickup location</option>
                            <option value="Jakarta Office" <?php echo (isset($_POST['pickup_location']) && $_POST['pickup_location'] == 'Jakarta Office') ? 'selected' : ''; ?>>Jakarta Office</option>
                            <option value="Bandung Office" <?php echo (isset($_POST['pickup_location']) && $_POST['pickup_location'] == 'Bandung Office') ? 'selected' : ''; ?>>Bandung Office</option>
                            <option value="Surabaya Office" <?php echo (isset($_POST['pickup_location']) && $_POST['pickup_location'] == 'Surabaya Office') ? 'selected' : ''; ?>>Surabaya Office</option>
                            <option value="Bali Office" <?php echo (isset($_POST['pickup_location']) && $_POST['pickup_location'] == 'Bali Office') ? 'selected' : ''; ?>>Bali Office</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <a href="beranda.php" class="btn btn-secondary">Back</a>
                        <button type="submit" class="btn">Book Now</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2025 RentCar Service. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Add JavaScript for date validation
        document.addEventListener('DOMContentLoaded', function () {
            const pickupDateInput = document.getElementById('pickup_date');
            const returnDateInput = document.getElementById('return_date');

            if (pickupDateInput && returnDateInput) {
                pickupDateInput.addEventListener('change', function () {
                    // Set min return date to one day after pickup date
                    const pickupDate = new Date(this.value);
                    const nextDay = new Date(pickupDate);
                    nextDay.setDate(pickupDate.getDate() + 1);

                    // Format the date as YYYY-MM-DD
                    const year = nextDay.getFullYear();
                    const month = String(nextDay.getMonth() + 1).padStart(2, '0');
                    const day = String(nextDay.getDate()).padStart(2, '0');

                    returnDateInput.min = `${year}-${month}-${day}`;

                    // Reset return date if it's now invalid
                    if (new Date(returnDateInput.value) <= new Date(this.value)) {
                        returnDateInput.value = `${year}-${month}-${day}`;
                    }
                });
            }
        });
    </script>
</body>

</html>