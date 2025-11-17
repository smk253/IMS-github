<?php
session_start();
require_once 'db.php';

// --- Auth Check ---
if (!isset($_SESSION['supervisor_id'])) {
    header("Location: supervisor_login.php");
    exit();
}

$supervisor_id = (int)$_SESSION['supervisor_id'];
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // --- Validation ---
    if (strlen($new_password) < 6) {
        $message = "❌ New password must be at least 6 characters long.";
    } elseif (!preg_match('/[A-Z]/', $new_password)) {
        $message = "❌ Password must contain at least one uppercase letter.";
    } elseif (!preg_match('/[0-9]/', $new_password)) {
        $message = "❌ Password must contain at least one number.";
    } elseif (!preg_match('/[\W]/', $new_password)) {
        $message = "❌ Password must contain at least one special character.";
    } elseif ($new_password !== $confirm_password) {
        $message = "❌ New password and confirm password do not match.";
    } else {
        // Fetch current password from DB
        $stmt = $conn->prepare("SELECT password FROM supervisors WHERE id = ?");
        $stmt->bind_param("i", $supervisor_id);
        $stmt->execute();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        $stmt->close();

        if (password_verify($current_password, $hashed_password)) {
            $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE supervisors SET password=? WHERE id=?");
            $update_stmt->bind_param("si", $new_hashed, $supervisor_id);

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
<title>Supervisor - Change Password</title>
<style>
body { font-family: Arial, sans-serif; background:#f4f6f9; display:flex; justify-content:center; align-items:center; height:100vh; }
.container { background:white; padding:30px; border-radius:10px; box-shadow:0 4px 10px rgba(0,0,0,0.1); width:350px; }
h2 { text-align:center; margin-bottom:20px; }
input { width:100%; padding:10px; margin-bottom:15px; border-radius:5px; border:1px solid #ccc; }
button { width:100%; padding:10px; background:#0047AB; color:white; border:none; border-radius:5px; cursor:pointer; }
button:hover { background:#003366; }
.message { text-align:center; margin-bottom:10px; font-weight:bold; }
.error { color:red; }
.success { color:green; }
.back-btn { display:block; margin-top:10px; text-align:center; background:#ddd; padding:8px; border-radius:5px; text-decoration:none; color:#333; }
.back-btn:hover { background:#bbb; }
</style>
</head>
<body>
<div class="container">
    <h2>Change Password</h2>
    <?php if($message): ?>
        <p class="message <?= strpos($message,'✅')!==false?'success':'error' ?>"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <form method="POST">
        <input type="password" name="current_password" placeholder="Current Password" required>
        <input type="password" name="new_password"  required>
        <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
        <button type="submit">Update Password</button>
    </form>
    <a href="supervisor_dashboard.php" class="back-btn">⬅ Back to Dashboard</a>
</div>
</body>
</html>
