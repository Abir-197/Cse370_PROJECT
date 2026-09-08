<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'admin_guard.php';
require_once 'admin_db.php';

// Detect and normalize connection variable
if (!isset($conn) && isset($con)) {
    $conn = $con;
}

if (!$conn) {
    die("Database Connection Error: Could not connect to studyverse.");
}

// Fallback session key detection for AdminID
$current_admin_id = $_SESSION['AdminID'] 
    ?? $_SESSION['admin_id'] 
    ?? $_SESSION['adminID'] 
    ?? $_SESSION['admin'] 
    ?? '';

$current_admin_id = mysqli_real_escape_string($conn, trim($current_admin_id));

$msg = '';
$err = '';

// Pure PHP Form Action Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_handle_password'])) {
    $target_uid = mysqli_real_escape_string($conn, trim($_POST['target_uid'] ?? ''));
    $decision   = strtoupper(trim($_POST['decision'] ?? ''));

    if (empty($current_admin_id)) {
        $err = "Session Error: Your AdminID is not recognized. Please log out and log back in.";
    } elseif (empty($target_uid)) {
        $err = "Invalid Target: No student user selected.";
    } else {
        if ($decision === 'APPROVE') {
            $sql = "UPDATE user_info 
                    SET password = pending_password, 
                        pending_password = NULL, 
                        password_status = 'APPROVED' 
                    WHERE userID = '$target_uid' 
                      AND password_status = 'PENDING' 
                      AND AdminID = '$current_admin_id'";
            
            $res = mysqli_query($conn, $sql);
            if ($res && mysqli_affected_rows($conn) > 0) {
                $msg = "Password approved successfully for user $target_uid.";
            } else {
                $err = "Action Failed: Student $target_uid does not have a pending password or belongs to another admin. " . mysqli_error($conn);
            }
        } elseif ($decision === 'REJECT') {
            $sql = "UPDATE user_info 
                    SET pending_password = NULL, 
                        password_status = 'REJECTED' 
                    WHERE userID = '$target_uid' 
                      AND password_status = 'PENDING' 
                      AND AdminID = '$current_admin_id'";
            
            $res = mysqli_query($conn, $sql);
            if ($res && mysqli_affected_rows($conn) > 0) {
                $msg = "Password request rejected for user $target_uid.";
            } else {
                $err = "Action Failed: Student $target_uid does not have a pending password or belongs to another admin. " . mysqli_error($conn);
            }
        }
    }
}

// Fetch pending requests restricted to this AdminID
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

// Diagnostic counter across ALL admins
$global_pending_q = mysqli_query($conn, "SELECT COUNT(*) total FROM user_info WHERE password_status='PENDING'");
$global_pending = $global_pending_q ? mysqli_fetch_assoc($global_pending_q)['total'] : 0;
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
        .diag-box { background: #f8fafc; border: 1px dashed #94a3b8; border-radius: 6px; padding: 8px 12px; font-size: 12px; color: #334155; margin-bottom: 14px; }
    </style>
</head>
<body>
<div class="shell">
    <aside>
        <h2>StudyVerse</h2>
        <p>Admin Panel</p>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="allcourse.php">See All Course Info</a>
        <a href="allrepository.php">See All Repository</a>
        <a href="admin_profile.php">View Profile</a>
        <a href="work_due.php">Work Due</a>
        <a href="checkAllUser.php">See All User</a>
        <a href="admin_response.php">Response Other Admins</a>
        <a href="manage_reports.php">Manage Reports</a>
        <a href="website_performance.php">Website Performance</a>
        <a href="group_admin.php">Manage Groups</a>
        <a href="adminapprovepass.php" style="background:#1e293b; color:#fff;">Approve Passwords</a>
        <a href="logout.php">Logout</a>
    </aside>

    <main>
        <h1>🔑 User Password Change Requests</h1>

        <!-- Diagnostic Information Box -->
        <div class="diag-box">
            Logged-in as Admin ID: <strong><?= !empty($current_admin_id) ? htmlspecialchars($current_admin_id) : '<span style="color:red;">NOT DETECTED</span>' ?></strong> 
            · Pending for you: <strong><?= $pending_res ? mysqli_num_rows($pending_res) : 0 ?></strong> 
            · System-wide pending across all admins: <strong><?= $global_pending ?></strong>
        </div>

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
                                <form method="POST" action="adminapprovepass.php" style="display:inline; margin-right: 4px;">
                                    <input type="hidden" name="action_handle_password" value="1">
                                    <input type="hidden" name="target_uid" value="<?= htmlspecialchars($row['userID']) ?>">
                                    <input type="hidden" name="decision" value="APPROVE">
                                    <button type="submit" class="btn-approve">Approve</button>
                                </form>
                                <form method="POST" action="adminapprovepass.php" style="display:inline;">
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
                            No pending password approval requests found for Admin <strong><?= htmlspecialchars($current_admin_id ?: 'Unknown') ?></strong>.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</div>
</body>
</html>