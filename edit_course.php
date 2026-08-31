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
$code=$_GET["coursecode"]??"";$r=mysqli_query($conn,"SELECT * FROM course WHERE coursecode='$code' AND admin='$adminID'");
if(!$r||mysqli_num_rows($r)==0){header("Location: allcourse.php");exit();}$course=mysqli_fetch_assoc($r);$message="";
if(isset($_POST["update"])){
$name=$_POST["name"];$desc=$_POST["description"];$credit=$_POST["credit"];$mark=$_POST["total_mark"];
$sql="UPDATE course SET name='$name',description='$desc',credit='$credit',total_mark='$mark' WHERE coursecode='$code' AND admin='$adminID'";
$message=mysqli_query($conn,$sql)?"Course updated successfully.":"Update failed: ".mysqli_error($conn);
$course=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM course WHERE coursecode='$code'"));
}
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Update Course</title><link rel="stylesheet" href="admin_pages.css"></head><body><div class="admin-layout"><aside class="sidebar"><div class="logo">Study<span>verse</span></div><nav class="nav"><a href="admin_dashboard.php">Dashboard</a><a class="active" href="allcourse.php">Courses</a><a href="logout.php" class="logout">Logout</a></nav></aside><main class="main"><div class="top"><div><h1>Update Course</h1><p class="muted"><?php echo e($code);?></p></div></div><section class="panel"><?php if($message!=""){?><div class="alert ok"><?php echo e($message);?></div><?php }?><form method="POST"><div class="field"><label>Name</label><input name="name" value="<?php echo e($course["name"]);?>" required></div><div class="field"><label>Description</label><textarea name="description" required><?php echo e($course["description"]);?></textarea></div><div class="form-grid"><div class="field"><label>Credit</label><input name="credit" value="<?php echo e($course["credit"]);?>" required></div><div class="field"><label>Total Mark</label><input name="total_mark" value="<?php echo e($course["total_mark"]);?>" required></div></div><button class="btn" name="update">Update</button> <a class="btn gray" href="allcourse.php">Back</a></form></section></main></div></body></html>