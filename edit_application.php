
<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

if (!isset($_GET['id'])) {
    die("No application selected.");
}

$id = intval($_GET['id']);

$sql = "SELECT * FROM applications WHERE id = ? AND student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Application not found.");
}

$app = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Internship Application</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #74ebd5 0%, #9face6 100%);
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 80px auto;
            background: #ffffffdd;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 119, 182, 0.3);
        }

        h2 {
            text-align: center;
            color: #0077b6;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        input[type="text"],
        input[type="date"],
        input[type="file"],
        textarea {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 1rem;
            box-sizing: border-box;
        }

        label {
            font-weight: 600;
            color: #0077b6;
            margin-top: 5px;
        }

        input[type="submit"] {
            background-color: #2a9df4;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #1b82d1;
        }

        .back-link {
            display: block;
            margin: 25px auto 0;
            padding: 10px 20px;
            background-color: #0077b6;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            text-align: center;
            max-width: 220px;
            transition: background-color 0.3s ease;
        }

        .back-link:hover {
            background-color: #005f8c;
        }

        .current-file {
            font-size: 0.9rem;
            color: #333;
            margin-top: -10px;
            margin-bottom: 15px;
        }

        .current-file a {
            color: #0077b6;
            text-decoration: none;
        }

        .current-file a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>✏️ Edit Internship Application</h2>

    <form action="update_application.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= htmlspecialchars($app['id']) ?>">

        <input type="text" name="company_name" placeholder="🏢 Company Name" value="<?= htmlspecialchars($app['company_name']) ?>" required>

        <input type="text" name="position" placeholder="💼 Position Title" value="<?= htmlspecialchars($app['position']) ?>" required>

        <input type="text" name="location" placeholder="📍 Location" value="<?= htmlspecialchars($app['location']) ?>" required>

        <label for="start_date">📅 Start Date</label>
        <input type="date" name="start_date" value="<?= htmlspecialchars($app['start_date']) ?>" required>

        <label for="end_date">📅 End Date</label>
        <input type="date" name="end_date" value="<?= htmlspecialchars($app['end_date']) ?>" required>

        <textarea name="description" rows="4" placeholder="📝 Description (Optional)" ><?= htmlspecialchars($app['description']) ?></textarea>

        <label for="attachment">📎 Upload CV or File (leave empty to keep existing)</label>
        <input type="file" name="attachment" accept=".pdf,.doc,.docx">

        <?php if (!empty($app['attachment'])): ?>
            <div class="current-file">
                Current File: <a href="uploads/<?= htmlspecialchars($app['attachment']) ?>" target="_blank">View</a>
            </div>
        <?php endif; ?>

        <input type="submit" value="🔄 Update Application">
    </form>

    <a href="student_dashboard.php" class="back-link">🔙 Back to Dashboard</a>
</div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
