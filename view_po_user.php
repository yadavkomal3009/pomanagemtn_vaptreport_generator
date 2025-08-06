<?php
include 'db_connect.php';
session_start();

// 🔐 Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user']['user_id'];
$username = $_SESSION['user']['username'] ?? 'User ';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My POs</title>
  <style>
      body {
          font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
          background-color: #f4f6f8;
          margin: 0;
          padding: 30px;
      }
      .container {
          max-width: 1100px;
          margin: auto;
          background: #fff;
          padding: 25px;
          border-radius: 8px;
          box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      }
      h3 {
          margin-bottom: 20px;
          color: #333;
      }
      table {
          width: 100%;
          border-collapse: collapse;
          margin-top: 15px;
      }
      th, td {
          padding: 10px;
          border: 1px solid #dee2e6;
          text-align: left;
      }
      th {
          background-color: #6694c2ff;
          color: white;
      }
      tr:nth-child(even) {
          background-color: #f8f9fa;
      }
      tr:hover {
          background-color: #e6f2ff;
      }
      .badge {
          padding: 5px 10px;
          border-radius: 4px;
          color: white;
      }
      .badge.success { background-color: #28a745; }
      .badge.danger { background-color: #dc3545; }
      .badge.secondary { background-color: #6c757d; }
      .btn {
          padding: 6px 12px;
          border-radius: 4px;
          border: none;
          cursor: pointer;
          text-decoration: none;
          transition: background-color 0.2s ease-in-out;
      }
      .btn-outline-danger {
          background-color: white;
          border: 1px solid #dc3545;
          color: #dc3545;
      }
      .btn-outline-danger:hover {
          background-color: #dc3545;
          color: white;
      }
      .btn-secondary {
          background-color: #6c757d;
          color: white;
      }
      .btn-secondary:hover {
          background-color: #5a6268;
      }
  </style>
</head>
<body>
<div class="container">
  <h3 class="mb-4">📋 My Purchase Orders</h3>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>PO No</th>
        <th>Date</th>
        <th>Service</th>
        <th>Value</th>
        <th>Status</th>
        <th>File</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $stmt = $conn->prepare("SELECT * FROM po WHERE user_id = ? ORDER BY po_date DESC");
      $stmt->bind_param("i", $userId);
      $stmt->execute();
      $result = $stmt->get_result();
      $count = 1;

      while ($row = $result->fetch_assoc()) {
          echo "<tr>";
          echo "<td>" . $count++ . "</td>";
          echo "<td>" . htmlspecialchars($row['po_no']) . "</td>";
          echo "<td>" . htmlspecialchars($row['po_date']) . "</td>";
          echo "<td>" . htmlspecialchars($row['service_type']) . "</td>";
          echo "<td>₹" . number_format($row['value'], 2) . "</td>";

          // 🎯 Status Badge
          $status = ucfirst($row['status'] ?? 'Pending');
          $badgeClass = match ($status) {
              'Approved' => 'success',
              'Rejected' => 'danger',
              default => 'secondary',
          };
          echo "<td><span class='badge $badgeClass'>$status</span></td>";

          // 📎 File Download
          $file = $row['tender_file'] ?? '';
          if ($file && file_exists("uploads/$file")) {
              echo "<td><a href='uploads/$file' target='_blank'>Download</a></td>";
          } else {
              echo "<td>--</td>";
          }

          // ❌ Optional Delete if status is Pending
          if ($status === 'Pending') {
              echo "<td><a href='delete_po.php?id=" . $row['id'] . "' class='btn btn-sm btn-outline-danger' onclick=\"return confirm('Are you sure you want to delete this PO?');\">Delete</a></td>";
          } else {
              echo "<td>--</td>";
          }

          echo "</tr>";
      }

      $stmt->close();
      ?>
    </tbody>
  </table>

  <a href="admin_dashboard.php" class="btn btn-secondary mt-3">⬅ Back to Dashboard</a>
</div>
</body>
</html>
