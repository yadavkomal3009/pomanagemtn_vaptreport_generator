<?php
include 'db_connect.php';
$orgid = intval($_GET['orgid'] ?? 0);
$poid = intval($_GET['poid'] ?? 0);

// Get data from DB
$query = $conn->query("
    SELECT d.devname, v.vlevel, COUNT(*) as count
    FROM report r
    JOIN device d ON r.did = d.devid
    JOIN vul v ON r.vid = v.vid
    WHERE r.orgid = $orgid AND r.poid = $poid
    GROUP BY d.devname, v.vlevel
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

while ($row = $query->fetch_assoc()) {
    $data[$row['devname']][$row['vlevel']] = $row['count'];
}

// Draw image
$width = 800;
$height = 400;
$image = imagecreate($width, $height);
$bg = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);

// Fonts
$font = 3;
$barWidth = 20;
$gap = 10;
$startX = 80;
$yBase = $height - 50;

// Scaling
$maxVal = 0;
foreach ($data as $dev => $vals) {
    foreach ($vals as $v => $count) {
        if ($count > $maxVal) $maxVal = $count;
    }
}
$scale = ($height - 100) / ($maxVal ?: 1);

$x = $startX;
foreach ($data as $dev => $vals) {
    foreach ($levels as $level) {
        $val = $vals[$level] ?? 0;
        $barHeight = $val * $scale;
        $color = imagecolorallocate($image, ...$colors[$level]);

        imagefilledrectangle($image, $x, $yBase - $barHeight, $x + $barWidth, $yBase, $color);
        imagestring($image, 1, $x, $yBase - $barHeight - 12, $val > 0 ? $val : '', $black);

        $x += $barWidth + 2;
    }
    imagestring($image, $font, $x - ($barWidth * count($levels)) / 2 - 5, $yBase + 5, $dev, $black);
    $x += $gap;
}

header("Content-type: image/png");
imagepng($image);
imagedestroy($image);
?>
