<?php
include 'db_connect.php';
session_start();

// Check session
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$loggedInUserId = $_SESSION['user_id'];
$loggedInUsername = $_SESSION['username'] ?? 'Unknown User';
$loggedInRole = $_SESSION['role'] ?? 'auditor';

// Role-based dashboard redirect
$dashboardUrl = match ($loggedInRole) {
    'admin' => 'admin_dashboard.php',
    'auditor' => 'auditor_dashboard.php',
    'user' => 'user_dashboard.php',
    default => 'login.php'
};

// Get user info
$stmt = $conn->prepare("SELECT vapt_status, access_request_status FROM users WHERE id = ?");
$stmt->bind_param("i", $loggedInUserId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$vaptStatus = $row['vapt_status'] ?? 'pending';
$requestStatus = $row['access_request_status'] ?? 'none';

// Handle access request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_access'])) {
    $update = $conn->prepare("UPDATE users SET access_request_status = 'requested' WHERE id = ?");
    $update->bind_param("i", $loggedInUserId);
    $update->execute();
    $requestStatus = 'requested';
    echo "<script>alert('✅ Your request has been sent to Superadmin.');</script>";
}

// Show Access Denied if not approved
if ($vaptStatus !== 'approved') {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Request VAPT Access</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f8f9fa;
                text-align: center;
                padding-top: 100px;
            }
            .box {
                display: inline-block;
                background-color: white;
                padding: 30px 50px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            .btn {
                padding: 10px 20px;
                font-size: 14px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                margin-top: 20px;
            }
            .btn-request {
                background-color: #007bff;
                color: white;
            }
            .btn-back {
                background-color: #6c757d;
                color: white;
                margin-left: 10px;
            }
        </style>
    </head>
    <body>
        <div class="box">
            <h2>❌ Access Denied</h2>
            <p>You are not approved by Superadmin to perform VAPT.</p>

            <?php if ($requestStatus === 'requested'): ?>
                <p style="color: green;">Your request is already sent. Please wait for approval.</p>
            <?php else: ?>
                <form method="post">
                    <button type="submit" name="request_access" class="btn btn-request">Request Access</button>
                </form>
            <?php endif; ?>

            <button onclick="window.location.href='<?= $dashboardUrl ?>'" class="btn btn-back">⬅️ Back</button>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// If approved, continue page logic
$adminName = ($loggedInRole === 'admin') ? $loggedInUsername : '';
$auditorName = ($loggedInRole === 'auditor') ? $loggedInUsername : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Organization</title>
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
            padding: 7px 12px;
            border: none;
            border-radius: 4px;
            font-size: 13px;
            text-decoration: none;
            margin: 2px;
            cursor: pointer;
        }
        .btn-select { background-color: #28a745; color: white; }
        .btn-edit { background-color: #ffc107; color: black; }
        .btn-delete { background-color: #dc3545; color: white; }
        .btn-add {
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
    <h2>🏢 Select Organization</h2>
    <?php if ($adminName): ?>
        <h4>Admin: <?= htmlspecialchars($adminName) ?></h4>
    <?php endif; ?>
    <?php if ($auditorName): ?>
        <h4>Auditor: <?= htmlspecialchars($auditorName) ?></h4>
    <?php endif; ?>

    <button onclick="window.location.href='<?= $dashboardUrl ?>'" class="btn btn-back">⬅️ Back</button>
    <a href="add_organization.php" class="btn btn-add">➕ Add New Organization</a>

    <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="🔍 Search organization...">

    <table id="orgTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Address</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $res = $conn->query("SELECT * FROM organizations ORDER BY orgid DESC");
        while ($row = $res->fetch_assoc()) {
            echo "<tr>
                <td>{$row['orgid']}</td>
                <td>{$row['orgname']}</td>
                <td>{$row['address']}</td>
                <td>{$row['contact']}</td>
                <td>{$row['email']}</td>
                <td>
                    <a href='select_po.php?orgid={$row['orgid']}&orgname=" . urlencode($row['orgname']) . "' class='btn btn-select'>Select</a>
                    <a href='edit_organization.php?orgid={$row['orgid']}' class='btn btn-edit'>Edit</a>
                    <a href='delete_organization.php?orgid={$row['orgid']}' class='btn btn-delete' onclick=\"return confirm('Are you sure?');\">Delete</a>
                </td>
            </tr>";
        }
        ?>
        </tbody>
    </table>
</div>

</body>
</html>
