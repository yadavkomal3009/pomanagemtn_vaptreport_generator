<?php
require 'db_connect.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$query = "SELECT r.rid, r.poid, r.report_file, r.reportstatus, p.po_no, u.username
          FROM report r 
          JOIN po p ON r.poid = p.id
          JOIN users u ON r.username = u.username
          ORDER BY r.created_at DESC";


$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>📁 Submitted Reports</h2>
    <table class="table table-bordered mt-4">
        <thead>
            <tr>
                <th>Report ID</th>
                <th>User</th>
                <th>PO ID</th>
                <th>File</th>
                <th>Status</th>
                <th>Submitted At</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= $row['poid'] ?></td>
                    <td><a href="uploads/reports/<?= $row['report_file'] ?>" target="_blank">View</a></td>
                    <td><?= $row['status'] ?></td>
                    <td><?= $row['created_at'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
