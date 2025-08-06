<?php
session_start();
include '../db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['vuln_no'])) {
    $vuln_no = $_POST['vuln_no'];

    $sql = "SELECT vr.report_id, v.*, u.username, p.po_no, p.po_date, r.customer_name, r.scope
            FROM vapt_report_vulns vr
            JOIN vulnerabilities v ON vr.vuln_id = v.vuln_id
            JOIN vapt_reports r ON vr.report_id = r.report_id
            JOIN users u ON r.user_id = u.user_id
            JOIN purchase_orders p ON r.po_id = p.po_id
            WHERE v.vuln_no = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $vuln_no);
    $stmt->execute();
    $result = $stmt->get_result();

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="vapt_report.csv"');

    $output = fopen("php://output", "w");
    fputcsv($output, ['PO No', 'PO Date', 'Auditor', 'Vuln No', 'Name', 'Severity', 'Description', 'URL', 'Parameters', 'CVE', 'Recommendation']);

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['po_no'], $row['po_date'], $row['username'], $row['vuln_no'], $row['name'],
            $row['severity'], $row['description'], $row['location_url'], $row['parameters'],
            $row['cve_reference'], $row['recommendation']
        ]);
    }
    fclose($output);
    exit;
}
?>
