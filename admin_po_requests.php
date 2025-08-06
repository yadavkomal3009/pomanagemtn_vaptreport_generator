<?php
session_start();
require 'db_connect.php';

// ✅ Ensure only admin can access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ✅ Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['request_id'])) {
    $request_id = intval($_POST['request_id']);
    $action = $_POST['action'] === 'approve' ? 'approved' : 'rejected';

    $update = "UPDATE po_requests SET status = '$action' WHERE id = $request_id";
    mysqli_query($conn, $update);
}

// 🔽 Fetch all requests
$query = "SELECT r.*, u.username, p.po_no 
          FROM po_requests r 
          JOIN users u ON r.userid = u.id
          JOIN po p ON r.poid = p.id
          ORDER BY r.created_at DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>PO Requests (Admin)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3>📋 PO Edit/Delete Requests</h3>
    <table class="table table-bordered mt-4">
        <thead class="table-light">
            <tr>
                <th>User</th>
                <th>PO Number</th>
                <th>Type</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Requested At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['po_no']) ?></td>
                <td><?= ucfirst($row['type']) ?></td>
                <td><?= nl2br(htmlspecialchars($row['reason'])) ?></td>
                <td>
                    <span class="badge bg-<?= 
                        $row['status'] === 'approved' ? 'success' : 
                        ($row['status'] === 'rejected' ? 'danger' : 'warning') ?>">
                        <?= ucfirst($row['status']) ?>
                    </span>
                </td>
                <td><?= $row['created_at'] ?></td>
                <td>
                    <?php if ($row['status'] === 'pending'): ?>
                        <form method="POST" class="d-flex gap-2">
                            <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                            <button name="action" value="approve" class="btn btn-sm btn-success">Approve</button>
                            <button name="action" value="reject" class="btn btn-sm btn-danger">Reject</button>
                        </form>
                    <?php else: ?>
                        <em>No action</em>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <a href="admin_dashboard.php" class="btn btn-secondary mt-3">⬅ Back to Dashboard</a>
</div>
</body>
</html>
