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

// Initialize messages
$success = "";
$error = "";

// Get supervisor ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid supervisor ID.");
}

$id = (int) $_GET['id'];

// Fetch supervisor data
$stmt = $conn->prepare("SELECT name, email FROM supervisors WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Supervisor not found.");
}

$supervisor = $result->fetch_assoc();
$stmt->close();

// Initialize variables for form pre-fill
$name = $supervisor['name'];
$email = $supervisor['email'];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password']; // optional, if empty no password update

    if ($name === "" || $email === "") {
        $error = "Name and Email are required.";
    } else {
        // Check if email is used by another supervisor
        $check = $conn->prepare("SELECT id FROM supervisors WHERE email = ? AND id != ?");
        $check->bind_param("si", $email, $id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Another supervisor with this email already exists.";
        } else {
            if ($password !== "") {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $update = $conn->prepare("UPDATE supervisors SET name = ?, email = ?, password = ? WHERE id = ?");
                $update->bind_param("sssi", $name, $email, $hashed_password, $id);
            } else {
                // No password update
                $update = $conn->prepare("UPDATE supervisors SET name = ?, email = ? WHERE id = ?");
                $update->bind_param("ssi", $name, $email, $id);
            }

            if ($update->execute()) {
                $success = "Supervisor updated successfully.";
            } else {
                $error = "Database error. Please try again.";
            }
            $update->close();
        }
        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Edit Supervisor</title>
<style>
  body {
    font-family: Arial, sans-serif;
    background: #f0f4ff;
    margin: 0;
    padding: 0;
  }
  .container {
    max-width: 500px;
    margin: 80px auto;
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.1);
  }
  h2 {
    text-align: center;
    color: #0047AB;
    margin-bottom: 20px;
  }
  label {
    display: block;
    margin-bottom: 6px;
    font-weight: bold;
  }
  input[type="text"], input[type="email"], input[type="password"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 6px;
    border: 1px solid #ccc;
  }
  input[type="submit"], .back-button {
    width: 100%;
    padding: 10px;
    background-color: #0047AB;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    text-align: center;
    text-decoration: none;
    display: block;
    margin-top: 10px;
  }
  input[type="submit"]:hover, .back-button:hover {
    background-color: #003377;
  }
  .success {
    color: green;
    text-align: center;
    margin-bottom: 10px;
  }
  .error {
    color: red;
    text-align: center;
    margin-bottom: 10px;
  }
</style>
</head>
<body>

<div class="container">
  <h2>Edit Supervisor</h2>

  <?php if ($success) echo "<p class='success'>$success</p>"; ?>
  <?php if ($error) echo "<p class='error'>$error</p>"; ?>

  <form method="POST">
    <label for="name">Name</label>
    <input type="text" name="name" id="name" required value="<?= htmlspecialchars($name) ?>">

    <label for="email">Email</label>
    <input type="email" name="email" id="email" required value="<?= htmlspecialchars($email) ?>">

    <label for="password">Password (Leave blank to keep current)</label>
    <input type="password" name="password" id="password" placeholder="New password">

    <input type="submit" value="Update Supervisor">
  </form>

  <a href="view_supervisor.php" class="back-button">Back to Supervisors List</a>
</div>

</body>
</html>
