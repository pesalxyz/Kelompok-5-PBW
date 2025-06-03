<?php
session_start();
require_once __DIR__ . "/../koneksi.php";

$userId = $_SESSION['user_id'];

// Ambil data user
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];

    // Upload foto profil
    if ($_FILES['profile_pic']['name']) {
        $target_dir = "../uploads/";
        $filename = uniqid() . "_" . basename($_FILES["profile_pic"]["name"]);
        $target_file = $target_dir . $filename;
        move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file);
        $profile_pic = $filename;
        // Kolom sesuai database
        $stmt = $conn->prepare("UPDATE users SET username=?, nama=?, no_hp=?, foto=? WHERE id=?");
        $stmt->bind_param("ssssi", $username, $fullname, $phone, $profile_pic, $userId);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username=?, nama=?, no_hp=? WHERE id=?");
        $stmt->bind_param("sssi", $username, $fullname, $phone, $userId);
    }
    $stmt->execute();
    header("Location: profile.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="max-w-xl mx-auto mt-10 bg-white p-8 rounded shadow">
        <h2 class="text-2xl font-bold mb-6">Edit Profile</h2>
        <form method="post" enctype="multipart/form-data">
            <div class="mb-4">
                <label class="block mb-1">Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" class="w-full border rounded px-3 py-2" required>
            </div>
            <div class="mb-4">
                <label class="block mb-1">Full Name</label>
                <input type="text" name="fullname" value="<?= htmlspecialchars($user['nama'] ?? '') ?>" class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block mb-1">Phone Number</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($user['no_hp'] ?? '') ?>" class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block mb-1">Profile Photo</label>
                <input type="file" name="profile_pic" accept="image/*" class="w-full">
                <?php if (!empty($user['foto'])): ?>
                    <img src="../uploads/<?= htmlspecialchars($user['foto']) ?>" alt="Profile" class="w-20 h-20 rounded-full mt-2">
                <?php endif; ?>
            </div>
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Save Changes</button>
            <a href="profile.php" class="ml-2 text-gray-600">Cancel</a>
        </form>
    </div>
</body>
</html>