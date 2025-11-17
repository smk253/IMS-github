<?php
session_start();
require_once 'db.php';

// ✅ Supervisor Login Check
if (!isset($_SESSION['supervisor_id'])) {
    header("Location: login.php");
    exit();
}

$supervisor_id = (int)$_SESSION['supervisor_id'];

// 🔎 Search / Filter (optional)
$search_name   = isset($_GET['student_name']) ? trim($_GET['student_name']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';

// 📌 Build query: applications ⇄ students (supervisors table JOIN မလို — session ကနေ supervisor_id ရ already)
$sql = "
    SELECT
        s.id   AS student_id,
        s.name AS student_name,
        s.email,
        COUNT(a.id) AS app_count,
        SUM(CASE WHEN a.status='Pending'  THEN 1 ELSE 0 END) AS pending_count,
        SUM(CASE WHEN a.status='Accepted' THEN 1 ELSE 0 END) AS accepted_count,
        SUM(CASE WHEN a.status='Rejected' THEN 1 ELSE 0 END) AS rejected_count,
        MAX(a.applied_at) AS last_applied_at
    FROM applications a
    JOIN students s ON a.student_id = s.id
    WHERE a.supervisor_id = ?
";

$types  = "i";
$params = [$supervisor_id];

if ($search_name !== '') {
    $sql     .= " AND s.name LIKE ?";
    $types   .= "s";
    $params[] = "%{$search_name}%";
}
if ($filter_status !== '') {
    $sql     .= " AND a.status = ?";
    $types   .= "s";
    $params[] = $filter_status;
}

$sql .= "
    GROUP BY s.id, s.name, s.email
    ORDER BY last_applied_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supervisor - Assigned Students</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f7fa; }
        .container { max-width: 1200px; margin: 40px auto; background: #fff; padding: 20px; border-radius: 8px; }
        h2 { text-align: center; color: #0077b6; margin-bottom: 16px; }
        .muted { color:#555; text-align:center; margin-top:-8px; }
        form.search { margin: 18px 0 8px; display:flex; gap:10px; flex-wrap:wrap; }
        input, select, button { padding:8px; border-radius:6px; border:1px solid #ddd; }
        button { background:#2a9df4; color:#fff; border:none; cursor:pointer; }
        button:hover { background:#1b82d1; }
        table { width:100%; border-collapse: collapse; margin-top: 12px; }
        th, td { padding:10px; border:1px solid #e6eef5; }
        th { background:#0077b6; color:#fff; text-align:left; }
        tr:nth-child(even) { background:#f2f9ff; }
        a { color:#2a9df4; text-decoration:none; }
        a:hover { text-decoration:underline; }
        .badge { padding:3px 8px; border-radius:12px; font-size:12px; border:1px solid #ddd; background:#f8fafc; }
        .center { text-align:center; }
    </style>
</head>
<body>
<div class="container">
    <h2>👥 Assigned Students</h2>
    <p class="muted">You can see students who have at least one application assigned to you.</p>

    <!-- 🔎 Search / Filter -->
    <form method="get" class="search">
        <input type="text" name="student_name" placeholder="Search by student name"
               value="<?= htmlspecialchars($search_name) ?>">
        <select name="status">
            <option value="">All statuses</option>
            <option value="Pending"  <?= $filter_status==='Pending'  ? 'selected' : '' ?>>Pending</option>
            <option value="Accepted" <?= $filter_status==='Accepted' ? 'selected' : '' ?>>Accepted</option>
            <option value="Rejected" <?= $filter_status==='Rejected' ? 'selected' : '' ?>>Rejected</option>
        </select>
        <button type="submit">Search</button>
        <?php if ($search_name !== '' || $filter_status !== ''): ?>
            <a href="assigned_student.php" class="badge">Reset</a>
        <?php endif; ?>
    </form>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th style="width:28%">Student</th>
                    <th>Email</th>
                    <th class="center">Total Apps</th>
                    <th class="center">Pending</th>
                    <th class="center">Accepted</th>
                    <th class="center">Rejected</th>
                    <th>Last Applied</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['student_name']) ?></td>
                    <td><?= htmlspecialchars($row['email'] ?? '') ?></td>
                    <td class="center"><?= (int)$row['app_count'] ?></td>
                    <td class="center"><?= (int)$row['pending_count'] ?></td>
                    <td class="center"><?= (int)$row['accepted_count'] ?></td>
                    <td class="center"><?= (int)$row['rejected_count'] ?></td>
                    <td><?= htmlspecialchars($row['last_applied_at']) ?></td>
                    <td>
                        <!-- 👉 Link to see ONLY this student's assigned applications (read-only) -->
                        <a href="superview_application_list.php?student_id=<?= (int)$row['student_id'] ?>">View Applications</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No assigned students found.</p>
    <?php endif; ?>
</div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
