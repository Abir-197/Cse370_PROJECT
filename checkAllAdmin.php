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
<?php $r=mysqli_query($conn,"SELECT AdminID,name,email,phone FROM admin_info ORDER BY AdminID");?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>All Admins</title><link rel="stylesheet" href="admin_pages.css"></head><body><div class="admin-layout"><aside class="sidebar"><div class="logo">Study<span>verse</span></div><nav class="nav"><a href="admin_dashboard.php">Dashboard</a><a class="active" href="checkAllAdmin.php">Admins</a><a href="checkAllFaculty.php">Faculty</a><a href="checkAllStudent.php">Students</a><a href="logout.php" class="logout">Logout</a></nav></aside><main class="main"><div class="top"><div><h1>Check All Admin</h1></div></div><section class="panel"><table><tr><th>AdminID</th><th>Name</th><th>Email</th><th>Phone</th><th>Action</th></tr><?php while($x=mysqli_fetch_assoc($r)){?><tr><td><?php echo e($x["AdminID"]);?></td><td><?php echo e($x["name"]);?></td><td><?php echo e($x["email"]);?></td><td><?php echo e($x["phone"]);?></td><td><a class="btn" href="view_profile.php?type=admin&id=<?php echo urlencode($x["AdminID"]);?>">View Profile</a></td></tr><?php }?></table></section></main></div></body></html>