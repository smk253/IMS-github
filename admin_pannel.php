<?php
session_start();
require_once 'db.php';

// --- Admin Login Check ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// --- Function to safely get count ---
function get_count($conn, $table){
    $res = $conn->query("SELECT COUNT(*) AS total FROM $table");
    if(!$res){
        die("Query Error on table $table: " . $conn->error);
    }
    $row = $res->fetch_assoc();
    return $row['total'] ?? 0;
}

// --- Fetch stats safely ---
$total_students = get_count($conn, 'students');
$total_reports = get_count($conn, 'daily_reports');
$total_applications = get_count($conn, 'applications');
$total_supervisors = get_count($conn, 'supervisors');

// --- Fetch new reports for notification badge ---
$new_reports = $conn->query("SELECT COUNT(*) AS total FROM daily_reports WHERE is_new=1");
$new_reports_count = $new_reports ? $new_reports->fetch_assoc()['total'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Dashboard - Intern Management System</title>
<style>
* { box-sizing: border-box; margin:0; padding:0; font-family: Arial,sans-serif; }
body { background: #f4f6f9; }

/* Navbar */
.navbar {
    background-color: #0047AB;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 20px;
    height: 60px;
    position: fixed;
    top:0; left:0; right:0;
    z-index:1000;
}
.navbar .brand { font-weight: bold; font-size: 18px; }
.navbar .logout-container { display:flex; align-items:center; position:relative; }
.navbar .logout-btn { background-color:#242423ff; border:none; color:white; padding:8px 15px; cursor:pointer; border-radius:4px; font-size:14px; margin-left:10px; position:relative;}
.navbar .logout-btn .badge { position:absolute; top:-5px; right:-5px; background:red; color:white; font-size:12px; font-weight:bold; border-radius:50%; width:18px; height:18px; display:flex; align-items:center; justify-content:center; }
.navbar .date { margin-left:10px; font-size:14px; font-weight:bold; }

/* Hamburger */
.hamburger { cursor:pointer; width:25px; height:20px; display:flex; flex-direction:column; justify-content:space-between; }
.hamburger div { height:3px; background:white; border-radius:2px; }

/* Sidebar */
.sidebar {
    position: fixed;
    top:60px;
    left:-250px;
    width:250px;
    height:100%;
    background-color:#00357a;
    color:white;
    padding-top:20px;
    transition: left 0.3s ease;
    overflow-y:auto;
    z-index:999;
}
.sidebar.active { left:0; }
.sidebar ul { list-style:none; padding:0; }
.sidebar ul li { padding:12px 20px 12px 40px; cursor:pointer; position:relative; display:flex; align-items:center; }
.sidebar ul li:hover { background-color:#0050e6; }
.sidebar ul li.has-submenu::after { content:"▼"; position:absolute; right:20px; font-size:12px; transform:rotate(0deg); transition: transform 0.3s ease;}
.sidebar ul li.has-submenu.active::after { transform:rotate(-180deg);}
.sidebar ul li .submenu { list-style:none; margin-top:4px; padding-left:0; display:none; position:absolute; top:100%; left:0; background-color:#0047AB; border-radius:4px; width:100%; box-shadow:0 4px 6px rgba(0,0,0,0.1); z-index:10;}
.sidebar ul li.active>.submenu { display:block; }
.sidebar ul li .submenu li { padding:8px 20px; font-size:14px; margin-bottom:4px; display:flex; align-items:center; }
.sidebar ul li .submenu li:hover { background-color:#0066ff; }

/* Content */
.content { margin-top:60px; padding:20px; margin-left:0; transition: margin-left 0.3s ease; max-width:1100px; margin-left:auto; margin-right:auto;}
.content.sidebar-active { margin-left:250px; }

/* Stats Cards */
h1 { font-size:24px; color:#003366; margin-bottom:20px; text-align:center;}
.stats-container { display:flex; flex-direction:column; gap:20px; }
.stats { display:flex; flex-wrap:wrap; gap:15px; justify-content:center; }
.card { flex:1 1 200px; max-width:250px; background:#fff; padding:20px; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.1); text-align:center; transition:transform 0.2s ease;}
.card h3 { color:#0077b6; margin-bottom:10px;}
.card p { font-size:22px; font-weight:bold; margin:0; color:#003366;}
@media(max-width:768px){.stats { flex-direction:column; align-items:center; }}
</style>
</head>
<body>

<nav class="navbar">
    <div style="display:flex; align-items:center; gap:10px;">
        <div class="hamburger" id="hamburger">
            <div></div><div></div><div></div>
        </div>
        <div class="brand">Intern Management System</div>
    </div>
    <div class="logout-container">
        <span class="date"><?= date("Y-m-d") ?></span>
        <form method="POST" action="logout.php" style="display:inline;">
            <button class="logout-btn" type="submit">
                Logout
                <?php if($new_reports_count > 0): ?>
                    <span class="badge"><?= $new_reports_count ?></span>
                <?php endif; ?>
            </button>
        </form>
    </div>
</nav>

<aside class="sidebar" id="sidebar">
    <ul>
        <li class="has-submenu" id="manage-supervisor-menu">Manage Supervisor
            <ul class="submenu">
                <li onclick="window.location.href='create_supervisor.php'">Add Supervisor</li>
                <li onclick="window.location.href='view_supervisor.php'">View Supervisor</li>
            </ul>
        </li>
        <li class="has-submenu" id="manage-student-menu">Manage Student
            <ul class="submenu">
                <li onclick="window.location.href='adminview_application_list.php'">View All Student</li>
                <li onclick="window.location.href='adminview_application_list.php'">Assign Supervisor</li>
                <li onclick="window.location.href='adminview_uploaded_report.php'">View Student Reports</li>
            </ul>
        </li>
        <li class="has-submenu" id="settings-menu">Settings
            <ul class="submenu">
                <li onclick="window.location.href='create_admin.php'">Create Admin</li>
                <li onclick="window.location.href='manage_admin.php'">Manage Admin</li>
                <li onclick="window.location.href='change_profile.php'">Change Profile</li>

            </ul>
        </li>
    </ul>
</aside>

<main class="content" id="content">
    <h1>Welcome to Admin Dashboard</h1>

    <!-- Stats Cards -->
    <div class="stats-container">
        <div class="stats">
            <div class="card">
                <h3>Total Students</h3>
                <p><?= $total_students ?></p>
            </div>
            <div class="card">
                <h3>Total Reports</h3>
                <p><?= $total_reports ?></p>
            </div>
            <div class="card">
                <h3>Total Applications</h3>
                <p><?= $total_applications ?></p>
            </div>
            <div class="card">
                <h3>Total Supervisors</h3>
                <p><?= $total_supervisors ?></p>
            </div>
        </div>
    </div>
</main>

<script>
const hamburger = document.getElementById('hamburger');
const sidebar = document.getElementById('sidebar');
const content = document.getElementById('content');
const manageStudentMenu = document.getElementById('manage-student-menu');
const manageSupervisorMenu = document.getElementById('manage-supervisor-menu');
const settingsMenu = document.getElementById('settings-menu');

hamburger.addEventListener('click', ()=>{
    sidebar.classList.toggle('active');
    content.classList.toggle('sidebar-active');
});
manageStudentMenu.addEventListener('click', ()=>{manageStudentMenu.classList.toggle('active');});
manageSupervisorMenu.addEventListener('click', ()=>{manageSupervisorMenu.classList.toggle('active');});
settingsMenu.addEventListener('click', ()=>{settingsMenu.classList.toggle('active');});
</script>

</body>
</html>
