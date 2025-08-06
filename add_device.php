<?php
require 'db_connect.php';
session_start();

$msg = "";
$show_form = true;

// Get return URL (either from query param or HTTP referer)
$returnUrl = $_GET['return'] ?? $_SERVER['HTTP_REFERER'] ?? 'manage_devices.php';

// Get orgid and poid from GET (for form pre-fill), or POST (after submission)
$orgid = $_GET['orgid'] ?? ($_POST['orgid'] ?? '');
$poid  = $_GET['poid']  ?? ($_POST['poid'] ?? '');

// ✅ Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $devname     = $_POST['devname'] ?? '';
    $devtype     = $_POST['devtype'] ?? '';
    $devmake     = $_POST['devmake'] ?? '';
    $devmodel    = $_POST['devmodel'] ?? '';
    $devdesc     = $_POST['devdesc'] ?? '';
    $devip       = $_POST['devip'] ?? '';
    $devloc      = $_POST['devloc'] ?? '';
    $devremarks  = $_POST['devremarks'] ?? '';
    $created_at  = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO device (orgid, poid, devname, devtype, devmake, devmodel, devdesc, devip, devloc, devremarks, created_at)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisssssssss", $orgid, $poid, $devname, $devtype, $devmake, $devmodel, $devdesc, $devip, $devloc, $devremarks, $created_at);

    if ($stmt->execute()) {
        header("Location: " . $returnUrl);
        exit();
    } else {
        $msg = "❌ Failed to add device: " . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Device</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f1f1f1;
            padding: 20px;
        }
        .container {
            max-width: 700px;
            margin: auto;
            background: white;
            padding: 25px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }
        h2 {
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: 600;
            margin-top: 12px;
            display: block;
        }
        .form-control {
            width: 100%;
            padding: 10px 12px;
            margin-top: 5px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .btn {
            padding: 10px 18px;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            color: white;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-secondary {
            background-color: #6c757d;
            margin-left: 10px;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 5px;
            color: white;
        }
        .alert {
            background-color: #ffdddd;
            color: #d8000c;
            border: 1px solid #d8000c;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>➕ Add New Device</h2>

    <?php if (!empty($msg)): ?>
        <div class="alert"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <?php if ($show_form): ?>
        <form method="POST">
            <input type="hidden" name="orgid" value="<?= htmlspecialchars($orgid) ?>">
            <input type="hidden" name="poid" value="<?= htmlspecialchars($poid) ?>">

            <label class="form-label">Organization ID</label>
            <input type="text" value="<?= htmlspecialchars($orgid) ?>" class="form-control" disabled>

            <label class="form-label">PO ID</label>
            <input type="text" value="<?= htmlspecialchars($poid) ?>" class="form-control" disabled>

            <label class="form-label">Device Name</label>
            <input type="text" name="devname" class="form-control" required>

            <label class="form-label">Device Type</label>
            <input type="text" name="devtype" class="form-control" required>

            <label class="form-label">Device Make</label>
            <input type="text" name="devmake" class="form-control" required>

            <label class="form-label">Device Model</label>
            <input type="text" name="devmodel" class="form-control" required>

            <label class="form-label">Description</label>
            <textarea name="devdesc" class="form-control" rows="2"></textarea>

            <label class="form-label">IP Address</label>
            <input type="text" name="devip" class="form-control">

            <label class="form-label">Location</label>
            <input type="text" name="devloc" class="form-control">

            <label class="form-label">Remarks</label>
            <textarea name="devremarks" class="form-control" rows="2"></textarea>

            <button type="submit" class="btn">✅ Submit</button>
            <a href="<?= htmlspecialchars($returnUrl) ?>" class="btn-secondary">⬅️ Back</a>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
