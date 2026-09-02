<?php
session_start();
include 'db.php';

$error = "";

if (isset($_POST['login'])) {
    $user_id  = trim($_POST['userid']);
    $password = trim($_POST['password']);

    // Check if user exists with matching password
    $sql = "SELECT userID FROM user_info WHERE userID = '$user_id' AND password = '$password'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        // Save the User ID in session
        $_SESSION['userID'] = $user_id;

        // Check if ID exists in student_info
        $sql_student = "SELECT userID FROM student_info WHERE userID = '$user_id'";
        $res_student = mysqli_query($conn, $sql_student);

        if (mysqli_num_rows($res_student) > 0) {
            header("Location: student_dashboard.php");
            exit();
        }

        // Check if ID exists in faculty_info
        $sql_faculty = "SELECT userID FROM faculty_info WHERE userID = '$user_id'";
        $res_faculty = mysqli_query($conn, $sql_faculty);

        if (mysqli_num_rows($res_faculty) > 0) {
            header("Location: faculty_dashboard.php");
            exit();
        }

        $error = "Role not assigned to this account.";
    } else {
        $error = "Invalid User ID or Password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Studyverse</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>

<div class="auth-card">
    <div class="auth-header">
        <h2 class="brand-title">Studyverse</h2>
        <p class="brand-subhead">Your Study Partner</p>
    </div>

    <?php if (!empty($error)) { ?>
        <div style="color: red; margin-bottom: 12px; font-weight: bold;"><?php echo $error; ?></div>
    <?php } ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label class="form-label" for="userid">User ID</label>
            <input 
                type="text" 
                id="userid" 
                name="userid" 
                class="form-input" 
                placeholder="Enter your User ID" 
                required
            >
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                class="form-input" 
                placeholder="Enter your password" 
                required
            >
        </div>

        <button type="submit" name="login" class="btn-submit">Sign In</button>
    </form>

    <div class="auth-footer" style="margin-top: 15px; text-align: center; font-size: 13px;">
        <p style="margin: 6px 0;">
            <span>Don't have an account? </span>
            <a href="user_register.php" class="auth-link">Register here</a>
        </p>
        <p style="margin: 6px 0;">
            <span>Are you an Admin? </span>
            <a href="admin_login.php" class="auth-link" style="color: #007bff; font-weight: bold;">Login as Admin here</a>
        </p>
    </div>
</div>

</body>
</html>