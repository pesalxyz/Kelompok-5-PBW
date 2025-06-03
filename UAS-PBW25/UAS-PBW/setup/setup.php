<?php
// Check if the uploads directory exists, if not create it
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

// Create a default avatar if it doesn't exist
if (!file_exists('uploads/default-avatar.png')) {
    // This is a small placeholder script to create a default avatar
    // In a production environment, you'd want to copy an actual image file
    
    // Create a simple image
    $image = imagecreatetruecolor(200, 200);
    $bg = imagecolorallocate($image, 230, 57, 70);    // Red background (#e63946)
    $fg = imagecolorallocate($image, 255, 255, 255);  // White text
    
    // Fill background
    imagefilledrectangle($image, 0, 0, 200, 200, $bg);
    
    // Add a circle for the default avatar
    imagefilledellipse($image, 100, 80, 120, 120, $fg);
    
    // Add body shape
    imagefilledrectangle($image, 60, 140, 140, 200, $fg);
    
    // Save the image
    imagepng($image, 'uploads/default-avatar.png');
    imagedestroy($image);
}

// Database connection configuration
$host = "localhost";
$dbname = "rentcar";
$username = "root";
$password = "";

// Check if the database exists, if not create it
try {
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $conn->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    
    // Connect to the database
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create tables if they don't exist
    $sql = "
    -- Users table
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        nama VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        no_hp VARCHAR(15) NOT NULL,
        alamat TEXT NOT NULL,
        password VARCHAR(255) NOT NULL,
        foto VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

    -- Admin table
    CREATE TABLE IF NOT EXISTS admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        nama VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    ";
    
    $conn->exec($sql);
    
    // Check if admin exists, if not create a default admin
    $stmt = $conn->prepare("SELECT COUNT(*) FROM admin");
    $stmt->execute();
    $adminCount = $stmt->fetchColumn();
    
    if ($adminCount == 0) {
        // Create default admin (admin/admin123)
        $adminUsername = "admin";
        $adminPassword = password_hash("admin123", PASSWORD_DEFAULT);
        $adminName = "Administrator";
        $adminEmail = "admin@rentcar.com";
        
        $stmt = $conn->prepare("INSERT INTO admin (username, password, nama, email) VALUES (:username, :password, :nama, :email)");
        $stmt->bindParam(':username', $adminUsername);
        $stmt->bindParam(':password', $adminPassword);
        $stmt->bindParam(':nama', $adminName);
        $stmt->bindParam(':email', $adminEmail);
        $stmt->execute();
        
        echo "<p>Default admin created:<br>Username: admin<br>Password: admin123</p>";
    } else {
        echo "<p>Admin user already exists</p>";
        
        // Let's debug by checking the stored password hash
        $stmt = $conn->prepare("SELECT username, password FROM admin WHERE username = 'admin'");
        $stmt->execute();
        $adminUser = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($adminUser) {
            // Reset admin password to ensure it's correct
            $adminPassword = password_hash("admin123", PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admin SET password = :password WHERE username = 'admin'");
            $stmt->bindParam(':password', $adminPassword);
            $stmt->execute();
            echo "<p>Admin password has been reset to 'admin123'</p>";
        }
    }
    
    echo "Setup completed successfully!";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}