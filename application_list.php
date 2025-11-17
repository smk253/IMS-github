<?php
session_start();
require_once 'db.php';

// Login check
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$student_name = isset($_SESSION['student_name']) ? $_SESSION['student_name'] : 'User';

$sql = "SELECT * FROM applications WHERE student_id = ? ORDER BY applied_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Internship Applications</title>
<style>
:root {
    --primary: #0077b6;
    --accent: #2a9df4;
    --accent-hover: #1b82d1;
    --danger: #d9534f;
    --bg: #f4f7fa;
    --white: #fff;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: var(--bg);
    margin: 0;
    padding: 0;
}

.container {
    max-width: 1000px;
    margin: 40px auto;
    padding: 20px;
    background: var(--white);
    border-radius: 12px;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
}

h2 {
    color: var(--primary);
    text-align: center;
    margin-bottom: 20px;
}

a.back-link {
    display: inline-block;
    margin-bottom: 20px;
    color: var(--primary);
    text-decoration: none;
}
a.back-link:hover {
    text-decoration: underline;
}

.table-wrapper {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 900px;
}

th, td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: left;
    vertical-align: middle;
    font-size: 14px;
}

th {
    background-color: var(--primary);
    color: var(--white);
    font-weight: 600;
}

tr:nth-child(even) {
    background-color: #f2f9ff;
}

.action-cell {
    white-space: nowrap;
    text-align: center;
}

.action-btn {
    display: inline-block;
    text-decoration: none;
    padding: 4px 8px;
    border-radius: 5px;
    color: white;
    font-size: 0.85rem;
    margin: 0 2px;
}

.edit-btn { background-color: var(--accent); }
.delete-btn { background-color: var(--danger); }

.edit-btn:hover { background-color: var(--accent-hover); }
.delete-btn:hover { background-color: #c9302c; }

/* Responsive: stack cards on small screens */
@media (max-width: 768px) {
    table, thead, tbody, th, td, tr {
        display: block;
        width: 100%;
    }
    thead {
        display: none;
    }
    tr {
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px;
        background: #fff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }
    td {
        border: none;
        padding: 6px 10px;
        display: flex;
        justify-content: space-between;
        position: relative;
    }
    td::before {
        content: attr(data-label);
        font-weight: bold;
        color: #555;
    }
    .action-cell {
        text-align: right;
        margin-top: 10px;
    }
}
</style>
</head>
<body>
<div class="container">
    <h2>📋 My Internship Applications</h2>
    <a href="student_dashboard.php" class="back-link">🔙 Back to Dashboard</a>

    <?php if ($result->num_rows > 0): ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Company</th>
                    <th>Position</th>
                    <th>Location</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Description</th>
                    <th>Attachment</th>
                    <th>Applied At</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td data-label="Name"><?= htmlspecialchars($student_name) ?></td>
                    <td data-label="Company"><?= htmlspecialchars($row['company_name']) ?></td>
                    <td data-label="Position"><?= htmlspecialchars($row['position']) ?></td>
                    <td data-label="Location"><?= htmlspecialchars($row['location']) ?></td>
                    <td data-label="Start Date"><?= htmlspecialchars($row['start_date']) ?></td>
                    <td data-label="End Date"><?= htmlspecialchars($row['end_date']) ?></td>
                    <td data-label="Description"><?= nl2br(htmlspecialchars($row['description'])) ?></td>
                    <td data-label="Attachment">
                        <?php if ($row['attachment']): ?>
                            <a href="uploads/<?= htmlspecialchars($row['attachment']) ?>" target="_blank">View File</a>
                        <?php else: ?> N/A <?php endif; ?>
                    </td>
                    <td data-label="Applied At"><?= htmlspecialchars($row['applied_at']) ?></td>
                    <td data-label="Status"><?= htmlspecialchars($row['status']) ?></td>
                    <td class="action-cell" data-label="Actions">
                        <a href="edit_application.php?id=<?= $row['id'] ?>" class="action-btn edit-btn">✏️ Edit</a>
                        <a href="delete_application.php?id=<?= $row['id'] ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this application?')">🗑️ Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p>No internship applications found. <a href="apply_intern.php">Apply now</a></p>
    <?php endif; ?>
</div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
