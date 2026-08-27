<?php
session_start();
include 'db.php';

if (!isset($_SESSION['adminID'])) {
    header("Location: admin_login.php");
    exit();
}

$admin_id = $_SESSION['adminID'];
$admin_name = $_SESSION['adminName'] ?? 'Administrator';
$message = "";
$error = "";

/* Add course */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    $coursecode = strtoupper(trim($_POST['coursecode'] ?? ''));
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $credit = (float)($_POST['credit'] ?? 0);
    $total_mark = (int)($_POST['total_mark'] ?? 0);

    if ($coursecode === '' || $name === '' || $description === '' || $credit <= 0 || $total_mark <= 0) {
        $error = "Please fill in all course fields correctly.";
    } elseif (strlen($coursecode) > 10 || strlen($name) > 100 || strlen($description) > 500) {
        $error = "One or more fields exceed the database limit.";
    } else {
        $check = mysqli_prepare($conn, "SELECT coursecode FROM course WHERE coursecode = ? LIMIT 1");
        mysqli_stmt_bind_param($check, "s", $coursecode);
        mysqli_stmt_execute($check);
        $check_result = mysqli_stmt_get_result($check);

        if (mysqli_num_rows($check_result) > 0) {
            $error = "Course code already exists.";
        } else {
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO course (coursecode, name, description, credit, total_mark, admin)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt, "sssdis", $coursecode, $name, $description, $credit, $total_mark, $admin_id);

            if (mysqli_stmt_execute($stmt)) {
                $message = "Course " . $coursecode . " was added successfully.";
            } else {
                $error = "Could not add course: " . mysqli_error($conn);
            }
        }
    }
}

/* Delete course */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_course'])) {
    $coursecode = strtoupper(trim($_POST['coursecode'] ?? ''));

    if ($coursecode === '') {
        $error = "Invalid course code.";
    } else {
        $stmt = mysqli_prepare($conn, "DELETE FROM course WHERE coursecode = ?");
        mysqli_stmt_bind_param($stmt, "s", $coursecode);

        if (mysqli_stmt_execute($stmt)) {
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                $message = "Course " . $coursecode . " was deleted successfully.";
            } else {
                $error = "Course not found.";
            }
        } else {
            $error = "Could not delete course: " . mysqli_error($conn);
        }
    }
}

/* Dashboard data */
$count_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM course");
$total_courses = (int)(mysqli_fetch_assoc($count_result)['total'] ?? 0);

$courses = mysqli_query(
    $conn,
    "SELECT coursecode, name, description, credit, total_mark, admin
     FROM course ORDER BY coursecode ASC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Studyverse</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-page">

<header class="topbar">
    <a class="brand" href="admin_dashboard.php">
        <span class="brand-mark small">S</span>
        <span>Studyverse</span>
    </a>

    <div class="topbar-right">
        <div class="admin-user">
            <span class="avatar"><?= htmlspecialchars(strtoupper(substr($admin_name, 0, 1))) ?></span>
            <div>
                <strong><?= htmlspecialchars($admin_name) ?></strong>
                <small><?= htmlspecialchars($admin_id) ?> · Administrator</small>
            </div>
        </div>
        <a class="logout-btn" href="admin_logout.php">Logout</a>
    </div>
</header>

<main class="dashboard">
    <section class="hero">
        <div>
            <span class="eyebrow">ADMIN COMMAND CENTER</span>
            <h1>Good to see you, <?= htmlspecialchars($admin_name) ?>.</h1>
            <p>Manage the Studyverse course catalog from one simple dashboard.</p>
        </div>
        <div class="hero-stat">
            <span>Total Courses</span>
            <strong><?= $total_courses ?></strong>
        </div>
    </section>

    <?php if ($message): ?>
        <div class="alert success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <section class="content-grid">
        <div class="panel add-panel">
            <div class="panel-title">
                <div>
                    <span class="panel-icon">＋</span>
                    <h2>Add New Course</h2>
                </div>
                <span class="badge">Course Management</span>
            </div>

            <form method="POST" class="course-form">
                <div class="field-row">
                    <div>
                        <label>Course Code</label>
                        <input type="text" name="coursecode" maxlength="10"
                               placeholder="e.g. CSE370" required>
                    </div>
                    <div>
                        <label>Course Name</label>
                        <input type="text" name="name" maxlength="100"
                               placeholder="e.g. Database Systems" required>
                    </div>
                </div>

                <label>Description</label>
                <textarea name="description" maxlength="500"
                          placeholder="Write a short course description..." required></textarea>

                <div class="field-row three">
                    <div>
                        <label>Credit</label>
                        <input type="number" name="credit" min="0.1" step="0.1"
                               placeholder="3.0" required>
                    </div>
                    <div>
                        <label>Total Mark</label>
                        <input type="number" name="total_mark" min="1" max="999"
                               placeholder="100" required>
                    </div>
                    <div class="submit-wrap">
                        <button class="primary-btn" type="submit" name="add_course">
                            Add Course
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="panel">
            <div class="panel-title">
                <div>
                    <span class="panel-icon">📚</span>
                    <h2>Course Catalog</h2>
                </div>
                <span class="count-badge"><?= $total_courses ?> courses</span>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Code</th>
                        <th>Course</th>
                        <th>Credit</th>
                        <th>Marks</th>
                        <th>Added By</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($courses && mysqli_num_rows($courses) > 0): ?>
                        <?php while ($course = mysqli_fetch_assoc($courses)): ?>
                            <tr>
                                <td><span class="code-pill"><?= htmlspecialchars($course['coursecode']) ?></span></td>
                                <td>
                                    <strong><?= htmlspecialchars($course['name']) ?></strong>
                                    <small><?= htmlspecialchars($course['description']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($course['credit']) ?></td>
                                <td><?= htmlspecialchars($course['total_mark']) ?></td>
                                <td><?= htmlspecialchars($course['admin']) ?></td>
                                <td>
                                    <form method="POST" onsubmit="return confirm('Delete this course? Related records may also be removed because of database cascade rules.');">
                                        <input type="hidden" name="coursecode" value="<?= htmlspecialchars($course['coursecode']) ?>">
                                        <button class="delete-btn" type="submit" name="delete_course">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="empty">No courses found. Add your first course above.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</main>
</body>
</html>
