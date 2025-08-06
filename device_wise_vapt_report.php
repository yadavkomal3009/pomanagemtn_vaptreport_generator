<?php
// device_wise_vapt_report.php
include 'db_connect.php';
session_start();

$orgid = intval($_GET['orgid'] ?? 0);
$poid = intval($_GET['poid'] ?? 0);

if (!$orgid || !$poid) {
    echo "<p style='color:red;'>❌ Missing Org ID or PO ID.</p>";
    exit;
}

// =================== Table Data with correct V.No ===================
$table_res = $conn->query("
    SELECT r.rid, r.orgid, r.poid, r.did, r.vid, v.vlevel,
    (SELECT COUNT(*) FROM report r2 JOIN vul v2 ON r2.vid = v2.vid WHERE r2.did = r.did AND v2.vlevel = v.vlevel AND r2.orgid = $orgid AND r2.poid = $poid) AS vno
    FROM report r
    JOIN vul v ON r.vid = v.vid
    WHERE r.orgid = $orgid AND r.poid = $poid
    GROUP BY r.rid, r.orgid, r.poid, r.did, r.vid, v.vlevel
    ORDER BY r.did ASC, FIELD(v.vlevel, 'Critical', 'High', 'Medium', 'Low', 'Informational')
");

// =================== Graph Data ===================
$graph_res = $conn->query("
    SELECT d.devname, v.vlevel, COUNT(*) as count
    FROM report r
    JOIN device d ON r.did = d.devid
    JOIN vul v ON r.vid = v.vid
    WHERE r.orgid = $orgid AND r.poid = $poid
    GROUP BY d.devname, v.vlevel
");

$graph_data = [];
$severity_count = ['Critical' => 0, 'High' => 0, 'Medium' => 0, 'Low' => 0, 'Informational' => 0];

while ($row = $graph_res->fetch_assoc()) {
    $graph_data[$row['devname']][$row['vlevel']] = $row['count'];
    $severity_count[$row['vlevel']] += $row['count'];
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Device Wise VAPT Report</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<style>
    #barChart, #pieChart {
        max-width: 400px;
        max-height: 250px;
        margin: 10px auto;
        display: block;
    }

    body {
        font-family: 'Segoe UI', sans-serif;
        background: #f9f9f9;
        padding: 20px;
    }
    .container {
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        max-width: 1000px;
        margin: auto;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    h2 {
        color: #333;
        text-align: center;
    }
    table {
        border-collapse: collapse;
        width: 100%;
        margin-top: 20px;
        background: #fff;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: center;
    }
    th {
        background: #f0f0f0;
    }
    button {
        padding: 8px 14px;
        background: #007BFF;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin-bottom: 10px;
    }
    button:hover {
        background: #0056b3;
    }
    @media print {
        button {
            display: none;
        }
    }
</style>

</head>
<body>
<div class="container">
    <h2>📊 Device Wise VAPT Report with Correct Table & Graphs</h2>
    <button onclick="window.print()">🖨️ Print</button>

    <!-- Table -->
    <table>
        <thead>
            <tr>
                <th>RID</th>
                <th>OrgID</th>
                <th>POID</th>
                <th>DID</th>
                <th>VID</th>
                <th>Severity</th>
                <th>V.No</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $table_res->fetch_assoc()): ?>
            <tr>
                <td><?= $row['rid'] ?></td>
                <td><?= $row['orgid'] ?></td>
                <td><?= $row['poid'] ?></td>
                <td><?= $row['did'] ?></td>
                <td><?= $row['vid'] ?></td>
                <td><?= $row['vlevel'] ?></td>
                <td><?= $row['vno'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Graphs -->
    <canvas id="barChart"></canvas>
    <canvas id="pieChart"></canvas>
</div>

<script>
Chart.register(ChartDataLabels); // ✅ Register datalabels

const ctxBar = document.getElementById('barChart').getContext('2d');
const ctxPie = document.getElementById('pieChart').getContext('2d');

const deviceLabels = <?php echo json_encode(array_keys($graph_data)); ?>;
const severityLevels = ['Critical', 'High', 'Medium', 'Low', 'Informational'];
const datasetColors = ['#ff8f33ff', '#ff6666', '#ffcc66', '#99ff99', '#cccccc'];

const graphData = <?php echo json_encode($graph_data); ?>;

const datasets = severityLevels.map((level, idx) => ({
    label: level,
    backgroundColor: datasetColors[idx],
    data: deviceLabels.map(device => graphData[device]?.[level] ?? 0)
}));

// Horizontal Grouped Bar Chart with Percentages
new Chart(ctxBar, {
    type: 'bar',
    data: {
        labels: deviceLabels,
        datasets: datasets
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: {
            legend: { position: 'top' },
            datalabels: {
                anchor: 'end',
                align: 'right',
                color: '#000',
                formatter: (value) => value > 0 ? value : ''
            },
            tooltip: {
                callbacks: {
                    label: (context) => {
                        const dataset = context.dataset.data;
                        const total = dataset.reduce((a,b)=>a+b,0);
                        const percentage = total > 0 ? ((context.parsed.x / total) * 100).toFixed(1) : 0;
                        return `${context.dataset.label}: ${context.parsed.x} (${percentage}%)`;
                    }
                }
            }
        },
        scales: {
            x: { beginAtZero: true, stepSize: 1 }
        }
    },
    plugins: [ChartDataLabels]
});

// Pie Chart with Percentages
new Chart(ctxPie, {
    type: 'pie',
    data: {
        labels: severityLevels,
        datasets: [{
            data: <?php echo json_encode(array_values($severity_count)); ?>,
            backgroundColor: datasetColors
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom' },
            datalabels: {
                formatter: (value, ctx) => {
                    const total = ctx.chart.data.datasets[0].data.reduce((a,b)=>a+b,0);
                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                    return value > 0 ? `${percentage}%` : '';
                },
                color: '#000'
            }
        }
    },
    plugins: [ChartDataLabels]
});
</script>
</body>
</html>
