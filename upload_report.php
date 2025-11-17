<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['report'])) {
    $student_id = $_SESSION['student_id'];
    $report_date = $_POST['report_date'];
    $file = $_FILES['report'];

    if ($file['error'] === 0) {
        $filename = time() . '_' . basename($file['name']);
        $destination = 'uploads/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $stmt = $conn->prepare("INSERT INTO daily_reports (student_id, report_date, report_file) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $student_id, $report_date, $filename);
            $stmt->execute();
            $success = "✅ Report uploaded successfully.";
        } else {
            $error = "❌ Failed to upload file.";
        }
    } else {
        $error = "❌ File upload error.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Daily Report</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f8ff;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 500px;
            margin: 80px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0, 119, 182, 0.2);
        }

        h2 {
            text-align: center;
            color: #0077b6;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        input[type="date"],
        input[type="file"],
        input[type="submit"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        input[type="submit"] {
            background-color: #0077b6;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #005f8c;
        }

        .message {
            text-align: center;
            margin-bottom: 15px;
            color: green;
        }

        .error {
            text-align: center;
            margin-bottom: 15px;
            color: red;
        }

        .btn-container {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-top: 15px;
        }

        a.btn-link {
            flex: 1;
            text-align: center;
            background-color: #0077b6;
            color: white;
            padding: 10px 0;
            border-radius: 8px;
            font-weight: bold;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        a.btn-link:hover {
            background-color: #005f8c;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>📤 Upload Daily Report</h2>

    <?php if (!empty($success)) echo "<p class='message'>$success</p>"; ?>
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

    <form method="POST"  enctype="multipart/form-data">
        <label for="report_date">🗓️ Report Date:</label>
        <input type="date" name="report_date" id="report_date" required>

        <label for="report">📎 Upload File (PDF/DOCX):</label>
        <input type="file" name="report" id="report" accept=".pdf,.doc,.docx" required>

        <input type="submit" value="Upload Report">
    </form>

    <div class="btn-container">
        <a href="student_dashboard.php" class="btn-link">🔙 Back</a>
        <a href="view_uploaded_report.php" class="btn-link">👀 View</a>
    </div>
</div>

</body>
</html>
