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
if(isset($_POST["resolve"])){ $rid=$_POST["reportID"];mysqli_query($conn,"UPDATE admin_reports SET status='RESOLVED',handledBy='$adminID',handled_at=NOW() WHERE reportID='$rid'");$message="Report marked as resolved."; }
if(isset($_POST["delete_target"])){
$rid=$_POST["reportID"];$type=$_POST["targetType"];$tid=$_POST["targetID"];
if($type=="USER"){mysqli_query($conn,"DELETE FROM user_info WHERE userID='$tid'");}
elseif($type=="GROUP"){mysqli_query($conn,"DELETE FROM group_info WHERE groupID='$tid' AND adminID='$adminID'");}
elseif($type=="COURSE"){mysqli_query($conn,"DELETE FROM course WHERE coursecode='$tid' AND admin='$adminID'");}
elseif($type=="REPOSITORY"){mysqli_query($conn,"DELETE FROM repository WHERE repoID='$tid' AND adminID='$adminID'");}
mysqli_query($conn,"UPDATE admin_reports SET status='RESOLVED',handledBy='$adminID',handled_at=NOW() WHERE reportID='$rid'");
$message="Reported target action completed and report resolved.";
}
$reports=mysqli_query($conn,"SELECT * FROM admin_reports ORDER BY status ASC,reported_at DESC");
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Manage Reports</title><link rel="stylesheet" href="admin_pages.css"></head><body><div class="admin-layout"><aside class="sidebar"><div class="logo">Study<span>verse</span></div><nav class="nav"><a href="admin_dashboard.php">Dashboard</a><a class="active" href="manage_reports.php">Manage Reports</a><a href="checkAllUser.php">Users</a><a href="allcourse.php">Courses</a><a href="allrepository.php">Repository</a><a href="logout.php" class="logout">Logout</a></nav></aside><main class="main"><div class="top"><div><h1>Manage Reports</h1><p class="muted">Reports can target users, groups, courses or repository content.</p></div></div><?php if($message!=""){?><div class="alert ok"><?php echo e($message);?></div><?php }?>
<section class="panel"><table><tr><th>Report ID</th><th>Reporter</th><th>Type</th><th>Target ID</th><th>Reason</th><th>Status</th><th>Action</th></tr><?php while($r=mysqli_fetch_assoc($reports)){?><tr><td><?php echo e($r["reportID"]);?></td><td><?php echo e($r["reporterID"]);?></td><td><?php echo e($r["targetType"]);?></td><td><?php echo e($r["targetID"]);?></td><td><?php echo e($r["reason"]);?></td><td><span class="badge <?php echo $r["status"]=="PENDING"?"pending":"resolved";?>"><?php echo e($r["status"]);?></span></td><td><?php if($r["status"]=="PENDING"){?><form method="POST" class="actions-inline"><input type="hidden" name="reportID" value="<?php echo e($r["reportID"]);?>"><input type="hidden" name="targetType" value="<?php echo e($r["targetType"]);?>"><input type="hidden" name="targetID" value="<?php echo e($r["targetID"]);?>"><button class="btn green" name="resolve">Resolve</button><button class="btn red" name="delete_target">Delete Target</button></form><?php }else{echo "Handled";}?></td></tr><?php }?></table></section></main></div></body></html>