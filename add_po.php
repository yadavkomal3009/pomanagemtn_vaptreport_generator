<?php
session_start();
require 'db_connect.php';

// ✅ Redirect to login if user not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user     = $_SESSION['user'];
$orgid    = $user['orgid'] ?? null;
$user_id  = $user['user_id'] ?? null;

$msg = "";

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $po_no         = trim($_POST['po_no']);
    $po_date       = $_POST['po_date'];
    $service_type  = trim($_POST['service_type']);
    $scope_of_work = trim($_POST['scope_of_work']);
    $duration      = trim($_POST['duration']);
    $value         = (float) $_POST['value'];
    $filename      = '';

    // ✅ Handle file upload
    if (isset($_FILES['tender_file']) && $_FILES['tender_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $original_name = basename($_FILES['tender_file']['name']);
        $filename = uniqid('po_', true) . '_' . $original_name;
        $target_path = $upload_dir . $filename;

        if (!move_uploaded_file($_FILES['tender_file']['tmp_name'], $target_path)) {
            $msg = "❌ Failed to upload file.";
        }
    }

    // ✅ Insert into DB if no error
    if (empty($msg)) {
        $stmt = $conn->prepare("INSERT INTO po(orgid, po_no, po_date, service_type, scope_of_work, duration, value, tender_file, user_id)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssssi", $orgid, $po_no, $po_date, $service_type, $scope_of_work, $duration, $value, $filename, $user_id);

        if ($stmt->execute()) {
            $msg = "✅ Purchase Order added successfully!";
        } else {
            $msg = "❌ Database error: " . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Purchase Order</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 25px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .form-label {
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        .form-control {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 15px;
            border: 1px solid #ced4da;
            border-radius: 5px;
        }
        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            margin-left: 10px;
        }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #007bff;
            border-radius: 5px;
            background-color: #e7f1ff;
            color: #0056b3;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>➕ Add New Purchase Order</h2>

    <?php if (!empty($msg)): ?>
        <div class="alert"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label class="form-label">PO Number</label>
        <input type="text" name="po_no" class="form-control" required>

        <label class="form-label">PO Date</label>
        <input type="date" name="po_date" class="form-control" required>

        <label class="form-label">Service Type</label>
        <input type="text" name="service_type" class="form-control" required>

        <label class="form-label">Scope of Work</label>
        <textarea name="scope_of_work" class="form-control" rows="3" required></textarea>

        <label class="form-label">Duration</label>
        <input type="text" name="duration" class="form-control" required>

        <label class="form-label">Value (₹)</label>
        <input type="number" step="0.01" name="value" class="form-control" required>

        <label class="form-label">Tender File (PDF)</label>
        <input type="file" name="tender_file" class="form-control" accept=".pdf">

        <button type="submit" class="btn btn-primary">Submit PO</button>
        <a href="manage_pos.php" class="btn btn-secondary">Back</a>
    </form>
</div>
</body>
</html>
