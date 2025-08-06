<?php
session_start();
include '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vuln_ids']) && is_array($_POST['vuln_ids'])) {
    $_SESSION['vuln_ids'] = $_POST['vuln_ids'];

    $orgid = $_SESSION['orgid'] ?? '';
    $po_id = $_SESSION['po_id'] ?? 0;
    $device_ids = $_SESSION['device_ids'] ?? [];
    $vuln_ids = $_POST['vuln_ids'];
    $round = $_SESSION['audit_round'] ?? 'Round 1';

    foreach ($device_ids as $did) {
        foreach ($vuln_ids as $vid) {
            $check = $conn->prepare("SELECT 1 FROM report WHERE orgid = ? AND po_id = ? AND did = ? AND vid = ? AND round = ?");
            $check->bind_param("siiss", $orgid, $po_id, $did, $vid, $round);
            $check->execute();
            $check->store_result();

            if ($check->num_rows == 0) {
                $stmt = $conn->prepare("INSERT INTO report (orgid, po_id, did, vid, round, reportstatus, created_at) VALUES (?, ?, ?, ?, ?, 'Pending', NOW())");
                $stmt->bind_param("siiss", $orgid, $po_id, $did, $vid, $round);
                $stmt->execute();
                $stmt->close();
            }

            $check->close();
        }
    }

    // ✅ Redirect to report page
    header("Location: vapt_final_report.php");
    exit();
} else {
    // ❌ No vulnerabilities selected
    header("Location: select_vulnerability.php");
    exit();
}
