<?php
session_start();
require_once 'db.php';

// Optional login check
/*
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
*/

// Get supervisor ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid supervisor ID.");
}

$id = (int) $_GET['id'];

// Check if supervisor exists
$stmt = $conn->prepare("SELECT id FROM supervisors WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    die("Supervisor not found.");
}
$stmt->close();

// Delete supervisor
$del = $conn->prepare("DELETE FROM supervisors WHERE id = ?");
$del->bind_param("i", $id);

if ($del->execute()) {
    $del->close();
    // Redirect to supervisor list after deletion
    header("Location: view_supervisor.php?msg=deleted");
    exit();
} else {
    $del->close();
    die("Failed to delete supervisor.");
}
