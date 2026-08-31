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
$message="";
if(isset($_POST["update"])){
$name=$_POST["name"];$email=$_POST["email"];$phone=$_POST["phone"];
mysqli_query($conn,"UPDATE admin_info SET name='$name',email='$email',phone='$phone' WHERE AdminID='$adminID'");
$message="Profile updated successfully.";
$admin=mysqli_fetch_assoc(mysqli_query($conn,"SELECT AdminID,name,email,phone FROM admin_info WHERE AdminID='$adminID'"));
}
if(isset($_POST["delete"])){
mysqli_query($conn,"DELETE FROM admin_info WHERE AdminID='$adminID'");
session_destroy();header("Location: admin_login.php");exit();
}
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Admin Profile</title><link rel="stylesheet" href="admin_pages.css"></head><body><div class="admin-layout"><aside class="sidebar"><div class="logo">Study<span>verse</span></div><nav class="nav"><a href="admin_dashboard.php">Dashboard</a><a class="active" href="admin_profile.php">My Profile</a><a href="logout.php" class="logout">Logout</a></nav></aside><main class="main"><div class="top"><div><h1>Admin Profile</h1></div></div><section class="panel"><?php if($message!=""){?><div class="alert ok"><?php echo e($message);?></div><?php }?><form method="POST"><div class="form-grid"><div class="field"><label>AdminID</label><input value="<?php echo e($admin["AdminID"]);?>" disabled></div><div class="field"><label>Name</label><input name="name" value="<?php echo e($admin["name"]);?>" required></div><div class="field"><label>Email</label><input name="email" value="<?php echo e($admin["email"]);?>" required></div><div class="field"><label>Phone</label><input name="phone" value="<?php echo e($admin["phone"]);?>" required></div></div><button class="btn" name="update">Update Profile</button><button class="btn red" name="delete">Delete Profile</button></form></section></main></div></body></html>