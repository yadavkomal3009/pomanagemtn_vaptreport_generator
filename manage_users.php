<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'superadmin') {
    header("Location: login.php");
    exit();
}

// Approve/Reject VAPT Request
if (isset($_GET['vapt_action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['vapt_action'] === 'approve' ? 'approved' : 'rejected';
    $stmt = $conn->prepare("UPDATE users SET vapt_status=? WHERE id=?");
    $stmt->bind_param("si", $action, $id);
    $stmt->execute();
    header("Location: manage_users.php?msg=VAPT+request+" . $action . "+for+user+ID+$id");
    exit();
}

// Get all users
$sql = "
    SELECT users.id, users.username, users.email, users.role, users.is_active, users.vapt_status,
           organizations.orgname
    FROM users
    LEFT JOIN organizations ON users.orgid = organizations.orgid
    ORDER BY users.created_at DESC
";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
   <style>
  body {
    font-family: 'Segoe UI', sans-serif;
    background: #f4f6f9;
    padding: 40px;
    margin: 0;
  }
  .container {
    max-width: 1400px;
    margin: auto;
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  }
  h3 {
    text-align: center;
    color: #0d6efd;
    margin-bottom: 25px;
    font-size: 24px;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    font-size: 17px;
    background: white;
  }
  th, td {
    padding: 16px 20px;
    border: 1px solid #ddd;
    text-align: center;
  }
  th {
    background: #0d6efd;
    color: white;
    font-size: 18px;
  }
  .badge {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 14px;
    color: white;
    display: inline-block;
  }
  .bg-success { background: #28a745; }
  .bg-secondary { background: #6c757d; }
  .bg-warning { background: #ffc107; color: black; }
  .bg-danger { background: #dc3545; }
  .btn {
    padding: 10px 14px;
    font-size: 15px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    margin: 3px;
    font-weight: 600;
    transition: background-color 0.2s ease-in-out;
  }
  .btn-warning { background: #ffc107; color: black; }
  .btn-success { background: #28a745; color: white; }
  .btn-danger { background: #dc3545; color: white; }
  .btn-success:hover { background: #218838; }
  .btn-danger:hover { background: #c82333; }
  .btn-warning:hover { background: #e0a800; }
  .alert {
    background: #d4edda;
    color: #155724;
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    text-align: center;
    font-size: 15px;
    font-weight: 500;
  }
</style>

</head>
<body>
<div class="container">
    <h3>👥 Manage Users (VAPT Permissions)</h3>
        <a href="superadmin_dashboard.php" class="btn btn-warning">🔙 Back</a>


    <?php if (isset($_GET['msg'])): ?>
        <div class="alert"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Role</th>
            <th>Email</th>
            <th>Organization</th>
            <th>Status</th>
            <th>VAPT Permission</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($user = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= htmlspecialchars($user['id']) ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['role']) ?></td>
                <td><?= htmlspecialchars($user['email'] ?: 'N/A') ?></td>
                <td><?= htmlspecialchars($user['orgname'] ?: 'N/A') ?></td>
                <td>
                    <?php if ($user['is_active']): ?>
                        <span class="badge bg-success">Active</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Inactive</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($user['vapt_status'] === 'approved'): ?>
                        <span class="badge bg-success">Approved</span>
                    <?php elseif ($user['vapt_status'] === 'rejected'): ?>
                        <span class="badge bg-danger">Rejected</span>
                    <?php else: ?>
                        <span class="badge bg-warning">Pending</span>
                    <?php endif; ?>
                </td>
                <td>
    <?php if ($user['vapt_status'] === 'pending'): ?>
        <a href="manage_users.php?vapt_action=approve&id=<?= $user['id'] ?>" class="btn btn-success">✅ Approve VAPT</a>
        <a href="manage_users.php?vapt_action=reject&id=<?= $user['id'] ?>" class="btn btn-danger">❌ Reject VAPT</a>
    <?php elseif ($user['vapt_status'] === 'rejected'): ?>
        <a href="manage_users.php?vapt_action=approve&id=<?= $user['id'] ?>" class="btn btn-success">🔁 Re-Approve</a>
    <?php elseif ($user['vapt_status'] === 'approved'): ?>
        <a href="manage_users.php?vapt_action=reject&id=<?= $user['id'] ?>" class="btn btn-danger">↩️ Reject Again</a>
    <?php else: ?>
        <span style="font-size: 12px; color: #555;">No action</span>
    <?php endif; ?>
</td>

            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
