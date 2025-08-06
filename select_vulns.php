<?php
session_start();
include 'db_connect.php';

$orgid = $_GET['orgid'] ?? 1;
$poid = $_GET['poid'] ?? 1;

// Devices user selected previously
$selected_devices = $_SESSION['selected_devices'] ?? [];

if (empty($selected_devices)) {
    die("❌ No devices selected. <a href='select_device.php?orgid=$orgid&poid=$poid'>Select Devices</a>");
}

// Fetch all vulnerabilities
$vul_result = $conn->query("SELECT * FROM vul");
$vulnerabilities = $vul_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Select Vulnerabilities Per Device</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        th, td { padding: 8px; border: 1px solid #ccc; text-align: center; font-size: 14px; }
        th { background: #4a69bd; color: white; }
        select, input[type="text"], textarea { width: 100%; padding: 6px; }
        button { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; }
        .add-btn { background: #28a745; color: white; }
        .remove-btn { background: #dc3545; color: white; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; }
        #deviceSelector { padding: 6px; margin-left: 10px; }
        #searchInput { padding: 6px; width: 250px; }
    </style>
</head>
<body>

<h2>Select Vulnerabilities per Device</h2>

<form method="POST" action="generate_report.php">
    <input type="hidden" name="orgid" value="<?= $orgid ?>">
    <input type="hidden" name="poid" value="<?= $poid ?>">

    <div class="top-bar">
        <div>
            🔍 <input type="text" id="searchInput" placeholder="Search vulnerabilities..." onkeyup="filterVulns()">
        </div>
        <div>
            🖥️ Select Device:
            <select id="deviceSelector">
                <?php foreach ($selected_devices as $devid): ?>
                    <option value="<?= $devid ?>">Device <?= $devid ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <table id="vulTable">
        <thead>
            <tr>
                <th>ID</th><th>Name</th><th>Parameter</th><th>Description</th><th>Impact</th><th>CVE</th><th>Solution</th><th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($vulnerabilities as $v): ?>
            <tr>
                <td><?= $v['vid'] ?></td>
                <td><?= htmlspecialchars($v['vname']) ?></td>
                <td><?= htmlspecialchars($v['vpara']) ?></td>
                <td><?= htmlspecialchars($v['vdesc']) ?></td>
                <td><?= htmlspecialchars($v['vimpact']) ?></td>
                <td><?= htmlspecialchars($v['vcve']) ?></td>
                <td><?= htmlspecialchars($v['vsolu']) ?></td>
                <td><button type="button" class="add-btn" onclick="addVul(<?= htmlspecialchars(json_encode($v)) ?>)">➕ Add</button></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3>📝 Selected Vulnerabilities</h3>
    <table id="selectedTable">
        <thead>
            <tr>
                <th>Device ID</th><th>Vul ID</th><th>Name</th><th>CVE</th><th>Solution</th><th>Remove</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <br>
    <button type="submit" style="background:#4a69bd; color:white;">✅ Generate Report</button>
</form>

<script>
    let allSelections = {};  // Store per-device selected vulns
    let currentDevice = document.getElementById('deviceSelector').value;

    document.getElementById('deviceSelector').addEventListener('change', () => {
        currentDevice = document.getElementById('deviceSelector').value;
        renderSelectedTable();
    });

    function addVul(vul) {
        const key = vul.vid;
        if (!allSelections[currentDevice]) allSelections[currentDevice] = {};
        if (allSelections[currentDevice][key]) {
            alert("Already added for this device.");
            return;
        }

        allSelections[currentDevice][key] = {
            ...vul,
            vcve: vul.vcve,
            vsolu: vul.vsolu
        };

        renderSelectedTable();
    }

    function removeVul(devid, vid) {
        if (allSelections[devid]) {
            delete allSelections[devid][vid];
            if (Object.keys(allSelections[devid]).length === 0) {
                delete allSelections[devid];
            }
        }
        renderSelectedTable();
    }

    function renderSelectedTable() {
        const tbody = document.getElementById('selectedTable').querySelector('tbody');
        tbody.innerHTML = '';

        if (!allSelections[currentDevice]) return;

        Object.entries(allSelections[currentDevice]).forEach(([vid, vul]) => {
            const row = document.createElement('tr');
            const uniqKey = `${currentDevice}_${vid}`;
            row.innerHTML = `
                <td>
                    <input type="hidden" name="vulns[${uniqKey}][devid]" value="${currentDevice}">
                    ${currentDevice}
                </td>
                <td>
                    <input type="hidden" name="vulns[${uniqKey}][vid]" value="${vul.vid}">
                    ${vul.vid}
                </td>
                <td>${vul.vname}</td>
                <td>
                    <input type="text" name="vulns[${uniqKey}][vcve]" value="${vul.vcve}">
                </td>
                <td>
                    <textarea name="vulns[${uniqKey}][vsolu]">${vul.vsolu}</textarea>
                </td>
                <td>
                    <button type="button" class="remove-btn" onclick="removeVul('${currentDevice}', '${vid}')">❌</button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    function filterVulns() {
        const search = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('#vulTable tbody tr');
        rows.forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(search) ? '' : 'none';
        });
    }

    // Initial render
    renderSelectedTable();
</script>


</body>
</html>
