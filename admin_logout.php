<?php
session_start();
unset($_SESSION['adminID'], $_SESSION['adminName']);
session_regenerate_id(true);
header("Location: admin_login.php");
exit();
?>
