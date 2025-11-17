<?php
session_start();
require_once "db.php";

// ✅ Only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $confirm  = $_POST["confirm_password"];

    if (empty($username) || empty($password) || empty($confirm)) {
        $message = "All fields are required.";
    } elseif (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email.";
    } elseif ($password !== $confirm) {
        $message = "Passwords do not match.";
    } elseif (strlen($password) < 6 || 
              !preg_match('/[A-Z]/', $password) || 
              !preg_match('/[0-9]/', $password) || 
              !preg_match('/[\W]/', $password)) {
        $message = "Password must be at least 6 chars and include uppercase, number, and special character.";
    } else {
        // Check duplicate
        $stmt = $conn->prepare("SELECT id FROM users WHERE username=? AND role='admin'");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "An admin with this email already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username,password,role) VALUES (?,?, 'admin')");
            $stmt->bind_param("ss", $username, $hashed);
            if ($stmt->execute()) {
                $message = "✅ New Admin created successfully.";
            } else {
                $message = "❌ Database error.";
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Admin</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f4f6f9; }
        .container {
            width: 400px; margin:50px auto; padding:25px; 
            background:#fff; border-radius:10px; box-shadow:0 4px 10px rgba(0,0,0,0.1);
        }
        h2 { color:#003366; text-align:center; margin-bottom:20px; }
        label { font-weight:bold; display:block; margin-top:15px; }
        input { width:100%; padding:10px; margin-top:5px; border:1px solid #ccc; border-radius:6px; }
        button {
            width:100%; margin-top:20px; padding:12px; 
            background:#003366; color:#fff; border:none; border-radius:6px; cursor:pointer;
        }
        button:hover { background:#0059b3; }
        .message { text-align:center; margin:10px 0; font-weight:bold; }
        .success { color:green; }
        .error { color:red; }
        .back { display:block; text-align:center; margin-top:15px; }
    </style>
</head>
<body>
<div class="container">
    <h2>➕ Create Admin</h2>
    <?php if ($message): ?>
        <p class="message <?= strpos($message,'✅')!==false ? 'success':'error' ?>">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>
    <form method="POST">
        <label>Email</label>
        <input type="email" name="username" required>
        
        <label>Password</label>
        <input type="password" name="password" required>
        
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" required>
        
        <button type="submit">Create Admin</button>
    </form>
    <a class="back" href="admin_pannel.php">⬅ Back to Dashboard</a>
</div>
</body>
</html>
