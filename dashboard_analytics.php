<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>📊 Dashboard Analytics</title>
 <a href="superadmin_dashboard.php" class="btn btn-warning">🔙 Back</a>
<style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background: #f4f6f9;
        margin: 0;
        padding: 20px;
    }
    .container {
        max-width: 1200px;
        margin: auto;
    }
    h2 {
        text-align: center;
        color: #0d6efd;
        margin-bottom: 30px;
    }
    .charts {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: center;
    }
    .chart-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        flex: 1 1 400px;
        max-width: 550px;
        position: relative;
    }
    canvas {
        width: 100%;
        height: auto;
        background: #fff;
        border-radius: 8px;
    }
    h4 {
        margin-bottom: 15px;
        color: #333;
    }
    .legend {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
        font-size: 14px;
    }
    .legend span {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .legend-box {
        width: 12px;
        height: 12px;
        border-radius: 3px;
        display: inline-block;
    }
</style>
</head>
<body>
<div class="container">
    <h2>📊 Dashboard Analytics</h2>
    <div class="charts">
        <div class="chart-card">
            <h4>Total Users by Role</h4>
            <canvas id="barChart" width="500" height="300"></canvas>
        </div>
        <div class="chart-card">
            <h4>Vulnerabilities per Month</h4>
            <canvas id="lineChart" width="500" height="300"></canvas>
        </div>
        <div class="chart-card">
            <h4>Vulnerabilities by Severity</h4>
            <canvas id="pieChart" width="300" height="300"></canvas>
            <div class="legend" id="pieLegend"></div>
        </div>
        <div class="chart-card">
            <h4>Reports Approved / Rejected</h4>
            <canvas id="doughnutChart" width="300" height="300"></canvas>
            <div class="legend" id="doughnutLegend"></div>
        </div>
    </div>
</div>

<script>
// Dummy Data
const userRoles = { "Admin": 5, "User": 15, "Superadmin": 2 };
const months = ["Mar", "Apr", "May", "Jun", "Jul", "Aug"];
const vulnCounts = [2, 5, 3, 8, 6, 4];
const severityData = { "Critical": 2, "High": 5, "Medium": 7, "Low": 3 };
const reportsData = [6, 2]; // Approved, Rejected

// Utility to add legend
function renderLegend(colors, labels, containerId) {
    const container = document.getElementById(containerId);
    container.innerHTML = "";
    labels.forEach((label, i) => {
        const item = document.createElement("span");
        item.innerHTML = `<span class="legend-box" style="background:${colors[i]}"></span> ${label}`;
        container.appendChild(item);
    });
}

// Bar Chart
(function () {
    const ctx = document.getElementById("barChart").getContext("2d");
    const keys = Object.keys(userRoles);
    const values = Object.values(userRoles);
    const w = ctx.canvas.width, h = ctx.canvas.height;
    const barWidth = w / values.length * 0.4;
    const maxVal = Math.max(...values);

    ctx.clearRect(0, 0, w, h);
    ctx.font = "14px sans-serif";

    values.forEach((val, i) => {
        const x = i * (w / values.length) + barWidth / 2;
        const barHeight = (val / maxVal) * (h - 60);
        const y = h - barHeight - 20;
        ctx.fillStyle = "#0d6efd";
        ctx.fillRect(x, y, barWidth, barHeight);
        ctx.fillStyle = "#333";
        ctx.textAlign = "center";
        ctx.fillText(keys[i], x + barWidth / 2, h - 5);
        ctx.fillText(val, x + barWidth / 2, y - 5);
    });
})();

// Line Chart
(function () {
    const ctx = document.getElementById("lineChart").getContext("2d");
    const w = ctx.canvas.width, h = ctx.canvas.height;
    const maxVal = Math.max(...vulnCounts);

    ctx.clearRect(0, 0, w, h);
    ctx.strokeStyle = "#0d6efd";
    ctx.lineWidth = 2;
    ctx.beginPath();

    vulnCounts.forEach((val, i) => {
        const x = i * (w / (vulnCounts.length - 1));
        const y = h - ((val / maxVal) * (h - 60)) - 20;
        if (i === 0) ctx.moveTo(x, y);
        else ctx.lineTo(x, y);
        ctx.fillStyle = "#0d6efd";
        ctx.beginPath();
        ctx.arc(x, y, 4, 0, 2 * Math.PI);
        ctx.fill();
    });

    ctx.stroke();
    ctx.fillStyle = "#333";
    months.forEach((month, i) => {
        const x = i * (w / (months.length - 1));
        ctx.textAlign = "center";
        ctx.fillText(month, x, h - 5);
    });
})();

// Pie Chart
// PIE CHART (Vulnerabilities by Severity) with Percentages
(function () {
    const ctx = document.getElementById("pieChart").getContext("2d");
    const w = ctx.canvas.width, h = ctx.canvas.height;
    const total = Object.values(severityData).reduce((a, b) => a + b, 0);
    const colors = ["#dc3545", "#fd7e14", "#ffc107", "#198754"];
    let startAngle = 0;
    const keys = Object.keys(severityData);
    ctx.font = "14px sans-serif";
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";

    Object.values(severityData).forEach((val, i) => {
        const slice = (val / total) * 2 * Math.PI;
        const midAngle = startAngle + slice / 2;
        const radius = Math.min(w, h) / 2 - 30;

        // Draw slice
        ctx.beginPath();
        ctx.moveTo(w / 2, h / 2);
        ctx.arc(w / 2, h / 2, Math.min(w, h) / 2 - 10, startAngle, startAngle + slice);
        ctx.closePath();
        ctx.fillStyle = colors[i];
        ctx.fill();

        // Label percentage
        const percent = ((val / total) * 100).toFixed(1) + "%";
        const labelX = w / 2 + radius * Math.cos(midAngle);
        const labelY = h / 2 + radius * Math.sin(midAngle);
        ctx.fillStyle = "#000";
        ctx.fillText(percent, labelX, labelY);

        startAngle += slice;
    });

    renderLegend(colors, keys, "pieLegend");
})();


// Doughnut Chart
// DOUGHNUT CHART (Reports Approved/Rejected) with Percentages
(function () {
    const ctx = document.getElementById("doughnutChart").getContext("2d");
    const w = ctx.canvas.width, h = ctx.canvas.height;
    const total = reportsData.reduce((a, b) => a + b, 0);
    const colors = ["#198754", "#dc3545"];
    const labels = ["Approved", "Rejected"];
    let startAngle = 0;
    const radius = Math.min(w, h) / 2 - 10;
    ctx.font = "14px sans-serif";
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";

    reportsData.forEach((val, i) => {
        const slice = (val / total) * 2 * Math.PI;
        const midAngle = startAngle + slice / 2;

        // Draw slice
        ctx.beginPath();
        ctx.moveTo(w / 2, h / 2);
        ctx.arc(w / 2, h / 2, radius, startAngle, startAngle + slice);
        ctx.closePath();
        ctx.fillStyle = colors[i];
        ctx.fill();

        // Label percentage
        const percent = ((val / total) * 100).toFixed(1) + "%";
        const labelX = w / 2 + (radius - 30) * Math.cos(midAngle);
        const labelY = h / 2 + (radius - 30) * Math.sin(midAngle);
        ctx.fillStyle = "#000";
        ctx.fillText(percent, labelX, labelY);

        startAngle += slice;
    });

    // Draw hole
    ctx.beginPath();
    ctx.arc(w / 2, h / 2, 50, 0, 2 * Math.PI);
    ctx.fillStyle = "#f4f6f9";
    ctx.fill();

    renderLegend(colors, labels, "doughnutLegend");
})();

</script>
</body>
</html>
