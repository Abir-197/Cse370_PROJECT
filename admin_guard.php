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