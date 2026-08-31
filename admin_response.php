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
if(isset($_POST["send"])){
$receiver=$_POST["receiver"];$text=$_POST["message"];
$sql="INSERT INTO admin_messages(senderAdminID,receiverAdminID,message) VALUES('$adminID','$receiver','$text')";
$message=mysqli_query($conn,$sql)?"Message sent successfully.":"Could not send message: ".mysqli_error($conn);
}
if(isset($_GET["read"])){ $id=$_GET["read"];mysqli_query($conn,"UPDATE admin_messages SET status='READ' WHERE messageID='$id' AND receiverAdminID='$adminID'");}
$admins=mysqli_query($conn,"SELECT AdminID,name FROM admin_info WHERE AdminID!='$adminID' ORDER BY AdminID");
$messages=mysqli_query($conn,"SELECT m.*,s.name AS senderName,r.name AS receiverName FROM admin_messages m JOIN admin_info s ON m.senderAdminID=s.AdminID JOIN admin_info r ON m.receiverAdminID=r.AdminID WHERE m.senderAdminID='$adminID' OR m.receiverAdminID='$adminID' ORDER BY m.sent_at DESC");
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Admin Response</title><link rel="stylesheet" href="admin_pages.css"></head><body><div class="admin-layout"><aside class="sidebar"><div class="logo">Study<span>verse</span></div><nav class="nav"><a href="admin_dashboard.php">Dashboard</a><a class="active" href="admin_response.php">Admin Responses</a><a href="checkAllUser.php">Users</a><a href="logout.php" class="logout">Logout</a></nav></aside><main class="main"><div class="top"><div><h1>Response Other Admins</h1></div></div><?php if($message!=""){?><div class="alert ok"><?php echo e($message);?></div><?php }?>
<section class="panel"><h2>Send Response</h2><form method="POST"><div class="form-grid"><div class="field"><label>Receiver Admin</label><select name="receiver" required><?php while($a=mysqli_fetch_assoc($admins)){?><option value="<?php echo e($a["AdminID"]);?>"><?php echo e($a["AdminID"]." - ".$a["name"]);?></option><?php }?></select></div></div><div class="field"><label>Message</label><textarea name="message" maxlength="1000" required></textarea></div><button class="btn" name="send">Send</button></form></section>
<section class="panel"><h2>Messages</h2><table><tr><th>From</th><th>To</th><th>Message</th><th>Status</th><th>Time</th><th>Action</th></tr><?php while($m=mysqli_fetch_assoc($messages)){?><tr><td><?php echo e($m["senderAdminID"]." - ".$m["senderName"]);?></td><td><?php echo e($m["receiverAdminID"]." - ".$m["receiverName"]);?></td><td><?php echo e($m["message"]);?></td><td><?php echo e($m["status"]);?></td><td><?php echo e($m["sent_at"]);?></td><td><?php if($m["receiverAdminID"]==$adminID&&$m["status"]=="UNREAD"){?><a class="btn green" href="admin_response.php?read=<?php echo e($m["messageID"]);?>">Mark Read</a><?php }?></td></tr><?php }?></table></section></main></div></body></html>