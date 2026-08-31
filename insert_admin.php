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
<?php $message="";if(isset($_POST["add"])){ $id=trim($_POST["AdminID"]);$name=$_POST["name"];$email=$_POST["email"];$phone=$_POST["phone"];$pass=$_POST["password"];
if(strlen($id)>8||strtoupper(substr($id,0,1))!="A"){$message="AdminID must start with A and be maximum 8 characters.";}else{$sql="INSERT INTO admin_info(AdminID,name,email,phone,password) VALUES('$id','$name','$email','$phone','$pass')";$message=mysqli_query($conn,$sql)?"Admin added successfully.":"Could not add admin: ".mysqli_error($conn);}}
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Insert Admin</title><link rel="stylesheet" href="admin_pages.css"></head><body><div class="admin-layout"><aside class="sidebar"><div class="logo">Study<span>verse</span></div><nav class="nav"><a href="admin_dashboard.php">Dashboard</a><a href="checkAllUser.php">Users</a><a class="active" href="insert_admin.php">Insert Admin</a><a href="logout.php" class="logout">Logout</a></nav></aside><main class="main"><div class="top"><div><h1>Insert Admin</h1></div></div><section class="panel"><?php if($message!=""){?><div class="alert ok"><?php echo e($message);?></div><?php }?><form method="POST"><div class="form-grid"><div class="field"><label>AdminID</label><input name="AdminID" maxlength="8" required></div><div class="field"><label>Name</label><input name="name" required></div><div class="field"><label>Email</label><input name="email" required></div><div class="field"><label>Phone</label><input name="phone" required></div><div class="field"><label>Password</label><input type="password" name="password" required></div></div><button class="btn" name="add">Add Admin</button></form></section></main></div></body></html>