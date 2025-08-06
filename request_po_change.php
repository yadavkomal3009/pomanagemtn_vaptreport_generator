<?php
session_start();
require 'db_connect.php';

// ✅ Only logged-in users can access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user']['user_id'];
$org_id = $_SESSION['user']['orgid']; // Make sure orgid is stored in session during login
$msg = "";

// 🔽 Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $poid = $_POST['poid'];
    $type = $_POST['type'];
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);

    $query = "INSERT INTO po_requests (poid, userid, orgid, type, reason) 
              VALUES ('$poid', '$user_id', '$org_id', '$type', '$reason')";

    if (mysqli_query($conn, $query)) {
        $msg = "✅ Request submitted successfully!";
    } else {
        $msg = "❌ Error: " . mysqli_error($conn);
    }
}

// 🔽 Fetch user's POs only
$po_result = mysqli_query($conn, "SELECT id, po_no FROM po WHERE orgid = $org_id");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Request PO Change</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3 class="mb-4">✏️ Request PO Edit/Delete</h3>

    <?php if ($msg): ?>
        <div class="alert alert-info"><?= $msg ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label>Choose PO</label>
            <select name="poid" class="form-control" required>
                <option value="">-- Select PO --</option>
                <?php while($po = mysqli_fetch_assoc($po_result)): ?>
                    <option value="<?= $po['id'] ?>">PO# <?= htmlspecialchars($po['po_no']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Request Type</label>
            <select name="type" class="form-control" required>
                <option value="">-- Select Type --</option>
                <option value="edit">Edit PO</option>
                <option value="delete">Delete PO</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Reason for Request</label>
            <textarea name="reason" class="form-control" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit Request</button>
        <a href="user_dashboard.php" class="btn btn-secondary">Back</a>
    </form>
</div>
</body>
</html>
