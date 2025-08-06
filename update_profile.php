<?php
session_start();
require 'db_connect.php';

// ✅ Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user']['user_id'];
$username = $_SESSION['user']['username'] ?? '';
$email = $_SESSION['user']['email'] ?? '';
$message = "";

// ✅ Handle update request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    $new_password = trim($_POST['password']);

    if ($new_username === '' || $new_email === '') {
        $message = "Username and Email cannot be empty.";
    } else {
        if ($new_password !== '') {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username=?, email=?, password=? WHERE id=?");
            $stmt->bind_param("sssi", $new_username, $new_email, $hashed_password, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username=?, email=? WHERE id=?");
            $stmt->bind_param("ssi", $new_username, $new_email, $user_id);
        }

        if ($stmt->execute()) {
            $_SESSION['user']['username'] = $new_username;
            $_SESSION['user']['email'] = $new_email;
            $message = "✅ Profile updated successfully.";
        } else {
            $message = "❌ Failed to update profile: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Profile</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .card {
            background: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 350px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #0d6efd;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
            transition: border 0.3s;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #0d6efd;
            outline: none;
        }
        button {
            padding: 12px;
            background: #0d6efd;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        button:hover {
            background: #084cbe;
        }
        .message {
            text-align: center;
            margin-bottom: 15px;
            color: #333;
        }
        .back-link {
            text-align: center;
            margin-top: 10px;
        }
        .back-link a {
            text-decoration: none;
            color: #0d6efd;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>Update Profile</h2>
    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="username" value="<?= htmlspecialchars($username) ?>" placeholder="Username" required>
        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="Email" required>
        <input type="password" name="password" placeholder="New Password (leave blank to keep)">
        <button type="submit">Update</button>
    </form>
    <div class="back-link">
        <a href="superadmin_dashboard.php">⬅ Back to Dashboard</a>
    </div>
</div>

</body>
</html>
