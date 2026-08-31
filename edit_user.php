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
$id=$_GET["id"]??"";
$r=mysqli_query($conn,"SELECT * FROM user_info WHERE userID='$id' AND AdminID='$adminID'");
if(!$r||mysqli_num_rows($r)==0){header("Location: checkAllUser.php");exit();}$u=mysqli_fetch_assoc($r);$message="";
if(isset($_POST["update"])){
$name=$_POST["name"];$email=$_POST["email"];$road=$_POST["road"];$area=$_POST["area"];
$sql="UPDATE user_info SET name='$name',email='$email',road='$road',area='$area' WHERE userID='$id' AND AdminID='$adminID'";
$message=mysqli_query($conn,$sql)?"User updated successfully.":"Update failed: ".mysqli_error($conn);
$u=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM user_info WHERE userID='$id'"));
}
if(isset($_POST["delete"])){
mysqli_query($conn,"DELETE FROM user_info WHERE userID='$id' AND AdminID='$adminID'");
header("Location: checkAllUser.php");exit();
}
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Edit User</title><link rel="stylesheet" href="admin_pages.css"></head><body><div class="admin-layout"><aside class="sidebar"><div class="logo">Study<span>verse</span></div><nav class="nav"><a href="admin_dashboard.php">Dashboard</a><a href="checkAllUser.php" class="active">Users</a><a href="logout.php" class="logout">Logout</a></nav></aside><main class="main"><div class="top"><div><h1>Edit User</h1><p class="muted">Only users with your same AdminID can be changed.</p></div></div><section class="panel"><?php if($message!=""){?><div class="alert ok"><?php echo e($message);?></div><?php }?><form method="POST"><div class="form-grid"><div class="field"><label>UserID</label><input value="<?php echo e($u["userID"]);?>" disabled></div><div class="field"><label>AdminID</label><input value="<?php echo e($u["AdminID"]);?>" disabled></div><div class="field"><label>Name</label><input name="name" value="<?php echo e($u["name"]);?>" required></div><div class="field"><label>Email</label><input name="email" value="<?php echo e($u["email"]);?>" required></div><div class="field"><label>Road</label><input name="road" value="<?php echo e($u["road"]);?>" required></div><div class="field"><label>Area</label><input name="area" value="<?php echo e($u["area"]);?>" required></div></div><button class="btn" name="update">Update</button><button class="btn red" name="delete">Delete</button></form></section></main></div></body></html>