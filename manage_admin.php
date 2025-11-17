<?php
session_start();
require_once 'db.php';

// --- Auth check (only admin can access) ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all admins
$sql = "SELECT id, username, password, role, created_at FROM users WHERE role='admin' ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Manage Admins</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background:#f4f6f9; padding:20px; font-family:Arial, sans-serif; }
.container { background:#fff; padding:20px; border-radius:10px; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
h2 { margin-bottom:20px; }
table { width:100%; border-collapse:collapse; }
th, td { padding:12px; text-align:left; border-bottom:1px solid #ddd; }
th { background:#0047AB; color:white; text-transform:uppercase; font-size:13px; }
.action-icons a { margin:0 5px; font-size:18px; }
.action-icons .edit { color:#007bff; }
.action-icons .delete { color:#dc3545; }
</style>
</head>
<body>
<div class="container">
    <h2>👨‍💻 Manage Admins</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Password (hashed)</th>
                <th>Role</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><code><?= htmlspecialchars($row['password']) ?></code></td>
                    <td><?= htmlspecialchars($row['role']) ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td class="action-icons">
                        <a href="edit_admin.php?id=<?= $row['id'] ?>" class="edit" title="Edit"><i class="bi bi-pencil-square"></i></a>
                        <a href="delete_admin.php?id=<?= $row['id'] ?>" class="delete" title="Delete" onclick="return confirm('Are you sure you want to delete this admin?');"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6" class="text-center">No admins found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    <a href="admin_pannel.php" class="btn btn-secondary mt-3">⬅ Back to Admin Panel</a>
</div>
</body>
</html>
