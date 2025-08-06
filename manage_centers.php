<?php
// DB connection
$conn = new mysqli("localhost", "root", "", "po_management");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Handle create/update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'] ?? '';
    $name = $_POST['name'];
   

    if ($id === '') {
        $stmt = $conn->prepare("INSERT INTO centers (name) VALUES (?)");
        $stmt->bind_param("s", $name);
    } else {
        $stmt = $conn->prepare("UPDATE centers SET name=? WHERE id=?");
        $stmt->bind_param("ssi", $name, $id);
    }

    $stmt->execute();
    header("Location: manage_centers.php");
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM centers WHERE id=$id");
    header("Location: manage_centers.php");
    exit;
}

// Fetch all centers
$centers = $conn->query("SELECT * FROM centers ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Centers</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            padding: 30px;
        }

        h2 {
            text-align: center;
        }

        form, .table-container {
            max-width: 700px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
        }

        input {
            padding: 8px;
            width: 100%;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            padding: 10px 15px;
            border: none;
            background-color: #0d6efd;
            color: white;
            border-radius: 6px;
            cursor: pointer;
        }

        #search {
            width: 60%;
            margin: 15px auto;
            display: block;
            padding: 8px;
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
        }

        .table-scroll {
            max-height: 400px;
            overflow-y: auto;
        }

        .actions a {
            margin-right: 10px;
            color: #0d6efd;
            text-decoration: none;
        }
    </style>
</head>
<body>

<h2>🏢 Manage Centers</h2>

<form method="POST">
    <input type="hidden" name="id" id="id">
    
    <label>Center Name</label>
    <input type="text" name="name" id="name" required>

    

    <button type="submit">Save Center</button>
</form>

<input type="text" id="search" placeholder="🔍 Search by name or location...">

<div class="table-container">
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Center Name</th>
                    <th>Location</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="dataTable">
                <?php while ($c = $centers->fetch_assoc()): ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td><?= htmlspecialchars($c['name']) ?></td>
                        
                        <td><?= $c['created_at'] ?></td>
                        <td class="actions">
                            <a href="#" onclick='editRow(<?= json_encode($c) ?>)'>✏️</a>
                            <a href="?delete=<?= $c['id'] ?>" onclick="return confirm('Delete this center?')">🗑️</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    const search = document.getElementById('search');
    search.addEventListener('input', () => {
        const filter = search.value.toLowerCase();
        document.querySelectorAll('#dataTable tr').forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });

    function editRow(data) {
        document.getElementById('id').value = data.id;
        document.getElementById('name').value = data.name;
        document.getElementById('location').value = data.location;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
</script>

</body>
</html>
