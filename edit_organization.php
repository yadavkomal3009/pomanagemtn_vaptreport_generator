<?php
session_start();
require_once 'db_connect.php';

// ✅ Allow only logged-in users (no role/approval checks)
if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit;
}

// Get organization ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("❌ Invalid organization ID.");
}

// Fetch organization
$stmt = $conn->prepare("SELECT * FROM organizations WHERE orgid = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$org = $result->fetch_assoc();

if (!$org) {
    die("❌ Organization not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orgname = trim($_POST['orgname']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact']);
    $address = trim($_POST['address']);

    if (empty($orgname)) {
        die("❌ Organization name is required.");
    }

    $update = "UPDATE organizations SET orgname=?, email=?, contact=?, address=? WHERE orgid=?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("ssssi", $orgname, $email, $contact, $address, $id);

    if ($stmt->execute()) {
        header("Location: manage_organizations.php");
        exit;
    } else {
        echo "❌ Update Error: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Organization</title>
    <style>
        body { font-family: Arial; background-color: #f4f6f8; padding: 40px; }
        .container {
            max-width: 500px; margin: auto;
            background: #fff; padding: 25px;
            border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h2 { color: #333; text-align: center; }
        input, textarea {
            width: 100%; padding: 10px; margin-bottom: 15px;
            border: 1px solid #ccc; border-radius: 4px;
        }
        button, .btn-secondary {
            padding: 10px 16px; border: none; border-radius: 4px;
            text-decoration: none; color: white; cursor: pointer;
        }
        button { background-color: #007bff; }
        .btn-secondary { background-color: #6c757d; margin-left: 10px; }
        .btn-secondary:hover { background-color: #5a6268; }
    </style>
</head>
<body>
<div class="container">
    <h2>Edit Organization</h2>
    <form method="post">
        <input type="text" name="orgname" value="<?= htmlspecialchars($org['orgname']) ?>" required>
        <input type="email" name="email" value="<?= htmlspecialchars($org['email']) ?>">
        <input type="text" name="contact" value="<?= htmlspecialchars($org['contact']) ?>">
        <textarea name="address"><?= htmlspecialchars($org['address']) ?></textarea>
        <button type="submit">Update</button>
        <a href="manage_organizations.php" class="btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
