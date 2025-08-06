<?php
// audit_logs.php
session_start();
require_once 'db_connect.php';

$result = $conn->query("SELECT * FROM audit_logs ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head><title>Audit Logs</title></head>
<body>
<h2>Audit Logs</h2>
<table border="1">
    <tr>
        <th>User</th><th>Action</th><th>Date/Time</th>
    </tr>
    <?php while ($log = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($log['username']) ?></td>
            <td><?= htmlspecialchars($log['action']) ?></td>
            <td><?= $log['created_at'] ?></td>
        </tr>
    <?php endwhile; ?>
</table>
</body>
</html>
