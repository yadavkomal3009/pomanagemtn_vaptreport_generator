<?php
require 'db_connect.php';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    die("❌ Invalid device ID.");
}

// 🔍 Fetch device details
$stmt = $conn->prepare("SELECT * FROM device WHERE devid = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$device = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$device) {
    die("❌ Device not found.");
}

$msg = "";

// ✅ Handle update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $devname = $_POST['devname'] ?? '';
    $devip   = $_POST['devip'] ?? '';
    $devloc  = $_POST['devloc'] ?? '';

    $stmt = $conn->prepare("UPDATE device SET devname=?, devip=?, devloc=? WHERE devid=?");
    $stmt->bind_param("sssi", $devname, $devip, $devloc, $id);

    if ($stmt->execute()) {
        header("Location: manage_devices.php?msg=updated");
        exit();
    } else {
        $msg = "❌ Update failed: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Device</title>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 600px;
            margin: 3rem auto;
        }
        .card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 1.5rem;
        }
        .card-header {
            background-color: #ffc107;
            padding: 1rem;
            border-radius: 8px 8px 0 0;
        }
        .card-header h4 {
            margin: 0;
        }
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        .form-control {
            width: 100%;
            padding: 0.5rem;
            margin-bottom: 1rem;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            text-decoration: none;
            cursor: pointer;
            border-radius: 4px;
            margin-right: 0.5rem;
        }
        .btn-success {
            background-color: #28a745;
            color: #fff;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: #fff;
        }
        .alert {
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header">
            <h4>✏️ Edit Device</h4>
        </div>
        <div class="card-body">
            <?php if ($msg): ?>
                <div class="alert"><?= htmlspecialchars($msg) ?></div>
            <?php endif; ?>

            <form method="post">
                <label class="form-label">Device Name</label>
                <input type="text" name="devname" class="form-control"
                       value="<?= htmlspecialchars($device['devname']) ?>" required>

                <label class="form-label">IP Address</label>
                <input type="text" name="devip" class="form-control"
                       value="<?= htmlspecialchars($device['devip']) ?>" required>

                <label class="form-label">Location</label>
                <input type="text" name="devloc" class="form-control"
                       value="<?= htmlspecialchars($device['devloc']) ?>">

                <button type="submit" class="btn btn-success">💾 Update</button>
                <a href="manage_devices.php" class="btn btn-secondary">⬅ Back</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
