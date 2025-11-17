<?php
session_start();

// --- Auth check ---
if (!isset($_SESSION['student_id']) || !isset($_SESSION['student_name'])) {
    header("Location: ../../login.php");
    exit();
}

$student_id   = (int) $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];

include("db.php");

// find student's supervisor_id
$supervisor_id = null;
$sql = "SELECT supervisor_id FROM students WHERE id=$student_id LIMIT 1";
$res = mysqli_query($conn, $sql);
if ($res && mysqli_num_rows($res) > 0) {
    $supervisor_id = (int) mysqli_fetch_assoc($res)['supervisor_id'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Dashboard</title>
<style>
:root {
    --primary: #003366;
    --btn-hover: #0059b3;
    --bg: #f4f7fa;
    --box-bg: #fff;
    --box-hover: #e6f0ff;
}

body { background: var(--bg); font-family: Arial, sans-serif; margin: 0; padding: 0; }
.navbar {
    display: flex; justify-content: space-between; align-items: center;
    background: var(--primary); color: #fff; padding: 15px 20px;
}
.navbar h1 { font-size: 1.5rem; }
.navbar a {
    background: #002244; color: #fff; text-decoration: none;
    padding: 8px 14px; border-radius: 6px; font-weight: bold;
}
.navbar a:hover { background: var(--btn-hover); }

.dashboard { max-width: 1200px; margin: 30px auto; padding: 0 15px; }
.dashboard p { text-align: center; margin-bottom: 30px; }

.box-container {
    display: grid; grid-template-columns: repeat(auto-fit,minmax(200px,1fr));
    gap: 20px;
}
.box {
    background: var(--box-bg); padding: 40px 20px; text-align: center;
    border-radius: 12px; text-decoration: none; color: var(--primary);
    font-weight: bold; box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transition: 0.3s;
}
.box:hover { background: var(--box-hover); transform: translateY(-5px); }

.announcements {
    margin-top: 40px; padding: 20px; background: #fff;
    border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.announcements h2 { margin-bottom: 15px; color: var(--primary); }
.announcement-box {
    padding: 12px; border-bottom: 1px solid #eee; font-size: 0.95rem;
}
.announcement-box:last-child { border-bottom: none; }
.announcement-box .date { font-size: 0.8rem; color: #666; margin-top: 4px; }

.btn-view-all {
    display: inline-block; margin-top: 15px; background: var(--primary);
    color: #fff; padding: 8px 14px; border-radius: 6px; text-decoration: none;
}
.btn-view-all:hover { background: var(--btn-hover); }
</style>
</head>
<body>

<div class="navbar">
    <h1>🎓 Welcome, <?= htmlspecialchars($student_name) ?>!</h1>
    <a href="home.php">Logout</a>
</div>

<div class="dashboard">
    <p>You are now logged into the internship management system.</p>

    <div class="box-container">
        <a href="apply_intern.php" class="box">📄 Apply Internship</a>
        <a href="application_list.php" class="box">📂 Manage Application</a>
        <a href="upload_report.php" class="box">📝 Upload Daily Report</a>
        <a href="studentview_feedback.php" class="box">✅ View Feedback</a>
    </div>

    <!-- Announcements Section -->
    <div class="announcements">
        <h2>📢 Supervisor Announcements</h2>
        <?php
        if ($supervisor_id) {
            $annSql = "SELECT sa.message, sa.created_at, su.name as supervisor_name 
                       FROM supervisor_announcements sa
                       JOIN supervisors su ON sa.supervisor_id=su.id
                       WHERE sa.supervisor_id=$supervisor_id
                       ORDER BY sa.created_at DESC LIMIT 3";
            $annRes = mysqli_query($conn, $annSql);

            if ($annRes && mysqli_num_rows($annRes) > 0) {
                while ($row = mysqli_fetch_assoc($annRes)) {
                    echo "<div class='announcement-box'>
                            <strong>".htmlspecialchars($row['supervisor_name']).":</strong> 
                            ".htmlspecialchars($row['message'])."
                            <div class='date'>".date('M d, Y H:i', strtotime($row['created_at']))."</div>
                          </div>";
                }
            } else {
                echo "<p style='color:#666'>No announcements from your supervisor yet.</p>";
            }
            echo '<a href="all_announcements.php" class="btn-view-all">🔎 See All</a>';
        } else {
            echo "<p style='color:#666'>You don’t have a supervisor assigned yet.</p>";
        }
        ?>
    </div>
</div>
</body>
</html>
