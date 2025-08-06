<?php
include 'db_connect.php';
session_start();

$rid = $_GET['rid'] ?? 0;
if ($rid) {
    $stmt = $conn->prepare("UPDATE report SET reportstatus='Accepted' WHERE rid=?");
    $stmt->bind_param("i", $rid);
    $stmt->execute();
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
