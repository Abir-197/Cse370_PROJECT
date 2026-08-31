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
if(isset($_POST["add"])){ $title=$_POST["title"];$desc=$_POST["description"];$date=$_POST["due_date"];mysqli_query($conn,"INSERT INTO admin_work_due(title,description,due_date,assignedAdminID) VALUES('$title','$desc','$date','$adminID')");$message="Work item added."; }
if(isset($_POST["done"])){ $id=$_POST["workID"];mysqli_query($conn,"UPDATE admin_work_due SET status='DONE' WHERE workID='$id' AND assignedAdminID='$adminID'"); }
if(isset($_GET["delete"])){ $id=$_GET["delete"];mysqli_query($conn,"DELETE FROM admin_work_due WHERE workID='$id' AND assignedAdminID='$adminID'");header("Location: work_due.php");exit();}
$r=mysqli_query($conn,"SELECT * FROM admin_work_due WHERE assignedAdminID='$adminID' ORDER BY status ASC,due_date ASC");
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Work Due</title><link rel="stylesheet" href="admin_pages.css"></head><body><div class="admin-layout"><aside class="sidebar"><div class="logo">Study<span>verse</span></div><nav class="nav"><a href="admin_dashboard.php">Dashboard</a><a class="active" href="work_due.php">Work Due</a><a href="manage_reports.php">Reports</a><a href="logout.php" class="logout">Logout</a></nav></aside><main class="main"><div class="top"><div><h1>Work Due</h1><p class="muted">Create and complete administrative work items.</p></div></div><?php if($message!=""){?><div class="alert ok"><?php echo e($message);?></div><?php }?>
<section class="panel"><h2>Add Work</h2><form method="POST"><div class="form-grid"><div class="field"><label>Title</label><input name="title" required></div><div class="field"><label>Due Date</label><input type="date" name="due_date" required></div></div><div class="field"><label>Description</label><textarea name="description" required></textarea></div><button class="btn" name="add">Add Work</button></form></section>
<section class="panel"><table><tr><th>Title</th><th>Description</th><th>Due Date</th><th>Status</th><th>Action</th></tr><?php while($x=mysqli_fetch_assoc($r)){?><tr><td><?php echo e($x["title"]);?></td><td><?php echo e($x["description"]);?></td><td><?php echo e($x["due_date"]);?></td><td><?php echo e($x["status"]);?></td><td><?php if($x["status"]=="PENDING"){?><form method="POST" style="display:inline"><input type="hidden" name="workID" value="<?php echo e($x["workID"]);?>"><button class="btn green" name="done">Done</button></form><?php }?> <a class="btn red" href="work_due.php?delete=<?php echo e($x["workID"]);?>">Delete</a></td></tr><?php }?></table></section></main></div></body></html>