<?php
session_start();
include "admin_db.php";

if (!isset($_SESSION["AdminID"])) {
    header("Location: admin_login.php");
    exit();
}

$adminID = $_SESSION["AdminID"];

if (strlen($adminID) > 8 || strtoupper(substr($adminID, 0, 1)) != "A") {
    session_destroy();
    header("Location: admin_login.php");
    exit();
}

$adminQuery = "SELECT AdminID, name, email, phone FROM admin_info WHERE AdminID='$adminID'";
$adminResult = mysqli_query($conn, $adminQuery);

if (!$adminResult || mysqli_num_rows($adminResult) == 0) {
    session_destroy();
    header("Location: admin_login.php");
    exit();
}

$admin = mysqli_fetch_assoc($adminResult);

function e($value) {
    return htmlspecialchars($value ?? "", ENT_QUOTES, "UTF-8");
}
?>
<?php
function countTable($conn,$table){
    $r=mysqli_query($conn,"SELECT COUNT(*) AS total FROM $table");
    if(!$r){return 0;}
    $x=mysqli_fetch_assoc($r);
    return $x["total"];
}
$totalCourses=countTable($conn,"course");
$totalRepo=countTable($conn,"repository");
$totalUsers=countTable($conn,"user_info");
$totalAdmins=countTable($conn,"admin_info");
$totalStudents=countTable($conn,"student_info");
$totalFaculty=countTable($conn,"faculty_info");
$workR=mysqli_query($conn,"SELECT COUNT(*) AS total FROM admin_work_due WHERE assignedAdminID='$adminID' AND status='PENDING'");
$workDue=$workR?mysqli_fetch_assoc($workR)["total"]:0;
$reportR=mysqli_query($conn,"SELECT COUNT(*) AS total FROM admin_reports WHERE status='PENDING'");
$reports=$reportR?mysqli_fetch_assoc($reportR)["total"]:0;
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Dashboard</title><link rel="stylesheet" href="admin_dash.css"></head>
<body><div class="admin-layout"><aside class="sidebar"><div class="logo">Study<span>verse</span><br><small style="font-size:12px;color:#94a3b8">ADMIN PANEL</small></div>
<nav class="nav"><a class="active" href="admin_dashboard.php">Dashboard</a><a href="allcourse.php">All Courses</a><a href="allrepository.php">All Repository</a><a href="checkAllUser.php">All Users</a><a href="manage_reports.php">Manage Reports</a><a href="admin_profile.php">My Profile</a><a href="admin_response.php">Admin Responses</a><a href="work_due.php">Work Due</a><a href="website_performance.php">Performance</a><a class="logout" href="logout.php">Logout</a></nav></aside>
<main class="main"><div class="top"><div><h1>Welcome back, <?php echo e($admin["name"]); ?> 👋</h1><p class="muted">Manage courses, repository, users, reports and admin activities.</p></div><div class="admin-chip">AdminID: <b><?php echo e($adminID); ?></b></div></div>
<section class="stats"><div class="stat"><small>All Courses</small><strong><?php echo $totalCourses;?></strong></div><div class="stat"><small>All Repository</small><strong><?php echo $totalRepo;?></strong></div><div class="stat"><small>All Users</small><strong><?php echo $totalUsers;?></strong></div><div class="stat"><small>Work Due</small><strong><?php echo $workDue;?></strong></div></section>
<section class="actions">
<a class="action" href="allcourse.php"><div class="icon">📚</div><h3>See All Course Info</h3><p>View, insert, update and delete courses.</p><span class="go">Open →</span></a>
<a class="action" href="allrepository.php"><div class="icon">🗂️</div><h3>See All Repository</h3><p>View and manage repository records and resources.</p><span class="go">Open →</span></a>
<a class="action" href="admin_profile.php"><div class="icon">👤</div><h3>View Profile</h3><p>Update or delete your own admin profile.</p><span class="go">Open →</span></a>
<a class="action" href="work_due.php"><div class="icon">📝</div><h3>Work Due</h3><p><?php echo $workDue;?> pending work item(s).</p><span class="go">Open →</span></a>
<a class="action" href="checkAllUser.php"><div class="icon">👥</div><h3>See All User</h3><p>Check admins, faculty and students.</p><span class="go">Open →</span></a>
<a class="action" href="admin_response.php"><div class="icon">💬</div><h3>Response Other Admins</h3><p>Send and read admin-to-admin responses.</p><span class="go">Open →</span></a>
<a class="action" href="manage_reports.php"><div class="icon">🚩</div><h3>Manage Reports</h3><p><?php echo $reports;?> pending report(s).</p><span class="go">Open →</span></a>
<a class="action" href="website_performance.php"><div class="icon">📊</div><h3>Website Performance</h3><p>See database-level website statistics.</p><span class="go">Open →</span></a>
</section>
<section class="panel" style="margin-top:22px"><h2>System Snapshot</h2><div class="stats" style="margin:0"><div class="stat"><small>Admins</small><strong><?php echo $totalAdmins;?></strong></div><div class="stat"><small>Students</small><strong><?php echo $totalStudents;?></strong></div><div class="stat"><small>Faculty</small><strong><?php echo $totalFaculty;?></strong></div><div class="stat"><small>Pending Reports</small><strong><?php echo $reports;?></strong></div></div></section>
</main></div></body></html>