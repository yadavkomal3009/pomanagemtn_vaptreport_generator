<?php
include 'db_connect.php';
session_start();

$orgid = $_GET['orgid'] ?? 0;
$poid = $_GET['poid'] ?? 0;
$orgname = $_GET['orgname'] ?? 'Unknown Organization';

$loggedInUsername = $_SESSION['username'] ?? 'Unknown User';
$loggedInRole = $_SESSION['role'] ?? 'auditor';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION['selected_devices'] = $_POST['devids'] ?? [];
    header("Location: select_vulns.php?orgid=$orgid&poid=$poid&orgname=" . urlencode($orgname));
    exit();
}

// Get latest round
$roundRes = $conn->query("SELECT MAX(round) as latest_round FROM report WHERE orgid = $orgid AND poid = $poid");
$latestRound = $roundRes->fetch_assoc()['latest_round']; // Can be NULL

// Fetch untested devices
$devices = [];
$res = $conn->query("SELECT * FROM device WHERE orgid = $orgid");
while ($row = $res->fetch_assoc()) {
    $deviceId = $row['devid'];

    $checkSql = is_null($latestRound)
        ? "SELECT COUNT(*) as count FROM report WHERE did = $deviceId AND orgid = $orgid AND poid = $poid AND round IS NULL"
        : "SELECT COUNT(*) as count FROM report WHERE did = $deviceId AND orgid = $orgid AND poid = $poid AND round = $latestRound";

    $check = $conn->query($checkSql);
    $alreadyTested = $check->fetch_assoc()['count'] > 0;

    if (!$alreadyTested) {
        $devices[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Select Devices</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f8f9fa; }
        h2, h4, p { text-align: center; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; background: #fff; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background: #2967d1ff; color: white; }
        button, .btn { padding: 6px 12px; background: #007b83; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover, .btn:hover { background: #2a64e2ff; }
        .scrollable { max-height: 300px; overflow-y: auto; display: block; }
        #searchInput { padding: 8px; width: 300px; margin: 10px auto; display: block; border-radius: 4px; border: 1px solid #ccc; }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .top-bar a {
            margin: 5px;
        }
    </style>
    <script>
        let selectedDevices = new Set();

        function filterTable() {
            const input = document.getElementById("searchInput").value.toUpperCase();
            const trs = document.getElementById("deviceTable").getElementsByTagName("tr");
            for (let i = 1; i < trs.length; i++) {
                let tds = trs[i].getElementsByTagName("td");
                let found = false;
                for (let td of tds) {
                    if (td.innerText.toUpperCase().includes(input)) {
                        found = true;
                        break;
                    }
                }
                trs[i].style.display = found ? "" : "none";
            }
        }

        function addDevice(id, name, type, ip, loc) {
            if (selectedDevices.has(id)) return;

            selectedDevices.add(id);
            const table = document.getElementById("selectedDevices");
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${name}</td>
                <td>${type}</td>
                <td>${ip}</td>
                <td>${loc}</td>
                <td><button type="button" onclick="removeDevice('${id}', this)">❌ Remove</button></td>
            `;
            table.appendChild(row);

            const hiddenInput = document.createElement("input");
            hiddenInput.type = "hidden";
            hiddenInput.name = "devids[]";
            hiddenInput.value = id;
            hiddenInput.id = "input-" + id;
            document.getElementById("hiddenInputs").appendChild(hiddenInput);
        }

        function removeDevice(id, btn) {
            selectedDevices.delete(id);
            btn.closest("tr").remove();
            document.getElementById("input-" + id).remove();
        }
    </script>
</head>
<body>

<div class="top-bar">
    <a href="select_po.php?orgid=<?= $orgid ?>&orgname=<?= urlencode($orgname) ?>" class="btn">⬅️ Back to PO List</a>
    <a href="add_device.php?orgid=<?= $orgid ?>" class="btn">➕ Add Device</a>
</div>

<h2>Select Devices</h2>
<h4>Organization: <?= htmlspecialchars($orgname) ?> | Auditor: <?= htmlspecialchars($loggedInUsername) ?> (<?= htmlspecialchars($loggedInRole) ?>)</h4>
<p>(Showing only untested devices for Org ID <strong><?= $orgid ?></strong> and PO ID <strong><?= $poid ?></strong>)</p>

<input type="text" id="searchInput" onkeyup="filterTable()" placeholder="🔍 Search device...">

<form method="POST">
    <div class="scrollable">
        <table id="deviceTable">
            <thead>
                <tr>
                    <th>ID</th><th>Name</th><th>Type</th><th>Make</th><th>Model</th><th>IP</th><th>Location</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($devices)): ?>
                    <?php foreach ($devices as $d): ?>
                        <tr>
                            <td><?= $d['devid'] ?></td>
                            <td><?= htmlspecialchars($d['devname']) ?></td>
                            <td><?= htmlspecialchars($d['devtype']) ?></td>
                            <td><?= htmlspecialchars($d['devmake']) ?></td>
                            <td><?= htmlspecialchars($d['devmodel']) ?></td>
                            <td><?= htmlspecialchars($d['devip']) ?></td>
                            <td><?= htmlspecialchars($d['devloc']) ?></td>
                            <td>
                                <button type="button" onclick="addDevice('<?= $d['devid'] ?>', '<?= htmlspecialchars($d['devname']) ?>', '<?= htmlspecialchars($d['devtype']) ?>', '<?= htmlspecialchars($d['devip']) ?>', '<?= htmlspecialchars($d['devloc']) ?>')">➕ Select</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8">🚫 No available devices.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <h3>✅ Selected Devices</h3>
    <table>
        <thead>
            <tr><th>Name</th><th>Type</th><th>IP</th><th>Location</th><th>Remove</th></tr>
        </thead>
        <tbody id="selectedDevices"></tbody>
    </table>

    <div id="hiddenInputs"></div>
    <center><button type="submit">➡️ Next: Select Vulnerabilities</button></center>
</form>

</body>
</html>
