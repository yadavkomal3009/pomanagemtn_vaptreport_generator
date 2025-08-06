<?php
// FILE: org_device_menu.php
session_start();
include '../../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$result = $conn->query("SELECT orgid, orgname FROM organizations");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Select Organization - Add Device</title>
</head>
<body>
    <h2>📋 Select Organization to Add Device</h2>
    <ul>
        <?php while ($row = $result->fetch_assoc()): ?>
            <li>
                <strong><?= htmlspecialchars($row['orgname']) ?></strong>
                — <a href="add_device.php?orgid=<?= $row['orgid'] ?>">➕ Add Device</a>
            </li>
        <?php endwhile; ?>
    </ul>
</body>
</html>
