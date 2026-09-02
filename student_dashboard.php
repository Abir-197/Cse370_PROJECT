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
    <title>Student Dashboard — Studyverse</title>
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
                <a href="Student_SeeAllCourse.php">All Courses</a>
                <a href="StudentCourses.php">My Courses</a>
                <a href="SeeAllContent.php">Resources</a>
                <a href="Student_UgPlanner.php">UG Planner</a>
                <a href="mygroups.php">Groups</a>
                <a href="StudentMentor.php">Mentors</a>
                <a href="StudentSearchFaculty.php">Faculties</a>
                <a href="student_consultation_requests.php">Consultations</a>
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
            <h2>Welcome back, <?php echo htmlspecialchars($user_id); ?>!</h2>
            <p>Access your degree planner, course catalog, peer circles, project submissions, and consultation schedules.</p>
        </section>

        <!-- Dashboard Cards Grid (All 14 Cards Retained) -->
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
                <div class="card-icon">📖</div>
                <h3>My Courses</h3>
                <p>View your enrolled courses, course materials, and class announcements.</p>
                <a href="StudentCourses.php" class="card-btn">View Courses</a>
            </div>

            <!-- 3. All Courses -->
            <div class="card">
                <div class="card-icon">📚</div>
                <h3>All Courses</h3>
                <p>Explore complete course catalog, check hard/soft prerequisites, and dynamic peer reviews.</p>
                <a href="Student_SeeAllCourse.php" class="card-btn">Browse Catalog</a>
            </div>

            <!-- 4. Course Content & Resources -->
            <div class="card">
                <div class="card-icon">📁</div>
                <h3>Course Content</h3>
                <p>Access lecture notes, past exam questions, slides, and leave resource reviews.</p>
                <a href="SeeAllContent.php" class="card-btn">Browse Content</a>
            </div>

           

            <!-- 6. My Groups -->
            <div class="card">
                <div class="card-icon">👥</div>
                <h3>My Groups</h3>
                <p>Collaborate with classmates and join project and study circles.</p>
                <a href="mygroups.php" class="card-btn">Open Groups</a>
            </div>

            <!-- 7. Find Student -->
            <div class="card">
                <div class="card-icon">🔍</div>
                <h3>Find Student</h3>
                <p>Search student directory, view peer profiles, and connect with batchmates.</p>
                <a href="student_search_student.php" class="card-btn">Browse Students</a>
            </div>

           
            </div>

            <!-- 9. Be a Mentor (Peer Mentorship Hub) -->
            <div class="card">
                <div class="card-icon">🎓</div>
                <h3>Mentor</h3>
                <p>Register as a mentor, manage your taught course, accept requests, and schedule sessions.</p>
                <a href="StudentMentor.php" class="card-btn">Mentorship Hub</a>
            </div>

            <!-- 10. Schedule -->
            <div class="card">
                <div class="card-icon">🗓️</div>
                <h3>My Schedule</h3>
                <p>Check your weekly class timetable, exams, and scheduled study sessions.</p>
                <a href="myschedule.php" class="card-btn">View Routine</a>
            </div>

            <!-- 11. Faculties -->
            <div class="card">
                <div class="card-icon">👨‍🏫</div>
                <h3>Faculty Directory</h3>
                <p>Browse faculty directory, check initials, office hours, and contact info.</p>
                <a href="StudentSearchFaculty.php" class="card-btn">Browse Faculty</a>
            </div>

            <!-- 12. Consultation Requests -->
            <div class="card">
                <div class="card-icon">📅</div>
                <h3>Faculty Consultations</h3>
                <p>Request consultation slots from professors and track your appointment status.</p>
                <a href="student_consultation_requests.php" class="card-btn">Request Consultation</a>
            </div>

            <!-- 13. My Works -->
            <div class="card">
                <div class="card-icon">🧩</div>
                <h3>My Works</h3>
                <p>Upload projects, research papers, and journals for faculty review and feedback.</p>
                <a href="my_works.php" class="card-btn">Upload Work</a>
            </div>

          
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>© 2026 Studyverse. All rights reserved.</p>
    </footer>

</body>
</html>