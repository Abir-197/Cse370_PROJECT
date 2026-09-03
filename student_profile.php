<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

// Strict Student Authentication Guard
if (!isset($_SESSION['userID']) || empty($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['userID'];
$msg = '';
$err = '';

// 1. Handle Profile Update Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_update_profile'])) {
    $name         = mysqli_real_escape_string($conn, trim($_POST['name'] ?? ''));
    $road         = mysqli_real_escape_string($conn, trim($_POST['road'] ?? ''));
    $area         = mysqli_real_escape_string($conn, trim($_POST['area'] ?? ''));
    $phone        = mysqli_real_escape_string($conn, trim($_POST['phone'] ?? ''));
    $new_password = trim($_POST['new_password'] ?? '');

    // Update editable personal fields
    $update_user_sql = "UPDATE user_info 
                        SET name = '$name', road = '$road', area = '$area' 
                        WHERE userID = '$user_id'";
    mysqli_query($conn, $update_user_sql);

    // Update phone record cleanly in userphone
    if (!empty($phone)) {
        $chk_phone = mysqli_query($conn, "SELECT phone FROM userphone WHERE userID = '$user_id' LIMIT 1");
        if ($chk_phone && mysqli_num_rows($chk_phone) > 0) {
            mysqli_query($conn, "UPDATE userphone SET phone = '$phone' WHERE userID = '$user_id'");
        } else {
            mysqli_query($conn, "INSERT INTO userphone (userID, phone) VALUES ('$user_id', '$phone')");
        }
    }

    // Submit Password Change Request (If typed)
    if (!empty($new_password)) {
        $escaped_pwd = mysqli_real_escape_string($conn, $new_password);
        
        $pwd_sql = "UPDATE user_info 
                    SET pending_password = '$escaped_pwd', 
                        password_status = 'PENDING' 
                    WHERE userID = '$user_id'";
        if (mysqli_query($conn, $pwd_sql)) {
            $msg = "Profile updated! Password change request submitted for Admin approval.";
        } else {
            $err = "Error submitting password request: " . mysqli_error($conn);
        }
    } else {
        $msg = "Profile updated successfully!";
    }
}

// 2. Fetch Complete Student Info matching schema
$profile_sql = "
    SELECT 
        u.userID, u.name, u.email, u.road, u.area, u.AdminID, u.password_status,
        s.SID, s.cgpa, s.semester, s.current_semester_no, 
        s.completed_cod_credits, s.thesis_semesters_completed,
        s.is_doing_thesis, s.is_doing_internship, s.dept,
        up.phone,
        a.name AS managing_admin_name
    FROM user_info u
    JOIN student_info s ON u.userID = s.userID
    LEFT JOIN userphone up ON u.userID = up.userID
    LEFT JOIN admin_info a ON u.AdminID = a.AdminID
    WHERE u.userID = '$user_id'
    LIMIT 1
";
$profile_res = mysqli_query($conn, $profile_sql);
$student = mysqli_fetch_assoc($profile_res);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile — Studyverse</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; background: #f8fafc; margin: 0; padding: 24px; color: #1e293b; }
        .profile-container { max-width: 900px; margin: 0 auto; }
        .profile-header { background: #0f172a; color: #fff; padding: 20px 24px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .profile-header h2 { margin: 0 0 6px 0; font-size: 22px; }
        .profile-header p { margin: 0; color: #94a3b8; font-size: 13px; }
        .profile-header a { color: #38bdf8; text-decoration: none; font-size: 13px; font-weight: bold; }
        .profile-header a:hover { text-decoration: underline; }
        .form-section { background: #fff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .form-section h3 { margin-top: 0; color: #0f172a; font-size: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; margin-bottom: 16px; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group.full-width { grid-column: 1 / -1; }
        .form-group label { font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px; }
        .form-input { padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 13px; color: #1e293b; }
        .form-input:focus { outline: none; border-color: #2563eb; }
        .form-input.read-only { background: #f1f5f9; color: #64748b; cursor: not-allowed; }
        .help-text { font-size: 12px; color: #64748b; margin-top: 5px; }
        .alert { padding: 12px 16px; border-radius: 6px; font-size: 13px; font-weight: bold; margin-bottom: 16px; }
        .alert-success { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .alert-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .alert-warning { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .btn-update { background: #2563eb; color: #fff; border: none; padding: 10px 24px; border-radius: 5px; font-size: 13px; font-weight: bold; cursor: pointer; }
        .btn-update:hover { background: #1d4ed8; }
        .form-actions { display: flex; justify-content: flex-end; }
    </style>
</head>
<body>

    <div class="profile-container">
        
        <div class="profile-header">
            <div>
                <h2>🎓 Student Profile & Account Settings</h2>
                <p>Managing Admin: <strong><?= htmlspecialchars($student['managing_admin_name'] ?? $student['AdminID']) ?> (<?= htmlspecialchars($student['AdminID']) ?>)</strong></p>
            </div>
            <div>
                <a href="student_dashboard.php">← Back to Dashboard</a>
            </div>
        </div>

        <?php if ($msg): ?><div class="alert alert-success">✅ <?= htmlspecialchars($msg) ?></div><?php endif; ?>
        <?php if ($err): ?><div class="alert alert-danger">⚠️ <?= htmlspecialchars($err) ?></div><?php endif; ?>

        <!-- Dynamic Feedback for Password Approval Status -->
        <?php if ($student['password_status'] === 'PENDING'): ?>
            <div class="alert alert-warning">
                ⏳ <strong>Pending Approval:</strong> Your password change request is currently pending authorization from Admin <strong><?= htmlspecialchars($student['AdminID']) ?></strong>.
            </div>
        <?php elseif ($student['password_status'] === 'APPROVED'): ?>
            <div class="alert alert-success">
                ✅ <strong>Approved:</strong> Your latest password change has been approved by your managing admin.
            </div>
        <?php elseif ($student['password_status'] === 'REJECTED'): ?>
            <div class="alert alert-danger">
                ❌ <strong>Request Denied:</strong> Your previous password change was rejected by your Admin. You can submit another password below.
            </div>
        <?php endif; ?>

        <form method="POST" action="student_profile.php">
            <input type="hidden" name="action_update_profile" value="1">

            <!-- Read-Only Academic Details -->
            <div class="form-section">
                <h3>📌 Academic Records (Read Only)</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>User ID</label>
                        <input type="text" class="form-input read-only" value="<?= htmlspecialchars($student['userID']) ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Student ID (SID)</label>
                        <input type="text" class="form-input read-only" value="<?= htmlspecialchars($student['SID'] ?? 'Not Set') ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Official Email</label>
                        <input type="email" class="form-input read-only" value="<?= htmlspecialchars($student['email']) ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Current CGPA</label>
                        <input type="text" class="form-input read-only" value="<?= number_format((float)$student['cgpa'], 2) ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Current Semester</label>
                        <input type="text" class="form-input read-only" value="<?= htmlspecialchars($student['semester'] ?? 'Spring 2026') ?> (Sem <?= $student['current_semester_no'] ?>)" readonly>
                    </div>
                    <div class="form-group">
                        <label>COD Completed Credits</label>
                        <input type="text" class="form-input read-only" value="<?= $student['completed_cod_credits'] ?> Credits Completed" readonly>
                    </div>
                </div>
            </div>

            <!-- Editable Personal & Contact Details -->
            <div class="form-section">
                <h3>✏️ Personal & Contact Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" class="form-input" value="<?= htmlspecialchars($student['name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Contact Phone</label>
                        <input type="text" name="phone" class="form-input" value="<?= htmlspecialchars($student['phone'] ?? '') ?>" placeholder="017xxxxxxxx">
                    </div>
                    <div class="form-group">
                        <label>Road</label>
                        <input type="text" name="road" class="form-input" value="<?= htmlspecialchars($student['road']) ?>" placeholder="Road 11">
                    </div>
                    <div class="form-group">
                        <label>Area</label>
                        <input type="text" name="area" class="form-input" value="<?= htmlspecialchars($student['area']) ?>" placeholder="Banani">
                    </div>
                </div>
            </div>

            <!-- Password Change Request Section -->
            <div class="form-section">
                <h3>🔒 Security & Password Change</h3>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label>New Password (Leave blank to keep unchanged)</label>
                        <input type="password" name="new_password" class="form-input" placeholder="Type new password to request approval...">
                        <small class="help-text">Password changes require manual review and authorization from your managing Admin (<?= htmlspecialchars($student['AdminID']) ?>).</small>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-update">Save & Update Profile</button>
            </div>
        </form>

    </div>

</body>
</html>
