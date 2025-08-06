<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die("❌ Unauthorized access.");
}

$entry_id = $_GET['entry_id'] ?? null;
$report_id = $_GET['report_id'] ?? null;
$devid = $_GET['device_id'] ?? null;
$vuln_id = $_GET['vuln_id'] ?? null;

if (!$entry_id || !$report_id || !$devid || !$vuln_id) {
    die("❌ Invalid parameters.");
}

$insert = $conn->prepare("INSERT INTO vapt_report_vulns (report_id, vuln_id, devid, audit_round, severity, description, recommendation) VALUES (?, ?, ?, ?, ?, ?, ?)");
$insert->bind_param("iiissss", $report_id, $vuln_id, $devid, $audit_round, $severity, $description, $recommendation);


// Fetch report entry
$stmt = $conn->prepare("
SELECT vr.id AS entry_id, vr.audit_round, v.vuln_id, v.vuln_no, v.name AS vuln_name, v.description, v.recommendation, v.severity,
       d.devname, d.devip, o.orgname, p.po_no, p.po_date
FROM vapt_report_vulns vr
JOIN vulnerabilities v ON vr.vuln_id = v.vuln_id
JOIN vapt_reports r ON vr.report_id = r.report_id
JOIN org o ON r.orgid = o.orgid
JOIN purchase_orders p ON r.po_id = p.po_id
LEFT JOIN device d ON vr.devid = d.devid
WHERE vr.id = ? AND vr.report_id = ? AND vr.vuln_id = ? AND vr.devid = ?
");
$stmt->bind_param("iiii", $entry_id, $report_id, $vuln_id, $devid);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    die("❌ Entry not found.");
}

// Save edits
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $severity = $_POST['severity'] ?? '';
    $description = $_POST['description'] ?? '';
    $recommendation = $_POST['recommendation'] ?? '';

    $update = $conn->prepare("UPDATE vulnerabilities SET severity = ?, description = ?, recommendation = ? WHERE vuln_id = ?");
    $update->bind_param("sssi", $severity, $description, $recommendation, $vuln_id);
    $update->execute();
    $update->close();

    echo "<script>alert('✅ Entry updated successfully.'); location.href='vapt_final_report.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>✏️ Edit Report Entry</title>
    <style>
        body { font-family: Arial; padding: 30px; background: #f9f9f9; }
        input, textarea, select, button { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; }
        label { font-weight: bold; }
        .btn { background: #007bff; color: white; border: none; cursor: pointer; }
        .btn:hover { background: #0056b3; }
        .danger { background: red; }
    </style>
</head>
<body>

<h2>✏️ Edit Entry #<?= htmlspecialchars($entry_id) ?></h2>
<p><strong>Organization:</strong> <?= $data['orgname'] ?></p>
<p><strong>PO Number:</strong> <?= $data['po_no'] ?> (<?= $data['po_date'] ?>)</p>
<p><strong>Device:</strong> <?= $data['devname'] ?> (<?= $data['devip'] ?>)</p>
<p><strong>Vulnerability:</strong> <?= $data['vuln_no'] ?> - <?= $data['vuln_name'] ?></p>

<form method="POST">
    <label>Severity:</label>
    <select name="severity" required>
        <option <?= $data['severity'] === 'Critical' ? 'selected' : '' ?>>Critical</option>
        <option <?= $data['severity'] === 'High' ? 'selected' : '' ?>>High</option>
        <option <?= $data['severity'] === 'Medium' ? 'selected' : '' ?>>Medium</option>
        <option <?= $data['severity'] === 'Low' ? 'selected' : '' ?>>Low</option>
    </select>

    <label>Description:</label>
    <textarea name="description" rows="4"><?= htmlspecialchars($data['description']) ?></textarea>

    <label>Recommendation:</label>
    <textarea name="recommendation" rows="4"><?= htmlspecialchars($data['recommendation']) ?></textarea>

    <button class="btn" type="submit">💾 Save</button>
    <a href="vapt_final_report.php" class="btn">⬅ Back</a>
</form>

</body>
</html>
