  <?php
session_start();
include 'db.php';


if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['userID'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard - Studyverse</title>
    <link rel="stylesheet" href="faculty_dash.css">
</head>
<body>

    <!-- Header & Navigation Bar -->
    <header class="navbar">
        <div class="nav-container">
            <div class="brand">Studyverse</div>
            <nav class="nav-links">
                <a href="faculty_dashboard.php" class="active">Dashboard</a>
                <a href="faculty_profile.php">Profile</a>
                <a href="mycourses.php">My Courses</a>
                <a href="resources.php">Resources</a>
                <a href="faculty_consultation_requests.php">Consultations</a>
                <a href="rate_projects.php">Projects & Papers</a>
            </nav>
            <div class="nav-user">
                <span class="user-name">ID: <?php echo htmlspecialchars($user_id); ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="main-content">
        <section class="welcome-banner">
            <h2>Welcome back, <?php echo $user_id; ?>!</h2>
            <p>Access your enrolled courses, study circles, mentorship programs, and daily schedule.</p>
        </section>

        <!-- Dashboard Cards Grid -->
        <section class="dashboard-grid">
            <!-- 1. Academic Profile -->
            <div class="card">
                <div class="card-icon">👤</div>
                <h3>My Profile</h3>
                <p>View your registration details and update your faculty bio.</p>
                <a href="faculty_profile.php" class="card-btn">View Profile</a>
            </div>

            <!-- 2. My Courses -->
            <div class="card">
                <div class="card-icon">📚</div>
                <h3>My Courses</h3>
                <p>View your enrolled courses, course materials, and class announcements.</p>
                <a href="mycourses.php" class="card-btn">View Courses</a>
            </div>

            <!-- Faculty Resources -->
            <div class="card">
                <div class="card-icon">📁</div>
                <h3>Faculty Resources</h3>
                <p>Upload course content, class notes, previous quiz questions, assignments, and other teaching materials.</p>
                <a href="resources.php" class="card-btn">Open Resource Hub</a>
            </div>
            <!-- 4. Consultation Requests -->
            <div class="card">
                <div class="card-icon">📅</div>
                <h3>Consultation Requests</h3>
               <p>Set your available time slots and review student consultation requests.</p>
               <a href="faculty_consultation_requests.php" class="card-btn">Manage Requests</a>
           </div>
            <!-- 5. Projects & Papers -->
                <div class="card">
                <div class="card-icon">📝</div>
                <h3>Projects & Papers</h3>
                <p>Review student submissions, rate their work, and leave feedback or notes.</p>
                <a href="rate_projects.php" class="card-btn">Rate & Review</a>
            </div>
            
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>© 2026 Studyverse. All rights reserved.</p>
    </footer>

</body>
</html>