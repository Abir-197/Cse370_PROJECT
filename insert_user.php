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
if(isset($_POST["add"])){
$id=$_POST["userID"];$name=$_POST["name"];$email=$_POST["email"];$pass=$_POST["password"];$road=$_POST["road"];$area=$_POST["area"];$role=$_POST["role"];
$sql="INSERT INTO user_info(userID,name,email,password,road,area,AdminID) VALUES('$id','$name','$email','$pass','$road','$area','$adminID')";
if(mysqli_query($conn,$sql)){
if($role=="student"){mysqli_query($conn,"INSERT INTO student_info(userID) VALUES('$id')");}
if($role=="faculty"){mysqli_query($conn,"INSERT INTO faculty_info(userID) VALUES('$id')");}
$message="User added successfully.";
}else{$message="Could not add user: ".mysqli_error($conn);}
}
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Insert User</title><link rel="stylesheet" href="admin_pages.css"></head><body><div class="admin-layout"><aside class="sidebar"><div class="logo">Study<span>verse</span></div><nav class="nav"><a href="admin_dashboard.php">Dashboard</a><a href="checkAllUser.php">Users</a><a class="active" href="insert_user.php">Insert User</a><a href="logout.php" class="logout">Logout</a></nav></aside><main class="main"><div class="top"><div><h1>Insert User</h1><p class="muted">New user is assigned to AdminID <?php echo e($adminID);?></p></div></div><section class="panel"><?php if($message!=""){?><div class="alert ok"><?php echo e($message);?></div><?php }?><form method="POST"><div class="form-grid"><div class="field"><label>UserID</label><input name="userID" maxlength="8" required></div><div class="field"><label>Name</label><input name="name" required></div><div class="field"><label>Email</label><input name="email" required></div><div class="field"><label>Password</label><input type="password" name="password" required></div><div class="field"><label>Road</label><input name="road" required></div><div class="field"><label>Area</label><input name="area" required></div><div class="field"><label>Role</label><select name="role"><option value="student">Student</option><option value="faculty">Faculty</option><option value="user">General User</option></select></div></div><button class="btn" name="add">Add User</button></form></section></main></div></body></html>