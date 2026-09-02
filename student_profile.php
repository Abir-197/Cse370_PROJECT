<?php
session_start();
include 'db.php';

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
    $name         = mysqli_real_escape_string($conn, trim($_POST['name']));
    $road         = mysqli_real_escape_string($conn, trim($_POST['road']));
    $area         = mysqli_real_escape_string($conn, trim($_POST['area']));
    $phone        = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $new_password = trim($_POST['new_password']);

    // Update editable personal fields
    $update_user_sql = "UPDATE user_info 
                        SET name = '$name', road = '$road', area = '$area' 
                        WHERE userID = '$user_id'";
    mysqli_query($conn, $update_user_sql);

    // Update phone record
    if (!empty($phone)) {
        mysqli_query($conn, "INSERT INTO userphone (userID, phone) 
                             VALUES ('$user_id', '$phone') 
                             ON DUPLICATE KEY UPDATE phone = VALUES(phone)");
    }

    // Submit Password Change Request (If typed)
    if (!empty($new_password)) {
        $escaped_pwd = mysqli_real_escape_string($conn, $new_password);
        
        $pwd_sql = "UPDATE user_info 
                    SET pending_password = '$escaped_pwd', 
                        password_status = 'PENDING' 
                    WHERE userID = '$user_id'";
        mysqli_query($conn, $pwd_sql);
        
        $msg = "Profile updated! Password change request sent to your managing admin.";
    } else {
        $msg = "Profile updated successfully!";
    }
}

// 2. Fetch Complete Student Info
$profile_sql = "
    SELECT 
        u.userID, u.name, u.email, u.road, u.area, u.AdminID, u.password_status,
        s.SID, s.cgpa, s.semester, s.current_semester_no, 
        s.completed_cod_credits, s.thesis_semesters_completed,
        s.is_doing_thesis, s.is_doing_internship,
        up.phone,
        a.name AS managing_admin_name
    FROM user_info u
    JOIN student_info s ON u.userID = s.userID
    LEFT JOIN userphone up ON u.userID = up.userID
    LEFT JOIN admin_info a ON u.AdminID = a.AdminID
    WHERE u.userID = '$user_id'
";
$profile_res = mysqli_query($conn, $profile_sql);
$student = mysqli_fetch_assoc($profile_res);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile - Studyverse</title>
    <link rel="stylesheet" href="student_profile.css">
</head>
<body>

    <div class="profile-container">
        
        <div class="profile-header">
            <h2>🎓 Student Profile & Account Settings</h2>
            <p>Managing Admin: <strong><?php echo htmlspecialchars($student['managing_admin_name'] ?? $student['AdminID']); ?> (<?php echo htmlspecialchars($student['AdminID']); ?>)</strong></p>
        </div>

        <?php if ($msg): ?><div class="alert alert-success">✅ <?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
        <?php if ($err): ?><div class="alert alert-danger">⚠️ <?php echo htmlspecialchars($err); ?></div><?php endif; ?>

        <?php if ($student['password_status'] === 'PENDING'): ?>
            <div class="alert alert-warning">
                ⏳ <strong>Notice:</strong> Your password change request is currently <strong>Pending Approval</strong> from Admin <strong><?php echo htmlspecialchars($student['AdminID']); ?></strong>.
            </div>
        <?php endif; ?>

        <form method="POST" action="student_profile.php">
            <input type="hidden" name="action_update_profile" value="1">

            <!-- Locked Academic Details -->
            <div class="form-section">
                <h3>📌 Academic Records (Read Only)</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>User ID</label>
                        <input type="text" class="form-input read-only" value="<?php echo htmlspecialchars($student['userID']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Student ID (SID)</label>
                        <input type="text" class="form-input read-only" value="<?php echo htmlspecialchars($student['SID'] ?? 'Not Set'); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Official Email</label>
                        <input type="email" class="form-input read-only" value="<?php echo htmlspecialchars($student['email']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Current CGPA</label>
                        <input type="text" class="form-input read-only" value="<?php echo number_format((float)$student['cgpa'], 2); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Current Semester</label>
                        <input type="text" class="form-input read-only" value="<?php echo htmlspecialchars($student['semester'] ?? 'Spring 2026'); ?> (Sem <?php echo $student['current_semester_no']; ?>)" readonly>
                    </div>
                    <div class="form-group">
                        <label>COD Completed</label>
                        <input type="text" class="form-input read-only" value="<?php echo $student['completed_cod_credits']; ?> Credits Done" readonly>
                    </div>
                </div>
            </div>

            <!-- Editable Personal & Contact Details -->
            <div class="form-section">
                <h3>✏️ Personal & Contact Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" class="form-input" value="<?php echo htmlspecialchars($student['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Contact Phone</label>
                        <input type="text" name="phone" class="form-input" value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>" placeholder="017xxxxxxxx">
                    </div>
                    <div class="form-group">
                        <label>Road</label>
                        <input type="text" name="road" class="form-input" value="<?php echo htmlspecialchars($student['road']); ?>" placeholder="Road 11">
                    </div>
                    <div class="form-group">
                        <label>Area</label>
                        <input type="text" name="area" class="form-input" value="<?php echo htmlspecialchars($student['area']); ?>" placeholder="Banani">
                    </div>
                </div>
            </div>

            <!-- Password Change Section -->
            <div class="form-section">
                <h3>🔒 Security & Password Change</h3>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label>New Password (Leave blank to keep unchanged)</label>
                        <input type="password" name="new_password" class="form-input" placeholder="Type new password to request approval...">
                        <small class="help-text">Password changes require approval from your managing Admin (<?php echo htmlspecialchars($student['AdminID']); ?>).</small>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-update">Update Profile</button>
            </div>
        </form>

    </div>

</body>
</html>