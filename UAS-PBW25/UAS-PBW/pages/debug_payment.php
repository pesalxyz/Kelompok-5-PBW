<?php
session_start();

echo "<h1>Payment Debug Information</h1>";

// Check session data
echo "<h2>Session Data:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Check POST data
echo "<h2>POST Data:</h2>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

// Check FILES data
echo "<h2>FILES Data:</h2>";
echo "<pre>";
print_r($_FILES);
echo "</pre>";

// Check upload directory
echo "<h2>Upload Directory Check:</h2>";
$upload_dirs = [
    "uploads/payment_proofs/",
    "../uploads/payment_proofs/",
    "../../uploads/payment_proofs/"
];

foreach ($upload_dirs as $dir) {
    echo "<p><strong>Testing: $dir</strong></p>";
    if (file_exists($dir)) {
        echo "<span style='color: green;'>✓ Directory exists</span><br>";
        if (is_writable($dir)) {
            echo "<span style='color: green;'>✓ Directory is writable</span><br>";
        } else {
            echo "<span style='color: red;'>✗ Directory is not writable</span><br>";
        }
    } else {
        echo "<span style='color: red;'>✗ Directory does not exist</span><br>";
        // Try to create it
        if (mkdir($dir, 0777, true)) {
            echo "<span style='color: blue;'>→ Directory created successfully</span><br>";
        } else {
            echo "<span style='color: red;'>→ Failed to create directory</span><br>";
        }
    }
    echo "<br>";
}

// Test database connection and tables
echo "<h2>Database Test:</h2>";
$host = "localhost";
$dbname = "rentcar";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<span style='color: green;'>✓ Database connected</span><br>";
    
    // Check orders table
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM orders");
        $count = $stmt->fetchColumn();
        echo "<span style='color: green;'>✓ Orders table exists ({$count} records)</span><br>";
    } catch (Exception $e) {
        echo "<span style='color: red;'>✗ Orders table error: " . $e->getMessage() . "</span><br>";
    }
    
    // Check payments table
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM payments");
        $count = $stmt->fetchColumn();
        echo "<span style='color: green;'>✓ Payments table exists ({$count} records)</span><br>";
    } catch (Exception $e) {
        echo "<span style='color: red;'>✗ Payments table error: " . $e->getMessage() . "</span><br>";
        echo "<p>Create payments table with this SQL:</p>";
        echo "<textarea rows='10' cols='80' readonly>";
        echo "CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` enum('pending_verification','verified','rejected') NOT NULL DEFAULT 'pending_verification',
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`payment_id`),
  KEY `order_id` (`order_id`),
  KEY `transaction_id` (`transaction_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE
);";
        echo "</textarea>";
    }
    
} catch(PDOException $e) {
    echo "<span style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</span><br>";
}

// Test file upload simulation
echo "<h2>File Upload Test:</h2>";
if ($_FILES && isset($_FILES['test_file'])) {
    echo "<h3>Processing test upload...</h3>";
    $upload_dir = "uploads/payment_proofs/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $test_file = $upload_dir . "test_" . time() . ".txt";
    if (move_uploaded_file($_FILES['test_file']['tmp_name'], $test_file)) {
        echo "<span style='color: green;'>✓ Test file uploaded successfully to: $test_file</span><br>";
        // Clean up
        unlink($test_file);
        echo "<span style='color: blue;'>→ Test file deleted</span><br>";
    } else {
        echo "<span style='color: red;'>✗ Test file upload failed</span><br>";
    }
} else {
    echo "<form method='POST' enctype='multipart/form-data'>";
    echo "<p>Test file upload: <input type='file' name='test_file'></p>";
    echo "<button type='submit'>Test Upload</button>";
    echo "</form>";
}

// Check PHP configuration
echo "<h2>PHP Configuration:</h2>";
echo "<p>upload_max_filesize: " . ini_get('upload_max_filesize') . "</p>";
echo "<p>post_max_size: " . ini_get('post_max_size') . "</p>";
echo "<p>max_file_uploads: " . ini_get('max_file_uploads') . "</p>";
echo "<p>file_uploads: " . (ini_get('file_uploads') ? 'Enabled' : 'Disabled') . "</p>";
echo "<p>upload_tmp_dir: " . (ini_get('upload_tmp_dir') ?: 'Default') . "</p>";

echo "<h2>Current Working Directory:</h2>";
echo "<p>" . getcwd() . "</p>";

echo "<h2>Directory Listing:</h2>";
echo "<pre>";
print_r(scandir('.'));
echo "</pre>";
?>