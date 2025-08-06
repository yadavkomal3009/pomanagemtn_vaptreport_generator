<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$username = $user['username'];
$role = $user['role'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status']) && $role === 'superadmin') {
    $rid = $_POST['rid'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE report SET vid = ?, created_at = NOW() WHERE rid = ?");
$stmt->bind_param("ii", $new_vid, $rid);

    $stmt->execute();
    $stmt->close();

    $successMsg = "✅ Report ID $rid status updated to $status.";
}

$query = "
    SELECT r.rid, r.created_at, r.round, r.vstatus, r.reportstatus,
           o.orgname,
           p.po_no,
           v.vname
    FROM report r
    LEFT JOIN organizations o ON r.orgid = o.orgid
    LEFT JOIN po p ON r.poid = p.poid
    LEFT JOIN vul v ON r.vid = v.vid
    ORDER BY r.created_at DESC
";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>📄 Generated Reports</title>
    <a href="superadmin_dashboard.php" class="btn" style="margin-bottom: 20px;">🔙 Back</a>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            padding: 30px;
        }

        h2 {
            margin-bottom: 20px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-left: 5px solid #28a745;
        }

        .search-box {
            margin-bottom: 20px;
            text-align: right;
        }

        .search-box input {
            padding: 8px;
            width: 250px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px 15px;
            border: 1px solid #ccc;
        }

        th {
            background-color: #1976d2;
            color: white;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        .btn {
            background: #1976d2;
            color: white;
            padding: 6px 10px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            margin-right: 5px;
            cursor: pointer;
        }

        .btn:hover {
            background: #135ba1;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #b52a37;
        }
    </style>
</head>
<body>

<h2>📄 Generated Reports</h2>

<?php if (!empty($successMsg)): ?>
    <div class="success"><?= $successMsg ?></div>
<?php endif; ?>

<div class="search-box">
    <input type="text" id="searchInput" placeholder="🔍 Search reports...">
</div>

<?php if ($result && $result->num_rows > 0): ?>
    <table id="reportTable">
        <thead>
        <tr>
            <th>S.No.</th>
            <th>RID</th>
            <th>Round</th>
            <th>Organization</th>
            <th>PO No</th>
            <th>Vulnerability</th>
            <th>Status</th>
            <th>Created</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td class="sno-col"></td>
                <td><?= $row['rid'] ?></td>
                <td><?= htmlspecialchars($row['round'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['orgname'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['po_no'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['vname'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['reportstatus'] ?: 'Pending') ?></td>
                <td><?= $row['created_at'] ?></td>
             <td>
    <?php if ($role === 'superadmin'): ?>
        <?php if ($row['reportstatus'] === 'Pending' || empty($row['reportstatus'])): ?>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="rid" value="<?= $row['rid'] ?>">
                <input type="hidden" name="status" value="Approved">
                <button type="submit" name="update_status" class="btn">Approve</button>
            </form>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="rid" value="<?= $row['rid'] ?>">
                <input type="hidden" name="status" value="Rejected">
                <button type="submit" name="update_status" class="btn btn-danger">Reject</button>
            </form>
        <?php else: ?>
            ✅ <?= htmlspecialchars($row['reportstatus']) ?>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Edit button for admin and user -->
    <?php if ($role === 'admin' || $role === 'user'): ?>
        <a class="btn" href="edit_report.php?rid=<?= $row['rid'] ?>">✏️ Edit Vuls</a>
    <?php endif; ?>

    <!-- View button for everyone -->
    <a class="btn" href="view_report.php?rid=<?= $row['rid'] ?>">👁️ View</a>

    <?php if ($role !== 'superadmin' && $row['reportstatus'] === 'Approved'): ?>
        <a class="btn" href="generate_certificate.php?rid=<?= $row['rid'] ?>">🎓 Generate Certificate</a>
    <?php endif; ?>
</td>

            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>❌ No reports found.</p>
<?php endif; ?>

<script>
    const searchInput = document.getElementById("searchInput");
    const tableRows = document.querySelectorAll("#reportTable tbody tr");

    function updateSerialNumbers() {
        let count = 1;
        tableRows.forEach(row => {
            if (row.style.display !== "none") {
                row.querySelector(".sno-col").innerText = count++;
            } else {
                row.querySelector(".sno-col").innerText = '';
            }
        });
    }

    searchInput.addEventListener("input", function () {
        const searchText = this.value.toLowerCase();
        tableRows.forEach(row => {
            const rowText = row.innerText.toLowerCase();
            row.style.display = rowText.includes(searchText) ? "" : "none";
        });
        updateSerialNumbers();
    });

    // Initial numbering on page load
    updateSerialNumbers();
</script>

</body>
</html>
