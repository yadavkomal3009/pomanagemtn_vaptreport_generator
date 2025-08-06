<?php
session_start();
require_once 'db_connect.php';

// 🔒 Allow only superadmin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'superadmin') {
    echo "⛔ Access denied.";
    exit;
}

// Your DB credentials from db_connect.php
$host = 'localhost';
$user = 'your_db_username';
$pass = 'your_db_password';
$db   = 'po_management';

// Create backup directory if not exists
$backupDir = __DIR__ . '/backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$message = "";

if (isset($_POST['backup'])) {
    $timestamp = date("Y-m-d_H-i-s");
    $backupFile = $backupDir . "/backup_$timestamp.sql";

    $command = "mysqldump -u {$user} -p'{$pass}' {$db} > {$backupFile} 2>&1";
    $output = shell_exec($command);

    if (file_exists($backupFile)) {
        $downloadLink = "backups/backup_$timestamp.sql";
        $message = "<p style='color:green;'>✅ Backup created: <a href='$downloadLink' download>$downloadLink</a></p>";
    } else {
        $message = "<p style='color:red;'>❌ Backup failed. Please check server permissions.</p>";
    }
}

if (isset($_FILES['restore_file'])) {
    $file = $_FILES['restore_file']['tmp_name'];

    if (is_uploaded_file($file)) {
        $command = "mysql -u {$user} -p'{$pass}' {$db} < {$file} 2>&1";
        $output = shell_exec($command);
        $message = "<p style='color:green;'>✅ Database restored from uploaded file.</p>";
    } else {
        $message = "<p style='color:red;'>❌ Failed to upload SQL file.</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>📦 Backup & Restore</title>
</head>
<body>
    <h2>📦 Database Backup & Restore (Superadmin Only)</h2>

    <?= $message ?>

    <form method="POST">
        <button name="backup" type="submit">📤 Create Backup</button>
    </form>

    <hr>

    <form method="POST" enctype="multipart/form-data">
        <label>📥 Upload SQL file to restore:</label><br><br>
        <input type="file" name="restore_file" required><br><br>
        <button type="submit">♻️ Restore Database</button>
    </form>
</body>
</html>
