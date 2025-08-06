<?php
session_start();
require 'db_connect.php';

// Redirect to login if user not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$status = "";
$role = strtolower($_SESSION['user']['role'] ?? 'user'); // default to user if role not set

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newpass = trim($_POST['newpass']);

    if (!empty($newpass)) {
        $hashed = password_hash($newpass, PASSWORD_BCRYPT);
        $uid = $_SESSION['user']['id'];

        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed, $uid);

        if ($stmt->execute()) {
            $status = "✅ Password updated successfully!";
        } else {
            $status = "❌ Error updating password.";
        }

        $stmt->close();
    } else {
        $status = "❌ Password cannot be empty.";
    }
}

// Set dashboard path based on role
$dashboardLink = ($role === 'admin') ? 'admin_dashboard.php' : 'user_dashboard.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Settings</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; padding: 40px; }
        form { max-width: 400px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px #ccc; }
        input, button { width: 100%; padding: 10px; margin-top: 15px; border: 1px solid #ccc; border-radius: 6px; }
        button { background: #007b83; color: white; cursor: pointer; border: none; }
        h2 { text-align: center; color: #007b83; }
        .status { text-align: center; color: green; font-weight: bold; margin-top: 10px; }
        .back { display: block; text-align: center; margin-top: 20px; text-decoration: none; color: #007b83; }
    </style>
</head>
<body>

<h2>🔐 Admin Settings</h2>

<?php if ($status): ?>
    <p class="status"><?= $status ?></p>
<?php endif; ?>

<form method="POST">
    <input name="newpass" type="password" placeholder="New Password" required>
    <button type="submit">Update Password</button>
</form>

<a href="<?= $dashboardLink ?>" class="back">⬅ Back to Dashboard</a>

</body>
</html>
