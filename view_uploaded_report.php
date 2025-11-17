<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$success = "";
$error = "";

// Delete action
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);

    // Get old file name for deleting from server
    $stmt = $conn->prepare("SELECT report_file FROM daily_reports WHERE id = ? AND student_id = ?");
    $stmt->bind_param("ii", $delete_id, $student_id);
    $stmt->execute();
    $stmt->bind_result($old_file);
    if ($stmt->fetch()) {
        if (file_exists("uploads/" . $old_file)) {
            unlink("uploads/" . $old_file);
        }
    }
    $stmt->close();

    // Delete DB record
    $stmt = $conn->prepare("DELETE FROM daily_reports WHERE id = ? AND student_id = ?");
    $stmt->bind_param("ii", $delete_id, $student_id);
    if ($stmt->execute()) {
        $success = "✅ Report deleted successfully.";
    } else {
        $error = "❌ Failed to delete report.";
    }
    $stmt->close();
}

// Edit action
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT report_date, report_file FROM daily_reports WHERE id = ? AND student_id = ?");
    $stmt->bind_param("ii", $edit_id, $student_id);
    $stmt->execute();
    $stmt->bind_result($report_date, $report_file);
    if ($stmt->fetch()) {
        $edit_data = [
            'id' => $edit_id,
            'report_date' => $report_date,
            'report_file' => $report_file
        ];
    }
    $stmt->close();
}

// Handle update after editing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $edit_id = intval($_POST['edit_id']);
    $report_date = $_POST['report_date'];

    // If new file uploaded
    if (!empty($_FILES['report']['name'])) {
        $file = $_FILES['report'];
        if ($file['error'] === 0) {
            $filename = time() . '_' . basename($file['name']);
            $destination = 'uploads/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                // Delete old file
                $stmt = $conn->prepare("SELECT report_file FROM daily_reports WHERE id = ? AND student_id = ?");
                $stmt->bind_param("ii", $edit_id, $student_id);
                $stmt->execute();
                $stmt->bind_result($old_file);
                if ($stmt->fetch() && file_exists("uploads/" . $old_file)) {
                    unlink("uploads/" . $old_file);
                }
                $stmt->close();

                // Update with new file
                $stmt = $conn->prepare("UPDATE daily_reports SET report_date = ?, report_file = ? WHERE id = ? AND student_id = ?");
                $stmt->bind_param("ssii", $report_date, $filename, $edit_id, $student_id);
                $stmt->execute();
                $stmt->close();
                $success = "✅ Report updated successfully.";
            } else {
                $error = "❌ Failed to upload new file.";
            }
        } else {
            $error = "❌ File upload error.";
        }
    } else {
        // Update only date
        $stmt = $conn->prepare("UPDATE daily_reports SET report_date = ? WHERE id = ? AND student_id = ?");
        $stmt->bind_param("sii", $report_date, $edit_id, $student_id);
        $stmt->execute();
        $stmt->close();
        $success = "✅ Report date updated successfully.";
    }
}

// Fetch all reports
$sql = "SELECT dr.*, s.name AS student_name 
        FROM daily_reports dr
        JOIN students s ON dr.student_id = s.id
        WHERE dr.student_id = ?
        ORDER BY dr.report_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Uploaded Reports</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        .container {
            max-width: 900px;
            margin: 60px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0, 119, 182, 0.2);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        h2 {
            text-align: center;
            color: #0077b6;
            margin-bottom: 25px;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #0077b6; color: white; }
        tr:nth-child(even) { background-color: #f2f9ff; }
        .back-link { display: inline-block; margin-top: 20px; text-decoration: none; color: #0077b6; }
        .back-link:hover { text-decoration: underline; }
        .message { text-align: center; margin-bottom: 10px; color: green; }
        .error { text-align: center; margin-bottom: 10px; color: red; }
        .action-btn { padding: 5px 8px; border-radius: 4px; text-decoration: none; color: white; }
        .edit-btn { background: #ffa500; }
        .delete-btn { background: #ff4d4d; }
    </style>
</head>
<body>
<div class="container">
    <h2>📄 Uploaded Reports</h2>
    <?php if ($success) echo "<p class='message'>$success</p>"; ?>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>

    <?php if ($edit_data): ?>
        <h3>✏️ Edit Report</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="edit_id" value="<?= $edit_data['id'] ?>">
            <label>Report Date:</label>
            <input type="date" name="report_date" value="<?= htmlspecialchars($edit_data['report_date']) ?>" required>
            <p>Current File: <a href="uploads/<?= htmlspecialchars($edit_data['report_file']) ?>" target="_blank"><?= htmlspecialchars($edit_data['report_file']) ?></a></p>
            <label>Change File (optional):</label>
            <input type="file" name="report" accept=".pdf,.doc,.docx">
            <input type="submit" value="Update Report">
        </form>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Date</th>
                    <th>Report File</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['student_name']) ?></td>
                        <td><?= htmlspecialchars($row['report_date']) ?></td>
                        <td><a href="uploads/<?= htmlspecialchars($row['report_file']) ?>" target="_blank">View File</a></td>
                        <td>
                            <a class="action-btn edit-btn" href="?edit=<?= $row['id'] ?>">Edit</a>
                            <a class="action-btn delete-btn" href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure to delete this report?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No reports uploaded yet.</p>
    <?php endif; ?>

    <a class="back-link" href="student_dashboard.php">← Back to Dashboard</a>
</div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
