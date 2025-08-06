<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['user']['role'];
$orgid = $_SESSION['user']['orgid'] ?? null;

$poid = $_GET['id'] ?? null;
if (!$poid) {
    die("❌ PO ID missing.");
}

// Fetch PO
$stmt = $conn->prepare("SELECT * FROM po WHERE id = ?");
$stmt->bind_param("i", $poid);
$stmt->execute();
$result = $stmt->get_result();
$po = $result->fetch_assoc();

if (!$po) {
    die("❌ PO not found.");
}

// If user, check ownership
if ($role !== 'admin' && $po['orgid'] != $orgid) {
    die("⛔ Unauthorized delete.");
}

// Delete PO
$stmt = $conn->prepare("DELETE FROM po WHERE id = ?");
$stmt->bind_param("i", $poid);

if ($stmt->execute()) {
    header("Location: manage_pos.php?msg=deleted");
} else {
    die("❌ Failed to delete: " . $conn->error);
}
?>
