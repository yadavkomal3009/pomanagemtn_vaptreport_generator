<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user'])) {
    die("⛔ Login required.");
}

$user = $_SESSION['user'];
$role = strtolower($user['role'] ?? '');
$orgid = $user['orgid'] ?? null;

$back_url = match ($role) {
    'admin' => 'admin_dashboard.php',
    'user' => 'user_dashboard.php',
    'superadmin' => 'superadmin_dashboard.php',
    default => 'index.php'
};

$report_mode = true;

if (($role === 'admin' || $role === 'user') && !$report_mode) {
    $stmt = $conn->prepare("SELECT devid, poid, devname, devtype, devmake, devmodel, devdesc, devip, devloc, devremarks FROM device WHERE orgid = ?");
    $stmt->bind_param("i", $orgid);
} else {
    $stmt = $conn->prepare("SELECT devid, poid, devname, devtype, devmake, devmodel, devdesc, devip, devloc, devremarks FROM device");
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Manage Devices</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f4f6f9;
      padding: 40px;
      margin: 0;
    }
    .container {
      max-width: 1400px;
      margin: auto;
      background: #fff;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
    }
    h3 {
      font-size: 26px;
      font-weight: 700;
      color: #0d6efd;
      margin: 0;
    }
    .header,
    .search-back {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    input[type="text"] {
      width: 60%;
      padding: 12px 14px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 16px;
    }
    input[type="text"]:focus {
      border-color: #0d6efd;
      outline: none;
    }
    .btn {
      padding: 10px 16px;
      font-size: 15px;
      font-weight: 600;
      border-radius: 6px;
      border: none;
      text-decoration: none;
      cursor: pointer;
      display: inline-block;
      transition: background-color 0.3s ease;
    }
    .btn-success {
      background: #0d6efd;
      color: white;
    }
    .btn-success:hover {
      background: #084298;
    }
    .btn-secondary {
      background: #6c757d;
      color: white;
    }
    .btn-secondary:hover {
      background: #495057;
    }
    .btn-warning {
      background: #ffc107;
      color: #212529;
    }
    .btn-warning:hover {
      background: #e0a800;
    }
    .btn-danger {
      background: #dc3545;
      color: white;
    }
    .btn-danger:hover {
      background: #b02a37;
    }
    .btn-sm {
      padding: 6px 10px;
      font-size: 14px;
      border-radius: 5px;
      margin-right: 4px;
    }
    .alert-warning {
      background: #fff3cd;
      padding: 12px 15px;
      border-radius: 8px;
      font-size: 15px;
      color: #856404;
      font-weight: 600;
    }

    /* Scrollable Table Wrapper */
    .table-wrapper {
      width: 100%;
      overflow-x: auto;
      border: 1px solid #ddd;
      border-radius: 8px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 16px;
      margin-top: 10px;
      min-width: 1000px; /* Ensures scroll if too many columns */
    }
    th, td {
      border: 1px solid #ddd;
      padding: 14px 18px;
      text-align: left;
      vertical-align: middle;
    }
    th {
      background-color: #0d6efd;
      color: white;
      font-size: 17px;
      font-weight: 600;
    }
    tr:nth-child(even) {
      background-color: #f9fafd;
    }
    tr:hover {
      background-color: #e3f2fd;
    }
  </style>

  <script>
    function searchDevices() {
      let input = document.getElementById("searchInput").value.toLowerCase();
      let rows = document.querySelectorAll("#deviceTable tbody tr");
      rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(input) ? "" : "none";
      });
    }
  </script>
</head>
<body>
  <div class="container">
    <div class="header">
      <h3>📟 Device List</h3>
      <?php if ($role === 'admin' || $role === 'user'): ?>
        <a href="add_device.php" class="btn btn-success">➕ Add New Device</a>
      <?php elseif ($role === 'superadmin'): ?>
        <div class="alert-warning">🔒 Superadmin has view-only access</div>
      <?php endif; ?>
    </div>

    <div class="search-back">
      <input type="text" id="searchInput" onkeyup="searchDevices()" placeholder="🔍 Search device details...">
      <a href="<?= $back_url ?>" class="btn btn-secondary">⬅️ Back</a>
    </div>

    <div class="table-wrapper">
      <table id="deviceTable">
        <thead>
          <tr>
            <th>PO ID</th>
            <th>Device</th>
            <th>Type</th>
            <th>Make</th>
            <th>Model</th>
            <th>Description</th>
            <th>IP</th>
            <th>Location</th>
            <th>Remarks</th>
            <?php if ($role === 'admin' || $role === 'user'): ?>
              <th>Actions</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['poid']) ?></td>
                <td><?= htmlspecialchars($row['devname']) ?></td>
                <td><?= htmlspecialchars($row['devtype']) ?></td>
                <td><?= htmlspecialchars($row['devmake']) ?></td>
                <td><?= htmlspecialchars($row['devmodel']) ?></td>
                <td><?= htmlspecialchars($row['devdesc']) ?></td>
                <td><?= htmlspecialchars($row['devip']) ?></td>
                <td><?= htmlspecialchars($row['devloc']) ?></td>
                <td><?= htmlspecialchars($row['devremarks']) ?></td>
                <?php if ($role === 'admin' || $role === 'user'): ?>
                  <td>
                    <a href="edit_device.php?id=<?= $row['devid'] ?>" class="btn btn-warning btn-sm">✏️</a>
                    <a href="delete_device.php?id=<?= $row['devid'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">🗑️</a>
                  </td>
                <?php endif; ?>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="<?= ($role === 'admin' || $role === 'user') ? '10' : '9' ?>" style="text-align:center; font-weight:600; color:#666;">
                🚫 No devices found.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
