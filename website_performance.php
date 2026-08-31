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
function ct($c,$t){$r=mysqli_query($c,"SELECT COUNT(*) AS n FROM $t");return $r?mysqli_fetch_assoc($r)["n"]:0;}
$users=ct($conn,"user_info");$admins=ct($conn,"admin_info");$students=ct($conn,"student_info");$faculty=ct($conn,"faculty_info");$courses=ct($conn,"course");$repos=ct($conn,"repository");$groups=ct($conn,"group_info");$resources=ct($conn,"course_resouce");$reviews=ct($conn,"student_reviews_resources")+ct($conn,"student_review_course");
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Website Performance</title><link rel="stylesheet" href="admin_pages.css"></head><body><div class="admin-layout"><aside class="sidebar"><div class="logo">Study<span>verse</span></div><nav class="nav"><a href="admin_dashboard.php">Dashboard</a><a class="active" href="website_performance.php">Performance</a><a href="logout.php" class="logout">Logout</a></nav></aside><main class="main"><div class="top"><div><h1>Website Performance</h1><p class="muted">Basic database-backed platform statistics.</p></div></div><section class="stats"><div class="stat"><small>Users</small><strong><?php echo $users;?></strong></div><div class="stat"><small>Admins</small><strong><?php echo $admins;?></strong></div><div class="stat"><small>Students</small><strong><?php echo $students;?></strong></div><div class="stat"><small>Faculty</small><strong><?php echo $faculty;?></strong></div></section><section class="stats"><div class="stat"><small>Courses</small><strong><?php echo $courses;?></strong></div><div class="stat"><small>Repositories</small><strong><?php echo $repos;?></strong></div><div class="stat"><small>Study Groups</small><strong><?php echo $groups;?></strong></div><div class="stat"><small>Reviews</small><strong><?php echo $reviews;?></strong></div></section><section class="panel"><h2>Resource Records</h2><p class="muted">Course resources stored: <?php echo $resources;?></p></section></main></div></body></html>