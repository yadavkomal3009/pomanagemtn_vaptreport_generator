<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'superadmin') {
    die("❌ Access denied.");
}

// Handle approval or denial
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = intval($_POST['request_id']);
    $action = $_POST['action'];

    // Get user_id from request
    $res = mysqli_query($conn, "SELECT user_id FROM user_requests WHERE id = $requestId");
    if ($row = mysqli_fetch_assoc($res)) {
        $userId = $row['user_id'];

        if ($action === 'approve') {
            // Mark user active
            mysqli_query($conn, "UPDATE users SET is_active = 1, approval_status = 'Approved' WHERE user_id = $userId");
            mysqli_query($conn, "UPDATE user_requests SET status = 'Approved' WHERE id = $requestId");
        } elseif ($action === 'deny') {
            mysqli_query($conn, "UPDATE users SET approval_status = 'Denied' WHERE user_id = $userId");
            mysqli_query($conn, "UPDATE user_requests SET status = 'Denied' WHERE id = $requestId");
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Access Requests - Superadmin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            padding: 30px;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        table {
            width: 90%;
            margin: auto;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        form {
            display: inline;
        }

        .btn {
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
        }

        .approve { background: #28a745; color: white; }
        .deny { background: #dc3545; color: white; }

        .status-approved { color: green; font-weight: bold; }
        .status-denied { color: red; font-weight: bold; }
        .status-pending { color: orange; font-weight: bold; }
    </style>
</head>
<body>

<h2>📝 User Access Requests</h2>

<table>
    <thead>
        <tr>
            <th>Request ID</th>
            <th>User ID</th>
            <th>Username</th>
            <th>Status</th>
            <th>Requested At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $result = mysqli_query($conn, "
        SELECT ur.id, ur.user_id, ur.status, ur.requested_at, u.username 
        FROM user_requests ur
        JOIN users u ON ur.user_id = u.user_id
        ORDER BY ur.requested_at DESC
    ");

    while ($row = mysqli_fetch_assoc($result)) {
        $statusClass = 'status-' . strtolower($row['status']);
        echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['user_id']}</td>
            <td>" . htmlspecialchars($row['username']) . "</td>
            <td class='$statusClass'>{$row['status']}</td>
            <td>{$row['requested_at']}</td>
            <td>";

        if ($row['status'] === 'Pending') {
            echo "<form method='POST'>
                    <input type='hidden' name='request_id' value='{$row['id']}'>
                    <button type='submit' name='action' value='approve' class='btn approve'>✅ Approve</button>
                    <button type='submit' name='action' value='deny' class='btn deny'>❌ Deny</button>
                  </form>";
        } else {
            echo "—";
        }

        echo "</td></tr>";
    }
    ?>
    </tbody>
</table>

</body>
</html>
