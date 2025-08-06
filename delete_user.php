<?php
session_start();
require 'db_connect.php';

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: manage_users.php?msg=User deleted");
exit();
