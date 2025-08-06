<?php
session_start();
include 'db_connect.php';

// Session validation
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$username = $user['username'];
$role = $user['role'];

// Check if RID is provided
if (!isset($_GET['rid'])) {
    echo "❌ Report ID not provided.";
    exit();
}

$rid = intval($_GET['rid']);

// Fetch the report details if approved
$stmt = $conn->prepare("
    SELECT r.*, o.orgname, p.po_no, d.devname, v.vname
    FROM report r
    LEFT JOIN organizations o ON r.orgid = o.orgid
    LEFT JOIN po p ON r.poid = p.id
    LEFT JOIN device d ON r.did = d.devid
    LEFT JOIN vul v ON r.vid = v.vid
    WHERE r.rid = ? AND r.reportstatus = 'Approved'
");
$stmt->bind_param("i", $rid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "❌ Report not found or not approved by superadmin.";
    exit();
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Certificate for Report ID <?= $rid ?></title>
    <style>
        body {
            font-family: 'Georgia', serif;
            text-align: center;
            background: #f5f5f5;
            padding: 50px;
        }
        .certificate {
            border: 10px solid #1976d2;
            background: white;
            padding: 50px;
            width: 800px;
            margin: auto;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
        }
        h1 {
            font-size: 36px;
            color: #1976d2;
        }
        h2 {
            font-size: 24px;
            margin-top: 10px;
        }
        p {
            font-size: 18px;
        }
        .print-btn {
            margin-top: 20px;
            padding: 10px 20px;
            background: #1976d2;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .print-btn:hover {
            background: #135ba1;
        }
    </style>
</head>
<body>

<div class="certificate" id="certificate">
    <h1>Certificate of VAPT Report Approval</h1>
    <h2>Report ID: <?= htmlspecialchars($row['rid']) ?></h2>
    <p>This is to certify that the VAPT report has been successfully reviewed and approved.</p>
    <br>
    <p><strong>Organization:</strong> <?= htmlspecialchars($row['orgname'] ?? 'N/A') ?></p>
    <p><strong>PO Number:</strong> <?= htmlspecialchars($row['po_no'] ?? 'N/A') ?></p>
    <p><strong>Device:</strong> <?= htmlspecialchars($row['devname'] ?? 'N/A') ?></p>
    <p><strong>Vulnerability:</strong> <?= htmlspecialchars($row['vname'] ?? 'N/A') ?></p>
    <p><strong>Created At:</strong> <?= htmlspecialchars($row['created_at']) ?></p>
    <p><strong>Approved By:</strong> Superadmin</p>
    <br><br>
    <p>Issued On: <?= date("Y-m-d") ?></p>
</div>

<button class="print-btn" onclick="window.print()">🖨️ Download Certificate (Print PDF)</button>

</body>
</html>
