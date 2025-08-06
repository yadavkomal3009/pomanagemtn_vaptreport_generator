<?php
session_start();
require 'db_connect.php';

// ✅ Ensure only admin can access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'superadmin') {
    header("Location: login.php");
    exit();
}

$message = "";

// ✅ Handle admin response
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'], $_POST['admin_response'])) {
    $req_id = (int)$_POST['request_id'];
    $admin_response = trim($_POST['admin_response']);

    if ($admin_response !== '') {
        $stmt = $conn->prepare("UPDATE support_requests SET admin_response=?, status='closed' WHERE id=?");
        $stmt->bind_param("si", $admin_response, $req_id);
        if ($stmt->execute()) {
            $message = "✅ Response submitted and request closed.";
            // ✅ OPTIONAL: insert into notifications table
            $res = $conn->query("SELECT user_id FROM support_requests WHERE id=$req_id");
            $row = $res->fetch_assoc();
            $user_id = $row['user_id'];
            $notify_msg = "Your support request #$req_id has been answered.";
            $stmt_n = $conn->prepare("INSERT INTO notifications (message, sender_id, created_at) VALUES (?, ?, NOW())");
            $admin_id = $_SESSION['user']['user_id'];
            $stmt_n->bind_param("si", $notify_msg, $admin_id);
            $stmt_n->execute();
            $stmt_n->close();
        } else {
            $message = "❌ Failed to submit response: " . $stmt->error;
        }
        $stmt->close();
    }
}

// ✅ Fetch all support requests
$res = $conn->query("
    SELECT sr.*, u.username 
    FROM support_requests sr 
    JOIN users u ON sr.user_id = u.id
    ORDER BY sr.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Support Requests</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6f9;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: auto;
        }
        h2 {
            text-align: center;
            color: #0d6efd;
        }
        .message {
            text-align: center;
            margin-bottom: 15px;
            color: #333;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 15px;
        }
        .card h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .card p {
            margin: 5px 0;
        }
        form textarea {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-top: 10px;
            resize: vertical;
        }
        button {
            padding: 10px 15px;
            background: #0d6efd;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background: #084cbe;
        }
        .back-link {
            text-align: center;
            margin-top: 15px;
        }
        .back-link a {
            text-decoration: none;
            color: #0d6efd;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Support Requests</h2>
    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php while ($row = $res->fetch_assoc()): ?>
        <div class="card">
            <h3>#<?= $row['id'] ?> • <?= htmlspecialchars($row['subject']) ?> [<?= strtoupper($row['status']) ?>]</h3>
            <p><strong>User:</strong> <?= htmlspecialchars($row['username']) ?></p>
            <p><strong>Message:</strong><br><?= nl2br(htmlspecialchars($row['message'])) ?></p>
            <p><strong>Submitted:</strong> <?= date("d M Y h:i A", strtotime($row['created_at'])) ?></p>
            <?php if ($row['status'] === 'open'): ?>
                <form method="POST">
                    <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                    <textarea name="admin_response" placeholder="Write your response here..." required></textarea>
                    <button type="submit">Submit Response & Close</button>
                </form>
            <?php else: ?>
                <p><strong>Admin Response:</strong><br><?= nl2br(htmlspecialchars($row['admin_response'])) ?></p>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>

    <div class="back-link">
        <a href="superadmin_dashboard.php">⬅ Back to Dashboard</a>
    </div>
</div>

</body>
</html>
