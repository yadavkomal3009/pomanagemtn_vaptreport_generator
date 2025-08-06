<?php
include 'db_connect.php';
session_start();

$rid = intval($_POST['rid'] ?? 0);
$orgid = intval($_POST['orgid'] ?? 0);
$poid = intval($_POST['poid'] ?? 0);

if ($rid === 0 || $orgid === 0 || $poid === 0) {
    echo "❌ Missing required parameters. <a href='javascript:history.back()'>Go Back</a>";
    exit;
}

if (isset($_FILES['poc_image']) && $_FILES['poc_image']['error'] === UPLOAD_ERR_OK) {
    $tmp_name = $_FILES['poc_image']['tmp_name'];
    $name = basename($_FILES['poc_image']['name']);
    $safe_name = preg_replace("/[^A-Za-z0-9_\.-]/", "_", $name);
    $target_dir = "poc_uploads/";
    $target = $target_dir . uniqid() . "_" . $safe_name;

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if (move_uploaded_file($tmp_name, $target)) {
        $stmt = $conn->prepare("UPDATE report SET vimage = ? WHERE rid = ?");
        $stmt->bind_param("si", $target, $rid);
        $stmt->execute();
        echo "✅ POC uploaded successfully.<br>";
        echo "<a href='vapt_final_report.php?orgid=" . htmlspecialchars($orgid) . "&poid=" . htmlspecialchars($poid) . "'>🔙 Go Back to Report</a>";
    } else {
        echo "❌ Failed to move uploaded file. Check folder permissions.";
    }
} else {
    echo "❌ Upload error or no file selected.";
}
?>
