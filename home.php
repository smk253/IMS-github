<?php
session_start();

$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "intern_system_db";
$port = "3307";

// Database connection
$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fixed admin credentials
$fixed_admin_username = "admin@example.com";
$fixed_admin_password = "admin123";

// Check if admin exists
function adminExists($conn, $username) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND role = 'admin'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();
    return $exists;
}

// If admin doesn't exist, create one
if (!adminExists($conn, $fixed_admin_username)) {
    $hashed_password = password_hash($fixed_admin_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
    $stmt->bind_param("ss", $fixed_admin_username, $hashed_password);
    $stmt->execute();
    $stmt->close();
}

$message = "";

// Login process
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // 1. Check in users (admin)
    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $hashed_password, $role);
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;

            header("Location: admin_pannel.php");
            exit();
        } else {
            $message = "Incorrect password.";
        }
    } else {
        $stmt->close();

        // 2. Check students table
        $stmt = $conn->prepare("SELECT id, password, name FROM students WHERE email = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $hashed_password, $student_name);
            $stmt->fetch();
            if (password_verify($password, $hashed_password)) {
                $_SESSION['student_id'] = $id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'student';
                $_SESSION['student_name'] = $student_name;

                header("Location: student_dashboard.php");
                exit();
            } else {
                $message = "Incorrect password.";
            }
        } else {
            $stmt->close();

            // 3. Check supervisors table
            $stmt = $conn->prepare("SELECT id, password, name FROM supervisors WHERE email = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                $stmt->bind_result($id, $hashed_password, $supervisor_name);
                $stmt->fetch();
                if (password_verify($password, $hashed_password)) {
                    $_SESSION['supervisor_id'] = $id;
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = 'supervisor';
                    $_SESSION['supervisor_name'] = $supervisor_name;

                    header("Location: supervisor_dashboard.php");
                    exit();
                } else {
                    $message = "Incorrect password.";
                }
            } else {
                $message = "User not found.";
            }
        }
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Login Form</title>
<style>
  body, html {
    margin: 0; padding: 0; height: 100%;
    font-family: Arial, sans-serif;
    background: url('image/bg.jpeg') no-repeat center center fixed;
    background-size: cover;
    display: flex;
    justify-content: center;
    align-items: center;
  }
  .login-container {
    background: #fff;
    padding: 30px 40px;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    width: 350px;
  }
  .login-container h2 {
    margin-bottom: 20px;
    font-weight: 600;
    text-align: center;
  }
  .login-container label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
  }
  .login-container input[type="email"],
  .login-container input[type="password"] {
    width: 100%;
    padding: 12px 15px;
    margin-bottom: 20px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
    box-sizing: border-box;
  }
  .login-container button {
    background-color: #0047AB; 
    border: none;
    padding: 12px;
    width: 100%;
    border-radius: 6px;
    font-size: 16px;
    color: white;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }
  .login-container button:hover {
    background-color: #242423;
  }
  .message {
    text-align: center;
    color: red;
    margin-bottom: 15px;
  }
  .register-link {
    text-align: center;
    margin-top: 15px;
    font-size: 14px;
  }
  .register-link a {
    color: #0047AB;
    text-decoration: none;
  }
  .register-link a:hover {
    text-decoration: underline;
  }
</style>
</head>
<body>
  <div class="login-container">
    <h2>Login</h2>
    <?php if ($message): ?>
      <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <form method="POST" action="">
      <label for="email">Email</label>
      <input type="email" id="email" name="username" placeholder="Enter your email" required />

      <label for="password">Password</label>
      <input type="password" id="password" name="password" placeholder="Enter your password" required />

      <button type="submit">Log in</button>
    </form>

    <p class="register-link">
      For students, please <a href="student_register.php">register here</a>.
    </p>
  </div>
</body>
</html>
