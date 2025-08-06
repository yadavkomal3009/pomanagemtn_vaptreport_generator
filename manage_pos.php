<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$role = strtolower($user['role'] ?? '');
$approval = $user['approval'] ?? '';
$userid = $user['user_id'] ?? null;
$orgid = $user['orgid'] ?? null;

$approval_warning = '';
if ($role === 'user' && $approval !== 'Approved') {
    $approval_warning = "⚠️ Your access is not yet approved. Please wait for approval.";
}
if ($role === 'user' && !$orgid) {
    $approval_warning = "❌ Your account is not linked to any organization. Please contact admin.";
}

$search = $_GET['search'] ?? '';
$like = "%{$search}%";

if ($role === 'superadmin' || $role === 'admin') {
    $stmt = $conn->prepare("
        SELECT p.*, o.orgname 
        FROM po p 
        LEFT JOIN organizations o ON p.orgid = o.orgid 
        WHERE p.po_no LIKE ? OR p.service_type LIKE ? 
        ORDER BY p.po_date DESC
    ");
    $stmt->bind_param("ss", $like, $like);
} elseif ($role === 'user') {
    $stmt = $conn->prepare("
        SELECT p.* 
        FROM po p 
        WHERE p.orgid = ? AND (p.po_no LIKE ? OR p.service_type LIKE ?) 
        ORDER BY p.po_date DESC
    ");
    $stmt->bind_param("iss", $orgid, $like, $like);
}

$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Purchase Orders</title>
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
        h2, h3, h4 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 20px;
        }
        input[type="text"], select {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 100%;
            max-width: 300px;
            margin-bottom: 15px;
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
            cursor: pointer;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }

        .btn {
            padding: 7px 12px;
            border: none;
            border-radius: 4px;
            font-size: 13px;
            text-decoration: none;
            margin: 2px;
            cursor: pointer;
        }
        .btn-back { background-color: #6c757d; color: white; }
        .btn-add { background-color: #007bff; color: white; }
        .btn-edit { background-color: #ffc107; color: black; }
        .btn-delete { background-color: #dc3545; color: white; }
        .btn-success { background-color: #28a745; color: white; }
        .btn-outline-secondary {
            background-color: white;
            border: 1px solid #6c757d;
            color: #6c757d;
        }
        .btn-outline-secondary:hover {
            background-color: #6c757d;
            color: white;
        }

        .alert {
            padding: 10px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        .alert-info {
            background-color: #cff4fc;
            color: #055160;
        }

        .text-muted {
            color: #6c757d;
            font-size: 14px;
        }

        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .me-2 { margin-right: 10px; }
        .btn-sm {
            font-size: 12px;
            padding: 5px 8px;
            margin-right: 4px;
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
        }
    </style>
</head>
<body>
<div class="container">
    <h3>📦 Manage Purchase Orders</h3>

    <?php if ($approval_warning): ?>
        <div class="alert alert-warning"><?= htmlspecialchars($approval_warning) ?></div>
    <?php endif; ?>

    <div class="actions">
        <a href="<?= $role === 'superadmin' ? 'superadmin_dashboard.php' : 'user_dashboard.php' ?>" class="btn btn-outline-secondary">🔙 Back to Dashboard</a>
        <form method="get">
            <input type="text" name="search" placeholder="🔍 Search PO No / Service" value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-outline-secondary">Search</button>
        </form>
        <div>
            <?php if ($role !== 'superadmin'): ?>
                <a href="add_po.php" class="btn btn-success me-2">➕ Add PO</a>
            <?php endif; ?>
            <a href="export_pos.php<?= $search ? '?search=' . urlencode($search) : '' ?>" class="btn btn-outline-secondary">📤 Export CSV</a>
        </div>
    </div>

    <span class="text-muted">Logged in as: <strong><?= htmlspecialchars($user['username']) ?></strong> (<?= htmlspecialchars($role) ?>)</span>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
        <div class="alert alert-success">✅ PO deleted successfully.</div>
    <?php endif; ?>

    <?php if ($result && $result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>PO No</th>
                    <th>Date</th>
                    <th>Service</th>
                    <th>Value (₹)</th>
                    <?php if ($role !== 'user'): ?><th>Organization</th><?php endif; ?>
                    <th>Tender File</th>
                    <?php if ($role !== 'superadmin'): ?><th>Actions</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($po = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($po['poid']) ?></td>
                        <td><?= htmlspecialchars($po['po_no']) ?></td>
                        <td><?= htmlspecialchars($po['po_date']) ?></td>
                        <td><?= htmlspecialchars($po['service_type']) ?></td>
                        <td>₹<?= number_format($po['value'], 2) ?></td>
                        <?php if ($role !== 'user'): ?>
                            <td><?= htmlspecialchars($po['orgname'] ?? 'N/A') ?></td>
                        <?php endif; ?>
                        <td>
                            <?php 
                                $filePath = 'uploads/' . $po['tender_file'];
                                if (!empty($po['tender_file']) && file_exists($filePath)): ?>
                                <a href="<?= $filePath ?>" target="_blank">📄 View</a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <?php if ($role !== 'superadmin'): ?>
                            <td>
                                <a href="edit_po.php?id=<?= $po['poid'] ?>" class="btn btn-edit btn-sm">✏️ Edit</a>
                                <?php if ($role === 'admin'): ?>
                                    <a href="delete_po.php?id=<?= $po['poid'] ?>" class="btn btn-delete btn-sm" onclick="return confirm('Are you sure you want to delete this PO?')">🗑️ Delete</a>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">ℹ️ No Purchase Orders found.</div>
    <?php endif; ?>
</div>
</body>
</html>
