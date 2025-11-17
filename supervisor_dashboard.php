<?php
session_start();
include("db.php"); // $conn

// --- Auth check ---
if (!isset($_SESSION['supervisor_id'])) {
    header("Location: supervisor_login.php");
    exit();
}
$supervisor_id = (int) $_SESSION['supervisor_id'];

// Supervisor name
$supervisor_name = $_SESSION['supervisor_name'] ?? '';
if ($supervisor_name === '') {
    $sq = mysqli_query($conn, "SELECT name FROM supervisors WHERE id=$supervisor_id LIMIT 1");
    if ($sq && mysqli_num_rows($sq) === 1) {
        $supervisor_name = mysqli_fetch_assoc($sq)['name'];
        $_SESSION['supervisor_name'] = $supervisor_name;
    } else {
        $supervisor_name = "Supervisor";
    }
}

$msg = '';

// --- Save feedback for Daily Report ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_report_feedback'])) {
    $report_id = (int)($_POST['report_id'] ?? 0);
    $feedback = trim($_POST['feedback'] ?? '');

    if ($report_id > 0 && $feedback !== '') {
        $sql = "INSERT INTO supervisor_reports(report_id, supervisor_id, feedback, created_at)
                VALUES($report_id, $supervisor_id, '".mysqli_real_escape_string($conn, $feedback)."', NOW())";
        if (mysqli_query($conn, $sql)) {
            $last_id = mysqli_insert_id($conn);
            $msg = "✅ Daily report feedback saved. (ID: $last_id, report_id: $report_id)";
        } else {
            $msg = "❌ SQL Error: " . mysqli_error($conn);
        }
    } else {
        $msg = "⚠ Missing report id or feedback. report_id=$report_id, feedback='$feedback'";
    }
}

// --- Save Announcement ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_announcement'])) {
    $message = trim($_POST['announcement'] ?? '');
    if ($message !== '') {
        $sql = "INSERT INTO supervisor_announcements (supervisor_id, message, created_at) 
                VALUES($supervisor_id, '".mysqli_real_escape_string($conn, $message)."', NOW())";
        if (mysqli_query($conn, $sql)) {
            $msg = "📢 Announcement posted successfully.";
        } else {
            $msg = "❌ SQL Error: " . mysqli_error($conn);
        }
    }
}

// Stats
$stats = ['total'=>0,'accepted'=>0,'pending'=>0,'rejected'=>0];
$q="SELECT COUNT(*) total,
SUM(CASE WHEN status='Accepted' THEN 1 ELSE 0 END) accepted,
SUM(CASE WHEN status='Pending' THEN 1 ELSE 0 END) pending,
SUM(CASE WHEN status='Rejected' THEN 1 ELSE 0 END) rejected
FROM applications WHERE supervisor_id=$supervisor_id";
$res=mysqli_query($conn,$q);
if($res) $stats=array_map('intval',mysqli_fetch_assoc($res));

// Applications
$appSql="SELECT a.id,a.student_id,a.company_name,a.position,a.status,
s.name student_name
FROM applications a
JOIN students s ON a.student_id=s.id
WHERE a.supervisor_id=$supervisor_id
ORDER BY a.applied_at DESC";
$appRes=mysqli_query($conn,$appSql);

// Announcements
$annSql="SELECT * FROM supervisor_announcements WHERE supervisor_id=$supervisor_id ORDER BY created_at DESC";
$annRes=mysqli_query($conn,$annSql);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Supervisor Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body{font-family:sans-serif;background:#f0f4ff;margin:0;}
.topbar{display:flex;justify-content:space-between;align-items:center;padding:14px 20px;background:#003366;color:#fff;border-radius:10px;margin-bottom:20px;flex-wrap:wrap;}
.topbar-left{font-size:18px;font-weight:bold;}
.topbar-right{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:20px;}
.card-stats{background:#fff;padding:16px;border-radius:12px;box-shadow:0 2px 4px rgba(0,0,0,.05);text-align:center;}
.card-stats .value{font-size:22px;font-weight:bold;}
.table-responsive{overflow-x:auto;}
table{min-width:700px;border-collapse:collapse;}
th,td{padding:12px;border-bottom:1px solid #eee;text-align:left;font-size:14px;}
th{background:#e6f0ff;color:#003366;font-size:12px;text-transform:uppercase;}
.btn{padding:6px 10px;border:none;border-radius:6px;cursor:pointer;font-size:13px;}
.btn-view{background:#0059b3;color:#fff;}
.modal-content{max-height:80vh;overflow:auto;}
.card-mobile{background:#fff;padding:12px;margin-bottom:12px;border-radius:12px;box-shadow:0 2px 6px rgba(0,0,0,.1);}
.card-mobile h5{margin-bottom:8px;color:#003366;}
.card-mobile p{margin-bottom:4px;font-size:14px;}
</style>
<script>
function openReports(studentId){
  fetch("superview_reports.php?student_id="+studentId)
    .then(r=>r.text())
    .then(html=>{
      document.getElementById("reportBody").innerHTML=html;
      var modal = new bootstrap.Modal(document.getElementById('reportModal'));
      modal.show();
    });
}
</script>
</head>
<body>
<div class="container">
<div class="topbar">
  <div class="topbar-left">Welcome, <?=htmlspecialchars($supervisor_name)?></div>
  <div class="topbar-right">
    <div class="badge bg-light text-primary">Date: <?=date("Y-m-d")?></div>
    <a href="supervisor_change_password.php" class="btn btn-light btn-sm" title="Change Password">
      <i class="bi bi-person-circle"></i>
    </a>
    <form action="logout.php" method="post" style="margin:0">
      <button class="btn btn-danger btn-sm" type="submit">Logout</button>
    </form>
  </div>
</div>

<?php if($msg):?><div class="alert alert-info"><?=$msg?></div><?php endif;?>

<div class="cards">
  <div class="card-stats">Total<div class="value text-primary"><?=$stats['total']?></div></div>
  <div class="card-stats">Accepted<div class="value text-success"><?=$stats['accepted']?></div></div>
  <div class="card-stats">Pending<div class="value text-warning"><?=$stats['pending']?></div></div>
  <div class="card-stats">Rejected<div class="value text-danger"><?=$stats['rejected']?></div></div>
</div>

<!-- Applications Table -->
<div class="table-responsive d-none d-md-block mb-4">
<table class="table table-hover">
  <thead><tr><th>ID</th><th>Student</th><th>Company</th><th>Position</th><th>Status</th><th>Report</th></tr></thead>
  <tbody>
  <?php while($row=mysqli_fetch_assoc($appRes)):?>
    <tr>
      <td><?=$row['id']?></td>
      <td><?=htmlspecialchars($row['student_name'])?></td>
      <td><?=htmlspecialchars($row['company_name'])?></td>
      <td><?=htmlspecialchars($row['position'])?></td>
      <td><?=$row['status']?></td>
      <td><button class="btn btn-view btn-sm" onclick="openReports(<?=$row['student_id']?>)">View Reports</button></td>
    </tr>
  <?php endwhile;?>
  </tbody>
</table>
</div>

<!-- Mobile Cards -->
<?php mysqli_data_seek($appRes,0); while($row=mysqli_fetch_assoc($appRes)):?>
<div class="card-mobile d-md-none">
  <h5><?=htmlspecialchars($row['student_name'])?></h5>
  <p><strong>ID:</strong> <?=$row['id']?></p>
  <p><strong>Company:</strong> <?=htmlspecialchars($row['company_name'])?></p>
  <p><strong>Position:</strong> <?=htmlspecialchars($row['position'])?></p>
  <p><strong>Status:</strong> <?=$row['status']?></p>
  <button class="btn btn-view btn-sm" onclick="openReports(<?=$row['student_id']?>)">View Reports</button>
</div>
<?php endwhile;?>

<!-- Announcements -->
<div class="card mb-4">
  <div class="card-body">
    <h5>📢 Post Announcement</h5>
    <form method="post">
      <textarea name="announcement" class="form-control mb-2" rows="3" placeholder="Write announcement..."></textarea>
      <button type="submit" name="submit_announcement" class="btn btn-primary">Post</button>
    </form>
  </div>
</div>

<div class="card mb-4">
  <div class="card-body">
    <h5>📌 My Announcements</h5>
    <?php if($annRes && mysqli_num_rows($annRes)>0): ?>
      <ul class="list-group">
      <?php while($a=mysqli_fetch_assoc($annRes)): ?>
        <li class="list-group-item">
          <?=nl2br(htmlspecialchars($a['message']))?>
          <div class="text-muted small"><?= $a['created_at']?></div>
        </li>
      <?php endwhile;?>
      </ul>
    <?php else: ?>
      <p class="text-muted">No announcements yet.</p>
    <?php endif;?>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content p-3">
      <div class="modal-header">
        <h5 class="modal-title">Student Reports</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div id="reportBody">Loading...</div>
    </div>
  </div>
</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
