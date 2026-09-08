<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'admin_guard.php'; 
require_once 'admin_db.php';

if (!function_exists('countRows')) {
    function countRows($conn, $sql) {
        if (!$conn) return 0;
        $r = mysqli_query($conn, $sql);
        if ($r && ($x = mysqli_fetch_assoc($r))) {
            return $x['total'] ?? 0;
        }
        return 0;
    }
}

$admin_id = (isset($_SESSION['AdminID']) && $conn) ? mysqli_real_escape_string($conn, $_SESSION['AdminID']) : '';

$c    = countRows($conn, 'SELECT COUNT(*) total FROM course'); 
$r    = countRows($conn, 'SELECT COUNT(*) total FROM repository'); 
$u    = countRows($conn, 'SELECT COUNT(*) total FROM user_info'); 
$f    = countRows($conn, 'SELECT COUNT(*) total FROM faculty_info'); 
$rep  = countRows($conn, "SELECT COUNT(*) total FROM content_reports WHERE status='PENDING'");
$pass = countRows($conn, "SELECT COUNT(*) total FROM user_info WHERE password_status='PENDING' AND AdminID='$admin_id'");
?>
<!doctype html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin_dash.css">
</head>
<body>
<div class="shell">
    <aside>
        <h2>StudyVerse</h2>
        <p>Admin Panel</p>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="allcourse.php">See All Course Info</a>
        <a href="allrepository.php">See All Repository</a>
        <a href="admin_profile.php">View Profile</a>
        <a href="work_due.php">Work Due</a>
        <a href="checkAllUser.php">See All User</a>
        <a href="admin_response.php">Response Other Admins</a>
        <a href="manage_reports.php">Manage Reports</a>
        <a href="website_performance.php">Website Performance</a>
        <a href="group_admin.php">Manage Groups</a>
        <a href="adminapprove.php">Approve Passwords</a>
        <a href="logout.php">Logout</a>
    </aside>
    <main>
        <h1>Welcome, <?= htmlspecialchars($_SESSION['AdminName'] ?? 'Admin') ?></h1>
        <div class="cards">
            <?php 
            $cards = [
                ['SeeAllcourseInfo', 'allcourse.php', $c, 'Course Catalog'],
                ['See Allrepository', 'allrepository.php', $r, 'Repositories'],
                ['View profile', 'admin_profile.php', '👤', 'Your Profile'],
                ['WorkDue', 'work_due.php', '!', 'Pending Work'],
                ['See AllUser', 'checkAllUser.php', $u, 'Users'],
                ['Response other admins', 'admin_response.php', '✉', 'Messages'],
                ['Manage reports', 'manage_reports.php', $rep, 'Pending Reports'],
                ['Website performance', 'website_performance.php', '↗', 'Analytics'],
                ['adminapprovepass', 'adminapprovepass.php', $pass, 'Password Requests']
            ]; 
            foreach ($cards as $x): ?>
                <a class="card" href="<?= $x[1] ?>">
                    <span class="icon"><?= $x[2] ?></span>
                    <h3><?= $x[0] ?></h3>
                    <small><?= $x[3] ?></small>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="quick">
            <a href="insert_course.php">＋ Add Course</a>
            <a href="insert_repository.php">＋ Add Repository</a>
            <a href="insert_admin.php">＋ Add Admin</a>
            <a href="insert_user.php">＋ Add User</a>
        </div>
    </main>
</div>
</body>
</html>