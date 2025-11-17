<?php
session_start();
include("db.php"); // DB connection

// Student login ထဲက ID ထုတ်ယူ
if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}
$student_id = $_SESSION['student_id'];

// Student ရဲ့ applications နဲ့ သက်ဆိုင်တဲ့ feedback တွေ query
$query = "SELECT sr.id as report_id, sr.feedback, sr.created_at,
                 a.company_name, a.position, a.start_date, a.end_date,
                 sup.name as supervisor_name
          FROM supervisor_reports sr
          JOIN applications a ON sr.application_id = a.id
          JOIN supervisors sup ON sr.supervisor_id = sup.id
          WHERE a.student_id = '$student_id'
          ORDER BY sr.created_at DESC";

$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Feedback</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f2f2f2; }
        .no-data { color: red; margin-top: 15px; }
    </style>
</head>
<body>

<h2>Supervisor Feedback</h2>

<?php if (mysqli_num_rows($result) > 0) { ?>
<table>
   <tr>
      <th>ID</th>
      <th>Company</th>
      <th>Position</th>
      <th>Start</th>
      <th>End</th>
      <th>Supervisor</th>
      <th>Feedback</th>
      <th>Date</th>
   </tr>
   <?php while($row = mysqli_fetch_assoc($result)) { ?>
   <tr>
      <td><?= $row['report_id'] ?></td>
      <td><?= $row['company_name'] ?></td>
      <td><?= $row['position'] ?></td>
      <td><?= $row['start_date'] ?></td>
      <td><?= $row['end_date'] ?></td>
      <td><?= $row['supervisor_name'] ?></td>
      <td><?= nl2br(htmlspecialchars($row['feedback'])) ?></td>
      <td><?= $row['created_at'] ?></td>
   </tr>
   <?php } ?>
</table>
<?php } else { ?>
    <p class="no-data">No feedback available yet.</p>
<?php } ?>

</body>
</html>
