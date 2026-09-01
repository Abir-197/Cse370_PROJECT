<?php
require 'admin_guard.php'; require 'admin_db.php';
$code=$_GET['coursecode']??''; $msg='';
$s=mysqli_prepare($conn,'SELECT requestID,requestedBy,status FROM admin_course_delete_requests WHERE coursecode=? AND status="PENDING" ORDER BY requestID DESC LIMIT 1');
mysqli_stmt_bind_param($s,'s',$code); mysqli_stmt_execute($s); $req=mysqli_fetch_assoc(mysqli_stmt_get_result($s));
if($req && isset($_GET['vote'])){
 $v=$_GET['vote']==='YES'?'YES':'NO'; $rid=(int)$req['requestID'];
 $s=mysqli_prepare($conn,'REPLACE INTO admin_course_delete_votes(requestID,adminID,vote) VALUES(?,?,?)'); mysqli_stmt_bind_param($s,'iss',$rid,$adminID,$v); mysqli_stmt_execute($s); $msg='Vote recorded.';
 $n=mysqli_fetch_assoc(mysqli_query($conn,'SELECT COUNT(*) n FROM admin_info'))['n'];
 $ys=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) n FROM admin_course_delete_votes WHERE requestID=$rid AND vote='YES'"))['n'];
 $no=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) n FROM admin_course_delete_votes WHERE requestID=$rid AND vote='NO'"))['n'];
 $safeCode=mysqli_real_escape_string($conn,$code); $course=mysqli_fetch_assoc(mysqli_query($conn,"SELECT admin FROM course WHERE coursecode='$safeCode'")); $owner=$course['admin']??'';
 $safeOwner=mysqli_real_escape_string($conn,$owner); $ov=mysqli_fetch_assoc(mysqli_query($conn,"SELECT vote FROM admin_course_delete_votes WHERE requestID=$rid AND adminID='$safeOwner'"));
 if($course && $ov && $ov['vote']==='YES' && $ys>$no && $ys>=ceil($n/2)){
   $d=mysqli_prepare($conn,'DELETE FROM course WHERE coursecode=?'); mysqli_stmt_bind_param($d,'s',$code); mysqli_stmt_execute($d);
   mysqli_query($conn,"UPDATE admin_course_delete_requests SET status='APPROVED' WHERE requestID=$rid"); $msg='Course deleted successfully by majority consensus.';
 }
}
?>
<!doctype html><html><head><title>Course Delete Votes</title><link rel="stylesheet" href="admin_pages.css"></head><body><div class="page"><div class="panel"><h1>Deletion Consensus: <?=htmlspecialchars($code)?></h1><?php if($msg)echo'<div class="notice">'.htmlspecialchars($msg).'</div>'; if($req):?><p>Requested by: <?=htmlspecialchars($req['requestedBy'])?></p><table><tr><th>Admin</th><th>Vote</th></tr><?php $v=mysqli_query($conn,'SELECT * FROM admin_course_delete_votes WHERE requestID='.(int)$req['requestID']);while($x=mysqli_fetch_assoc($v)):?><tr><td><?=$x['adminID']?></td><td><?=$x['vote']?></td></tr><?php endwhile;?></table><br><a class="btn success" href="?coursecode=<?=urlencode($code)?>&vote=YES">YES</a> <a class="btn danger" href="?coursecode=<?=urlencode($code)?>&vote=NO">NO</a><?php else:?><p>No pending request.</p><?php endif;?></div></div></body></html>
