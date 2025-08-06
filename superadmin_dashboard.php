<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'superadmin') {
    header("Location: login.php");
    exit();
}
$username = $_SESSION['user']['username'] ?? 'SuperAdmin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Superadmin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Segoe UI', sans-serif;
        background-color: #f8f9fa;
        color: #333;
    }

    .dashboard {
        max-width: 1150px;
        margin: auto;
        padding: 40px 20px;
        position: relative;
    }

    .logout {
        position: absolute;
        top: 20px;
        right: 30px;
        background-color: #dc3545;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
        font-weight: bold;
    }

    h2 {
        text-align: center;
        margin-bottom: 50px;
        font-size: 28px;
    }

    .grid {
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
        justify-content: center;
    }

    .card {
        background-color: white;
        width: 300px;
        padding: 30px 20px;
        text-align: center;
        border-radius: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        text-decoration: none;
        color: black;
        transition: transform 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.1);
    }

    .icon {
        width: 40px;
        height: 40px;
        margin: 0 auto 15px;
        fill: #0d6efd;
    }

    h5 {
        margin: 0;
        font-size: 18px;
    }
    </style>
</head>
<body>

<div class="dashboard">
    <a href="logout.php" class="logout">Logout</a>
    <h2>👑 Welcome, <?= htmlspecialchars($username) ?> (Superadmin)</h2>

    <div class="grid">
        <a href="manage_users.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z"/></svg>
            <h5>User Management</h5>
        </a>

        <a href="manage_organizations.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M3 13h18v-2H3v2zm0 4h18v-2H3v2zm0-8h18V7H3v2z"/></svg>
            <h5>Organization Management</h5>
        </a>

        <a href="manage_pos.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M20 8h-3V4H7v4H4v12h16V8zM9 6h6v2H9V6z"/></svg>
            <h5>PO Management</h5>
        </a>

        <a href="manage_devices.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M4 6h16v12H4z"/></svg>
            <h5>Device Management</h5>
        </a>

        <a href="user_approval.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z"/></svg>
            <h5>User Approval</h5>
        </a>

        <a href="manage_directorates.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z"/></svg>
            <h5>manages center</h5>
        </a>
        <a href="manage_vulnerabilities.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M12 2L2 22h20L12 2zm0 3.84L18.93 20H5.07L12 5.84z"/></svg>
            <h5>Vulnerability Management</h5>
        </a>

        <a href="list_reports.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M19 3H5a2 2 0 0 0-2 2v16l4-4h12a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/></svg>
            <h5>Report Approval</h5>
        </a>

        <a href="dashboard_analytics.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M3 17h2v-7H3v7zm4 0h2v-4H7v4zm4 0h2v-10h-2v10zm4 0h2v-2h-2v2z"/></svg>
            <h5>Analytics</h5>
        </a>

        <a href="notifications.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M12 22a2 2 0 0 0 2-2h-4a2 2 0 0 0 2 2zm6-6V9a6 6 0 0 0-12 0v7l-2 2v1h16v-1l-2-2z"/></svg>
            <h5>Send Notifications</h5>
        </a>

        <a href="view_support_requests.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M3 3h18v2H3V3zm0 4h12v2H3V7zm0 4h18v2H3v-2zm0 4h12v2H3v-2z"/></svg>
            <h5>Support Requests</h5>
        </a>
    </div>
</div>

</body>
</html>
