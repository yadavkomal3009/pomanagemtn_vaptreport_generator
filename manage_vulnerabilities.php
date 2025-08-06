<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user'])) {
    die("⛔ Login required.");
}

$user = $_SESSION['user'];
$role = strtolower($user['role'] ?? '');

$stmt = $conn->prepare("SELECT * FROM vulnerabilities ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vulnerability Management</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #eef2f5;
            padding: 30px;
        }
        .container {
            width: 95%;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        h3 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        input[type="text"] {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 100%;
            max-width: 300px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background: #007bff;
            color: white;
        }
        tr:nth-child(even) { background: #f9f9f9; }

        .btn {
            padding: 7px 12px;
            border: none;
            border-radius: 4px;
            font-size: 13px;
            text-decoration: none;
            margin: 2px;
            cursor: pointer;
        }
        .btn-success { background-color: #28a745; color: white; }
        .btn-warning { background-color: #ffc107; color: black; }
        .btn-danger { background-color: #dc3545; color: white; }
        .btn-secondary { background-color: #6c757d; color: white; }
        .btn-sm {
            font-size: 12px;
            padding: 5px 8px;
        }

        .alert {
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .d-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        @media (max-width: 768px) {
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
            td:nth-of-type(1)::before { content: "#"; }
            td:nth-of-type(2)::before { content: "Name"; }
            td:nth-of-type(3)::before { content: "Severity"; }
            td:nth-of-type(4)::before { content: "Description"; }
            td:nth-of-type(5)::before { content: "Recommendation"; }
            td:nth-of-type(6)::before { content: "Created By"; }
            td:nth-of-type(7)::before { content: "Created At"; }
            td:nth-of-type(8)::before { content: "Actions"; }
        }
    </style>
    <script>
        function searchVulnerabilities() {
            let input = document.getElementById("searchInput").value.toLowerCase();
            let rows = document.querySelectorAll("#vulnTable tbody tr");
            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                row.style.display = text.includes(input) ? "" : "none";
            });
        }
    </script>
</head>
<body>
<div class="container">
    <div class="d-flex">
        <h3 class="mb-0">🛡️ Vulnerability List</h3>
        <?php if ($role === 'admin' || $role === 'user'): ?>
            <a href="add_vulnerability.php" class="btn btn-success">➕ Add New Vulnerability</a>
        <?php elseif ($role === 'superadmin'): ?>
            <div class="alert alert-warning">🔒 Superadmin has view-only access</div>
        <?php endif; ?>
    </div>

    <div class="d-flex" style="margin-top: 15px;">
        <input type="text" id="searchInput" onkeyup="searchVulnerabilities()" placeholder="🔍 Search vulnerabilities...">
        <?php
        $backPage = match($role) {
            'admin' => 'admin_dashboard.php',
            'user' => 'user_dashboard.php',
            'superadmin' => 'superadmin_dashboard.php',
            default => 'dashboard.php'
        };
        ?>
        <a href="<?= $backPage ?>" class="btn btn-secondary">⬅️ Back</a>
    </div>

    <table id="vulnTable">
        <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Severity</th>
            <th>Description</th>
            <th>Recommendation</th>
            <th>Created By</th>
            <th>Created At</th>
            <?php if ($role === 'admin' || $role === 'user'): ?>
                <th>Actions</th>
            <?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['vuln_no']) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['severity']) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td><?= htmlspecialchars($row['recommendation']) ?></td>
                    <td><?= htmlspecialchars($row['created_by']) ?></td>
                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                    <?php if ($role === 'admin' || $role === 'user'): ?>
                        <td>
                            <a href="edit_vulnerability.php?id=<?= $row['vuln_id'] ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
                            <a href="delete_vulnerability.php?id=<?= $row['vuln_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">🗑️ Delete</a>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="<?= ($role === 'admin' || $role === 'user') ? '8' : '7' ?>" class="text-center">🚫 No vulnerabilities found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
