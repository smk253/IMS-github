<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: ../../login.php");
    exit();
}

include("db.php");

$student_id = (int) $_SESSION['student_id'];

// student's supervisor_id 
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
<title>All Announcements</title>
<style>
body { font-family: Arial, sans-serif; background: #f4f7fa; margin: 0; }
.container {
    max-width: 800px; margin: 40px auto; background: #fff; padding: 25px;
    border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
h2 { margin-bottom: 20px; color: #003366; }
.announcement { border-bottom: 1px solid #eee; padding: 15px 0; }
.announcement:last-child { border-bottom: none; }
.date { font-size: 0.85rem; color: #666; margin-top: 5px; }
.back-btn {
    display: inline-block; margin-top: 20px; background: #003366;
    color: #fff; padding: 10px 16px; border-radius: 6px; text-decoration: none;
}
.back-btn:hover { background: #0059b3; }
</style>
</head>
<body>

<div class="container">
    <h2>📢 All Announcements</h2>
    <?php
    if ($supervisor_id) {
        $sql = "SELECT sa.message, sa.created_at, su.name as supervisor_name 
                FROM supervisor_announcements sa
                JOIN supervisors su ON sa.supervisor_id = su.id
                WHERE sa.supervisor_id = $supervisor_id
                ORDER BY sa.created_at DESC";
        $res = mysqli_query($conn, $sql);

        if ($res && mysqli_num_rows($res) > 0) {
            while ($row = mysqli_fetch_assoc($res)) {
                echo "<div class='announcement'>
                        <strong>".htmlspecialchars($row['supervisor_name']).":</strong> 
                        ".htmlspecialchars($row['message'])."
                        <div class='date'>".date('M d, Y H:i', strtotime($row['created_at']))."</div>
                      </div>";
            }
        } else {
            echo "<p>No announcements from your supervisor yet.</p>";
        }
    } else {
        echo "<p>You don’t have a supervisor assigned yet.</p>";
    }
    ?>
    <a href="student_dashboard.php" class="back-btn">⬅ Back to Dashboard</a>
</div>

</body>
</html>
