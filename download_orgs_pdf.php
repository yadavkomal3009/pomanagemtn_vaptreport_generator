<?php
session_start();
require_once 'db_connect.php';

// Allow only Superadmin, Admin, or Approved User
$role = $_SESSION['role'] ?? null;
$approval_status = $_SESSION['approval_status'] ?? null;

if (($role === 'Superadmin' || $role === 'Admin' || ($role === 'User' && $approval_status === 'Approved'))) {
    die("❌ You don't have permission to download PDF.");
}

// ✅ Fetch organizations
$sql = "SELECT * FROM organizations ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Organization Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body class="container mt-4">
    <h2 class="mb-4">Organization Report</h2>
    
    <button onclick="window.print()" class="btn btn-primary mb-3 no-print">🖨️ Print / Save as PDF</button>
    <a href="manage_organizations.php" class="btn btn-secondary mb-3 no-print">⬅ Back</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Org Name</th>
                <th>Email</th>
                <th>Contact</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($org = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= htmlspecialchars($org['orgname']) ?></td>
                <td><?= htmlspecialchars($org['email']) ?></td>
                <td><?= htmlspecialchars($org['contact']) ?></td>
                <td><?= $org['created_at'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
