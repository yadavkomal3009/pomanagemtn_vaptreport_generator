<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['user']['username'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
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
    <h2>👋 Welcome, <?= htmlspecialchars($username) ?> (User)</h2>

    <div class="grid">
<a href="manage_organizations.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
            <h5>Organization Management</h5>
        </a>
        
        <a href="manage_pos.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M20 8h-3V4H7v4H4v12h16V8zM9 6h6v2H9V6z"/></svg>
            <h5>PO management </h5>
        </a>
         <a href="manage_devices.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M19 9h-4V3H5v18h14V9zm-6 7H8v-2h5v2zm3-4H8v-2h8v2z"/></svg>
            <h5>Device Management</h5>
        </a>
         <a href="manage_vulnerabilities.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M16 11c1.66 0 3-1.34 3-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zM8 11c1.66 0 3-1.34 3-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.99 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
            <h5>Vulnerability Management</h5>
        </a>
          <a href="select_org.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M20 8h-3V4H7v4H4v12h16V8zM9 6h6v2H9V6z"/></svg>
            <h5>Perform VAPT</h5>
        </a>

        <a href="list_reports.php" class="card">
           <svg class="icon" viewBox="0 0 24 24"><path d="M19 3H5a2 2 0 0 0-2 2v16l4-4h12a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/></svg>
           <h5>View Reports</h5>
        </a>


        <a href="submit_report.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M19 9h-4V3H5v18h14V9zm-6 7H8v-2h5v2zm3-4H8v-2h8v2z"/></svg>
            <h5>Submit Report</h5>
        </a>

        <a href="notifications.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M12 22a2 2 0 0 0 2-2h-4a2 2 0 0 0 2 2zm6-6V9a6 6 0 0 0-12 0v7l-2 2v1h16v-1l-2-2z"/></svg>
            <h5>My Notifications</h5>
        </a>

        <a href="update_profile.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5zm0 2c-3.33 0-10 1.67-10 5v2h20v-2c0-3.33-6.67-5-10-5z"/></svg>
            <h5>Update Profile</h5>
        </a>

        <a href="support.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M12 1C5.93 1 1 5.93 1 12s4.93 11 11 11 11-4.93 11-11S18.07 1 12 1zm1 17h-2v-2h2v2zm1.07-7.75l-.9.92C12.45 12.9 12 13.5 12 15h-2v-.5c0-.8.45-1.5 1.17-2.08l1.24-1.26A1.49 1.49 0 0 0 13 9.5a1.5 1.5 0 0 0-3 0H8a3.5 3.5 0 1 1 7 0c0 .83-.34 1.58-.93 2.25z"/></svg>
            <h5>Contact Support</h5>
        </a>

        <a href="manage_pos.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm0-4h2V3H3v2zm4 8h14v-2H7v2zm0 4h14v-2H7v2zm0-8h14V7H7v2zm0-6v2h14V3H7z"/></svg>
            <h5>Manage PO</h5>
        </a>

       
       

        

        <a href="request_po_change.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1.003 1.003 0 0 0 0-1.42l-2.34-2.34a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.82z"/></svg>
            <h5>Request PO Edit/Delete</h5>
        </a>

        <a href="my_po_requests.php" class="card">
            <svg class="icon" viewBox="0 0 24 24"><path d="M13 3a9 9 0 1 0 9 9h-9V3z"/></svg>
            <h5>My PO Requests</h5>
        </a>

    </div>
</div>

</body>
</html>
