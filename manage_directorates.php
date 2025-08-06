<?php
// DB Connection
$conn = new mysqli("localhost", "root", "", "po_management");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Insert / Update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'] ?? '';
    $center_id = $_POST['center_id'];
    $name = $_POST['name'];
    $code = $_POST['code'];
    $location = $_POST['location'];

    if ($id == '') {
        $stmt = $conn->prepare("INSERT INTO directorates (center_id, name, code, location) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $center_id, $name, $code, $location);
    } else {
        $stmt = $conn->prepare("UPDATE directorates SET center_id=?, name=?, code=?, location=? WHERE id=?");
        $stmt->bind_param("isssi", $center_id, $name, $code, $location, $id);
    }

    $stmt->execute();
    header("Location: manage_directorates.php");
    exit;
}

// Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM directorates WHERE id=$id");
    header("Location: manage_directorates.php");
    exit;
}

// Fetch data
$directorates = $conn->query("SELECT d.*, c.name AS center_name FROM directorates d JOIN centers c ON d.center_id = c.id ORDER BY d.id DESC");
$centers = $conn->query("SELECT id, name FROM centers ORDER BY name ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage center</title>
     <a href="superadmin_dashboard.php" class="btn btn-warning">🔙 Back</a>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            background: #f4f6f9;
        }

        h2 {
            text-align: center;
        }

        form {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 10px;
            max-width: 1000px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
        }

        form > * {
            flex: 1 1 150px;
        }

        input, select {
            padding: 8px;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            padding: 10px 15px;
            background-color: #0d6efd;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            flex: 1 1 100px;
        }

        .table-container {
            max-width: 1000px;
            margin: auto;
            background: white;
            border-radius: 10px;
            padding: 10px;
        }

        .table-scroll {
            max-height: 450px;
            overflow-y: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            position: sticky;
            top: 0;
            background: #0d6efd;
            color: white;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .actions a {
            margin-right: 10px;
            color: #0d6efd;
            text-decoration: none;
        }

        #search {
            width: 60%;
            margin: 15px auto;
            padding: 8px;
            display: block;
        }

        @media (max-width: 768px) {
            form {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<h2>📋 Manage center</h2>

<form method="POST">
    <input type="hidden" name="id" id="id">

    <select name="center_id" id="center_id" required>
        <option value="">-- Select directorate --</option>
        <?php while ($row = $centers->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
        <?php endwhile; ?>
    </select>

    <input type="text" name="name" id="name" placeholder="Center Name" required>
    <input type="text" name="code" id="code" placeholder="Center Code" required>
    <input type="text" name="location" id="location" placeholder="Location">
    <button type="submit">➕ Save</button>
</form>

<input type="text" id="search" placeholder="🔍 Search by name, code or center...">

<div class="table-container">
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Center</th>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Location</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="dataTable">
                <?php while ($d = $directorates->fetch_assoc()): ?>
                    <tr>
                        <td><?= $d['id'] ?></td>
                        <td><?= htmlspecialchars($d['center_name']) ?></td>
                        <td><?= htmlspecialchars($d['name']) ?></td>
                        <td><?= htmlspecialchars($d['code']) ?></td>
                        <td><?= htmlspecialchars($d['location']) ?></td>
                        <td><?= $d['created_at'] ?></td>
                        <td class="actions">
                            <a href="#" onclick="editRow(<?= htmlspecialchars(json_encode($d)) ?>)">✏️</a>
                            <a href="?delete=<?= $d['id'] ?>" onclick="return confirm('Delete this directorate?')">🗑️</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    const searchInput = document.getElementById('search');
    searchInput.addEventListener('input', () => {
        const filter = searchInput.value.toLowerCase();
        document.querySelectorAll('#dataTable tr').forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });

    function editRow(data) {
        document.getElementById('id').value = data.id;
        document.getElementById('name').value = data.name;
        document.getElementById('code').value = data.code;
        document.getElementById('location').value = data.location;
        document.getElementById('center_id').value = data.center_id;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
</script>

</body>
</html>
