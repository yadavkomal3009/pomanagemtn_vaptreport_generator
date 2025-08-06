<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['username'] !== 'superadmin_') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['rid']) && isset($_GET['status'])) {
    $rid = intval($_GET['rid']);
    $status = $_GET['status'];

    $allowed = ['Approved', 'Rejected'];
    if (in_array($status, $allowed)) {
        $stmt = $conn->prepare("UPDATE report SET vstatus = ? WHERE rid = ?");
        $stmt->bind_param("si", $status, $rid);
        $stmt->execute();
        $stmt->close();
    }
}

header("Location: view_reports.php");
exit();
?>
