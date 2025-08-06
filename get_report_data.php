<?php
require_once('db_connect.php');

function getReportData($report_id) {
    global $conn;
    $data = [];

    // Main report info
    $sql = "SELECT r.*, u.username, p.po_no, p.po_date
            FROM vapt_reports r
            JOIN users u ON r.user_id = u.user_id
            JOIN purchase_orders p ON r.po_id = p.po_id
            WHERE r.report_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $report_id);
    $stmt->execute();
    $data['report'] = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Grouped vulnerabilities by device
    $sql2 = "SELECT vr.*, v.*, d.devname, d.devtype, d.devip, d.devloc
             FROM vapt_report_vulns vr
             JOIN vulnerabilities v ON vr.vuln_id = v.vuln_id
             JOIN devices d ON vr.device_id = d.devid
             WHERE vr.report_id = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("i", $report_id);
    $stmt2->execute();
    $result = $stmt2->get_result();

    $grouped = [];
    while ($row = $result->fetch_assoc()) {
        $devid = $row['device_id'];
        if (!isset($grouped[$devid])) {
            $grouped[$devid] = [
                'devname' => $row['devname'],
                'devtype' => $row['devtype'],
                'devip' => $row['devip'],
                'devloc' => $row['devloc'],
                'vulnerabilities' => []
            ];
        }
        $grouped[$devid]['vulnerabilities'][] = [
            'vuln_id' => $row['vuln_id'],
            'vname' => $row['vname'],
            'vlevel' => $row['vlevel'],
            'vdesc' => $row['vdesc'],
            'vimpact' => $row['vimpact'],
            'vsolu' => $row['vsolu']
        ];
    }
    $stmt2->close();

    $data['devices'] = $grouped;

    return $data;
}
