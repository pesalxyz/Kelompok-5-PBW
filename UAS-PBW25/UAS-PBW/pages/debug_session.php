<?php
// File debug untuk troubleshooting session dan form data
session_start();

echo "<h1>Debug Session & Form Data</h1>";

echo "<h2>Session Data:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>POST Data:</h2>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

echo "<h2>GET Data:</h2>";
echo "<pre>";
print_r($_GET);
echo "</pre>";

echo "<h2>Files Data:</h2>";
echo "<pre>";
print_r($_FILES);
echo "</pre>";

// Test database connection
echo "<h2>Database Connection Test:</h2>";
$host = "localhost";
$dbname = "rentcar";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$rentcar", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Test if orders table exists
    $stmt = $conn->query("DESCRIBE orders");
    echo "<p style='color: green;'>✓ Orders table exists</p>";
    
    // Test if payments table exists
    try {
        $stmt = $conn->query("DESCRIBE payments");
        echo "<p style='color: green;'>✓ Payments table exists</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Payments table does not exist: " . $e->getMessage() . "</p>";
    }
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
}

// Check uploads directory
echo "<h2>Upload Directory Check:</h2>";
$upload_dir = "../uploads/payment_proofs/";
if (file_exists($upload_dir)) {
    echo "<p style='color: green;'>✓ Upload directory exists: " . $upload_dir . "</p>";
    if (is_writable($upload_dir)) {
        echo "<p style='color: green;'>✓ Upload directory is writable</p>";
    } else {
        echo "<p style='color: red;'>✗ Upload directory is not writable</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Upload directory does not exist: " . $upload_dir . "</p>";
}
?>