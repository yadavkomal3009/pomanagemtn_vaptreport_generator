<?php 
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['superadmin', 'admin', 'user'])) {
    header("Location: login.php");
    exit();
}

$rid = isset($_GET['rid']) ? intval($_GET['rid']) : 0;
if (!$rid) {
    die("Invalid Report ID");
}

// Fetch report details
$stmt = $conn->prepare("
    SELECT r.rid, r.orgid, r.poid, r.round, o.orgname, p.po_no
    FROM report r
    LEFT JOIN organizations o ON r.orgid = o.orgid
    LEFT JOIN po p ON r.poid = p.poid
    WHERE r.rid = ?
");
$stmt->bind_param("i", $rid);
$stmt->execute();
$result = $stmt->get_result();
$report = $result->fetch_assoc();
$stmt->close();

if (!$report) {
    die("Report not found");
}

// Handle form submission to add vulnerability
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_vul'])) {
    $new_vid = intval($_POST['vid']);

    // Fetch original report row
    $fetch = $conn->prepare("SELECT * FROM report WHERE rid = ?");
    $fetch->bind_param("i", $rid);
    $fetch->execute();
    $report_row = $fetch->get_result()->fetch_assoc();
    $fetch->close();

    if (!$report_row) {
        $errorMsg = "❌ Original report not found.";
    } else {
        $orgid = $report_row['orgid'];
        $poid = $report_row['poid'];
        $did = $report_row['did'];
        $round = $report_row['round'];
        $username = $_SESSION['user']['username'] ?? 'admin';
        $created_at = date('Y-m-d H:i:s');

        // Check if already added
        $check = $conn->prepare("SELECT 1 FROM report WHERE orgid=? AND poid=? AND did=? AND vid=? AND round=?");
        $check->bind_param("iiiii", $orgid, $poid, $did, $new_vid, $round);
        $check->execute();
        $check->store_result();

        if ($check->num_rows === 0) {
            // Fetch vuln info
            $vul_stmt = $conn->prepare("SELECT vlevel, vpara, vdesc, vimpact, vcve, vsolu FROM vul WHERE vid = ?");
            $vul_stmt->bind_param("i", $new_vid);
            $vul_stmt->execute();
            $vul = $vul_stmt->get_result()->fetch_assoc();
            $vul_stmt->close();

            if (!$vul) {
                $errorMsg = "❌ Invalid vulnerability selected.";
            } else {
                // Insert new row with new vid
                $insert = $conn->prepare("INSERT INTO report (orgid, poid, did, vid, vlevel, vpara, vdesc, vimpact, vcve, vsolu, created_at, username, round, reportstatus, vuln_status, certificate_generated)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'Open', 0)");

                $insert->bind_param("iiiissssssssi", $orgid, $poid, $did, $new_vid,
                    $vul['vlevel'], $vul['vpara'], $vul['vdesc'], $vul['vimpact'],
                    $vul['vcve'], $vul['vsolu'], $created_at, $username, $round);

                if ($insert->execute()) {
                    $successMsg = "✅ Vulnerability added successfully.";
                } else {
                    $errorMsg = "❌ Failed to add vulnerability.";
                }
                $insert->close();
            }
        } else {
            $errorMsg = "⚠️ This vulnerability is already assigned.";
        }

        $check->close();
    }
}

// Fetch already assigned vulnerabilities
$assigned = [];
$assigned_stmt = $conn->prepare("
    SELECT v.vname FROM report rv
    JOIN vul v ON rv.vid = v.vid
    WHERE rv.orgid = ? AND rv.poid = ? AND rv.did = ? AND rv.round = ?
");
$assigned_stmt->bind_param("iiii", $report['orgid'], $report_row['poid'], $report_row['did'], $report['round']);
$assigned_stmt->execute();
$assigned_result = $assigned_stmt->get_result();
while ($row = $assigned_result->fetch_assoc()) {
    $assigned[] = $row['vname'];
}
$assigned_stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Vulnerability - Report <?= $rid ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f0f2f5; }
        .btn { padding: 6px 12px; background: #1976d2; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #135ba1; }
        .success { background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border-left: 5px solid #28a745; }
        .error { background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-left: 5px solid #dc3545; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        th, td { padding: 8px; border: 1px solid #ccc; text-align: center; font-size: 14px; }
        th { background: #4a69bd; color: white; }
        #searchInput { padding: 6px; width: 250px; }
    </style>
</head>
<body>

<a href="list_reports.php" class="btn">🔙 Back to Reports</a>

<h2>Add Vulnerability to Report ID <?= $rid ?></h2>

<?php if (!empty($successMsg)): ?>
    <div class="success"><?= $successMsg ?></div>
<?php endif; ?>

<?php if (!empty($errorMsg)): ?>
    <div class="error"><?= $errorMsg ?></div>
<?php endif; ?>

<p><strong>Organization:</strong> <?= htmlspecialchars($report['orgname'] ?? 'N/A') ?></p>
<p><strong>PO No:</strong> <?= htmlspecialchars($report['po_no'] ?? 'N/A') ?></p>
<p><strong>Round:</strong> <?= htmlspecialchars($report['round'] ?? 'N/A') ?></p>

<h3>🛡️ Available Vulnerabilities</h3>
<input type="text" id="searchInput" placeholder="Search..." onkeyup="filterVulns()"><br><br>

<table id="vulTable">
    <thead>
        <tr>
            <th>ID</th><th>Name</th><th>Parameter</th><th>Description</th><th>Impact</th><th>CVE</th><th>Solution</th><th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $vul_result = $conn->query("SELECT * FROM vul ORDER BY vname ASC");
        while ($v = $vul_result->fetch_assoc()):
        ?>
        <tr>
            <td><?= $v['vid'] ?></td>
            <td><?= htmlspecialchars($v['vname']) ?></td>
            <td><?= htmlspecialchars($v['vpara']) ?></td>
            <td><?= htmlspecialchars($v['vdesc']) ?></td>
            <td><?= htmlspecialchars($v['vimpact']) ?></td>
            <td><?= htmlspecialchars($v['vcve']) ?></td>
            <td><?= htmlspecialchars($v['vsolu']) ?></td>
            <td>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="vid" value="<?= $v['vid'] ?>">
                    <button type="submit" name="add_vul" class="btn">➕ Add</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php if (!empty($assigned)): ?>
    <h3>✔️ Already Assigned Vulnerabilities</h3>
    <ul>
        <?php foreach ($assigned as $vname): ?>
            <li><?= htmlspecialchars($vname) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<script>
function filterVulns() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#vulTable tbody tr');
    rows.forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(search) ? '' : 'none';
    });
}
</script>

</body>
</html>
