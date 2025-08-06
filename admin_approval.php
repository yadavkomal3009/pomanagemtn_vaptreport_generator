<?php
session_start();
include 'db_connect.php';

// 🔒 Secure access for superadmin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: login.php");
    exit();
}

// ✅ Handle approval, rejection, activation, or deactivation
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'], $_POST['user_id'])) {
    $userId = intval($_POST['user_id']);
    $action = $_POST['action'];

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE users SET approval_status = 'Approved', is_active = 1 WHERE id = ?");
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE users SET approval_status = 'Rejected', is_active = 0 WHERE id = ?");
    } elseif ($action === 'block') {
        $stmt = $conn->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
    } elseif ($action === 'unblock') {
        $stmt = $conn->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
    }

    if (isset($stmt)) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch all users except superadmin
$result = $conn->query("SELECT id, username, email, role, approval_status, is_active FROM users WHERE role != 'superadmin' ORDER BY id DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Approval Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }
        .btn {
            margin-right: 5px;
        }
        .table {
            margin-top: 20px;
            border-collapse: collapse;
            width: 100%;
        }
        .table th, .table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #dee2e6;
        }
        .table th {
            background-color: #343a40;
            color: white;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f2f2f2;
        }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #dc3545;
            border-radius: 4px;
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>👥 Admin Approval Panel</h2>

    <a href="superadmin_dashboard.php" class="btn btn-secondary">⬅ Back to Dashboard</a>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Approval Status</th>
                <th>Active</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($user = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($user['id']) ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['role']) ?></td>
                <td><?= htmlspecialchars($user['approval_status']) ?></td>
                <td><?= $user['is_active'] ? '✅ Active' : '🚫 Blocked' ?></td>
                <td>
                    <form method="post" style="display:inline-block;">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <?php if ($user['approval_status'] !== 'Approved'): ?>
                            <button name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                            <button name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                        <?php endif; ?>

                        <?php if ($user['is_active']): ?>
                            <button name="action" value="block" class="btn btn-warning btn-sm">Block</button>
                        <?php else: ?>
                            <button name="action" value="unblock" class="btn btn-info btn-sm">Unblock</button>
                        <?php endif; ?>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
