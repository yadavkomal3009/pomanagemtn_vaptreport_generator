<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['user']['role'] ?? 'user';
$dashboardPage = match (strtolower($role)) {
    'admin' => 'admin_dashboard.php',
    'superadmin' => 'superadmin_dashboard.php',
    default => 'user_dashboard.php',
};

$sql = "SELECT * FROM organizations ORDER BY orgid DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Organizations</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #eef2f5;
            padding: 30px;
        }
        h2, h4 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 10px;
        }
        .container {
            width: 95%;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        #searchInput {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background: #007bff;
            color: white;
            cursor: pointer;
        }
        tr:nth-child(even) { background: #f9f9f9; }

        .btn {
            width: 100px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            margin: 2px;
            cursor: pointer;
        }
        .btn-select { background-color: #28a745; color: white; }
        .btn-edit   { background-color: #ffc107; color: black; }
        .btn-delete { background-color: #dc3545; color: white; }
        .btn-add    {
            background-color: #007bff;
            color: white;
            float: right;
            margin-bottom: 10px;
        }
        .btn-back {
            background-color: #6c757d;
            color: white;
            margin-bottom: 10px;
        }

        @media (max-width: 600px) {
            table, thead, tbody, th, td, tr { display: block; }
            tr { margin-bottom: 15px; }
            td, th {
                text-align: right;
                padding-left: 50%;
                position: relative;
            }
            td::before, th::before {
                position: absolute;
                left: 10px;
                width: 45%;
                padding-left: 15px;
                white-space: nowrap;
                text-align: left;
                font-weight: bold;
            }
            td:nth-of-type(1)::before { content: "ID"; }
            td:nth-of-type(2)::before { content: "Name"; }
            td:nth-of-type(3)::before { content: "Address"; }
            td:nth-of-type(4)::before { content: "Contact"; }
            td:nth-of-type(5)::before { content: "Email"; }
            td:nth-of-type(6)::before { content: "Action"; }
        }
    </style>

    <script>
        function filterTable() {
            let input = document.getElementById("searchInput").value.toUpperCase();
            let table = document.getElementById("orgTable");
            let trs = table.getElementsByTagName("tr");
            for (let i = 1; i < trs.length; i++) {
                let tds = trs[i].getElementsByTagName("td");
                let found = false;
                for (let j = 0; j < tds.length - 1; j++) {
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
<div class="container">
    <a href="<?= $dashboardPage ?>" class="btn btn-back">⬅️ Back</a>
    <a href="add_organization.php" class="btn btn-add">➕ Add Organization</a>
    <h2>Manage Organizations</h2>

    <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="🔍 Search organizations...">

    <?php if ($result && $result->num_rows > 0): ?>
        <table id="orgTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Organization</th>
                    <th>Address</th>
                    <th>Contact</th>
                    <th>Email</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php $count = 1; while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $count++ ?></td>
                    <td><?= htmlspecialchars($row['orgname']) ?></td>
                    <td><?= htmlspecialchars($row['address']) ?></td>
                    <td><?= htmlspecialchars($row['contact']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td>
                        <a href="edit_organization.php?id=<?= $row['orgid'] ?>" class="btn btn-edit">✏️ Edit</a>
                        <a href="delete_organization.php?id=<?= $row['orgid'] ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this organization?');">🗑️ Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-data">No organizations found.</div>
    <?php endif; ?>
</div>
</body>
</html>
