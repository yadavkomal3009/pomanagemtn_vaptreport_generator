<?php
include 'db_connect.php';
session_start();


// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $rid = $_POST['rid'];
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE report SET reportstatus=? WHERE rid=?");
    $stmt->bind_param("si", $status, $rid);
    $stmt->execute();
    $stmt->close();
}

// Fetch reports
$sql = "SELECT r.rid, r.reportstatus, r.orgid, r.poid, o.orgname 
        FROM report r
        JOIN organizations o ON r.orgid = o.orgid";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Reports</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #aaa;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #eee;
        }
        select, button {
            padding: 5px 10px;
        }
        a.view-btn {
            text-decoration: none;
            background-color: #007BFF;
            color: white;
            padding: 6px 10px;
            border-radius: 4px;
        }
        a.view-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<h2>📋 Manage VAPT Reports</h2>

<table>
    <thead>
        <tr>
            <th>RID</th>
            <th>Organization</th>
            <th>View Report</th>
            <th>Status</th>
            <th>Update</th>
        </tr>
    </thead>
    <tbody>
    <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <form method="post">
                <td><?= $row['rid'] ?></td>
                <td><?= htmlspecialchars($row['orgname']) ?></td>
                <td>
                    <a class="view-btn" href="vapt_final_report.php?orgid=<?= $row['orgid'] ?>&poid=<?= $row['poid'] ?>" target="_blank">🔍 View</a>
                </td>
                <td>
                    <select name="status">
                        <option value="Approved" <?= $row['reportstatus'] == "Approved" ? "selected" : "" ?>>Approved</option>
                        <option value="Rejected" <?= $row['reportstatus'] == "Rejected" ? "selected" : "" ?>>Rejected</option>
                        <option value="Pending" <?= $row['reportstatus'] == "Pending" ? "selected" : "" ?>>Pending</option>
                    </select>
                </td>
                <td>
                    <input type="hidden" name="rid" value="<?= $row['rid'] ?>">
                    <button type="submit" name="update_status">✅ Update</button>
                </td>
            </form>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>
