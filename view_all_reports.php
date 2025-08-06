<?php
include 'db_connect.php';
session_start();
require 'auth.php';

$sql = "SELECT username, report_file, vstatus FROM report";
$result = $conn->query($sql);

echo "<h2>All Reports</h2><table border='1' cellpadding='10'>";
echo "<tr><th>Uploaded By</th><th>Report File</th><th>Status</th><th>Action</th></tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$row['username']}</td>
        <td>";
        if (!empty($row['report_file'])) {
            echo basename($row['report_file']);  // just show the file name
        } else {
            echo "Not generated";
        }
    echo "</td>
        <td>{$row['vstatus']}</td>
        <td>";
        if (!empty($row['report_file'])) {
            echo "<a href='{$row['report_file']}' target='_blank'>View</a>";
        } else {
            echo "N/A";
        }
    echo "</td></tr>";
}
echo "</table>";
?>
