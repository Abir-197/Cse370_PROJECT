<?php
session_start(); require 'admin_db.php';
$msg='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $id=trim($_POST['AdminID']); $pass=$_POST['password'];
  if (!preg_match('/^A[A-Za-z0-9]{0,7}$/',$id)) $msg='AdminID must start with A and be maximum 8 characters.';
  else { $stmt=mysqli_prepare($conn,"SELECT AdminID,name,password FROM admin_info WHERE AdminID=?"); mysqli_stmt_bind_param($stmt,'s',$id); mysqli_stmt_execute($stmt); $r=mysqli_stmt_get_result($stmt); $a=mysqli_fetch_assoc($r);
    if($a && ($a['password']===$pass || password_verify($pass,$a['password']))) { $_SESSION['AdminID']=$a['AdminID']; $_SESSION['AdminName']=$a['name']; header('Location: admin_dashboard.php'); exit; }
    $msg='Invalid AdminID or password.';
  }
}
?>
<!doctype html><html><head><title>Admin Login</title><link rel="stylesheet" href="admin_dash.css"></head><body class="login-page"><form class="login-card" method="post"><h1>StudyVerse</h1><p>Admin Login</p><?php if($msg) echo '<div class="alert">'.htmlspecialchars($msg).'</div>'; ?><input name="AdminID" maxlength="8" placeholder="AdminID (A...)" required><input type="password" name="password" placeholder="Password" required><button>Login</button></form></body></html>
