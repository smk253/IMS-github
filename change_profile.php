<?php
session_start();
require_once 'db.php';

// Admin login check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // --- Password validation ---
    if (strlen($new_password) < 6) {
        $message = "❌ Password must be at least 6 characters long.";
    } elseif (!preg_match('/[A-Z]/', $new_password)) {
        $message = "❌ Password must contain at least one uppercase letter.";
    } elseif (!preg_match('/[0-9]/', $new_password)) {
        $message = "❌ Password must contain at least one number.";
    } elseif (!preg_match('/[\W]/', $new_password)) {
        $message = "❌ Password must contain at least one special character.";
    } elseif ($new_password !== $confirm_password) {
        $message = "❌ New password and confirm password do not match.";
    } else {
        // Fetch current hashed password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        $stmt->close();

        // Verify current password
        if (password_verify($current_password, $hashed_password)) {
            $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_hashed, $_SESSION['user_id']);
            if ($update_stmt->execute()) {
                $message = "✅ Password updated successfully!";
            } else {
                $message = "❌ Error updating password: " . $conn->error;
            }
            $update_stmt->close();
        } else {
            $message = "❌ Current password is incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Change Password</title>
<style>
body { font-family: Arial, sans-serif; background:#f4f6f9; display:flex; justify-content:center; align-items:center; height:100vh; }
.container { background:white; padding:30px; border-radius:10px; box-shadow:0 4px 10px rgba(0,0,0,0.1); width:350px; }
h2 { text-align:center; margin-bottom:20px; }
input { width:100%; padding:10px; margin-bottom:15px; border-radius:5px; border:1px solid #ccc; }
button { width:100%; padding:10px; background:#0047AB; color:white; border:none; border-radius:5px; cursor:pointer; margin-bottom:10px; }
button:hover { background:#003366; }
.message { text-align:center; color:red; margin-bottom:10px; }
.success { color:green; }
.back-btn { background:#6c757d; }
.back-btn:hover { background:#5a6268; }
</style>
</head>
<body>
<div class="container">
    <h2>Change Password</h2>
    <?php if($message): ?>
        <p class="message <?= strpos($message,'successfully')!==false?'success':'' ?>"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <form method="POST">
        <input type="password" name="current_password" placeholder="Current Password" required>
        <input type="password" name="new_password" placeholder="New Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
        <button type="submit">Update Password</button>
    </form>
    <form action="admin_pannel.php" method="get">
        <button type="submit" class="back-btn">Back</button>
    </form>
</div>
</body>
</html>
