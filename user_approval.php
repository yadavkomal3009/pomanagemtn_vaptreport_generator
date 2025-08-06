<?php
include 'db_connect.php';
session_start();


// 🔒 Allow only SuperAdmin


// ✅ Approve or reject if button clicked
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['action'])) {
    $userId = intval($_POST['user_id']);
    $action = $_POST['action'];

    if (in_array($action, ['approve', 'reject'])) {
        $status = ($action === 'approve') ? 'Approved' : 'Rejected';
        $stmt = $conn->prepare("UPDATE users SET approval_status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $userId);
        $stmt->execute();
        $stmt->close();

        // Optional: refresh page to clear POST data
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Pending User Approvals</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <h3 class="mb-4">🧾 Pending User Approvals</h3>

  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>#</th>
        <th>Username</th>
        <th>Role</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $query = "SELECT * FROM users WHERE approval_status = 'Pending'";
      $result = mysqli_query($conn, $query);
      $count = 1;

      while ($row = mysqli_fetch_assoc($result)) {
          echo "<tr>";
          echo "<td>" . $count++ . "</td>";
          echo "<td>" . htmlspecialchars($row['username']) . "</td>";
          echo "<td>" . htmlspecialchars($row['role']) . "</td>";
          echo "<td><span class='badge bg-warning text-dark'>Pending</span></td>";
          echo "<td>
                  <form method='POST' class='d-inline'>
                    <input type='hidden' name='user_id' value='" . intval($row['id']) . "'>
                    <button type='submit' name='action' value='approve' class='btn btn-success btn-sm'>Approve</button>
                    <button type='submit' name='action' value='reject' class='btn btn-danger btn-sm'>Reject</button>
                  </form>
                </td>";
          echo "</tr>";
      }

      if ($count === 1) {
          echo "<tr><td colspan='5' class='text-center text-muted'>No pending users</td></tr>";
      }
      ?>
    </tbody>
  </table>

  <a href="superadmin_dashboard.php" class="btn btn-secondary mt-3">⬅ Back to Dashboard</a>
</div>
</body>
</html>
