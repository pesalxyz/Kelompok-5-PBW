<?php
// Database connection settings
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

$error = "";
$success = "";

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $no_hp = trim($_POST['no_hp']);
    $alamat = trim($_POST['alamat']);
    $password = trim($_POST['password']);

    // Generate a username from the email (before the @ symbol)
    $username = strtolower(explode('@', $email)[0]);

    // Validate inputs
    if (empty($nama) || empty($email) || empty($no_hp) || empty($alamat) || empty($password)) {
        $error = "All fields are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $error = "Email already registered";
        } else {
            // Check if username exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            // If username exists, append numbers until unique
            $baseUsername = $username;
            $counter = 1;

            while ($stmt->rowCount() > 0) {
                $username = $baseUsername . $counter;
                $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
                $stmt->bindParam(':username', $username);
                $stmt->execute();
                $counter++;
            }

            // Process photo upload
            $foto = "";
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['foto']['name'];
                $filetype = pathinfo($filename, PATHINFO_EXTENSION);

                // Verify file extension
                if (in_array(strtolower($filetype), $allowed)) {
                    // Create unique filename
                    $newname = uniqid() . '.' . $filetype;
                    $target = 'uploads/' . $newname;

                    // Create uploads directory if it doesn't exist
                    if (!file_exists('uploads')) {
                        mkdir('uploads', 0777, true);
                    }

                    // Upload file
                    if (move_uploaded_file($_FILES['foto']['tmp_name'], $target)) {
                        $foto = $target;
                    } else {
                        $error = "Failed to upload file";
                    }
                } else {
                    $error = "Invalid file type. Only JPG, JPEG, PNG and GIF are allowed";
                }
            }

            // If no errors, insert into database
            if (empty($error)) {
                try {
                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Insert user
                    $stmt = $conn->prepare("INSERT INTO users (username, nama, email, no_hp, alamat, password, foto) 
                                           VALUES (:username, :nama, :email, :no_hp, :alamat, :password, :foto)");

                    $stmt->bindParam(':username', $username);
                    $stmt->bindParam(':nama', $nama);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':no_hp', $no_hp);
                    $stmt->bindParam(':alamat', $alamat);
                    $stmt->bindParam(':password', $hashed_password);
                    $stmt->bindParam(':foto', $foto);

                    $stmt->execute();

                    $success = "Registration successful! Your username is: " . $username;
                } catch (PDOException $e) {
                    $error = "Registration failed: " . $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Register Account - RentCar Service</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-image: url('bg_profile.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .wrapper {
            display: flex;
            justify-content: center;
            padding: 50px 20px;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 40px 30px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        }

        h2 {
            text-align: center;
            color: #e63946;
            margin-bottom: 30px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        input,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        input[type="file"] {
            border: none;
            margin-bottom: 20px;
        }

        button {
            background-color: #e63946;
            color: white;
            border: none;
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #d62828;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
        }

        .login-link a {
            color: #e63946;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <div class="form-container">
            <h2>REGISTER ACCOUNT</h2>

            <?php if (!empty($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="success">
                    <?php echo $success; ?>
                    <div class="login-link">
                        <a href="login.php">Login Now</a>
                    </div>
                </div>
            <?php else: ?>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
                    enctype="multipart/form-data">
                    <label>Username</label>
                    <input type="text" name="nama" placeholder="Create username" required
                        value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>">

                    <label>Email</label>
                    <input type="email" name="email" placeholder="Enter your email" required
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">

                    <label>Phone Number</label>
                    <input type="text" name="no_hp" placeholder="Enter your phone number" required
                        value="<?php echo isset($_POST['no_hp']) ? htmlspecialchars($_POST['no_hp']) : ''; ?>">

                    <label>Address</label>
                    <textarea name="alamat" placeholder="Enter your address"
                        required><?php echo isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : ''; ?></textarea>

                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter your password" required>

                    <label>Photo:</label>
                    <input type="file" name="foto" accept="image/*" required>

                    <button type="submit">Register</button>
                </form>

                <div class="login-link">
                    Already have an account? <a href="login.php">Login Here</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>

</html>