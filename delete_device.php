<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user'])) {
    die("Login required");
}

if (!isset($_GET['id'])) {
    die("Device ID missing.");
}

$devid = intval($_GET['id']); // ✅ convert to integer

// Optional: check if device exists
$stmt = $conn->prepare("SELECT * FROM device WHERE devid = ?");
$stmt->bind_param("i", $devid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Device not found.");
}

// Delete the device
$stmt = $conn->prepare("DELETE FROM device WHERE devid = ?");
$stmt->bind_param("i", $devid);

if ($stmt->execute()) {
    header("Location: manage_devices.php?msg=deleted");
    exit;
} else {
    die("❌ Failed to delete device.");
}
?>
