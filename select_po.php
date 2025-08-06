<?php
include 'db_connect.php';
session_start();

$orgid = $_GET['orgid'] ?? 0;
$orgname = $_GET['orgname'] ?? 'Unknown Organization';

// Get logged in user info
$loggedInUsername = $_SESSION['username'] ?? 'Unknown User';
$loggedInRole = $_SESSION['role'] ?? 'auditor';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Select Purchase Order</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f9fc;
            padding: 30px;
            text-align: center;
        }
        h2, h4 {
            color: #333;
        }
        table {
            margin: 0 auto;
            border-collapse: collapse;
            width: 95%;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 10px 15px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background: #3066dcff;
            color: white;
        }
        tr:nth-child(even) {
            background: #f2f2f2;
        }
        a.button {
            padding: 5px 10px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }
        a.back-button, a.add-button {
            padding: 6px 12px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 20px;
        }
        a.add-button {
            float: right;
            margin-right: 40px;
        }
        #searchInput {
            padding: 8px;
            width: 300px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
    </style>
    <script>
        function filterTable() {
            let input = document.getElementById("searchInput").value.toUpperCase();
            let table = document.getElementById("poTable");
            let trs = table.getElementsByTagName("tr");
            for (let i = 1; i < trs.length; i++) {
                let tds = trs[i].getElementsByTagName("td");
                let found = false;
                for (let j = 0; j < tds.length; j++) {
                    if (tds[j] && tds[j].innerText.toUpperCase().includes(input)) {
                        found = true;
                        break;
                    }
                }
                trs[i].style.display = found ? "" : "none";
            }
        }
    </script>
</head>
<body>

<h2>Select PO - <?= htmlspecialchars($orgname) ?></h2>
<h4>Logged in as: <?= htmlspecialchars($loggedInUsername) ?> (<?= htmlspecialchars($loggedInRole) ?>)</h4>
<a href="select_org.php" class="back-button">⬅️ Back to Organization</a>
<a href="add_po.php?orgid=<?= $orgid ?>&orgname=<?= urlencode($orgname) ?>" class="add-button">➕ Add PO</a>
<br style="clear: both;">
<input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search PO...">

<table id="poTable">
    <tr>
        <th>ID</th>
        <th>PO No</th>
        <th>Organization ID</th>
        <th>Vendor</th>
        <th>Service Type</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php
    $res = $conn->query("SELECT * FROM purchase_orders WHERE orgid = $orgid ORDER BY po_id DESC");
    while ($row = $res->fetch_assoc()) {
        echo "<tr>
        <td>{$row['po_id']}</td>
        <td>{$row['po_no']}</td>
        <td>{$row['orgid']}</td>
        <td>{$row['vendor']}</td>
        <td>{$row['service_type']}</td>
        <td>{$row['status']}</td>
        <td><a href='select_device.php?orgid={$row['orgid']}&poid={$row['po_id']}' class='button'>Select</a></td>
        </tr>";
    }
    ?>
</table>

</body>
</html>
