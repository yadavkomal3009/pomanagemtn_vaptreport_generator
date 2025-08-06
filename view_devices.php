<?php
require 'db_connect.php'; // make sure this connects to your DB

$query = "SELECT devid, devname, poid, devtype, devip, devloc FROM device";
$result = mysqli_query($conn, $query);

$devices = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $devices[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Device Inventory</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 25px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h2 {
            margin-bottom: 20px;
            font-size: 22px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th, td {
            padding: 10px 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #343a40;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #e6f2ff;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">📋 Device Inventory</h2>
    <table>
        <thead>
            <tr>
                <th>Device ID</th>
                <th>Device Name</th>
                <th>PO ID</th>
                <th>Type</th>
                <th>IP</th>
                <th>Location</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($devices as $device): ?>
                <tr>
                    <td><?= htmlspecialchars($device['devid']) ?></td>
                    <td><?= htmlspecialchars($device['devname']) ?></td>
                    <td><?= htmlspecialchars($device['poid']) ?></td>
                    <td><?= htmlspecialchars($device['devtype']) ?></td>
                    <td><?= htmlspecialchars($device['devip']) ?></td>
                    <td><?= htmlspecialchars($device['devloc']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
