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
    <title>Student Dashboard - Studyverse</title>
    <link rel="stylesheet" href="student_dash.css">
</head>
<body>

    <!-- Header & Navigation Bar -->
    <header class="navbar">
        <div class="nav-container">
            <div class="brand">Studyverse</div>
            <nav class="nav-links">
                <a href="student_dashboard.php" class="active">Dashboard</a>
                <a href="student_profile.php">Profile</a>
                <a href="mycourses.php">My Courses</a>
                <a href="mygroups.php">My Groups</a>
                <a href="findmentor.php">Find Mentor</a>
                <a href="faculties.php">Faculties</a>
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
                <p>View and update your student ID, CGPA, semester, and personal details.</p>
                <a href="student_profile.php" class="card-btn">Edit Profile</a>
            </div>

            <!-- 2. My Courses -->
            <div class="card">
                <div class="card-icon">📚</div>
                <h3>My Courses</h3>
                <p>View your enrolled courses, course materials, and class announcements.</p>
                <a href="mycourses.php" class="card-btn">View Courses</a>
            </div>

            <!-- My Groups -->
            <div class="card">
                <div class="card-icon">👥</div>
                <h3>My Groups</h3>
                <p>Collaborate with classmates and join project and study groups.</p>
                <a href="mygroups.php" class="card-btn">Open Groups</a>
            </div>
          <!-- Find student  -->
            <div class="card">
                <div class="card-icon">🔍</div>
                <h3>Find Student</h3>
                <p>Search the student directory, view peer profiles, and connect with batchmates.</p>
                <a href="student_search_student.php" class="card-btn">Browse Students</a>
            </div>
            <!-- Find Mentor -->
            <div class="card">
                <div class="card-icon">🔍</div>
                <h3>Find Mentor</h3>
                <p>Search for seniors and top scorers to guide you through tough subjects.</p>
                <a href="findmentor.php" class="card-btn">Find Guidance</a>
            </div>

            <!-- Me as Mentor -->
            <div class="card">
                <div class="card-icon">🎓</div>
                <h3>Be a Mentor</h3>
                <p>Apply to become a mentor, post availability, and manage your mentees.</p>
                <a href="meMentor.php" class="card-btn">Mentorship Hub</a>
            </div>

            <!-- Schedule -->
            <div class="card">
                <div class="card-icon">🗓️</div>
                <h3>My Schedule</h3>
                <p>Check your weekly class timetable, exams, and scheduled study sessions.</p>
                <a href="myschedule.php" class="card-btn">View Routine</a>
            </div>

            <!-- Faculties -->
            <div class="card">
                <div class="card-icon">👨‍🏫</div>
                <h3>Faculties</h3>
                <p>Browse faculty directory, check office hours, and find contact info.</p>
                <a href="faculties.php" class="card-btn">Browse Faculty</a>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>© 2026 Studyverse. All rights reserved.</p>
    </footer>

</body>
</html>