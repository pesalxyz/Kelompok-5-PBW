<?php
// Database connection settings
$host = "localhost";
$dbname = "rentcar";
$username = "root";
$password = "";

// Function to check database connection
function checkDatabaseConnection($host, $dbname, $username, $password) {
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return ["success" => true, "message" => "Database connection successful."];
    } catch(PDOException $e) {
        return ["success" => false, "message" => "Database connection failed: " . $e->getMessage()];
    }
}

// Function to check directory permissions
function checkDirectoryPermissions() {
    $directories = ["uploads"];
    $results = [];
    
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            $results[$dir] = "Directory does not exist. Will attempt to create it.";
            try {
                mkdir($dir, 0777, true);
                $results[$dir] = "Directory created successfully.";
            } catch (Exception $e) {
                $results[$dir] = "Failed to create directory: " . $e->getMessage();
            }
        } else if (!is_writable($dir)) {
            $results[$dir] = "Directory exists but is not writable.";
        } else {
            $results[$dir] = "Directory exists and is writable.";
        }
    }
    
    return $results;
}

// Function to check if tables exist
function checkTablesExist($conn) {
    $tables = ["users", "admin"];
    $results = [];
    
    foreach ($tables as $table) {
        $stmt = $conn->prepare("SHOW TABLES LIKE :table");
        $stmt->bindParam(':table', $table);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $results[$table] = "Table exists.";
        } else {
            $results[$table] = "Table does not exist.";
        }
    }
    
    return $results;
}

// Check if we're in "fix" mode
$fixMode = isset($_GET['fix']) && $_GET['fix'] == 'true';

// Perform checks
$dbCheck = checkDatabaseConnection($host, $dbname, $username, $password);
$dirCheck = checkDirectoryPermissions();

// Check tables if database connection is successful
$tableCheck = [];
if ($dbCheck["success"]) {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $tableCheck = checkTablesExist($conn);
    
    // If in fix mode and tables don't exist, run setup.php
    if ($fixMode) {
        $tablesMissing = false;
        foreach ($tableCheck as $table => $status) {
            if ($status != "Table exists.") {
                $tablesMissing = true;
                break;
            }
        }
        
        if ($tablesMissing) {
            include 'setup.php';
            // Recheck tables after setup
            $tableCheck = checkTablesExist($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Check - RentCar Service</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #e63946;
            text-align: center;
            margin-bottom: 30px;
        }
        
        h2 {
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-top: 30px;
        }
        
        .check-item {
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            border-left: 5px solid;
        }
        
        .check-item h3 {
            margin-top: 0;
            display: flex;
            align-items: center;
        }
        
        .success {
            border-color: #4CAF50;
        }
        
        .success h3::before {
            content: "✓";
            color: #4CAF50;
            margin-right: 10px;
            font-weight: bold;
        }
        
        .error {
            border-color: #e63946;
        }
        
        .error h3::before {
            content: "✗";
            color: #e63946;
            margin-right: 10px;
            font-weight: bold;
        }
        
        .warning {
            border-color: #ff9800;
        }
        
        .warning h3::before {
            content: "⚠";
            color: #ff9800;
            margin-right: 10px;
        }
        
        .message {
            margin-top: 10px;
            font-size: 14px;
            color: #666;
        }
        
        .actions {
            margin-top: 30px;
            text-align: center;
        }
        
        .btn {
            display: inline-block;
            background-color: #e63946;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin: 0 10px;
            font-weight: 500;
        }
        
        .btn:hover {
            background-color: #d62828;
        }
        
        .btn-secondary {
            background-color: #333;
        }
        
        .btn-secondary:hover {
            background-color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>RentCar Service System Check</h1>
        
        <h2>Database Connection</h2>
        <div class="check-item <?php echo $dbCheck['success'] ? 'success' : 'error'; ?>">
            <h3>Database Connection</h3>
            <div class="message"><?php echo $dbCheck['message']; ?></div>
        </div>
        
        <?php if ($dbCheck['success']): ?>
            <h2>Database Tables</h2>
            <?php foreach ($tableCheck as $table => $status): ?>
                <div class="check-item <?php echo $status == 'Table exists.' ? 'success' : 'error'; ?>">
                    <h3><?php echo ucfirst($table); ?> Table</h3>
                    <div class="message"><?php echo $status; ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <h2>Directory Permissions</h2>
        <?php foreach ($dirCheck as $dir => $status): ?>
            <div class="check-item <?php echo strpos($status, 'not') === false ? 'success' : 'error'; ?>">
                <h3><?php echo $dir; ?> Directory</h3>
                <div class="message"><?php echo $status; ?></div>
            </div>
        <?php endforeach; ?>
        
        <div class="actions">
            <?php if (!$dbCheck['success'] || in_array('Table does not exist.', $tableCheck) || in_array('Directory exists but is not writable.', $dirCheck)): ?>
                <a href="check.php?fix=true" class="btn">Fix Issues</a>
            <?php else: ?>
                <a href="index.php" class="btn">Go to Login</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>