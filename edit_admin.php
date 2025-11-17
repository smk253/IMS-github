<?php
session_start();
require_once 'db.php';

// --- Auth check (only admin can access) ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: manage_admin.php");
    exit();
}

$id = (int) $_GET['id'];
$message = "";

// Fetch admin info
$stmt = $conn->prepare("SELECT id, username, role FROM users WHERE id=? AND role='admin' LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $stmt->close();
    header("Location: manage_admin.php");
    exit();
}
$admin = $result->fetch_assoc();
$stmt->close();

// Update admin info
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $role = trim($_POST['role']);

    if ($username === "" || $role === "") {
        $message = "❌ All fields are required.";
    } else {
        $update = $conn->prepare("UPDATE users SET username=?, role=? WHERE id=?");
        $update->bind_param("ssi", $username, $role, $id);

        if ($update->execute()) {
            $message = "✅ Admin updated successfully!";
        } else {
            $message = "❌ Error: " . $conn->error;
        }
        $update->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background:#f4f6f9; display:flex; justify-content:center; align-items:center; height:100vh; font-family:Arial, sans-serif; }
.container { background:white; padding:30px; border-radius:10px; box-shadow:0 4px 10px rgba(0,0,0,0.1); width:400px; }
h2 { text-align:center; margin-bottom:20px; }
.message { text-align:center; margin-bottom:15px; font-weight:bold; }
.error { color:red; }
.success { color:green; }
</style>
</head>
<body>
<div class="container">
    <h2>Edit Admin</h2>
    <?php if($message): ?>
        <p class="message <?= strpos($message,'✅')!==false?'success':'error' ?>"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($admin['username']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-control" required>
                <option value="admin" <?= $admin['role']==='admin'?'selected':'' ?>>Admin</option>
                <option value="supervisor" <?= $admin['role']==='supervisor'?'selected':'' ?>>Supervisor</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary w-100">Update</button>
    </form>
    <a href="manage_admin.php" class="btn btn-secondary w-100 mt-2">⬅ Back</a>
</div>
</body>
</html>
