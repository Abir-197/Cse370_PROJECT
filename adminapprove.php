<?php
require 'admin_guard.php';
require 'admin_db.php';

$current_admin_id = mysqli_real_escape_string($conn, $_SESSION['AdminID'] ?? '');

$msg = '';
$err = '';

// Pure PHP Form Action (Zero JavaScript)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_handle_password'])) {
    $target_uid = mysqli_real_escape_string($conn, trim($_POST['target_uid'] ?? ''));
    $decision   = trim($_POST['decision'] ?? '');

    if (!empty($target_uid)) {
        if ($decision === 'APPROVE') {
            // Strictly verifies that AdminID matches the logged-in admin
            $sql = "UPDATE user_info 
                    SET password = pending_password, 
                        pending_password = NULL, 
                        password_status = 'APPROVED' 
                    WHERE userID = '$target_uid' 
                      AND password_status = 'PENDING' 
                      AND AdminID = '$current_admin_id'";
            if (mysqli_query($conn, $sql) && mysqli_affected_rows($conn) > 0) {
                $msg = "Password approved successfully for user: $target_uid";
            } else {
                $err = "Action Failed: User does not belong to your managed students or is not pending.";
            }
        } elseif ($decision === 'REJECT') {
            // Strictly verifies that AdminID matches the logged-in admin
            $sql = "UPDATE user_info 
                    SET pending_password = NULL, 
                        password_status = 'REJECTED' 
                    WHERE userID = '$target_uid' 
                      AND password_status = 'PENDING' 
                      AND AdminID = '$current_admin_id'";
            if (mysqli_query($conn, $sql) && mysqli_affected_rows($conn) > 0) {
                $msg = "Password request rejected for user: $target_uid";
            } else {
                $err = "Action Failed: User does not belong to your managed students or is not pending.";
            }
        }
    }
}

// Fetch pending requests restricted to THIS admin's AdminID
$pending_sql = "
    SELECT 
        u.userID, 
        u.name, 
        u.email, 
        u.pending_password, 
        u.AdminID,
        s.SID,
        s.dept
    FROM user_info u 
    LEFT JOIN student_info s ON u.userID = s.userID 
    WHERE u.password_status = 'PENDING' 
      AND u.AdminID = '$current_admin_id'
    ORDER BY u.userID ASC
";
$pending_res = mysqli_query($conn, $pending_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Approve Passwords — Studyverse Admin</title>
    <link rel="stylesheet" href="admin_dash.css">
    <style>
        .approval-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; margin-top: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); font-size: 13px; }
        .approval-table th { background: #0f172a; color: #fff; padding: 12px 14px; text-align: left; }
        .approval-table td { padding: 12px 14px; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
        .approval-table tr:hover { background: #f8fafc; }
        .badge-uid { background: #e0f2fe; color: #0369a1; padding: 3px 7px; border-radius: 4px; font-weight: bold; font-family: monospace; }
        .badge-admin { background: #f1f5f9; color: #475569; padding: 3px 6px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .btn-approve { background: #16a34a; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; font-weight: bold; cursor: pointer; }
        .btn-approve:hover { background: #15803d; }
        .btn-reject { background: #dc2626; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; font-weight: bold; cursor: pointer; }
        .btn-reject:hover { background: #b91c1c; }
        .alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; font-weight: bold; font-size: 13px; }
        .alert-success { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .alert-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
    </style>
</head>
<body>
<div class="shell">
    <aside>
        <h2>StudyVerse</h2>
        <p>Admin Panel (<?= htmlspecialchars($current_admin_id) ?>)</p>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="allcourse.php">See All Course Info</a>
        <a href="allrepository.php">See All Repository</a>
        <a href="admin_profile.php">View Profile</a>
        <a href="work_due.php">Work Due</a>
        <a href="checkAllUser.php">See All User</a>
        <a href="admin_response.php">Response Other Admins</a>
        <a href="manage_reports.php">Manage Reports</a>
        <a href="adminapprovepass.php" style="background:#1e293b; color:#fff;">Approve Passwords</a>
        <a href="logout.php">Logout</a>
    </aside>

    <main>
        <h1>🔑 User Password Change Requests</h1>
        <p style="color: #64748b; font-size: 13px; margin-top: -6px;">
            Showing pending requests for users managed by Admin <strong><?= htmlspecialchars($current_admin_id) ?></strong>.
        </p>

        <?php if ($msg): ?><div class="alert alert-success">✅ <?= htmlspecialchars($msg) ?></div><?php endif; ?>
        <?php if ($err): ?><div class="alert alert-danger">⚠️ <?= htmlspecialchars($err) ?></div><?php endif; ?>

        <table class="approval-table">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Student ID</th>
                    <th>Full Name</th>
                    <th>Email Address</th>
                    <th>Requested Password</th>
                    <th>Managing Admin</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($pending_res && mysqli_num_rows($pending_res) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($pending_res)): ?>
                        <tr>
                            <td><span class="badge-uid"><?= htmlspecialchars($row['userID']) ?></span></td>
                            <td><?= htmlspecialchars($row['SID'] ?? 'N/A') ?></td>
                            <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><code style="background: #f1f5f9; padding: 3px 6px; border-radius: 4px; font-weight: bold; color: #0f172a;"><?= htmlspecialchars($row['pending_password']) ?></code></td>
                            <td><span class="badge-admin"><?= htmlspecialchars($row['AdminID']) ?></span></td>
                            <td style="text-align: center;">
                                <form method="POST" action="" style="display:inline; margin-right: 4px;">
                                    <input type="hidden" name="action_handle_password" value="1">
                                    <input type="hidden" name="target_uid" value="<?= htmlspecialchars($row['userID']) ?>">
                                    <input type="hidden" name="decision" value="APPROVE">
                                    <button type="submit" class="btn-approve">Approve</button>
                                </form>
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="action_handle_password" value="1">
                                    <input type="hidden" name="target_uid" value="<?= htmlspecialchars($row['userID']) ?>">
                                    <input type="hidden" name="decision" value="REJECT">
                                    <button type="submit" class="btn-reject">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: #64748b; padding: 28px;">
                            No pending password approval requests found for Admin <?= htmlspecialchars($current_admin_id) ?>.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</div>
</body>
</html>