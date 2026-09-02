<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['userID'];
$successMessage = '';
$errorMessage = '';

$conn->query("CREATE TABLE IF NOT EXISTS faculty_info (
    userID VARCHAR(8) PRIMARY KEY
)");

$facultyColumns = [];
$columnResult = $conn->query('SHOW COLUMNS FROM faculty_info');
if ($columnResult) {
    while ($column = $columnResult->fetch_assoc()) {
        $facultyColumns[] = $column['Field'];
    }
}

if (!in_array('bio', $facultyColumns, true)) {
    $conn->query('ALTER TABLE faculty_info ADD COLUMN bio TEXT NULL');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_bio'])) {
    $bio = trim($_POST['bio'] ?? '');
    $bioStmt = $conn->prepare('UPDATE faculty_info SET bio = ? WHERE userID = ?');
    if (!$bioStmt) {
        $errorMessage = 'Unable to prepare the bio update.';
    } else {
        $bioStmt->bind_param('ss', $bio, $userId);
        if ($bioStmt->execute()) {
            $successMessage = 'Your bio was updated successfully.';
        } else {
            $errorMessage = 'Failed to update your bio: ' . $bioStmt->error;
        }
        $bioStmt->close();
    }
}

$profile = [
    'name' => '',
    'email' => '',
    'road' => '',
    'area' => '',
    'phone' => '',
    'bio' => ''
];

$profileStmt = $conn->prepare('SELECT ui.name, ui.email, ui.road, ui.area, up.phone, fi.bio
    FROM user_info ui
    LEFT JOIN userphone up ON up.userID = ui.userID
    LEFT JOIN faculty_info fi ON fi.userID = ui.userID
    WHERE ui.userID = ?');
if ($profileStmt) {
    $profileStmt->bind_param('s', $userId);
    $profileStmt->execute();
    $profileRow = $profileStmt->get_result()->fetch_assoc();
    if ($profileRow) {
        $profile = array_merge($profile, $profileRow);
    }
    $profileStmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Profile - Studyverse</title>
    <link rel="stylesheet" href="faculty_dash.css">
    <link rel="stylesheet" href="faculty_profile.css">
</head>
<body>
    <header class="navbar">
        <div class="nav-container">
            <div class="brand">Studyverse</div>
            <nav class="nav-links">
                <a href="faculty_dashboard.php">Dashboard</a>
                <a href="faculty_profile.php" class="active">Profile</a>
                <a href="mycourses.php">My Courses</a>
                <a href="resources.php">Resources</a>
                <a href="faculty_consultation_requests.php">Consultations</a>
                <a href="rate_projects.php">Projects & Papers</a>
            </nav>
            <div class="nav-user">
                <span class="user-name">ID: <?php echo htmlspecialchars($userId); ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <section class="welcome-banner">
            <h2>My Profile</h2>
            <p>Your registration information is shown below. Update your bio whenever you want.</p>
        </section>

        <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        <?php if ($errorMessage !== ''): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <section class="profile-grid">
            <div class="profile-card">
                <h3>Registration details</h3>
                <div class="detail-list">
                    <div><span>Faculty ID</span><strong><?php echo htmlspecialchars($userId); ?></strong></div>
                    <div><span>Full name</span><strong><?php echo htmlspecialchars($profile['name']); ?></strong></div>
                    <div><span>Email</span><strong><?php echo htmlspecialchars($profile['email']); ?></strong></div>
                    <div><span>Phone</span><strong><?php echo htmlspecialchars($profile['phone'] ?: 'Not provided'); ?></strong></div>
                    <div><span>Address</span><strong><?php echo htmlspecialchars(trim($profile['road'] . ', ' . $profile['area'], ', ')); ?></strong></div>
                </div>
            </div>

            <div class="profile-card">
                <h3>Bio</h3>
                <form method="POST" class="bio-form">
                    <label for="bio">Tell students about your expertise and teaching interests</label>
                    <textarea id="bio" name="bio" maxlength="2000" placeholder="Write a short professional bio..."><?php echo htmlspecialchars($profile['bio']); ?></textarea>
                    <button type="submit" name="save_bio" class="primary-btn">Save Bio</button>
                </form>
            </div>
        </section>
    </main>

    <footer class="footer">
        <p>© 2026 Studyverse. All rights reserved.</p>
    </footer>
</body>
</html>