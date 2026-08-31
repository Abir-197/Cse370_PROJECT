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
if(isset($_POST["add_repo"])){
$id=$_POST["repoID"];$course=$_POST["coursecode"];$name=$_POST["name"];
$sql="INSERT INTO repository(repoID,coursecode,name,adminID) VALUES('$id','$course','$name','$adminID')";
$message=mysqli_query($conn,$sql)?"Repository added successfully.":"Could not add repository: ".mysqli_error($conn);
}
if(isset($_GET["delete"])){ $id=$_GET["delete"];mysqli_query($conn,"DELETE FROM repository WHERE repoID='$id' AND adminID='$adminID'");header("Location: allrepository.php");exit();}
$courses=mysqli_query($conn,"SELECT coursecode,name FROM course ORDER BY coursecode");
$repos=mysqli_query($conn,"SELECT r.*,c.name AS courseName FROM repository r LEFT JOIN course c ON r.coursecode=c.coursecode ORDER BY r.repoID");
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>All Repository</title><link rel="stylesheet" href="admin_pages.css"></head><body><div class="admin-layout"><aside class="sidebar"><div class="logo">Study<span>verse</span></div><nav class="nav"><a href="admin_dashboard.php">Dashboard</a><a href="allcourse.php">Courses</a><a class="active" href="allrepository.php">Repository</a><a href="checkAllUser.php">Users</a><a href="manage_reports.php">Reports</a><a href="logout.php" class="logout">Logout</a></nav></aside><main class="main"><div class="top"><div><h1>See All Repository</h1><p class="muted">Repository management</p></div></div><?php if($message!=""){?><div class="alert ok"><?php echo e($message);?></div><?php }?>
<section class="panel"><h2>Add Repository</h2><form method="POST"><div class="form-grid"><div class="field"><label>Repository ID</label><input name="repoID" maxlength="10" required></div><div class="field"><label>Name</label><input name="name" required></div><div class="field"><label>Course</label><select name="coursecode" required><?php while($c=mysqli_fetch_assoc($courses)){?><option value="<?php echo e($c["coursecode"]);?>"><?php echo e($c["coursecode"]." - ".$c["name"]);?></option><?php }?></select></div></div><button class="btn" name="add_repo">Add Repository</button></form></section>
<section class="panel"><table><tr><th>Repo ID</th><th>Course</th><th>Name</th><th>Admin</th><th>Action</th></tr><?php while($r=mysqli_fetch_assoc($repos)){?><tr><td><?php echo e($r["repoID"]);?></td><td><?php echo e($r["coursecode"]." - ".$r["courseName"]);?></td><td><?php echo e($r["name"]);?></td><td><?php echo e($r["adminID"]);?></td><td class="actions-inline"><a class="btn" href="edit_repository.php?repoID=<?php echo urlencode($r["repoID"]);?>">Update</a><a class="btn red" href="allrepository.php?delete=<?php echo urlencode($r["repoID"]);?>">Delete</a></td></tr><?php }?></table></section></main></div></body></html>