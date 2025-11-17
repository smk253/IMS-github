<?php
session_start();
include("db.php"); // $conn

// --- Auth check ---
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}

$student_id = (int) $_SESSION['student_id'];
$student_name = $_SESSION['student_name'] ?? '';

// --- Query supervisor feedbacks ---
$sql = "SELECT sr.id, sr.feedback, sr.created_at, sp.name AS supervisor_name, dr.report_date, dr.report_file 
        FROM supervisor_reports sr
        JOIN daily_reports dr ON sr.application_id = dr.id
        JOIN supervisors sp ON sr.supervisor_id = sp.id
        WHERE dr.student_id = $student_id
        ORDER BY sr.created_at DESC";

$res = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Feedback - Computer University</title>
<style>
:root {
    --primary:#003366;
    --accent:#0059b3;
    --bg:#f4f6f9;
    --white:#fff;
}

body {
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    background: var(--bg);
    margin: 0;
}

.container {
    max-width: 1000px;
    margin: 30px auto;
    padding: 20px;
    background: var(--white);
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.05);
}

h2 {
    color: var(--primary);
    text-align: center;
    margin-bottom: 25px;
    font-size: 1.5rem;
}

.table-wrapper {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 800px;
    border-radius: 10px;
    overflow: hidden;
}

th, td {
    padding: 12px;
    border-bottom: 1px solid #eee;
    text-align: left;
    font-size: 14px;
}

th {
    background: #e6f0ff;
    color: var(--primary);
    text-transform: uppercase;
    font-size: 12px;
}

tr:nth-child(even) {
    background-color: #f2f9ff;
}

a.report-link {
    color: var(--accent);
    text-decoration: none;
    font-weight: bold;
}
a.report-link:hover {
    text-decoration: underline;
}

.back-button {
    display: block;
    width: 180px;
    margin: 25px auto 0;
    padding: 12px 0;
    background-color: var(--primary);
    color: var(--white);
    text-align: center;
    text-decoration: none;
    font-weight: bold;
    border-radius: 6px;
    transition: background-color 0.3s ease;
}
.back-button:hover {
    background-color: var(--accent);
}

/* Responsive styling */
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
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
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
        flex-basis: 45%;
    }
    td a.report-link {
        flex-basis: 55%;
        text-align: right;
    }
}
</style>
</head>
<body>
<div class="container">
    <h2>Welcome <?=htmlspecialchars($student_name)?>, Your Supervisor Feedback</h2>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Supervisor</th>
                    <th>Report Date</th>
                    <th>Report File</th>
                    <th>Feedback</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($res && mysqli_num_rows($res) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($res)): ?>
                        <tr>
                            <td data-label="ID"><?=$row['id']?></td>
                            <td data-label="Supervisor"><?=htmlspecialchars($row['supervisor_name'])?></td>
                            <td data-label="Report Date"><?=$row['report_date']?></td>
                            <td data-label="Report File">
                                <?php if ($row['report_file']): ?>
                                    <a class="report-link" href="uploads/<?=htmlspecialchars($row['report_file'])?>" target="_blank">
                                        <?=htmlspecialchars($row['report_file'])?>
                                    </a>
                                <?php else: ?> N/A <?php endif; ?>
                            </td>
                            <td data-label="Feedback"><?=nl2br(htmlspecialchars($row['feedback']))?></td>
                            <td data-label="Date"><?=$row['created_at']?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center;color:#888">
                            No feedback available yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <a href="student_dashboard.php" class="back-button">Back to Dashboard</a>
</div>
</body>
</html>
