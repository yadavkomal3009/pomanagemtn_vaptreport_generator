<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$rid = isset($_GET['rid']) ? intval($_GET['rid']) : 0;
if (!$rid) {
    die("Invalid Report ID");
}

// Step 1: Fetch base info (orgid, poid, round, orgname, po_no)
$info_stmt = $conn->prepare("
    SELECT r.orgid, r.poid, r.round, o.orgname, p.po_no
    FROM report r
    LEFT JOIN organizations o ON r.orgid = o.orgid
    LEFT JOIN po p ON r.poid = p.poid
    WHERE r.rid = ?
    LIMIT 1
");
$info_stmt->bind_param("i", $rid);
$info_stmt->execute();
$info_result = $info_stmt->get_result();
$info = $info_result->fetch_assoc();
$info_stmt->close();

if (!$info) {
    die("Report not found.");
}

$orgid = $info['orgid'];
$poid = $info['poid'];
$round = $info['round'];
$orgname = $info['orgname'];
$po_no = $info['po_no'];

// Step 2: Get all vulnerabilities under same orgid + poid + round
$vul_stmt = $conn->prepare("
    SELECT r.*, d.devname, d.devtype, d.devip, d.devloc, v.vname
    FROM report r
    LEFT JOIN device d ON r.did = d.devid
    LEFT JOIN vul v ON r.vid = v.vid
    WHERE r.orgid = ? AND r.poid = ? AND r.round = ?
    ORDER BY r.did ASC
");
$vul_stmt->bind_param("iii", $orgid, $poid, $round);
$vul_stmt->execute();
$vul_result = $vul_stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Full VAPT Report</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9fa; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #1976d2; color: white; }
        h2, h3 { color: #333; }
        .section { margin-bottom: 30px; }
        .btn { padding: 8px 14px; background: #1976d2; color: white; text-decoration: none; border-radius: 5px; }
        .btn:hover { background: #135ba1; }
    </style>
</head>
<body>

<a href="list_reports.php" class="btn">🔙 Back to Reports</a>

<h2>🛡️ VAPT Report</h2>

<div class="section">
    <p><strong>Organization:</strong> <?= htmlspecialchars($orgname) ?></p>
    <p><strong>PO Number:</strong> <?= htmlspecialchars($po_no) ?></p>
    <p><strong>Round:</strong> <?= htmlspecialchars($round) ?></p>
</div>

<h3>Vulnerabilities Found</h3>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Device Name</th>
            <th>Type</th>
            <th>IP</th>
            <th>Location</th>
            <th>Vulnerability</th>
            <th>Severity</th>
            <th>Description</th>
            <th>Impact</th>
            <th>CVEs</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php $i = 1; while ($row = $vul_result->fetch_assoc()): ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($row['devname']) ?></td>
            <td><?= htmlspecialchars($row['devtype']) ?></td>
            <td><?= htmlspecialchars($row['devip']) ?></td>
            <td><?= htmlspecialchars($row['devloc']) ?></td>
            <td><?= htmlspecialchars($row['vname']) ?></td>
            <td><?= htmlspecialchars($row['vlevel']) ?></td>
            <td><?= htmlspecialchars($row['vdesc']) ?></td>
            <td><?= htmlspecialchars($row['vimpact']) ?></td>
            <td><?= htmlspecialchars($row['vcve']) ?></td>
            <td><?= htmlspecialchars($row['vuln_status']) ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>
