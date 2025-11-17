<?php
session_start();
require_once 'db.php';

// --- Auth check (only admin can access) ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// --- Check if ID is provided ---
if (!isset($_GET['id'])) {
    header("Location: manage_admin.php");
    exit();
}

$id = (int) $_GET['id'];

// Prevent deleting your own account
if ($id === (int)$_SESSION['user_id']) {
    $_SESSION['msg'] = "❌ You cannot delete your own admin account.";
    header("Location: manage_admin.php");
    exit();
}

// Delete admin from users table
$stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'admin'");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['msg'] = "✅ Admin deleted successfully.";
} else {
    $_SESSION['msg'] = "❌ Error deleting admin: " . $conn->error;
}

$stmt->close();
header("Location: manage_admin.php");
exit();
