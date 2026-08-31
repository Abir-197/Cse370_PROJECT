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
if(isset($_POST["add_course"])){
$code=$_POST["coursecode"];$name=$_POST["name"];$desc=$_POST["description"];$credit=$_POST["credit"];$mark=$_POST["total_mark"];
$sql="INSERT INTO course(coursecode,name,description,credit,total_mark,admin) VALUES('$code','$name','$desc','$credit','$mark','$adminID')";
$message=mysqli_query($conn,$sql)?"Course added successfully.":"Course could not be added: ".mysqli_error($conn);
}
if(isset($_GET["delete"])){
$code=$_GET["delete"];mysqli_query($conn,"DELETE FROM course WHERE coursecode='$code' AND admin='$adminID'");header("Location: allcourse.php");exit();
}
$search=isset($_GET["q"])?$_GET["q"]:"";
$sql="SELECT * FROM course WHERE coursecode LIKE '%$search%' OR name LIKE '%$search%' ORDER BY coursecode";
$result=mysqli_query($conn,$sql);
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>All Courses</title><link rel="stylesheet" href="admin_pages.css"></head><body>
<div class="admin-layout"><aside class="sidebar"><div class="logo">Study<span>verse</span></div><nav class="nav"><a href="admin_dashboard.php">Dashboard</a><a class="active" href="allcourse.php">All Courses</a><a href="allrepository.php">Repository</a><a href="checkAllUser.php">All Users</a><a href="manage_reports.php">Reports</a><a href="logout.php" class="logout">Logout</a></nav></aside><main class="main"><div class="top"><div><h1>See All Course Info</h1><p class="muted">Course management for AdminID <?php echo e($adminID); ?></p></div></div>
<?php if($message!=""){?><div class="alert ok"><?php echo e($message);?></div><?php }?>
<section class="panel"><h2>Add Course</h2><form method="POST"><div class="form-grid"><div class="field"><label>Course Code</label><input name="coursecode" maxlength="10" required></div><div class="field"><label>Course Name</label><input name="name" required></div><div class="field"><label>Credit</label><input type="number" step="0.01" name="credit" required></div><div class="field"><label>Total Mark</label><input type="number" name="total_mark" required></div></div><div class="field"><label>Description</label><textarea name="description" required></textarea></div><button class="btn" name="add_course">Add Course</button></form></section>
<section class="panel"><form class="search"><input name="q" value="<?php echo e($search);?>" placeholder="Search course"><button class="btn">Search</button></form><table><tr><th>Code</th><th>Name</th><th>Description</th><th>Credit</th><th>Mark</th><th>Admin</th><th>Action</th></tr><?php while($r=mysqli_fetch_assoc($result)){?><tr><td><?php echo e($r["coursecode"]);?></td><td><?php echo e($r["name"]);?></td><td><?php echo e($r["description"]);?></td><td><?php echo e($r["credit"]);?></td><td><?php echo e($r["total_mark"]);?></td><td><?php echo e($r["admin"]);?></td><td class="actions-inline"><a class="btn" href="edit_course.php?coursecode=<?php echo urlencode($r["coursecode"]);?>">Update</a><a class="btn red" href="allcourse.php?delete=<?php echo urlencode($r["coursecode"]);?>">Delete</a></td></tr><?php }?></table></section></main></div></body></html>