<?php
session_start();
require 'db_connect.php';

// ✅ Only logged-in users can access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user']['user_id'];

// 🔽 Fetch user's requests
$query = "SELECT r.*, p.po_no 
          FROM po_requests r
          JOIN po p ON r.poid = p.poid
          WHERE r.userid = $user_id
          ORDER BY r.created_at DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My PO Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3 class="mb-4">📄 My PO Change Requests</h3>

    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>PO Number</th>
                <th>Request Type</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Requested At</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['po_no']) ?></td>
                    <td><?= ucfirst($row['type']) ?></td>
                    <td><?= nl2br(htmlspecialchars($row['reason'])) ?></td>
                    <td>
                        <?php
                            $status = $row['status'];
                            $badgeClass = $status === 'approved' ? 'success' : ($status === 'rejected' ? 'danger' : 'warning');
                            echo "<span class='badge bg-$badgeClass'>" . ucfirst($status) . "</span>";
                        ?>
                    </td>
                    <td><?= $row['created_at'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a href="user_dashboard.php" class="btn btn-secondary mt-3">⬅ Back to Dashboard</a>
</div>
</body>
</html>
