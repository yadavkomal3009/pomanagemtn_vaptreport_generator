<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['user']['username'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
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
    <h2>👋 Welcome, <?= htmlspecialchars($username) ?> (Admin)</h2>

    <div class="grid">
        <a href="user_approval.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M15.54 8.46a5 5 0 1 0-7.08 0 5 5 0 0 0 7.08 0zM12 13c-3.33 0-10 1.67-10 5v2h20v-2c0-3.33-6.67-5-10-5z"/></svg>
            <h5>Approve Users</h5>
        </a>

        <a href="list_reports.php" class="card">
    <svg class="icon" viewBox="0 0 24 24"><path d="M19 3H5a2 2 0 0 0-2 2v16l4-4h12a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/></svg>
    <h5>View Reports</h5>
</a>


       
        <a href="manage_organizations.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm0-4h2V3H3v2zm4 8h14v-2H7v2zm0 4h14v-2H7v2zm0-8h14V7H7v2zm0-6v2h14V3H7z"/></svg>
            <h5>Organization Management</h5>
        </a>

        <a href="manage_pos.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M20 8h-3V4H7v4H4v12h16V8zM9 6h6v2H9V6z"/></svg>
            <h5>PO Management</h5>
        </a>

        <a href="manage_devices.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M17 2H7a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h10a2 2 0 0 0 2-2V4c0-1.1-.9-2-2-2z"/></svg>
            <h5>Device Management</h5>
        </a>
 <a href="manage_vulnerabilities.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M16 11c1.66 0 3-1.34 3-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zM8 11c1.66 0 3-1.34 3-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.99 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
            <h5>Vulnerability</h5>
        </a>

        
        <a href="select_org.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M20 8h-3V4H7v4H4v12h16V8zM9 6h6v2H9V6z"/></svg>
            <h5>Perform VAPT</h5>
        </a>

        <a href="settings.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M19.14 12.94c.04-.31.06-.63.06-.94s-.02-.63-.06-.94l2.03-1.58a.5.5 0 0 0 .11-.65l-1.91-3.32a.5.5 0 0 0-.61-.22l-2.39.96a7.007 7.007 0 0 0-1.61-.94L14.5 2h-5l-.39 2.31c-.58.22-1.12.52-1.61.94l-2.39-.96a.5.5 0 0 0-.61.22l-1.91 3.32a.5.5 0 0 0 .11.65l2.03 1.58c-.04.31-.06.63-.06.94s.02.63.06.94l-2.03 1.58a.5.5 0 0 0-.11.65l1.91 3.32c.14.24.43.34.68.22l2.39-.96c.49.42 1.03.72 1.61.94l.39 2.31h5l.39-2.31c.58-.22 1.12-.52 1.61-.94l2.39.96a.5.5 0 0 0 .61-.22l1.91-3.32a.5.5 0 0 0-.11-.65l-2.03-1.58zM12 15.5A3.5 3.5 0 1 1 15.5 12 3.5 3.5 0 0 1 12 15.5z"/></svg>
            <h5>Settings</h5>
        </a>
        
        <a href="notifications.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M12 22a2 2 0 0 0 2-2H10a2 2 0 0 0 2 2zm6-6V9a6 6 0 0 0-12 0v7L4 18v1h16v-1l-2-2z"/></svg>
            <h5>Notifications</h5>
        </a>

        <a href="admin_po_requests.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M3 3h18v2H3V3zm0 4h12v2H3V7zm0 4h18v2H3v-2zm0 4h12v2H3v-2zm0 4h18v2H3v-2z"/></svg>
            <h5>PO Requests</h5>
        </a>
    </div>
</div>

</body>
</html>
