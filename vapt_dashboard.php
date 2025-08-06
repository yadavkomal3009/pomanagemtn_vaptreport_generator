<?php
session_start();
include '../db_connect.php';

// Clear previous session data to avoid stale selections
unset($_SESSION['orgid']);
unset($_SESSION['po_id']);
unset($_SESSION['device_ids']);
unset($_SESSION['vuln_ids']);
unset($_SESSION['audit_round']);
unset($_SESSION['report_id']);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$search = $_GET['search'] ?? '';

// SQL Query
$query = "SELECT * FROM org WHERE 1=1";
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $query .= " AND (
        orgname LIKE '%$search%' OR
        username LIKE '%$search%' OR
        contact LIKE '%$search%' OR
        poref LIKE '%$search%' OR
        address LIKE '%$search%'
    )";
}
$query .= " ORDER BY orgid DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Select Organization</title>
    <style>
        body {
            font-family: Arial;
            background-color: #f9f9f9;
            padding: 30px;
        }
        h2 {
            color: #004e72;
        }
        .btn {
            background-color: #2196f3;
            color: white;
            padding: 8px 14px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #0b7dda;
        }
        input[type="text"] {
            padding: 6px;
            width: 300px;
            margin-right: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }
        th {
            background-color: #004e72;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .center {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<h2>🏢 Select Organization to Start VAPT Flow</h2>

<!-- Add New Org -->
<p>
    <a href="organization/add_org.php" class="btn">➕ Add New Organization</a>
</p>

<!-- Search Form -->
<form method="GET" style="margin-bottom: 20px;">
    <input type="text" name="search" placeholder="Search by Name, PO Ref, Contact, etc." value="<?= htmlspecialchars($search) ?>">
    <button type="submit" class="btn">🔍 Search</button>
</form>

<!-- Org Table -->
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Org Name</th>
            <th>Username</th>
            <th>Contact</th>
            <th>PO Ref</th>
            <th>Address</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $count = 0;
        while ($org = $result->fetch_assoc()):
            if (++$count > 30) break;
        ?>
        <tr>
            <td><?= $org['orgid'] ?></td>
            <td><?= htmlspecialchars($org['orgname']) ?></td>
            <td><?= htmlspecialchars($org['username']) ?></td>
            <td><?= htmlspecialchars($org['contact']) ?></td>
            <td><?= htmlspecialchars($org['poref']) ?></td>
            <td><?= htmlspecialchars($org['address']) ?></td>
            <td>
                <form method="POST" action="/po/flow_handler.php">
                    <input type="hidden" name="orgid" value="<?= $org['orgid'] ?>">
                    <button type="submit" class="btn">➡ Proceed to PO</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>

        <?php if ($result->num_rows === 0): ?>
            <tr><td colspan="7" style="color:red; text-align:center;">❌ No organization found. Try different search.</td></tr>
        <?php endif; ?>

        <?php if ($result->num_rows > 30): ?>
            <tr><td colspan="7" style="text-align:center; color:gray;">🔄 Showing only first 30 records. Use search for more.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
