<?php
session_start();
require_once 'db_connect.php';

// ✅ Only check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// ✅ Validate and sanitize organization ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("❌ Invalid organization ID.");
}

// ✅ Delete organization using prepared statement
$sql = "DELETE FROM organizations WHERE orgid = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("❌ Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: manage_organizations.php");
    exit;
} else {
    echo "❌ Error: " . $stmt->error;
}
?>
