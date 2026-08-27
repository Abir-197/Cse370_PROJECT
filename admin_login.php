<?php
session_start();
require_once 'admin_db.php';

if (isset($_SESSION['admin_id'])) { header('Location: admin_dashboard.php'); exit(); }
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = strtoupper(trim($_POST['admin_id'] ?? ''));

    if (!preg_match('/^A[A-Z0-9]{0,7}$/', $admin_id)) {
        $error = 'Admin ID must start with A and be maximum 8 characters.';
    } else {
        $stmt = $conn->prepare('SELECT AdminID, name FROM admin_info WHERE AdminID = ?');
        $stmt->bind_param('s', $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $row['AdminID'];
            $_SESSION['admin_name'] = $row['name'];
            header('Location: admin_dashboard.php');
            exit();
        } else {
            $error = 'Admin ID not found.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Admin Login | Studyverse</title><link rel="stylesheet" href="admin.css">
</head>
<body class="admin-auth-page">
<div class="auth-card">
    <div class="logo-mark">S</div>
    <div class="eyebrow">STUDYVERSE</div>
    <h1>Admin Portal</h1>
    <p class="muted">Sign in with your existing Admin ID.</p>
    <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post">
        <label for="admin_id">Admin ID</label>
        <input id="admin_id" name="admin_id" type="text" maxlength="8" placeholder="e.g. A1" required autocomplete="off">
        <button class="primary-btn" type="submit">Enter Admin Dashboard <span>→</span></button>
    </form>
    <p class="login-note">Admin registration is not available.</p>
</div>
</body></html>
