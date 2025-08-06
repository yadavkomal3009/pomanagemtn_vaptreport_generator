<?php
session_start();
include 'db_connect.php';

$userId = intval($_SESSION['user_id'] ?? 0);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $po_no         = $_POST['po_no'] ?? '';
    $po_date       = $_POST['po_date'] ?? '';
    $service_type  = $_POST['service_type'] ?? '';
    $scope_of_work = $_POST['scope_of_work'] ?? '';
    $duration      = $_POST['duration'] ?? '';
    $value         = floatval($_POST['value'] ?? 0);
    $centre_id     = intval($_POST['centre_id'] ?? 0);

    $tenderFileName = null;

    if (isset($_FILES['tender_file']) && $_FILES['tender_file']['error'] === 0) {
        $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
        $fileExtension = strtolower(pathinfo($_FILES['tender_file']['name'], PATHINFO_EXTENSION));

        if (in_array($fileExtension, $allowedExtensions)) {
            $uploadDir = 'uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $tenderFileName = uniqid('tender_') . '.' . $fileExtension;
            $filePath = $uploadDir . $tenderFileName;

            if (!move_uploaded_file($_FILES['tender_file']['tmp_name'], $filePath)) {
                $message = "❌ File upload failed.";
            }
        } else {
            $message = "❌ Invalid file type. Only PDF, DOC, DOCX, XLS, XLSX allowed.";
        }
    }

    if (!$message && $po_no && $po_date && $service_type && $scope_of_work && $duration && $value > 0 && $centre_id > 0) {
        $stmt = $conn->prepare("INSERT INTO po (user_id, centre_id, po_no, po_date, service_type, scope_of_work, duration, value, tender_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssssds", $userId, $centre_id, $po_no, $po_date, $service_type, $scope_of_work, $duration, $value, $tenderFileName);

        if ($stmt->execute()) {
            $message = "✅ PO uploaded successfully. Admin will review.";
        } else {
            $message = "❌ Database error: " . $conn->error;
        }
        $stmt->close();
    } else if (!$message) {
        $message = "❌ Please fill all fields correctly.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Upload Purchase Order</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; padding: 40px; }
    .container { max-width: 700px; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
  </style>
</head>
<body>
<div class="container">
  <h2 class="mb-4">📤 Upload Purchase Order</h2>

  <?php if ($message): ?>
      <div class="alert alert-info"> <?= htmlspecialchars($message); ?> </div>
  <?php endif; ?>

  <form method="POST" action="upload_po.php" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label">PO Number</label>
      <input type="text" name="po_no" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">PO Date</label>
      <input type="date" name="po_date" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Service Type</label>
      <input type="text" name="service_type" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Scope of Work</label>
      <textarea name="scope_of_work" class="form-control" required></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Duration</label>
      <input type="text" name="duration" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Value (₹)</label>
      <input type="number" name="value" class="form-control" step="0.01" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Centre ID</label>
      <input type="number" name="centre_id" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Tender File (PDF, DOCX, XLSX)</label>
      <input type="file" name="tender_file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx" required>
    </div>

    <button type="submit" class="btn btn-primary">Upload PO</button>
    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
  </form>
</div>
</body>
</html>
