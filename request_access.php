<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$userId = intval($_SESSION['user']['user_id']);
$username = htmlspecialchars($_SESSION['user']['username']);
$message = '';
$canRequest = false;

// Check if user is already active
$stmt = mysqli_prepare($conn, "SELECT is_active FROM users WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $isActive);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if ($isActive) {
    $message = "✅ Hi <strong>$username</strong>, your account is already approved.";
} else {
    // Check if a request already exists
    $check = mysqli_query($conn, "SELECT * FROM user_requests WHERE user_id = $userId AND status = 'Pending'");
    if (mysqli_num_rows($check) > 0) {
        $message = "⏳ You have already requested approval. Please wait for admin approval.";
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Insert request
        $insert = mysqli_prepare($conn, "INSERT INTO user_requests (user_id) VALUES (?)");
        mysqli_stmt_bind_param($insert, "i", $userId);
        if (mysqli_stmt_execute($insert)) {
            $message = "✅ Your request has been submitted to the admin.";
        } else {
            $message = "❌ Error submitting request.";
        }
        mysqli_stmt_close($insert);
    } else {
        $canRequest = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request Approval</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            text-align: center;
            padding: 40px;
        }

        .box {
            background: #fff;
            max-width: 400px;
            margin: auto;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        p {
            margin: 15px 0;
            font-size: 16px;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 18px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            color: #555;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="box">
    <h2>Request Admin Approval</h2>

    <p><?= $message ?></p>

    <?php if ($canRequest): ?>
        <form method="POST">
            <button type="submit">📤 Request Approval</button>
        </form>
    <?php endif; ?>

    <a href="dashboard.php">⬅ Back to Dashboard</a>
</div>
</body>
</html>
