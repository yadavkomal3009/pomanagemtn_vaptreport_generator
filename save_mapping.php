<?php
session_start();
include '../db_connect.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$user_id = $_SESSION['user_id'] ?? null;
$orgid = $_SESSION['orgid'] ?? null;
$po_id = $_SESSION['po_id'] ?? ($_POST['po_id'] ?? null); // from session or fallback to POST
$device_ids = $_POST['device_ids'] ?? [];
$vuln_ids = $_POST['vuln_ids'] ?? [];
$audit_round = $_POST['audit_round'] ?? 'Round 1';
$username = $_SESSION['username'] ?? 'unknown_user';

// ✅ Validate
if (!$user_id || !$orgid || !$po_id || empty($device_ids) || empty($vuln_ids)) {
    die("❌ Session or form data incomplete. Please go back.");
}

// ✅ Insert each mapping if not exists
foreach ($device_ids as $did) {
    foreach ($vuln_ids as $vid) {
        // Avoid duplicate
        $check = $conn->prepare("SELECT rid FROM report WHERE orgid=? AND did=? AND vid=? AND round=? AND po_id=?");
        $check->bind_param("iiisi", $orgid, $did, $vid, $audit_round, $po_id);
        $check->execute();
        $exists = $check->get_result()->fetch_assoc();
        $check->close();

        if (!$exists) {
            $stmt = $conn->prepare("
                INSERT INTO report (orgid, did, vid, po_id, round, reportstatus, created_at, username) 
                VALUES (?, ?, ?, ?, ?, 'Pending', NOW(), ?)
            ");
            $stmt->bind_param("iiiiss", $orgid, $did, $vid, $po_id, $audit_round, $username);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// ✅ Redirect to final report
header("Location: vapt_final_report.php");
exit();
?>
