<?php
session_start();
require_once 'db.php'; 

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if ($name === "" || $email === "" || $password === "") {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif (!preg_match("/[A-Z]/", $password)) {
        $error = "Password must contain at least one uppercase letter.";
    } elseif (!preg_match("/[0-9]/", $password)) {
        $error = "Password must contain at least one number.";
    } elseif (!preg_match("/[!@#$%^&*(),.?\":{}|<>]/", $password)) {
        $error = "Password must contain at least one special character.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check duplicate email
        $check = $conn->prepare("SELECT id FROM supervisors WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Supervisor with this email already exists.";
        } else {
            $stmt = $conn->prepare("INSERT INTO supervisors (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);

            if ($stmt->execute()) {
                $success = "Supervisor created successfully.";
            } else {
                $error = "Database error. Please try again.";
            }
            $stmt->close();
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
<title>Create Supervisor - Computer University</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        background: #f4f6f9;
    }

    header {
        background-color: #003366;
        color: #fff;
        text-align: center;
        padding: 18px 0;
        font-size: 22px;
        font-weight: bold;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .container {
        max-width: 800px;
        margin: 40px auto;
        padding: 30px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        text-align: center;
    }

    h2 {
        color: #003366;
        margin-bottom: 25px;
    }

    form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .form-row {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    label {
        display: block;
        margin-bottom: 6px;
        font-weight: bold;
        text-align: left;
        color: #003366;
    }

    input[type="text"], 
    input[type="email"], 
    input[type="password"] {
        width: 100%;
        padding: 12px;
        border-radius: 6px;
        border: 1px solid #ccc;
        font-size: 15px;
        transition: border-color 0.2s;
    }
    input:focus {
        border-color: #003366;
        outline: none;
    }

    input[type="submit"] {
        width: 100%;
        padding: 12px;
        background-color: #003366;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    input[type="submit"]:hover {
        background-color: #0059b3;
    }

    .message {
        margin-bottom: 15px;
        font-weight: bold;
    }

    .success { color: green; }
    .error { color: red; }

    .back-button {
        display: inline-block;
        padding: 12px 25px;
        background-color: #003366;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        font-weight: bold;
        transition: background-color 0.3s ease;
        margin-top: 10px;
    }

    .back-button:hover {
        background-color: #0059b3;
    }

    /* ✅ Responsive Layout */
    @media (min-width: 768px) and (max-width: 1023px) {
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr; /* 2 columns */
            gap: 20px;
        }
        .form-row > div:nth-child(3) {
            grid-column: 1 / span 2;
        }
    }

    @media (min-width: 1024px) {
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr; /* 3 columns */
            gap: 20px;
        }
    }

    @media (max-width: 480px) {
        .container {
            margin: 20px;
            padding: 20px;
        }
        h2 {
            font-size: 18px;
        }
        input, .back-button {
            font-size: 14px;
            padding: 10px;
        }
    }
</style>
</head>
<body>

<header>Computer University - Admin Panel</header>

<div class="container">
    <h2>👨‍🏫 Create Supervisor</h2>

    <?php if ($success) echo "<p class='message success'>$success</p>"; ?>
    <?php if ($error) echo "<p class='message error'>$error</p>"; ?>

    <form method="POST" action="">
        <div class="form-row">
            <div>
                <label for="name">Name</label>
                <input type="text" name="name" id="name" required>
            </div>
            <div>
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div>
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
        </div>

        <input type="submit" value="Create Supervisor">
    </form>

    <a href="admin_pannel.php" class="back-button">⬅ Back to Dashboard</a>
</div>

</body>
</html>
