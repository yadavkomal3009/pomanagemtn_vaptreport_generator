<?php
include 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['device_ids'])) {
    $orgid = $_POST['orgid'];
    $poid = $_POST['poid'];
    $username = $_SESSION['username'];
    $vstatus = 'Open';
    $reportstatus = 'Draft';

    foreach ($_POST['device_ids'] as $devid) {
        if (isset($_POST['vuln_ids'][$devid])) {
            foreach ($_POST['vuln_ids'][$devid] as $vuln_id) {
                // Collect form fields (assuming they were sent via hidden or dynamically named inputs)
                $vlevel = $_POST["vlevel_{$devid}_{$vuln_id}"] ?? 'Medium';
                $vdesc = $_POST["vdesc_{$devid}_{$vuln_id}"] ?? '';
                $vimpact = $_POST["vimpact_{$devid}_{$vuln_id}"] ?? '';
                $vsolu = $_POST["vsolu_{$devid}_{$vuln_id}"] ?? '';
                $vpara = $_POST["vpara_{$devid}_{$vuln_id}"] ?? '';
                $vloc = $_POST["vloc_{$devid}_{$vuln_id}"] ?? '';
                $vimage = $_POST["vimage_{$devid}_{$vuln_id}"] ?? '';
                $vcve = $_POST["vcve_{$devid}_{$vuln_id}"] ?? '';
                $vlevelno = $_POST["vlevelno_{$devid}_{$vuln_id}"] ?? '';
                $vremarks = $_POST["vremarks_{$devid}_{$vuln_id}"] ?? '';

                // Insert into `report` table
                $stmt = $conn->prepare("INSERT INTO report 
                    (orgid, poid, did, vid, vlevel, vdesc, vimpact, vsolu, vpara, vloc, vimage, vcve, vlevelno, vstatus, vremarks, reportstatus, username) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iiiisssssssssssss", 
                    $orgid, $poid, $devid, $vuln_id, $vlevel, $vdesc, $vimpact, $vsolu, 
                    $vpara, $vloc, $vimage, $vcve, $vlevelno, $vstatus, $vremarks, $reportstatus, $username
                );
                $stmt->execute();
            }
        }
    }

    $_SESSION['status'] = "✅ Report data inserted successfully!";
    header("Location: vapt_final_report.php?orgid=$orgid&poid=$poid");
    exit();
} else {
    echo "❌ Invalid Request.";
}
?>
