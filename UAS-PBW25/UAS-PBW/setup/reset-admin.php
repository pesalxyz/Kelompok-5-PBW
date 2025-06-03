<?php
// This file will reset the admin password or create an admin account if it doesn't exist

// Database connection configuration
$host = "localhost";
$dbname = "rentcar";
$username = "root";
$password = "";

// Connect to database
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if admin exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM admin WHERE username = 'admin'");
    $stmt->execute();
    $adminCount = $stmt->fetchColumn();
    
    if ($adminCount == 0) {
        // Create new admin account
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
        
        echo "<p style='color:green;'>Admin account created successfully!</p>";
        echo "<p>Username: admin<br>Password: admin123</p>";
    } else {
        // Reset existing admin password
        $newPassword = "admin123";
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("UPDATE admin SET password = :password WHERE username = 'admin'");
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->execute();
        
        echo "<p style='color:green;'>Admin password reset successfully!</p>";
        echo "<p>Username: admin<br>Password: admin123</p>";
    }
    
    // Test password verification
    $stmt = $conn->prepare("SELECT password FROM admin WHERE username = 'admin'");
    $stmt->execute();
    $storedHash = $stmt->fetchColumn();
    
    $testPassword = "admin123";
    $verifyResult = password_verify($testPassword, $storedHash);
    
    echo "<p>Password verification test: ";
    echo $verifyResult ? "<span style='color:green;'>Success! The password verification works.</span>" : "<span style='color:red;'>Failed! The password hash may be incompatible.</span>";
    echo "</p>";
    
    echo "<p><a href='index.php'>Go to login page</a></p>";
    
} catch(PDOException $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}