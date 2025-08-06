<?php
session_start();
include '../db_connect.php';

// Ensure required session values
if (!isset($_SESSION['orgid']) || !isset($_SESSION['po_id']) || !isset($_SESSION['device_ids'])) {
    die("Missing session data.");
}

// Required fields
$orgid = $_SESSION['orgid'];
$po_id = $_SESSION['po_id'];
$device_ids = $_SESSION['device_ids'];
$vuln_ids = $_POST['vuln_ids'] ?? [];
$round = $_SESSION['audit_round'] ?? 'Round 1';

if (empty($vuln_ids)) {
    die("No vulnerabilities selected.");
}

// Save mappings into `report` table
foreach ($device_ids as $did) {
    foreach ($vuln_ids as $vid) {
        $stmt = $conn->prepare("INSERT INTO report (orgid, po_id, did, vid, round, reportstatus, created_at) VALUES (?, ?, ?, ?, ?, 'Pending', NOW())");
        $stmt->bind_param("sssss", $orgid, $po_id, $did, $vid, $round);
        $stmt->execute();
        $stmt->close();
    }
}

// ✅ Redirect to final report
header("Location: ../po/vapt_final_report.php");
exit();
