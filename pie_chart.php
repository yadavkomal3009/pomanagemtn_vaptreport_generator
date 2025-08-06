<?php
include 'db_connect.php';
$orgid = intval($_GET['orgid'] ?? 0);
$poid = intval($_GET['poid'] ?? 0);

$query = $conn->query("
    SELECT v.vlevel, COUNT(*) as count
    FROM report r
    JOIN vul v ON r.vid = v.vid
    WHERE r.orgid = $orgid AND r.poid = $poid
    GROUP BY v.vlevel
");

$data = [];
$levels = ['Critical', 'High', 'Medium', 'Low', 'Informational'];
$colors = [
    'Critical' => [255, 87, 34],
    'High' => [255, 0, 0],
    'Medium' => [255, 193, 7],
    'Low' => [76, 175, 80],
    'Informational' => [158, 158, 158]
];

$total = 0;
while ($row = $query->fetch_assoc()) {
    $data[$row['vlevel']] = $row['count'];
    $total += $row['count'];
}

$width = $height = 300;
$image = imagecreate($width, $height);
$bg = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);

$cx = $cy = $width / 2;
$radius = 100;
$start = 0;

foreach ($levels as $level) {
    $count = $data[$level] ?? 0;
    if ($count == 0) continue;

    $angle = round(($count / $total) * 360);
    $end = $start + $angle;

    $color = imagecolorallocate($image, ...$colors[$level]);
    imagefilledarc($image, $cx, $cy, $radius * 2, $radius * 2, $start, $end, $color, IMG_ARC_PIE);

    $start = $end;
}

$legendY = 10;
foreach ($levels as $level) {
    $val = $data[$level] ?? 0;
    if ($val > 0) {
        $color = imagecolorallocate($image, ...$colors[$level]);
        imagefilledrectangle($image, 210, $legendY, 225, $legendY + 10, $color);
        imagestring($image, 2, 230, $legendY, "$level: $val", $black);
        $legendY += 15;
    }
}

header("Content-type: image/png");
imagepng($image);
imagedestroy($image);
?>
