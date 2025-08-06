<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user'])) {
    die("Not logged in.");
}

$role = $_SESSION['user']['role'];
$orgid = $_SESSION['user']['orgid'] ?? null;
$search = $_GET['search'] ?? '';
$like = "%{$search}%";

// CSV headers
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="purchase_orders.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'PO No', 'Date', 'Service', 'Value', 'Organization']);

if ($role === 'admin') {
    $stmt = $conn->prepare("
        SELECT p.*, o.orgname 
        FROM po p 
        LEFT JOIN organizations o ON p.orgid = o.orgid 
        WHERE p.po_no LIKE ? OR p.service_type LIKE ?
    ");
    $stmt->bind_param("ss", $like, $like);
} else {
    $stmt = $conn->prepare("
        SELECT p.*, '' as orgname 
        FROM po p 
        WHERE p.orgid = ? AND (p.po_no LIKE ? OR p.service_type LIKE ?)
    ");
    $stmt->bind_param("iss", $orgid, $like, $like);
}
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['id'],
        $row['po_no'],
        $row['po_date'],
        $row['service_type'],
        $row['value'],
        $role === 'admin' ? $row['orgname'] : ''
    ]);
}

fclose($output);
exit;
