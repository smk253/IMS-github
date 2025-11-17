<?php
session_start();
require_once 'db.php';

// --- Admin Login check ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// --- Search / Filter parameters ---
$search_name = $_GET['student_name'] ?? '';
$filter_status = $_GET['status'] ?? '';

// --- Status + Supervisor Update ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['app_id'])) {
    $app_id = intval($_POST['app_id']);
    $status = $_POST['status'] ?? 'Pending';
    $supervisor_id = !empty($_POST['supervisor_id']) ? intval($_POST['supervisor_id']) : null;

    $update_sql = "UPDATE applications SET status = ?, supervisor_id = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sii", $status, $supervisor_id, $app_id);
    $update_stmt->execute();
    $update_stmt->close();

    if ($supervisor_id) {
        $stu_sql = "UPDATE students s
                    JOIN applications a ON s.id = a.student_id
                    SET s.supervisor_id = ?
                    WHERE a.id = ?";
        $stu_stmt = $conn->prepare($stu_sql);
        $stu_stmt->bind_param("ii", $supervisor_id, $app_id);
        $stu_stmt->execute();
        $stu_stmt->close();
    }

    header("Location: adminview_application_list.php");
    exit();
}

// --- Get supervisors list ---
$supervisors = [];
$super_sql = "SELECT id, name FROM supervisors ORDER BY name ASC";
$super_result = $conn->query($super_sql);
while ($row = $super_result->fetch_assoc()) {
    $supervisors[] = $row;
}

// --- Build application list SQL ---
$sql = "SELECT applications.*, students.name AS student_name, supervisors.name AS supervisor_name
        FROM applications
        JOIN students ON applications.student_id = students.id
        LEFT JOIN supervisors ON applications.supervisor_id = supervisors.id
        WHERE 1=1";

$params = [];
$types = "";

if (!empty($search_name)) {
    $sql .= " AND students.name LIKE ?";
    $params[] = "%$search_name%";
    $types .= "s";
}

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
<title>Admin - Internship Applications</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f7fa;
    margin: 0; padding: 0;
}
.container {
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
}
h2 { text-align: center; color: #0077b6; margin-bottom: 20px; }

/* Search Form */
form.search-form {
    display: flex; flex-wrap: wrap; justify-content: space-between; gap: 10px; margin-bottom: 20px;
}
.search-left { display: flex; flex-wrap: wrap; gap: 10px; }
input, select, button { padding: 8px; border-radius: 4px; border: 1px solid #ccc; font-size: 14px; }
button { background: #2a9df4; color: white; border: none; cursor: pointer; }
button:hover { background: #1b82d1; }

/* Desktop / Tablet Table */
.table-wrapper { width: 100%; overflow-x: auto; }
table { width: 100%; border-collapse: collapse; min-width: 900px; }
th, td { padding: 10px; border: 1px solid #ddd; text-align: left; font-size: 14px; white-space: nowrap; }
th { background: #0077b6; color: white; position: sticky; top: 0; z-index: 1; }
tr:nth-child(even) { background: #f2f9ff; }
td form { display: flex; flex-wrap: wrap; gap: 5px; }
td select, td button { flex: 1; min-width: 120px; }

/* Mobile Card Layout */
@media (max-width: 768px) {
    .table-wrapper { display: flex; flex-direction: column; gap: 15px; }
    table, thead, tbody, th, td, tr { display: block; border: none; width: 100%; }
    thead { display: none; }
    tr { background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    td { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
    td:last-child { border-bottom: none; }
    td::before { content: attr(data-label); font-weight: bold; color: #0077b6; flex-basis: 40%; }
    td form { flex-direction: column; gap: 5px; }
    td select, td button { width: 100%; min-width: unset; }
}

/* Mobile smaller adjustments */
@media (max-width: 480px) {
    .container { margin: 10px; padding: 15px; }
    h2 { font-size: 18px; }
    input, select, button { font-size: 13px; padding: 6px; }
}
</style>
</head>
<body>
<div class="container">
    <h2>📋 Internship Applications</h2>

    <form method="get" class="search-form">
        <div class="search-left">
            <input type="text" name="student_name" placeholder="Search by Student Name" value="<?= htmlspecialchars($search_name) ?>">
            <select name="status">
                <option value="">-- All Status --</option>
                <option value="Pending" <?= $filter_status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Accepted" <?= $filter_status === 'Accepted' ? 'selected' : '' ?>>Accepted</option>
                <option value="Rejected" <?= $filter_status === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
            <button type="submit">Search</button>
        </div>
        <button type="button" onclick="window.location.href='admin_pannel.php'">Back</button>
    </form>

    <div class="table-wrapper">
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
                    <th>Supervisor</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td data-label="Student Name"><?= htmlspecialchars($row['student_name']) ?></td>
                    <td data-label="Company"><?= htmlspecialchars($row['company_name']) ?></td>
                    <td data-label="Position"><?= htmlspecialchars($row['position']) ?></td>
                    <td data-label="Location"><?= htmlspecialchars($row['location']) ?></td>
                    <td data-label="Start Date"><?= htmlspecialchars($row['start_date']) ?></td>
                    <td data-label="End Date"><?= htmlspecialchars($row['end_date']) ?></td>
                    <td data-label="Attachment">
                        <?php if ($row['attachment']): ?>
                            <a href="uploads/<?= htmlspecialchars($row['attachment']) ?>" target="_blank">View</a>
                        <?php else: ?>N/A<?php endif; ?>
                    </td>
                    <td data-label="Applied At"><?= htmlspecialchars($row['applied_at']) ?></td>
                    <td data-label="Status"><?= htmlspecialchars($row['status']) ?></td>
                    <td data-label="Supervisor"><?= $row['supervisor_name'] ? htmlspecialchars($row['supervisor_name']) : 'Not Assigned' ?></td>
                    <td data-label="Update">
                        <form method="post">
                            <input type="hidden" name="app_id" value="<?= $row['id'] ?>">
                            <select name="status">
                                <option value="Pending" <?= $row['status']==='Pending'?'selected':'' ?>>Pending</option>
                                <option value="Accepted" <?= $row['status']==='Accepted'?'selected':'' ?>>Accepted</option>
                                <option value="Rejected" <?= $row['status']==='Rejected'?'selected':'' ?>>Rejected</option>
                            </select>
                            <select name="supervisor_id">
                                <option value="">-- Select Supervisor --</option>
                                <?php foreach($supervisors as $sup): ?>
                                <option value="<?= $sup['id'] ?>" <?= ($row['supervisor_id']==$sup['id'])?'selected':'' ?>>
                                    <?= htmlspecialchars($sup['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit">Save</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>No applications found.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
