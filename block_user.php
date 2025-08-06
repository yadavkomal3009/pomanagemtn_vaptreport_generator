<?php
session_start();
require 'db_connect.php';

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $conn->prepare("UPDATE users SET is_active = 0 WHERE user_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: manage_users.php?msg=User blocked");
exit();
