<?php

require_once __DIR__ . '/koneksi.php';
// Start session
session_start();

// Connect to database
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Username and password are required";
    } else {
        $loggedIn = false;

        // Coba login sebagai user
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_logged_in'] = true;
            $redirectTo = "pages/beranda.php";
            $loggedIn = true;
        } else {
            // Coba login sebagai admin
            $stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && $password == $admin['password']) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['is_admin'] = true;
                $redirectTo = "pages/admin/dashboard.php";
                $loggedIn = true;
            }
        }



        if ($loggedIn) {
            header("Location: $redirectTo");
            exit;
        } else {
            $error = "Invalid username or password";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>User Login - RentCar Service</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Basic styling if style.css is not available */
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form-box {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .logo-img {
            max-width: 80px;
            margin-bottom: 20px;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .options {
            display: flex;
            justify-content: flex-start;
            margin: 10px 0;
        }

        .regist-account {
            margin: 15px 0;
        }

        button {
            background-color: #e63946;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #d62828;
        }

        .error {
            color: #e63946;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="form-box">
            <img src="images/kunci-mobil.png" alt="Logo" class="logo-img" />
            <h2>Login to RentCar Service</h2>

            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="text" name="username" id="username" placeholder="Username" required
                    value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" />
                <input type="password" name="password" id="password" placeholder="Password" required />
                <div class="options">
                    <label><input type="checkbox" name="keep_logged_in" /> Keep me logged in</label>
                </div>
                <div class="regist-account">
                    <label>Don't have account yet?</label>
                    <a href="register.php">Register Account</a>
                </div>
                <button type="submit">Login</button>
            </form>
        </div>
    </div>

    <script>
        function validateForm() {
            var username = document.getElementById("username").value;
            var password = document.getElementById("password").value;

            if (username.trim() === "" || password.trim() === "") {
                alert("Username and password are required!");
                return false;
            }
            return true;
        }
    </script>
</body>

</html>