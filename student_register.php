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

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $message = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
    } elseif (!preg_match("/[A-Z]/", $password)) {
        $message = "Password must contain at least one uppercase letter.";
    } elseif (!preg_match("/[0-9]/", $password)) {
        $message = "Password must contain at least one number.";
    } elseif (!preg_match("/[!@#$%^&*(),.?\":{}|<>]/", $password)) {
        $message = "Password must contain at least one special character.";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        // Check if email exists in students table
        $stmt = $conn->prepare("SELECT id FROM students WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "This email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO students (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);

            if ($stmt->execute()) {
                $message = "Register successful!";
            } else {
                $message = "Registration failed. Please try again.";
            }
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Student Register</title>
<style>
  body, html {
    margin: 0; 
    padding: 0; 
    min-height: 100vh; 
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg, #6a85b6, #bac8e0);
    background-attachment: fixed;
    background-repeat: no-repeat;
    background-size: cover;
    display: flex;
    justify-content: center;
    align-items: center;
  }
  .register-container, .success-box {
    background: white;
    padding: 30px 40px;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    width: 350px;
    text-align: center;
  }
  .register-container h2,
  .success-box h3 {
    margin-bottom: 20px;
    font-weight: 600;
    text-align: center;
  }
  .register-container label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    text-align: left;
  }
  .register-container input[type="text"],
  .register-container input[type="email"],
  .register-container input[type="password"] {
    width: 100%;
    padding: 12px 15px;
    margin-bottom: 20px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
    box-sizing: border-box;
  }
  .register-container button,
  .success-box .login-btn {
    background-color: #0047AB;
    border: none;
    padding: 12px;
    width: 100%;
    border-radius: 6px;
    font-size: 16px;
    color: white;
    cursor: pointer;
    transition: background-color 0.3s ease;
    text-decoration: none;
    display: inline-block;
  }
  .register-container button:hover,
  .success-box .login-btn:hover {
    background-color: #003377;
  }
  .message {
    text-align: center;
    margin-bottom: 20px;
    color: red;
  }
  .message.success {
    color: green;
  }
  .success-box h3 {
    color: #2e7d32;
  }
  .success-box p {
    margin-bottom: 20px;
    font-size: 15px;
  }
</style>
</head>
<body>
  <?php if (strpos($message, 'successful') !== false): ?>
    <div class="success-box">
      <h3>🎉 Registration Successful!</h3>
      <p>You can now login to your account.</p>
      <a href="home.php" class="login-btn">Go to Login</a>
    </div>
  <?php else: ?>
    <div class="register-container">
      <h2>Student Register</h2>
      <?php if ($message): ?>
        <p class="message"><?= $message ?></p>
      <?php endif; ?>
      <form method="POST" action="">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" placeholder="Enter your full name" required />

        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Enter your email" required />

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter your password" required />

        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required />

        <button type="submit">Register</button>
      </form>
    </div>
  <?php endif; ?>
</body>
</html>
