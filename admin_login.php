<?php
session_start();
include 'db.php';

if (isset($_SESSION['adminID'])) {
    header("Location: admin_dashboard.php");
    exit();
}

/*
 * The supplied database did not originally contain an admin password column.
 * This one-time compatibility block creates it if necessary and gives the
 * supplied A1 admin the temporary password: admin123
 */
$columnCheck = mysqli_query($conn, "SHOW COLUMNS FROM admin_info LIKE 'password'");
if ($columnCheck && mysqli_num_rows($columnCheck) === 0) {
    mysqli_query($conn, "ALTER TABLE admin_info ADD COLUMN password VARCHAR(255) NOT NULL DEFAULT 'admin123'");
}

$error = "";

if (isset($_POST['admin_login'])) {
    $admin_id = strtoupper(trim($_POST['adminid'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    if (!preg_match('/^A[0-9]{1,7}$/', $admin_id)) {
        $error = "Admin ID must start with A and contain up to 8 characters.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT AdminID, name, password FROM admin_info WHERE AdminID = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $admin_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            // Supports the temporary plain-text password created above.
            // Change the password in phpMyAdmin after first login if desired.
            if ($password === $row['password']) {
                session_regenerate_id(true);
                $_SESSION['adminID'] = $row['AdminID'];
                $_SESSION['adminName'] = $row['name'];

                header("Location: admin_dashboard.php");
                exit();
            }
        }

        $error = "Invalid Admin ID or Password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Studyverse</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-auth-page">
<div class="auth-shell">
    <div class="auth-brand">
        <span class="brand-mark">S</span>
        <div>
            <h1>Studyverse</h1>
            <p>Administrator Portal</p>
        </div>
    </div>

    <div class="auth-card">
        <div class="auth-card-head">
            <span class="eyebrow">SECURE ACCESS</span>
            <h2>Welcome, Admin</h2>
            <p>Sign in to manage Studyverse courses.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="admin_login.php">
            <label for="adminid">Admin ID</label>
            <input id="adminid" name="adminid" type="text" maxlength="8"
                   placeholder="e.g. A1" pattern="A[0-9]{1,7}" required>

            <label for="password">Password</label>
            <input id="password" name="password" type="password"
                   placeholder="Enter admin password" required>

            <button type="submit" name="admin_login">Sign In to Dashboard</button>
        </form>

        <div class="auth-note">
            Default admin: <strong>A1</strong> / <strong>admin123</strong>
        </div>
        <a class="back-link" href="login.php">← Back to user login</a>
    </div>
</div>
</body>
</html>
