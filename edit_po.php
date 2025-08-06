<?php 
session_start();
require 'db_connect.php';

// 🔐 Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// 🔐 Get session details
$user = $_SESSION['user'];
$role = $user['role'] ?? '';
$orgid = $user['orgid'] ?? null;

// 🔎 Validate PO ID
$poid = $_GET['id'] ?? null;
if (!$poid || !is_numeric($poid)) {
    die("❌ Invalid PO ID.");
}

$msg = "";

// 🔍 Fetch PO
$stmt = $conn->prepare("SELECT * FROM po WHERE id = ?");
$stmt->bind_param("i", $poid);
$stmt->execute();
$result = $stmt->get_result();
$po = $result->fetch_assoc();

if (!$po) {
    die("❌ PO not found.");
}

// 🚫 Users can only edit POs from their org
if ($role !== 'admin' && $po['orgid'] != $orgid) {
    die("⛔ Unauthorized access.");
}

// 📝 Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $po_no = $_POST['po_no'];
    $po_date = $_POST['po_date'];
    $service_type = $_POST['service_type'];
    $scope_of_work = $_POST['scope_of_work'];
    $duration = $_POST['duration'];
    $value = $_POST['value'];

    // 🗃️ Handle file upload (optional)
    $tender_file = $po['tender_file'];
    if (!empty($_FILES['tender_file']['name'])) {
        $fileName = time() . '_' . basename($_FILES['tender_file']['name']);
        $target = "uploads/" . $fileName;
        if (move_uploaded_file($_FILES['tender_file']['tmp_name'], $target)) {
            $tender_file = $fileName;
        }
    }

    // 🛠️ Update PO
    $stmt = $conn->prepare("UPDATE po SET po_no=?, po_date=?, service_type=?, scope_of_work=?, duration=?, value=?, tender_file=? WHERE id=?");
    $stmt->bind_param("sssssdsi", $po_no, $po_date, $service_type, $scope_of_work, $duration, $value, $tender_file, $poid);

    if ($stmt->execute()) {
        header("Location: manage_pos.php?msg=updated");
        exit();
    } else {
        $msg = "❌ Update failed: " . $conn->error;
    }
}
?>

<!-- ✅ Edit PO Form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit PO</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h3 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        .mb-2 {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="date"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            background-color: #007bff;
            color: white;
            font-size: 16px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 4px;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .alert {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<div class="container">
    <h3>✏️ Edit PO #<?= htmlspecialchars($po['po_no']) ?></h3>

    <?php if ($msg): ?>
        <div class="alert"><?= $msg ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-2">
            <label>PO Number</label>
            <input type="text" name="po_no" value="<?= htmlspecialchars($po['po_no']) ?>" required>
        </div>

        <div class="mb-2">
            <label>PO Date</label>
            <input type="date" name="po_date" value="<?= htmlspecialchars($po['po_date']) ?>" required>
        </div>

        <div class="mb-2">
            <label>Service Type</label>
            <input type="text" name="service_type" value="<?= htmlspecialchars($po['service_type']) ?>" required>
        </div>

        <div class="mb-2">
            <label>Scope of Work</label>
            <textarea name="scope_of_work" required><?= htmlspecialchars($po['scope_of_work']) ?></textarea>
        </div>

        <div class="mb-2">
            <label>Duration</label>
            <input type="text" name="duration" value="<?= htmlspecialchars($po['duration']) ?>" required>
        </div>

        <div class="mb-2">
            <label>Value (₹)</label>
            <input type="number" name="value" step="0.01" value="<?= htmlspecialchars($po['value']) ?>" required>
        </div>

        <div class="mb-2">
            <label>Tender File</label>
            <input type="file" name="tender_file">
            <?php if ($po['tender_file']): ?>
                <p>Current: <a href="uploads/<?= htmlspecialchars($po['tender_file']) ?>" target="_blank">📄 View File</a></p>
            <?php endif; ?>
        </div>

        <button type="submit">💾 Update PO</button>
        <a href="manage_pos.php" class="btn-secondary">🔙 Back</a>
    </form>
</div>
</body>
</html>
