 
<?php
session_start();
include 'db.php';

// Authentication Guard
if (!isset($_SESSION['userID']) || empty($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['userID'];
$msg = '';
$err = '';

// 1. Fetch Logged-in Student Context
$current_student_q = mysqli_query($conn, "
    SELECT s.*, u.name 
    FROM student_info s 
    JOIN user_info u ON s.userID = u.userID 
    WHERE s.userID = '$user_id'
");
$current_student = mysqli_fetch_assoc($current_student_q);
$current_sem = $current_student['semester'] ?? 'Spring 2026';

// Fetch enrolled courses for the logged-in student in the current semester
$my_courses = [];
$my_courses_q = mysqli_query($conn, "
    SELECT coursecode 
    FROM course_student_took 
    WHERE userID = '$user_id' AND semester_taken = '$current_sem'
");
while ($c_row = mysqli_fetch_assoc($my_courses_q)) {
    $my_courses[] = $c_row['coursecode'];
}
$my_courses_list = !empty($my_courses) ? "'" . implode("','", array_map([$conn, 'real_escape_string'], $my_courses)) . "'" : "''";

// 2. Handle "Request Mentorship" Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_request_mentorship'])) {
    $mentor_uid  = mysqli_real_escape_string($conn, $_POST['mentor_student_uid']);
    $mentor_id   = mysqli_real_escape_string($conn, $_POST['mentor_id']);
    $time_sched  = mysqli_real_escape_string($conn, $_POST['timeschedule']);

    $insert_consult = "INSERT INTO student_consult_mentor (student_uID, mentor_studentID, mentorID, timeschedule) 
                       VALUES ('$user_id', '$mentor_uid', '$mentor_id', '$time_sched')";
    if (mysqli_query($conn, $insert_consult)) {
        $msg = "Mentorship consultation requested successfully!";
    } else {
        $err = "Could not book mentorship session: " . mysqli_error($conn);
    }
}

// 3. Filters & Search Parameters
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_type  = isset($_GET['filter']) ? trim($_GET['filter']) : 'all';
$selected_sem = isset($_GET['semester']) ? trim($_GET['semester']) : $current_sem;

// Base condition: Exclude logged-in user from the directory
$where_clauses = ["u.userID != '$user_id'"];

// Keyword Search
if ($search_query !== '') {
    $escaped_search = mysqli_real_escape_string($conn, $search_query);
    $where_clauses[] = "(u.name LIKE '%$escaped_search%' OR u.userID LIKE '%$escaped_search%' OR s.SID LIKE '%$escaped_search%')";
}

// Filter Routing Engine
if ($filter_type === 'same_courses') {
    if (!empty($my_courses)) {
        $where_clauses[] = "u.userID IN (
            SELECT DISTINCT userID 
            FROM course_student_took 
            WHERE coursecode IN ($my_courses_list) 
              AND semester_taken = '" . mysqli_real_escape_string($conn, $selected_sem) . "'
              AND userID != '$user_id'
        )";
    } else {
        $where_clauses[] = "1=0";
    }
} elseif ($filter_type === 'mentors_high_cgpa') {
    $where_clauses[] = "s.cgpa > 3.70";
    $where_clauses[] = "u.userID IN (SELECT DISTINCT userID FROM mentor)";
    if (!empty($my_courses)) {
        $where_clauses[] = "u.userID IN (
            SELECT DISTINCT m.userID 
            FROM mentor m 
            WHERE m.Subject IN ($my_courses_list)
        )";
    }
}

$where_sql = implode(' AND ', $where_clauses);

// 4. Directory Query
$sql = "SELECT 
            u.name, 
            u.userID, 
            s.SID, 
            s.cgpa, 
            s.semester,
            m.MentorID,
            m.Subject AS mentor_subject,
            (SELECT GROUP_CONCAT(cst.coursecode SEPARATOR ', ') 
             FROM course_student_took cst 
             WHERE cst.userID = u.userID AND cst.semester_taken = '" . mysqli_real_escape_string($conn, $selected_sem) . "') AS enrolled_courses
        FROM user_info u
        JOIN student_info s ON u.userID = s.userID
        LEFT JOIN mentor m ON u.userID = m.userID
        WHERE $where_sql
        GROUP BY u.userID, m.MentorID
        ORDER BY s.cgpa DESC, u.name ASC";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Student & Mentors - Studyverse</title>
    <link rel="stylesheet" href="student_dash.css">
    <link rel="stylesheet" href="student_search_student.css">
</head>
<body>

    <!-- Navigation Bar -->
    <header class="navbar">
        <div class="nav-container">
            <div class="brand">Studyverse</div>
            <nav class="nav-links">
                <a href="student_dashboard.php">Dashboard</a>
                <a href="student_profile.php">Profile</a>
                <a href="student_search_student.php" class="active">Find Student</a>
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

    <!-- Main Content -->
    <main class="main-content">
        <div class="welcome-banner">
            <h2>🎓 Student & Mentor Directory</h2>
            <p>Discover classmates sharing your courses or find high-achieving mentors (CGPA &gt; 3.7) for subject guidance.</p>
        </div>

        <?php if ($msg): ?><div class="alert-box alert-success">✅ <?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
        <?php if ($err): ?><div class="alert-box alert-danger">⚠️ <?php echo htmlspecialchars($err); ?></div><?php endif; ?>

        <!-- Search & Filter Controls -->
        <div class="filter-card">
            <form method="GET" action="student_search_student.php">
                <div class="filter-form-grid">
                    <div>
                        <input type="text" name="search" class="form-input" 
                               placeholder="🔍 Search name, SID, or User ID..." 
                               value="<?php echo htmlspecialchars($search_query); ?>">
                    </div>
                    <div>
                        <select name="filter" class="form-select" onchange="this.form.submit()">
                            <option value="all" <?php echo $filter_type === 'all' ? 'selected' : ''; ?>>🌐 All Other Students</option>
                            <option value="same_courses" <?php echo $filter_type === 'same_courses' ? 'selected' : ''; ?>>📚 Enrolled in Same Courses (<?php echo htmlspecialchars($selected_sem); ?>)</option>
                            <option value="mentors_high_cgpa" <?php echo $filter_type === 'mentors_high_cgpa' ? 'selected' : ''; ?>>🌟 Mentors with CGPA &gt; 3.7 (For My Courses)</option>
                        </select>
                    </div>
                    <div>
                        <input type="text" name="semester" class="form-input" 
                               placeholder="Semester" value="<?php echo htmlspecialchars($selected_sem); ?>">
                    </div>
                    <div>
                        <button type="submit" class="btn-apply">Filter</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Student & Mentor Directory Table -->
        <div class="table-card">
            <table class="students-table">
                <thead>
                    <tr>
                        <th>Student Name & Role</th>
                        <th>User ID</th>
                        <th>Student ID</th>
                        <th>CGPA</th>
                        <th>Enrolled / Mentored Courses</th>
                        <th style="text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $name      = $row['name'] ?: "Not Set";
                            $uid       = $row['userID'];
                            $sid       = $row['SID'] ?: "Not Set";
                            $cgpa      = $row['cgpa'] !== null ? number_format((float)$row['cgpa'], 2) : "N/A";
                            $is_mentor = !empty($row['MentorID']);
                            $enrolled  = $row['enrolled_courses'] ?: "No records for $selected_sem";
                    ?>
                            <tr>
                                <td class="student-name-cell">
                                    <strong>🎓 <?php echo htmlspecialchars($name); ?></strong>
                                    <?php if ($is_mentor): ?>
                                        <span class="badge-mentor">Mentor: <?php echo htmlspecialchars($row['mentor_subject']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($uid); ?></td>
                                <td><?php echo htmlspecialchars($sid); ?></td>
                                <td>
                                    <span class="badge-cgpa"><?php echo $cgpa; ?></span>
                                </td>
                                <td>
                                    <?php if ($is_mentor): ?>
                                        <strong style="color:#2563eb;">Mentoring:</strong> <?php echo htmlspecialchars($row['mentor_subject']); ?>
                                        <div style="font-size: 11px; color:#64748b;">Enrolled: <?php echo htmlspecialchars($enrolled); ?></div>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($enrolled); ?>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($is_mentor): ?>
                                        <form method="POST" action="student_search_student.php" style="display:inline-block; margin:0;">
                                            <input type="hidden" name="action_request_mentorship" value="1">
                                            <input type="hidden" name="mentor_student_uid" value="<?php echo htmlspecialchars($uid); ?>">
                                            <input type="hidden" name="mentor_id" value="<?php echo htmlspecialchars($row['MentorID']); ?>">
                                            <input type="hidden" name="timeschedule" value="<?php echo date('Y-m-d H:i:s', strtotime('+1 day 14:00:00')); ?>">
                                            <button type="submit" class="btn-mentor-req">
                                                🤝 Ask Mentorship
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button type="button" class="btn-report-small">⚠️ Report</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                    <?php
                        }
                    } else {
                    ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #64748b; padding: 28px;">
                                No student or mentor records found matching the selected filter criteria.
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer class="footer">
        <p>© 2026 Studyverse. All rights reserved.</p>
    </footer>

</body>
</html>