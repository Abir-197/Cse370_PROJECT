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
$type=$_GET["type"]??"user";$id=$_GET["id"]??"";
if($type=="admin"){
$r=mysqli_query($conn,"SELECT AdminID AS id,name,email,phone FROM admin_info WHERE AdminID='$id'");
}else{
$r=mysqli_query($conn,"SELECT u.userID AS id,u.name,u.email,u.road,u.area,u.AdminID,s.SID,s.cgpa,s.semester,f.FID,f.Initial,f.Dept FROM user_info u LEFT JOIN student_info s ON u.userID=s.userID LEFT JOIN faculty_info f ON u.userID=f.userID WHERE u.userID='$id'");
}
if(!$r||mysqli_num_rows($r)==0){header("Location: checkAllUser.php");exit();}$p=mysqli_fetch_assoc($r);
$role=$type=="admin"?"Admin":(!empty($p["FID"])?"Faculty":(!empty($p["SID"])?"Student":"User"));
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>View Profile</title><link rel="stylesheet" href="admin_pages.css"></head><body><div class="admin-layout"><aside class="sidebar"><div class="logo">Study<span>verse</span></div><nav class="nav"><a href="admin_dashboard.php">Dashboard</a><a href="checkAllUser.php">All Users</a><a href="logout.php" class="logout">Logout</a></nav></aside><main class="main"><div class="top"><div><h1>View Profile</h1><p class="muted"><?php echo e($p["id"]);?> • <?php echo $role;?></p></div></div><section class="panel"><div class="profile-grid"><div class="profile-item"><small>ID</small><b><?php echo e($p["id"]);?></b></div><div class="profile-item"><small>Role</small><b><?php echo e($role);?></b></div><div class="profile-item"><small>Name</small><b><?php echo e($p["name"]);?></b></div><div class="profile-item"><small>Email</small><b><?php echo e($p["email"]);?></b></div><?php if($type=="admin"){?><div class="profile-item"><small>Phone</small><b><?php echo e($p["phone"]);?></b></div><?php }else{?><div class="profile-item"><small>Road</small><b><?php echo e($p["road"]);?></b></div><div class="profile-item"><small>Area</small><b><?php echo e($p["area"]);?></b></div><div class="profile-item"><small>AdminID</small><b><?php echo e($p["AdminID"]);?></b></div><?php if($role=="Student"){?><div class="profile-item"><small>SID</small><b><?php echo e($p["SID"]);?></b></div><div class="profile-item"><small>CGPA</small><b><?php echo e($p["cgpa"]);?></b></div><div class="profile-item"><small>Semester</small><b><?php echo e($p["semester"]);?></b></div><?php }if($role=="Faculty"){?><div class="profile-item"><small>FID</small><b><?php echo e($p["FID"]);?></b></div><div class="profile-item"><small>Initial</small><b><?php echo e($p["Initial"]);?></b></div><div class="profile-item"><small>Department</small><b><?php echo e($p["Dept"]);?></b></div><?php }}?></div><br><?php if($type=="admin"){?><a class="btn" href="edit_admin.php?id=<?php echo urlencode($p["id"]);?>">Edit Admin</a><?php }else{?><a class="btn" href="edit_user.php?id=<?php echo urlencode($p["id"]);?>">Edit User</a><?php }?> <a class="btn gray" href="checkAllUser.php">Back</a></section></main></div></body></html>