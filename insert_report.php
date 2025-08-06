<?php
include '../db_connect.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $device_id = $_POST['device_id']; $vuln_id = $_POST['vuln_id']; $findings = $_POST['findings'];
    $stmt = $conn->prepare("INSERT INTO reports (device_id, vuln_id, findings) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $device_id, $vuln_id, $findings); $stmt->execute();
    echo "Report inserted."; }
?>
<form method="POST">
Device ID: <input type="text" name="device_id"><br>
Vulnerability ID: <input type="text" name="vuln_id"><br>
Findings: <textarea name="findings"></textarea><br>
<button type="submit">Insert Report</button>
</form>