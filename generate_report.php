<?php 
include 'db_connect.php';
session_start();

$submitted = $_SERVER['REQUEST_METHOD'] === 'POST';

$orgid = $submitted ? intval($_POST['orgid']) : 1;
$poid = $submitted ? intval($_POST['poid']) : 2;
$round = $submitted && isset($_POST['round']) ? intval($_POST['round']) : 1;

$vulns = $submitted && isset($_POST['vulns']) && is_array($_POST['vulns']) ? $_POST['vulns'] : [];
$username = $_SESSION['username'] ?? 'admin';
$created_at = date('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Generate VAPT Report</title>
  <style>
    body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; padding: 40px; }
    .form-box, .container {
      background: white; padding: 30px; max-width: 700px; margin: auto;
      border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    label { display: block; margin-top: 15px; font-weight: bold; }
    select, input[type="submit"] {
      margin-top: 5px; padding: 8px; width: 100%;
      border-radius: 4px; border: 1px solid #ccc;
    }
    input[type="submit"] {
      background: #1976d2; color: white; font-weight: bold; cursor: pointer;
    }
    input[type="submit"]:hover { background: #0d47a1; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: orange; font-weight: bold; }
    .button {
      background: #1976d2; color: white; border: none; padding: 10px 18px;
      border-radius: 5px; cursor: pointer; text-decoration: none;
      font-weight: 600; margin: 10px 5px; display: inline-block;
    }
    fieldset {
      margin-top: 20px; border: 1px solid #ccc;
      padding: 10px; border-radius: 5px;
    }
    legend { font-weight: bold; }
  </style>
</head>
<body>

<?php if (!$submitted): ?>
  <!-- ======= Form Page ======= -->
  <div class="form-box">
    <h2>Select Vulnerabilities to Generate VAPT Report</h2>
    <form method="POST">
      <input type="hidden" name="orgid" value="<?= $orgid ?>">
      <input type="hidden" name="poid" value="<?= $poid ?>">

      <label for="round">Select Round:</label>
      <select name="round" id="round">
        <option value="1">Round 1</option>
        <option value="2">Round 2</option>
        <option value="3">Round 3</option>
      </select>

      <!-- Example Devices -->
      <fieldset>
        <legend>Device ID 10</legend>
        <input type="hidden" name="vulns[0][devid]" value="10">
        <input type="hidden" name="vulns[0][vid]" value="5">
        <label>Vulnerability 5</label>
        <select name="vulns[0][status]">
          <option value="Open">Open</option>
          <option value="Closed">Closed</option>
        </select>

        <input type="hidden" name="vulns[1][devid]" value="10">
        <input type="hidden" name="vulns[1][vid]" value="6">
        <label>Vulnerability 6</label>
        <select name="vulns[1][status]">
          <option value="Open">Open</option>
          <option value="Closed">Closed</option>
        </select>
      </fieldset>

      <fieldset>
        <legend>Device ID 11</legend>
        <input type="hidden" name="vulns[2][devid]" value="11">
        <input type="hidden" name="vulns[2][vid]" value="7">
        <label>Vulnerability 7</label>
        <select name="vulns[2][status]">
          <option value="Open">Open</option>
          <option value="Closed">Closed</option>
        </select>
      </fieldset>

      <input type="submit" value="Generate Report">
    </form>
  </div>

<?php else: ?>
  <!-- ======= Report Processing Section ======= -->
  <div class="container">
    <h2>Generating Report...</h2>
<?php
flush();
ob_flush();
sleep(1);

if (!$orgid || !$poid || empty($vulns)) {
    echo "<p class='error'>\u26a0\ufe0f Missing required fields. Please select all required options.</p>";
    echo "<a href='javascript:history.back()' class='button'>\ud83d\udd19 Go Back</a>";
    exit;
}

$org_name = $conn->query("SELECT orgname FROM organizations WHERE orgid = $orgid")->fetch_assoc()['orgname'] ?? 'Unknown Org';
$po_name = $conn->query("SELECT po_no FROM po WHERE poid = $poid")->fetch_assoc()['po_no'] ?? 'Unknown PO';

echo "<h3>\ud83d\udcbc Organization: <strong>$org_name</strong></h3>";
echo "<h3>\ud83d\udee0\ufe0f Service: <strong>$po_name</strong></h3>";
echo "<h3>\ud83d\udd01 Round: <strong>$round</strong></h3>";
flush();

$inserted = 0;
$skipped = 0;
$invalidDevices = [];
$validDevices = [];

if ($round > 1) {
    $prev_round = $round - 1;
    $open_dev_check = $conn->prepare("SELECT DISTINCT did FROM report WHERE orgid=? AND poid=? AND round=? AND vuln_status='Open'");
    $open_dev_check->bind_param("iii", $orgid, $poid, $prev_round);
    $open_dev_check->execute();
    $open_result = $open_dev_check->get_result();
    $open_devs = array_column($open_result->fetch_all(MYSQLI_ASSOC), 'did');

    if (!empty($open_devs)) {
        echo "<p class='info'>\u26a0\ufe0f Note: These devices still have OPEN vulnerabilities in Round $prev_round: " . implode(', ', $open_devs) . "</p>";
    }
}

foreach ($vulns as $entry) {
    $devid = intval($entry['devid'] ?? 0);
    $vid = intval($entry['vid'] ?? 0);
    $status = $entry['status'] ?? 'Open';

    if (!$devid || !$vid) continue;

    if (in_array($devid, $invalidDevices)) continue;

    if (!in_array($devid, $validDevices)) {
        $check_device = $conn->prepare("SELECT poid FROM device WHERE devid = ? AND orgid = ?");
        $check_device->bind_param("ii", $devid, $orgid);
        $check_device->execute();
        $res = $check_device->get_result();
        if ($res->num_rows === 0 || $res->fetch_assoc()['poid'] != $poid) {
            echo "<p class='error'>\u274c Device ID $devid not valid for Org ID $orgid and PO ID $poid. Skipping.</p>";
            $invalidDevices[] = $devid;
            continue;
        }
        $validDevices[] = $devid;
    }

    $dup_check = $conn->prepare("SELECT 1 FROM report WHERE orgid=? AND poid=? AND did=? AND vid=? AND round=?");
    $dup_check->bind_param("iiiii", $orgid, $poid, $devid, $vid, $round);
    $dup_check->execute();
    if ($dup_check->get_result()->num_rows > 0) {
        echo "<p class='info'>\ud83d\udfe1 Already reported: Device $devid / Vuln $vid (Round $round)</p>";
        $skipped++;
        continue;
    }

    $vul_stmt = $conn->prepare("SELECT vlevel, vpara, vdesc, vimpact, vcve, vsolu FROM vul WHERE vid = ?");
    $vul_stmt->bind_param("i", $vid);
    $vul_stmt->execute();
    $vul_result = $vul_stmt->get_result();
    if ($vul_result->num_rows === 0) {
        echo "<p class='error'>\u274c Vulnerability ID $vid not found. Skipping.</p>";
        $skipped++;
        continue;
    }

    $vul = $vul_result->fetch_assoc();

    $insert = $conn->prepare("INSERT INTO report (orgid, poid, did, vid, vlevel, vpara, vdesc, vimpact, vcve, vsolu, created_at, username, round, reportstatus, vuln_status, certificate_generated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?, 0)");
    $insert->bind_param("iiiisssssssssi", $orgid, $poid, $devid, $vid, $vul['vlevel'], $vul['vpara'], $vul['vdesc'], $vul['vimpact'], $vul['vcve'], $vul['vsolu'], $created_at, $username, $round, $status);
    $insert->execute();
    $inserted++;
}

if ($inserted > 0) {
    echo "<p class='success'>\u2705 Report generated for <strong>$inserted</strong> vulnerabilities.</p>";
}
if ($skipped > 0) {
    echo "<p class='info'>\u26a0\ufe0f Skipped <strong>$skipped</strong> vulnerabilities due to duplicates or issues.</p>";
}

// Summary by device
foreach ($validDevices as $did) {
    $status_q = $conn->query("SELECT vuln_status, COUNT(*) as count FROM report WHERE orgid = $orgid AND poid = $poid AND did = $did AND round = $round GROUP BY vuln_status");
    $summary = [];
    while ($row = $status_q->fetch_assoc()) {
        $summary[] = $row['vuln_status'] . ": " . $row['count'];
    }
    echo "<p class='info'>\ud83d\udd0e Device $did status summary: " . implode(', ', $summary) . "</p>";
}
?>
    <a href="vapt_final_report.php?orgid=<?= $orgid ?>&poid=<?= $poid ?>&round=<?= $round ?>" class="button">\ud83d\udcc4 View Final Report</a>
    <a href="superadmin_dashboard.php" class="button">\ud83c\udfe0 Go to Dashboard</a>
  </div>
<?php endif; ?>
</body>
</html>
