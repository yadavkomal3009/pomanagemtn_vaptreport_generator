<?php
$conn = new mysqli("localhost", "root", "", "po_management");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
?>