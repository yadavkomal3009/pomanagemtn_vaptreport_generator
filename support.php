<?php
session_start();
require 'db_connect.php';

// ✅ Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user']['user_id'];
$username = $_SESSION['user']['username'];
$message = "";

// ✅ Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject']);
    $msg = trim($_POST['message']);

    if ($subject === '' || $msg === '') {
        $message = "❌ Subject and Message are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO support_requests (user_id, subject, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $subject, $msg);
        if ($stmt->execute()) {
            $message = "✅ Support request submitted successfully.";
        } else {
            $message = "❌ Failed to submit support request: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Support</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 20px;
        }
        .card {
            background: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 400px;
            max-width: 95%;
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
        textarea {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
            transition: border 0.3s;
            font-size: 15px;
        }
        input[type="text"]:focus,
        textarea:focus {
            border-color: #0d6efd;
            outline: none;
        }
        textarea {
            resize: vertical;
            min-height: 100px;
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
    <h2>Contact Support</h2>
    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="subject" placeholder="Subject" required>
        <textarea name="message" placeholder="Describe your issue or query..." required></textarea>
        <button type="submit">Submit</button>
    </form>
    <div class="back-link">
        <a href="superadmin_dashboard.php">⬅ Back to Dashboard</a>
    </div>
</div>

</body>
</html>
