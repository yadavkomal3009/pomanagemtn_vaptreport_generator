<?php
include 'db_connect.php';
session_start();

$orgid = intval($_GET['orgid'] ?? 0);
$poid = intval($_GET['poid'] ?? 0);

if (!$orgid || !$poid) {
    echo "<p style='color:red;'>❌ Missing Org ID or PO ID.</p>";
    exit;
}

// Handle inline edits
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_rid'])) {
    $rid = intval($_POST['edit_rid']);
    $vdesc = $conn->real_escape_string($_POST['vdesc']);
    $vimpact = $conn->real_escape_string($_POST['vimpact']);
    $vsolu = $conn->real_escape_string($_POST['vsolu']);
    $vlevel = $conn->real_escape_string($_POST['vlevel']);
    $vuln_status = $conn->real_escape_string($_POST['vuln_status']);

    $conn->query("UPDATE report SET vdesc='$vdesc', vimpact='$vimpact', vsolu='$vsolu', vuln_status='$vuln_status' WHERE rid=$rid");
    $conn->query("UPDATE vul v JOIN report r ON v.vid = r.vid SET v.vlevel='$vlevel' WHERE r.rid=$rid");

    header("Location: vapt_final_report.php?orgid=$orgid&poid=$poid");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>VAPT Final Report</title>
<style>
body { font-family: 'Segoe UI', sans-serif; background: #f9f9f9; margin: 20px; }
.container { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); max-width: 950px; margin: auto; }
h2, h3 { margin-top: 20px; color: #333; }
.card { background: #fff; padding: 15px 20px; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.05); margin-bottom: 20px; }
.details-row { display: flex; flex-wrap: wrap; margin: 5px 0; }
.details-label { flex: 1; font-weight: bold; color: #555; }
.details-value { flex: 2; color: #333; }
table { border-collapse: collapse; width: 100%; margin-bottom: 20px; background: #fff; }
th, td { border: 1px solid #ddd; padding: 8px; vertical-align: top; }
th { background: #f0f0f0; }
textarea, select { width: 100%; padding: 6px; margin-top: 5px; }
button { padding: 8px 14px; margin: 8px 0; background: #007BFF; color: #fff; border: none; border-radius: 5px; cursor: pointer; }
button:hover { background: #0056b3; }
a.button-link { background: #007bff; color: #fff; padding: 10px 15px; border-radius: 5px; text-decoration: none; display: inline-block; margin: 10px 0; }
.info-message {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeeba;
    padding: 10px 15px;
    border-radius: 5px;
    margin: 10px 0;
    font-weight: bold;
}
.print-only { display: none; }
.screen-only { display: block; }
@media print {
    .print-only { display: block; }
    .screen-only, button, .button-link { display: none !important; }
    * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .card { page-break-inside: avoid; break-inside: avoid; }
}
</style>
</head>
<body>
<div class='container'>
    <button onclick='window.print()'>🖨️ Print Report</button>
    <a href='javascript:history.back()' class='button-link'>🔙 Back</a>
    <p class="info-message">🟡 Already report created</p>

<?php
$res = $conn->query("SELECT r.*, o.orgname, o.address, o.contact, o.email, p.po_no, p.po_date, d.devid, d.devname, d.devtype, d.devip, d.devloc, v.vname, v.vlevel, v.vid FROM report r JOIN organizations o ON r.orgid = o.orgid JOIN purchase_orders p ON r.poid = p.po_id JOIN device d ON r.did = d.devid JOIN vul v ON r.vid = v.vid WHERE r.orgid = $orgid AND r.poid = $poid AND (r.vuln_status != 'Closed' OR r.vuln_status IS NULL) ORDER BY d.devname ASC, FIELD(v.vlevel, 'Critical', 'High', 'Medium', 'Low', 'Informational')");

if (!$res || $res->num_rows === 0) {
    echo "<p style='color:red;'>⚠️ No report data found for this Org and PO.</p>";
    exit;
}

$row = $res->fetch_assoc();

echo "<h2>🛡️ VAPT Final Report</h2>
<div class='card'>
<h3>Organization Details</h3>
<div class='details-row'><div class='details-label'>Name:</div><div class='details-value'>{$row['orgname']}</div></div>
<div class='details-row'><div class='details-label'>Address:</div><div class='details-value'>{$row['address']}</div></div>
<div class='details-row'><div class='details-label'>Contact:</div><div class='details-value'>{$row['contact']}</div></div>
<div class='details-row'><div class='details-label'>Email:</div><div class='details-value'>{$row['email']}</div></div>
</div>
<div class='card'>
<h3>Purchase Order Details</h3>
<div class='details-row'><div class='details-label'>PO No:</div><div class='details-value'>{$row['po_no']}</div></div>
<div class='details-row'><div class='details-label'>PO Date:</div><div class='details-value'>{$row['po_date']}</div></div>
</div>
<h3>Vulnerabilities Found</h3>";

$res->data_seek(0);
$grouped = [];
while ($row = $res->fetch_assoc()) {
    $deviceKey = $row['devid'];
    $grouped[$deviceKey]['device'] = [
        'name' => $row['devname'],
        'type' => $row['devtype'],
        'ip'   => $row['devip'],
        'loc'  => $row['devloc']
    ];
    $grouped[$deviceKey]['vulnerabilities'][] = $row;
}

$vulnCount = 1;
foreach ($grouped as $device) {
    echo "<div class='card'>";
    echo "<h3>🖥️ " . htmlspecialchars($device['device']['name']) . "</h3>";
    echo "<p><strong>Device Type:</strong> {$device['device']['type']}</p>";
    echo "<p><strong>IP Address:</strong> {$device['device']['ip']}</p>";
    echo "<p><strong>Location:</strong> {$device['device']['loc']}</p>";

    foreach ($device['vulnerabilities'] as $vuln) {
        $severity_color = match($vuln['vlevel']) {
            'Critical' => '#ff9999',
            'High' => '#ffcccc',
            'Medium' => '#fff0b3',
            'Low' => '#ccffcc',
            'Informational' => '#e0e0e0',
            default => '#ffffff'
        };

        echo "<div class='vuln-block'>
        <form method='POST' class='screen-only'>
        <table>
        <tr><th colspan='2'>🔹 Vulnerability #$vulnCount</th></tr>
        <tr><th>Vulnerability Name</th><td>{$vuln['vname']}</td></tr>
        <tr><th>Severity</th><td style='background:$severity_color;'>
            <select name='vlevel'>
                <option value='Critical' ".($vuln['vlevel']=='Critical'?'selected':'').">Critical</option>
                <option value='High' ".($vuln['vlevel']=='High'?'selected':'').">High</option>
                <option value='Medium' ".($vuln['vlevel']=='Medium'?'selected':'').">Medium</option>
                <option value='Low' ".($vuln['vlevel']=='Low'?'selected':'').">Low</option>
                <option value='Informational' ".($vuln['vlevel']=='Informational'?'selected':'').">Informational</option>
            </select>
        </td></tr>
        <tr><th>Description</th><td><textarea name='vdesc' rows='3'>".htmlspecialchars($vuln['vdesc'])."</textarea></td></tr>
        <tr><th>Impact</th><td><textarea name='vimpact' rows='3'>".htmlspecialchars($vuln['vimpact'])."</textarea></td></tr>
        <tr><th>Recommendation</th><td><textarea name='vsolu' rows='3'>".htmlspecialchars($vuln['vsolu'])."</textarea></td></tr>
        <tr><th>Status</th><td>
            <select name='vuln_status'>
                <option value='Open' ".($vuln['vuln_status']=='Open'?'selected':'').">Open</option>
                <option value='Closed' ".($vuln['vuln_status']=='Closed'?'selected':'').">Closed</option>
            </select>
        </td></tr>
        <tr><td colspan='2' style='text-align:right;'>
            <input type='hidden' name='edit_rid' value='{$vuln['rid']}'>
            <button type='submit'>💾 Save Changes</button>
        </td></tr>
        </table>
        </form>

        <div class='print-only'>
        <table>
        <tr><th colspan='2'>🔹 Vulnerability #$vulnCount</th></tr>
        <tr><th>Vulnerability Name</th><td>{$vuln['vname']}</td></tr>
        <tr><th>Severity</th><td style='background:$severity_color;'>{$vuln['vlevel']}</td></tr>
        <tr><th>Description</th><td>{$vuln['vdesc']}</td></tr>
        <tr><th>Impact</th><td>{$vuln['vimpact']}</td></tr>
        <tr><th>Recommendation</th><td>{$vuln['vsolu']}</td></tr>
        <tr><th>Status</th><td>{$vuln['vuln_status']}</td></tr>
        </table>
        </div>
        </div>";

        $vulnCount++;
    }
    echo "</div>";
}

echo "<p><a href='device_wise_vapt_report.php?orgid=$orgid&poid=$poid' target='_blank' class='button-link'>📊 View Device-wise Graphical Report</a></p>";
echo "<p><strong>Total Vulnerabilities:</strong> " . ($vulnCount - 1) . "</p>";
echo "</div></body></html>";
?>
