<?php
/* Include this form/action in any existing user/student/faculty page.
   No JavaScript is required.
*/
session_start();
include "admin_db.php";
$message="";
if(isset($_POST["submit_report"])){
$reporterID=$_SESSION["userID"] ?? "";
$type=$_POST["targetType"];$target=$_POST["targetID"];$reason=$_POST["reason"];
$sql="INSERT INTO admin_reports(reporterID,targetType,targetID,reason) VALUES('$reporterID','$type','$target','$reason')";
$message=mysqli_query($conn,$sql)?"Report submitted successfully.":"Report failed.";
}
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Report</title><link rel="stylesheet" href="admin_pages.css"></head><body><main class="main" style="margin:0 auto;max-width:650px"><section class="panel"><h2>Report Content / User</h2><?php if($message!=""){?><div class="alert ok"><?php echo e($message);?></div><?php }?><form method="POST"><div class="form-grid"><div class="field"><label>Target Type</label><select name="targetType"><option value="USER">User</option><option value="GROUP">Group</option><option value="COURSE">Course</option><option value="REPOSITORY">Repository</option></select></div><div class="field"><label>Target ID</label><input name="targetID" required></div></div><div class="field"><label>Reason</label><textarea name="reason" required></textarea></div><button class="btn" name="submit_report">Submit Report</button></form></section></main></body></html>