<?php
include 'db_connect.php';

// Superadmin credentials
$first_name = "Super";
$last_name = "Admin";
$email = "superadmin@example.com";
$directorate = "Head Office";
$stpi_center = "Delhi";
$contact_number = "9999999999";
$gender = "Other";
$username = "superadmin";
$password = password_hash("admin123", PASSWORD_DEFAULT); // Use strong password
$role = "superadmin"; // ✅ Important: set role correctly
$is_active = 1;
$approval_status = "Approved";

// Check if superadmin already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo "⚠️ Superadmin already exists.";
} else {
    // Insert superadmin
    $stmt = $conn->prepare("INSERT INTO users 
        (first_name, last_name, email, directorate, stpi_center, contact_number, gender, username, password, role, is_active, approval_status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param(
        "ssssssssssis",
        $first_name,
        $last_name,
        $email,
        $directorate,
        $stpi_center,
        $contact_number,
        $gender,
        $username,
        $password,
        $role,
        $is_active,
        $approval_status
    );

    if ($stmt->execute()) {
        echo "✅ Superadmin created successfully. You can now login with username: <b>superadmin</b> and password: <b>admin123</b>";
    } else {
        echo "❌ Error: " . $stmt->error;
    }
}

$stmt->close();
$conn->close();
?>
