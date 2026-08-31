<?php
session_start();
include "admin_db.php";

$message = "";

if (isset($_POST["login"])) {
    $adminID = trim($_POST["AdminID"]);
    $password = $_POST["password"];

    if (strlen($adminID) > 8 || strtoupper(substr($adminID, 0, 1)) != "A") {
        $message = "Invalid AdminID. It must start with A and be maximum 8 characters.";
    } else {
        $sql = "SELECT AdminID, password FROM admin_info WHERE AdminID='$adminID'";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) == 1) {
            $admin = mysqli_fetch_assoc($result);

            if ($password == $admin["password"]) {
                $_SESSION["AdminID"] = $admin["AdminID"];
                header("Location: admin_dashboard.php");
                exit();
            } else {
                $message = "Wrong password.";
            }
        } else {
            $message = "AdminID not found.";
        }
    }
}
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Admin Login</title><link rel="stylesheet" href="admin_pages.css"></head>
<body><main class="main" style="margin:0 auto;max-width:520px">
<div class="panel" style="margin-top:80px">
<h2>Studyverse Admin Login</h2><p class="muted" style="margin-bottom:18px">Special Admin login: AdminID starts with A and maximum 8 characters.</p>
<?php if($message!=""){ ?><div class="alert bad"><?php echo e($message); ?></div><?php } ?>
<form method="POST">
<div class="field"><label>AdminID</label><input name="AdminID" maxlength="8" required></div>
<div class="field"><label>Password</label><input type="password" name="password" required></div>
<button class="btn" name="login">Login</button>
</form>
</div></main></body></html>