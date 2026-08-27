 
 <?php
session_start();
include 'db.php';

// Check if user is logged in
if (isset($_SESSION['userID'])) {
    $user_id = $_SESSION['userID'];
} else {
    header("Location: login.php");
    exit();
}

// SQL query joining user_info and student_info
$sql = "SELECT user_info.name, user_info.userID, student_info.SID, student_info.dept 
        FROM user_info 
        JOIN student_info ON user_info.userID = student_info.userID";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Student - Studyverse</title>
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
                <span class="user-name">ID: <?php echo $user_id; ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="welcome-banner">
            <h2>Student Directory</h2>
            <p>List of all registered students in the system.</p>
        </div>

        <!-- Student Table Section -->
        <div class="table-card">
            <table class="students-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>User ID</th>
                        <th>Student ID (SID)</th>
                        <th>Department</th>
                        <th style="text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $name = $row['name'];
                            $uid  = $row['userID'];
                            $sid  = $row['SID'];
                            $dept = $row['dept'];

                            if ($name == "" || $name == null) {
                                $name = "Not Set";
                            }
                            if ($sid == "" || $sid == null) {
                                $sid = "Not Set";
                            }
                            if ($dept == "" || $dept == null) {
                                $dept = "Not Set";
                            }
                    ?>
                            <tr>
                                <td class="student-name-cell">🎓 <?php echo $name; ?></td>
                                <td><?php echo $uid; ?></td>
                                <td><?php echo $sid; ?></td>
                                <td><?php echo $dept; ?></td>
                                <td style="text-align: center;">
                                    <button type="button" class="btn-report-small" onclick="alert('Report submitted for Admin review.');">
                                        ⚠️ Report
                                    </button>
                                </td>
                            </tr>
                    <?php
                        }
                    } else {
                    ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #64748b; padding: 24px;">
                                No student records found.
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