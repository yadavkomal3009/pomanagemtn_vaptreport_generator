<?php
function requireLogin() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        die("❌ You must be logged in.");
    }
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isSuperAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin';
}

function isApprovedUser() {
    return isset($_SESSION['role'], $_SESSION['approval_status']) &&
           $_SESSION['role'] === 'user' &&
           $_SESSION['approval_status'] === 'approved';
}

function requireApprovedUserOrAdmin() {
    requireLogin();
    if (!isAdmin() && !isApprovedUser()) {
        die("❌ Access denied. Please request approval from Superadmin.");
    }
}

function requireSuperAdmin() {
    requireLogin();
    if (!isSuperAdmin()) {
        die("❌ Only SuperAdmin can access this page.");
    }
}
?>
