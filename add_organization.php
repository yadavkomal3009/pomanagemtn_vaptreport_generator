<?php
session_start();
require_once 'db_connect.php';

// ✅ Allow any logged-in user
if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id']; // Used for created_by

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orgname = trim($_POST['orgname']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact']);
    $address = trim($_POST['address']);
    $created_by = $user_id;

    if (empty($orgname)) {
        die("❌ Organization name is required.");
    }

    $stmt = $conn->prepare("INSERT INTO organizations (orgname, email, contact, address, created_by) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("❌ Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ssssi", $orgname, $email, $contact, $address, $created_by);

    if ($stmt->execute()) {
        header("Location: manage_organizations.php");
        exit;
    } else {
        echo "❌ Database Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Organization</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; padding: 30px; }
        .container { max-width: 600px; margin: auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        h2 { color: #333; margin-bottom: 20px; }
        .mb-3 { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="text"], input[type="email"], textarea {
            width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;
        }
        button {
            background-color: #007bff; color: white; padding: 10px 15px; border: none;
            border-radius: 4px; cursor: pointer; font-size: 16px;
        }
        button:hover { background-color: #0056b3; }
        .btn-secondary {
            background-color: #6c757d; color: white; text-decoration: none;
            padding: 10px 15px; border-radius: 4px; margin-left: 10px;
        }
        .btn-secondary:hover { background-color: #5a6268; }
    </style>
</head>
<body>
<div class="container">
    <h2>➕ Add Organization</h2>
    <form method="POST">
        <div class="mb-3">
            <label>Organization Name</label>
            <input type="text" name="orgname" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email">
        </div>
        <div class="mb-3">
            <label>Contact</label>
            <input type="text" name="contact">
        </div>
        <div class="mb-3">
            <label>Address</label>
            <textarea name="address"></textarea>
        </div>
        <button type="submit">Submit</button>
        <a href="manage_organizations.php" class="btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
