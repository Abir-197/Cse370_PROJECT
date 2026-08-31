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
$id=$_GET["repoID"]??"";$r=mysqli_query($conn,"SELECT * FROM repository WHERE repoID='$id' AND adminID='$adminID'");
if(!$r||mysqli_num_rows($r)==0){header("Location: allrepository.php");exit();}$repo=mysqli_fetch_assoc($r);$message="";
if(isset($_POST["update"])){ $name=$_POST["name"];$course=$_POST["coursecode"]; $sql="UPDATE repository SET name='$name',coursecode='$course' WHERE repoID='$id' AND adminID='$adminID'";$message=mysqli_query($conn,$sql)?"Repository updated successfully.":"Update failed: ".mysqli_error($conn);$repo=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM repository WHERE repoID='$id'"));}
$courses=mysqli_query($conn,"SELECT coursecode,name FROM course");
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Update Repository</title><link rel="stylesheet" href="admin_pages.css"></head><body><div class="admin-layout"><aside class="sidebar"><div class="logo">Study<span>verse</span></div><nav class="nav"><a href="admin_dashboard.php">Dashboard</a><a href="allrepository.php" class="active">Repository</a><a href="logout.php" class="logout">Logout</a></nav></aside><main class="main"><div class="top"><div><h1>Update Repository</h1></div></div><section class="panel"><?php if($message!=""){?><div class="alert ok"><?php echo e($message);?></div><?php }?><form method="POST"><div class="field"><label>Name</label><input name="name" value="<?php echo e($repo["name"]);?>" required></div><div class="field"><label>Course</label><select name="coursecode"><?php while($c=mysqli_fetch_assoc($courses)){?><option value="<?php echo e($c["coursecode"]);?>" <?php if($c["coursecode"]==$repo["coursecode"])echo"selected";?>><?php echo e($c["coursecode"]." - ".$c["name"]);?></option><?php }?></select></div><button class="btn" name="update">Update</button> <a class="btn gray" href="allrepository.php">Back</a></form></section></main></div></body></html>