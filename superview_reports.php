<?php
session_start();
include("db.php");

if (!isset($_SESSION['supervisor_id'])) { exit("Unauthorized"); }

$student_id = (int)($_GET['student_id'] ?? 0);

$q = "SELECT * FROM daily_reports WHERE student_id=$student_id ORDER BY submitted_at DESC";
$res = mysqli_query($conn, $q);

if ($res && mysqli_num_rows($res) > 0) {
  while ($r = mysqli_fetch_assoc($res)) {
    echo "<div style='border:1px solid #eee;padding:10px;margin-bottom:10px'>";
    echo "<strong>" . htmlspecialchars($r['report_date']) . "</strong> - ";
    echo "<a href='uploads/" . rawurlencode($r['report_file']) . "' target='_blank'>" . htmlspecialchars($r['report_file']) . "</a>";
    echo "<div><small>Submitted: " . $r['submitted_at'] . "</small></div>";

    // Feedback form
    echo "<form method='post' action='supervisor_dashboard.php' style='margin-top:6px'>";
    echo "<input type='hidden' name='submit_report_feedback' value='1'>";
    echo "<input type='hidden' name='report_id' value='" . (int)$r['id'] . "'>";
    echo "<textarea name='feedback' placeholder='Feedback for this report...' class='form-control mb-2' required></textarea>";
    echo "<button class='btn btn-primary btn-sm' type='submit'>Save Feedback</button>";
    echo "</form>";

    echo "</div>";
  }
} else {
  echo "<p>No reports found for this student.</p>";
}
?>
