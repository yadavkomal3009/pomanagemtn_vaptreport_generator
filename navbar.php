<?php
session_start();
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">📋 Admin Panel</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">

      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="organizations.php">Organizations</a></li>
        <li class="nav-item"><a class="nav-link" href="po_list.php">POs</a></li>
        <li class="nav-item"><a class="nav-link" href="users.php">Users</a></li>
        <li class="nav-item"><a class="nav-link" href="vulnerabilities.php">Vulnerabilities</a></li>

        <?php if ($_SESSION['role'] === 'Superadmin'): ?>
          <li class="nav-item"><a class="nav-link text-warning" href="download_orgs_pdf.php">📥 Org PDF</a></li>
          <li class="nav-item"><a class="nav-link text-warning" href="download_users_pdf.php">📥 Users PDF</a></li>
          <li class="nav-item"><a class="nav-link text-warning" href="download_pos_pdf.php">📥 POs PDF</a></li>
          <li class="nav-item"><a class="nav-link text-warning" href="download_vulnerabilities_pdf.php">📥 Vuln PDF</a></li>
        <?php endif; ?>
      </ul>

      <span class="navbar-text text-light me-3">
        Logged in as: <strong><?= $_SESSION['role'] ?? 'Guest' ?></strong>
      </span>
      <a href="logout.php" class="btn btn-outline-light">Logout</a>

    </div>
  </div>
</nav>
