<?php
session_start();
require_once 'db.php';

// Supervisor Login check
if (!isset($_SESSION['supervisor_id'])) {
    header("Location: login.php");
    exit();
}

// Get search/filter values from GET
$search_name = $_GET['student_name'] ?? '';
$filter_status = $_GET['status'] ?? '';

// Build SQL with optional filters
$sql = "SELECT applications.*, students.name AS student_name
        FROM applications
        JOIN students ON applications.student_id = students.id
        WHERE 1=1";

$params = [];
$types = "";

// Filter by student name
if (!empty($search_name)) {
    $sql .= " AND students.name LIKE ?";
    $params[] = "%$search_name%";
    $types .= "s";
}

// Filter by status
if (!empty($filter_status)) {
    $sql .= " AND applications.status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

$sql .= " ORDER BY applied_at DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supervisor - All Student Applications</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f7fa; }
        .container { max-width: 1200px; margin: 40px auto; background: #fff; padding: 20px; border-radius: 8px; }
        h2 { text-align: center; color: #0077b6; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; }
        th { background: #0077b6; color: white; }
        tr:nth-child(even) { background: #f2f9ff; }
        a { color: #2a9df4; text-decoration: none; }
        a:hover { text-decoration: underline; }
        form { margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap; }
        input, select, button { padding: 5px; border-radius: 4px; }
        button { background: #2a9df4; color: white; border: none; cursor: pointer; }
        button:hover { background: #1b82d1; }
    </style>
</head>
<body>
<div class="container">
    <h2>📋 All Student Internship Applications</h2>

    <!-- Search/Filter Form -->
    <form method="get">
        <input type="text" name="student_name" placeholder="Student Name" value="<?= htmlspecialchars($search_name) ?>">
        <select name="status">
            <option value="">-- All Status --</option>
            <option value="Pending" <?= $filter_status==='Pending'?'selected':'' ?>>Pending</option>
            <option value="Accepted" <?= $filter_status==='Accepted'?'selected':'' ?>>Accepted</option>
            <option value="Rejected" <?= $filter_status==='Rejected'?'selected':'' ?>>Rejected</option>
        </select>
        <button type="submit">Search</button>
    </form>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Company</th>
                    <th>Position</th>
                    <th>Location</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Attachment</th>
                    <th>Applied At</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['student_name']) ?></td>
                        <td><?= htmlspecialchars($row['company_name']) ?></td>
                        <td><?= htmlspecialchars($row['position']) ?></td>
                        <td><?= htmlspecialchars($row['location']) ?></td>
                        <td><?= htmlspecialchars($row['start_date']) ?></td>
                        <td><?= htmlspecialchars($row['end_date']) ?></td>
                        <td>
                            <?php if ($row['attachment']): ?>
                                <a href="uploads/<?= htmlspecialchars($row['attachment']) ?>" target="_blank">View</a>
                            <?php else: ?>N/A<?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['applied_at']) ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No applications found.</p>
    <?php endif; ?>
</div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
