<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['userID'];
$facultyCheck = $conn->prepare('SELECT userID FROM faculty_info WHERE userID = ?');
if (!$facultyCheck) {
    die('Unable to verify faculty access.');
}
$facultyCheck->bind_param('s', $userId);
$facultyCheck->execute();
$isFaculty = $facultyCheck->get_result()->num_rows > 0;
$facultyCheck->close();

if (!$isFaculty) {
    header('Location: student_dashboard.php');
    exit();
}

$conn->query("CREATE TABLE IF NOT EXISTS faculty_courselist (
    faculty_UID VARCHAR(8) NOT NULL,
    coursecode VARCHAR(10) NOT NULL
)");

$courseListColumns = [];
$columnResult = $conn->query('SHOW COLUMNS FROM faculty_courselist');
if ($columnResult) {
    while ($column = $columnResult->fetch_assoc()) {
        $courseListColumns[] = $column['Field'];
    }
}
if (!in_array('semester_name', $courseListColumns, true)) {
    $conn->query("ALTER TABLE faculty_courselist ADD COLUMN semester_name VARCHAR(20) NOT NULL DEFAULT 'Spring-2025'");
}

$courseSeed = [
    ['CSE 101', 'Introduction to Computer Science'],
    ['CSE 110', 'Programming Language I'],
    ['CSE 111', 'Programming Language II'],
    ['CSE 220', 'Data Structures'],
    ['CSE 221', 'Algorithms'],
    ['CSE 230', 'Discrete Mathematics'],
    ['CSE 250', 'Circuits and Electronics'],
    ['CSE 251', 'Electronic Devices and Circuits'],
    ['CSE 260', 'Digital Logic Design'],
    ['CSE 310', 'Object-Oriented Programming'],
    ['CSE 320', 'Data Communications'],
    ['CSE 321', 'Operating System'],
    ['CSE 330', 'Numerical Methods'],
    ['CSE 331', 'Automata and Computability'],
    ['CSE 340', 'Computer Architecture'],
    ['CSE 341', 'Microprocessors'],
    ['CSE 342', 'Computer Systems Engineering'],
    ['CSE 350', 'Digital Electronics and Pulse Techniques'],
    ['CSE 360', 'Computer Interfacing'],
    ['CSE 370', 'Database Systems'],
    ['CSE 371', 'Management Information Systems'],
    ['CSE 390', 'Technical Communication'],
    ['CSE 391', 'Programming for the Internet'],
    ['CSE 392', 'Signals and Systems'],
    ['CSE 410', 'Advance Programming In UNIX'],
    ['CSE 419', 'Programming Languages and Competitive Programming'],
    ['CSE 420', 'Compiler Design'],
    ['CSE 421', 'Computer Networks'],
    ['CSE 422', 'Artificial Intelligence'],
    ['CSE 423', 'Computer Graphics'],
    ['CSE 424', 'Pattern Recognition'],
    ['CSE 425', 'Neural Networks'],
    ['CSE 426', 'Advanced Algorithms'],
    ['CSE 427', 'Machine Learning'],
    ['CSE 428', 'Image Processing'],
    ['CSE 429', 'Basic Multimedia Theory'],
    ['CSE 430', 'Digital Signal Processing'],
    ['CSE 431', 'Natural Language Processing'],
    ['CSE 432', 'Speech Recognition and Synthesis'],
    ['CSE 460', 'VLSI Design'],
    ['CSE 461', 'Introduction to Robotics'],
    ['CSE 462', 'Fault-Tolerant Systems'],
    ['CSE 470', 'Software Engineering'],
    ['CSE 471', 'Systems Analysis and Design'],
    ['CSE 472', 'Human-Computer Interface'],
    ['CSE 473', 'Financial Engineering & Technology'],
    ['CSE 474', 'Simulation and Modeling'],
    ['CSE 490', 'Special Topics / WAN Routing and Technologies (Special Topics)'],
    ['CSE 491', 'Independent Study']
];

$seedStmt = $conn->prepare('INSERT INTO course (coursecode, name, description, credit, total_mark, admin) SELECT ?, ?, ?, 3, 100, ? WHERE NOT EXISTS (SELECT 1 FROM course WHERE coursecode = ? AND name = ?)');
if ($seedStmt) {
    $adminId = 'A1';
    foreach ($courseSeed as [$code, $name]) {
        $description = $name . ' (3 credits)';
        $seedStmt->bind_param('ssssss', $code, $name, $description, $adminId, $code, $name);
        $seedStmt->execute();
    }
    $seedStmt->close();
}

$successMessage = '';
$errorMessage = '';
$semesterPattern = '/^(Spring|Summer|Fall)-[0-9]{4}$/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $semester = trim($_POST['semester_name'] ?? '');
    $courseCode = trim($_POST['coursecode'] ?? '');

    if ($action === 'add_course') {
        if (!preg_match($semesterPattern, $semester)) {
            $errorMessage = 'Semester must use Spring-2025, Summer-2026, or Fall-2026 format.';
        } elseif ($courseCode === '') {
            $errorMessage = 'Please select a course.';
        } else {
            $courseCheck = $conn->prepare('SELECT coursecode FROM course WHERE coursecode = ?');
            $insert = $conn->prepare('INSERT INTO faculty_courselist (faculty_UID, coursecode, semester_name) SELECT ?, ?, ? WHERE EXISTS (SELECT 1 FROM course WHERE coursecode = ?) AND NOT EXISTS (SELECT 1 FROM faculty_courselist WHERE faculty_UID = ? AND coursecode = ? AND semester_name = ?)');
            if (!$courseCheck || !$insert) {
                $errorMessage = 'Unable to add the course.';
            } else {
                $courseCheck->bind_param('s', $courseCode);
                $courseCheck->execute();
                if ($courseCheck->get_result()->num_rows === 0) {
                    $errorMessage = 'Selected course was not found.';
                } else {
                    $insert->bind_param('sssssss', $userId, $courseCode, $semester, $courseCode, $userId, $courseCode, $semester);
                    if ($insert->execute() && $insert->affected_rows > 0) {
                        $successMessage = 'Course added to ' . $semester . '.';
                    } else {
                        $errorMessage = 'This course is already added for that semester.';
                    }
                }
                $courseCheck->close();
                $insert->close();
            }
        }
    } elseif ($action === 'rename_semester') {
        $oldSemester = trim($_POST['old_semester'] ?? '');
        if (!preg_match($semesterPattern, $semester) || $oldSemester === '') {
            $errorMessage = 'Please provide a valid semester name.';
        } else {
            $rename = $conn->prepare('UPDATE faculty_courselist SET semester_name = ? WHERE faculty_UID = ? AND semester_name = ?');
            if ($rename) {
                $rename->bind_param('sss', $semester, $userId, $oldSemester);
                $successMessage = $rename->execute() ? 'Semester updated successfully.' : 'Failed to update semester.';
                $rename->close();
            }
        }
    } elseif ($action === 'remove_course') {
        $remove = $conn->prepare('DELETE FROM faculty_courselist WHERE faculty_UID = ? AND coursecode = ? AND semester_name = ?');
        if ($remove) {
            $remove->bind_param('sss', $userId, $courseCode, $semester);
            $successMessage = $remove->execute() ? 'Course removed successfully.' : 'Failed to remove course.';
            $remove->close();
        }
    }
}

$search = trim($_GET['search'] ?? '');
$courseOptions = [];
$courseQuery = $conn->prepare('SELECT coursecode, name FROM course WHERE REPLACE(coursecode, " ", "") LIKE REPLACE(?, " ", "") OR name LIKE ? ORDER BY coursecode, name');
if ($courseQuery) {
    $searchPattern = '%' . $search . '%';
    $courseQuery->bind_param('ss', $searchPattern, $searchPattern);
    $courseQuery->execute();
    $courseResult = $courseQuery->get_result();
    while ($row = $courseResult->fetch_assoc()) {
        $courseOptions[] = $row;
    }
    $courseQuery->close();
}

$selectedCourses = [];
$selectedQuery = $conn->prepare('SELECT fc.coursecode, c.name, fc.semester_name FROM faculty_courselist fc JOIN course c ON c.coursecode = fc.coursecode WHERE fc.faculty_UID = ? ORDER BY fc.semester_name DESC, c.coursecode, c.name');
if ($selectedQuery) {
    $selectedQuery->bind_param('s', $userId);
    $selectedQuery->execute();
    $selectedResult = $selectedQuery->get_result();
    while ($row = $selectedResult->fetch_assoc()) {
        $selectedCourses[$row['semester_name']][] = $row;
    }
    $selectedQuery->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Studyverse</title>
    <link rel="stylesheet" href="faculty_dash.css">
    <link rel="stylesheet" href="mycourses.css">
</head>
<body>
    <header class="navbar">
        <div class="nav-container">
            <div class="brand">Studyverse</div>
            <nav class="nav-links">
                <a href="faculty_dashboard.php">Dashboard</a>
                <a href="faculty_profile.php">Profile</a>
                <a href="mycourses.php" class="active">My Courses</a>
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
            <h2>My Courses</h2>
            <p>Select the courses you are teaching and organize them by semester.</p>
        </section>

        <?php if ($successMessage !== ''): ?><div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div><?php endif; ?>
        <?php if ($errorMessage !== ''): ?><div class="alert alert-error"><?php echo htmlspecialchars($errorMessage); ?></div><?php endif; ?>

        <section class="course-layout">
            <div class="panel">
                <h3>Add teaching course</h3>
                <form method="GET" class="search-form">
                    <input type="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by course code or name">
                    <button type="submit" class="secondary-btn">Search</button>
                    <?php if ($search !== ''): ?><a href="mycourses.php" class="clear-link">Clear</a><?php endif; ?>
                </form>
                <form method="POST" class="course-form">
                    <input type="hidden" name="action" value="add_course">
                    <label>Semester
                        <input type="text" name="semester_name" placeholder="Spring-2025" pattern="(Spring|Summer|Fall)-[0-9]{4}" required>
                    </label>
                    <label>Course
                        <select name="coursecode" required>
                            <option value="">Select a course</option>
                            <?php foreach ($courseOptions as $course): ?>
                                <option value="<?php echo htmlspecialchars($course['coursecode']); ?>"><?php echo htmlspecialchars($course['coursecode'] . ' - ' . $course['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <button type="submit" class="primary-btn">Add Course</button>
                </form>
            </div>

            <div class="panel">
                <h3>Chosen courses</h3>
                <?php if (empty($selectedCourses)): ?>
                    <p class="empty-state">No teaching courses selected yet.</p>
                <?php else: ?>
                    <?php foreach ($selectedCourses as $semester => $courses): ?>
                        <div class="semester-section">
                            <div class="semester-heading">
                                <h4><?php echo htmlspecialchars($semester); ?></h4>
                                <form method="POST" class="rename-form">
                                    <input type="hidden" name="action" value="rename_semester">
                                    <input type="hidden" name="old_semester" value="<?php echo htmlspecialchars($semester); ?>">
                                    <input type="text" name="semester_name" value="<?php echo htmlspecialchars($semester); ?>" pattern="(Spring|Summer|Fall)-[0-9]{4}" required>
                                    <button type="submit" class="small-btn">Update</button>
                                </form>
                            </div>
                            <div class="chosen-list">
                                <?php foreach ($courses as $course): ?>
                                    <div class="chosen-course">
                                        <span><strong><?php echo htmlspecialchars($course['coursecode']); ?></strong> <?php echo htmlspecialchars($course['name']); ?></span>
                                        <form method="POST">
                                            <input type="hidden" name="action" value="remove_course">
                                            <input type="hidden" name="coursecode" value="<?php echo htmlspecialchars($course['coursecode']); ?>">
                                            <input type="hidden" name="semester_name" value="<?php echo htmlspecialchars($semester); ?>">
                                            <button type="submit" class="remove-btn">Remove</button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <footer class="footer"><p>© 2026 Studyverse. All rights reserved.</p></footer>
</body>
</html>
