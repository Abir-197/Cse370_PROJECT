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
$admins=mysqli_query($conn,"SELECT AdminID,name,email,phone FROM admin_info ORDER BY AdminID");
$users=mysqli_query($conn,"SELECT u.userID,u.name,u.email,u.AdminID,s.SID,s.semester,f.FID,f.Initial,f.Dept FROM user_info u LEFT JOIN student_info s ON u.userID=s.userID LEFT JOIN faculty_info f ON u.userID=f.userID ORDER BY u.userID");
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>All Users</title><link rel="stylesheet" href="admin_pages.css"></head><body><div class="admin-layout"><aside class="sidebar"><div class="logo">Study<span>verse</span></div><nav class="nav"><a href="admin_dashboard.php">Dashboard</a><a class="active" href="checkAllUser.php">All Users</a><a href="insert_user.php">Insert User</a><a href="insert_admin.php">Insert Admin</a><a href="logout.php" class="logout">Logout</a></nav></aside><main class="main"><div class="top"><div><h1>See All User</h1><p class="muted">Admins, students and faculty.</p></div></div><section class="panel"><h2>All Admin</h2><table><tr><th>AdminID</th><th>Name</th><th>Email</th><th>Phone</th><th>Action</th></tr><?php while($a=mysqli_fetch_assoc($admins)){?><tr><td><?php echo e($a["AdminID"]);?></td><td><?php echo e($a["name"]);?></td><td><?php echo e($a["email"]);?></td><td><?php echo e($a["phone"]);?></td><td><a class="btn" href="view_profile.php?type=admin&id=<?php echo urlencode($a["AdminID"]);?>">View Profile</a></td></tr><?php }?></table></section>
<section class="panel"><h2>All Student / Faculty / User</h2><table><tr><th>UserID</th><th>Name</th><th>Email</th><th>Role</th><th>AdminID</th><th>Action</th></tr><?php while($u=mysqli_fetch_assoc($users)){ $role=!empty($u["FID"])?"Faculty":(!empty($u["SID"])?"Student":"User");?><tr><td><?php echo e($u["userID"]);?></td><td><?php echo e($u["name"]);?></td><td><?php echo e($u["email"]);?></td><td><span class="badge"><?php echo $role;?></span></td><td><?php echo e($u["AdminID"]);?></td><td><a class="btn" href="view_profile.php?type=user&id=<?php echo urlencode($u["userID"]);?>">View Profile</a> <a class="btn" href="edit_user.php?id=<?php echo urlencode($u["userID"]);?>">Edit</a></td></tr><?php }?></table></section></main></div></body></html>