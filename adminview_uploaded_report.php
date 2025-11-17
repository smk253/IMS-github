<?php
session_start();
require_once 'db.php';

// Admin Login check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Search / Filter parameters
$search_name = $_GET['student_name'] ?? '';
$filter_date = $_GET['report_date'] ?? '';

// Pagination parameters
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Count total rows
$count_sql = "SELECT COUNT(*) as total 
              FROM daily_reports dr 
              JOIN students s ON dr.student_id = s.id 
              LEFT JOIN supervisors sup ON s.supervisor_id = sup.id 
              WHERE 1=1";
$count_params = [];
$count_types = "";

if (!empty($search_name)) {
    $count_sql .= " AND s.name LIKE ?";
    $count_params[] = "%$search_name%";
    $count_types .= "s";
}
if (!empty($filter_date)) {
    $count_sql .= " AND dr.report_date = ?";
    $count_params[] = $filter_date;
    $count_types .= "s";
}

$stmt = $conn->prepare($count_sql);
if ($count_params) $stmt->bind_param($count_types, ...$count_params);
$stmt->execute();
$total_result = $stmt->get_result()->fetch_assoc();
$total_rows = $total_result['total'];
$total_pages = ceil($total_rows / $limit);
$stmt->close();

// Main query
$sql = "SELECT dr.*, s.name AS student_name, sup.name AS supervisor_name
        FROM daily_reports dr
        JOIN students s ON dr.student_id = s.id
        LEFT JOIN supervisors sup ON s.supervisor_id = sup.id
        WHERE 1=1";

$params = [];
$types = "";

if (!empty($search_name)) {
    $sql .= " AND s.name LIKE ?";
    $params[] = "%$search_name%";
    $types .= "s";
}
if (!empty($filter_date)) {
    $sql .= " AND dr.report_date = ?";
    $params[] = $filter_date;
    $types .= "s";
}

$sql .= " ORDER BY dr.id ASC LIMIT ?, ?";
$params[] = $offset;
$params[] = $limit;
$types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Uploaded Reports</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f4f7fa; }
.container { max-width: 1200px; margin: 20px auto; }
h2 { text-align: center; margin-bottom: 20px; color: #0077b6; }

.filter-form { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
.filter-form input, .filter-form button { flex: 1 1 auto; min-width: 120px; }

/* Mobile Accordion Cards */
@media (max-width: 768px) {
    .table-responsive { display: none; }
    .accordion-button:focus { box-shadow: none; }
}

/* Pagination */
.pagination { margin-top: 20px; display: flex; justify-content: center; flex-wrap: wrap; gap: 5px; }
.pagination a { padding: 6px 10px; border-radius: 4px; text-decoration: none; color: #0077b6; border: 1px solid #0077b6; }
.pagination a.active { font-weight: bold; background: #0077b6; color: white; }
</style>
</head>
<body>
<div class="container">
<h2>📄 Uploaded Reports</h2>

<form method="get" class="filter-form mb-3">
    <input type="text" name="student_name" placeholder="Search by Student Name" class="form-control" value="<?= htmlspecialchars($search_name) ?>">
    <input type="date" name="report_date" class="form-control" value="<?= htmlspecialchars($filter_date) ?>">
    <button type="submit" class="btn btn-primary">Filter</button>
    <button type="button" class="btn btn-secondary" onclick="window.location.href='admin_pannel.php'">Back</button>
   <!--<button type="button" class="btn btn-success" onclick="window.location.href='download_all_reports.php'">Download All Reports</button>-->
</form>

<!-- Desktop / Tablet Table -->
<div class="table-responsive">
<?php if($result->num_rows > 0): ?>
<table class="table table-bordered table-hover">
    <thead class="table-primary">
        <tr>
            <th>ID</th>
            <th>Student Name</th>
            <th>Report Date</th>
            <th>File</th>
            <th>Submitted At</th>
            <th>Supervisor Name</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['student_name']) ?></td>
            <td><?= $row['report_date'] ?></td>
            <td><?= $row['report_file'] ? '<a href="uploads/'.htmlspecialchars($row['report_file']).'" target="_blank">View</a>' : 'N/A' ?></td>
            <td><?= $row['submitted_at'] ?></td>
            <td><?= $row['supervisor_name'] ? htmlspecialchars($row['supervisor_name']) : 'Not Assigned' ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
<?php else: ?>
<p class="text-center">No reports found.</p>
<?php endif; ?>
</div>

<!-- Mobile Accordion Cards -->
<?php if($result->num_rows > 0): ?>
<div class="accordion d-md-none" id="reportAccordion">
    <?php $result->data_seek(0); $counter=1; ?>
    <?php while($row = $result->fetch_assoc()): ?>
    <div class="accordion-item mb-2">
        <h2 class="accordion-header" id="heading<?= $counter ?>">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $counter ?>" aria-expanded="false" aria-controls="collapse<?= $counter ?>">
                <?= htmlspecialchars($row['student_name']) ?> - <?= $row['report_date'] ?>
            </button>
        </h2>
        <div id="collapse<?= $counter ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $counter ?>" data-bs-parent="#reportAccordion">
            <div class="accordion-body">
                <p><strong>ID:</strong> <?= $row['id'] ?></p>
                <p><strong>File:</strong> <?= $row['report_file'] ? '<a href="uploads/'.htmlspecialchars($row['report_file']).'" target="_blank">View</a>' : 'N/A' ?></p>
                <p><strong>Submitted At:</strong> <?= $row['submitted_at'] ?></p>
                <p><strong>Supervisor:</strong> <?= $row['supervisor_name'] ? htmlspecialchars($row['supervisor_name']) : 'Not Assigned' ?></p>
            </div>
        </div>
    </div>
    <?php $counter++; endwhile; ?>
</div>
<?php endif; ?>

<!-- Pagination -->
<div class="pagination">
    <?php for($i=1;$i<=$total_pages;$i++): ?>
        <a href="?student_name=<?= urlencode($search_name) ?>&report_date=<?= $filter_date ?>&page=<?= $i ?>" class="<?= $page==$i?'active':'' ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
