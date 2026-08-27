<?php
session_start();
require_once 'admin_db.php';
if (!isset($_SESSION['admin_id'])) { header('Location: admin_login.php'); exit(); }

$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$message = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_course') {
        $coursecode = strtoupper(trim($_POST['coursecode'] ?? ''));
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $credit = (float)($_POST['credit'] ?? 0);
        $total_mark = (int)($_POST['total_mark'] ?? 0);

        if ($coursecode === '' || $name === '' || $description === '' || $credit <= 0 || $total_mark <= 0) {
            $error = 'Please fill in all course fields correctly.';
        } else {
            $stmt = $conn->prepare('INSERT INTO course (coursecode, name, description, credit, total_mark, admin) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('sssdis', $coursecode, $name, $description, $credit, $total_mark, $admin_id);
            if ($stmt->execute()) { $message = 'Course added successfully.'; }
            else { $error = ($stmt->errno === 1062) ? 'This course code already exists.' : 'Could not add the course.'; }
            $stmt->close();
        }
    }

    if ($action === 'delete_course') {
        $coursecode = trim($_POST['coursecode'] ?? '');
        $stmt = $conn->prepare('DELETE FROM course WHERE coursecode = ?');
        $stmt->bind_param('s', $coursecode);
        if ($stmt->execute() && $stmt->affected_rows > 0) { $message = 'Course deleted successfully.'; }
        else { $error = 'Course was not found or could not be deleted.'; }
        $stmt->close();
    }
}

$count_result = $conn->query('SELECT COUNT(*) AS total FROM course');
$total_courses = (int)$count_result->fetch_assoc()['total'];
$courses = $conn->query('SELECT coursecode, name, description, credit, total_mark, admin FROM course ORDER BY coursecode ASC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Admin Dashboard | Studyverse</title><link rel="stylesheet" href="admin.css">
</head>
<body class="dashboard-page">
<header class="topbar">
  <a class="brand" href="admin_dashboard.php"><span class="brand-dot">S</span>Studyverse <small>ADMIN</small></a>
  <div class="top-actions"><span>Welcome, <strong><?= htmlspecialchars($admin_name) ?></strong></span><a class="logout" href="admin_logout.php">Logout</a></div>
</header>
<main class="dashboard-wrap">
  <section class="hero-row">
    <div><p class="eyebrow">ADMIN CONTROL CENTER</p><h1>Course Management</h1><p class="muted">Add new courses or remove existing courses from Studyverse.</p></div>
    <div class="stat-card"><div class="stat-number"><?= $total_courses ?></div><div>Total Courses</div></div>
  </section>
  <?php if ($message): ?><div class="alert success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <section class="content-grid">
    <div class="panel add-panel">
      <div class="panel-heading"><div><span class="panel-icon">＋</span><h2>Add Course</h2></div><span class="tag">NEW COURSE</span></div>
      <form method="post" class="course-form">
        <input type="hidden" name="action" value="add_course">
        <div class="two-col">
          <div><label>Course Code</label><input name="coursecode" maxlength="10" placeholder="e.g. CSE370" required></div>
          <div><label>Course Name</label><input name="name" maxlength="100" placeholder="e.g. Database Systems" required></div>
        </div>
        <div><label>Description</label><textarea name="description" maxlength="500" rows="4" placeholder="Brief course description" required></textarea></div>
        <div class="two-col">
          <div><label>Credit</label><input name="credit" type="number" min="0.1" step="0.1" placeholder="3.0" required></div>
          <div><label>Total Mark</label><input name="total_mark" type="number" min="1" max="999" placeholder="100" required></div>
        </div>
        <button class="primary-btn" type="submit">Add Course <span>＋</span></button>
      </form>
    </div>

    <div class="panel courses-panel">
      <div class="panel-heading"><div><span class="panel-icon">▦</span><h2>All Courses</h2></div><span class="tag"><?= $total_courses ?> COURSES</span></div>
      <div class="table-wrap">
        <table><thead><tr><th>Code</th><th>Course</th><th>Credit</th><th>Mark</th><th>Admin</th><th>Action</th></tr></thead><tbody>
        <?php if ($courses && $courses->num_rows): while ($course = $courses->fetch_assoc()): ?>
          <tr><td><span class="code-pill"><?= htmlspecialchars($course['coursecode']) ?></span></td><td><strong><?= htmlspecialchars($course['name']) ?></strong><div class="desc"><?= htmlspecialchars($course['description']) ?></div></td><td><?= htmlspecialchars($course['credit']) ?></td><td><?= htmlspecialchars($course['total_mark']) ?></td><td><?= htmlspecialchars($course['admin']) ?></td><td><form method="post" onsubmit="return confirm('Delete this course? Related records may also be removed because of database cascade rules.');"><input type="hidden" name="action" value="delete_course"><input type="hidden" name="coursecode" value="<?= htmlspecialchars($course['coursecode']) ?>"><button class="delete-btn" type="submit">Delete</button></form></td></tr>
        <?php endwhile; else: ?><tr><td colspan="6" class="empty">No courses found. Add your first course.</td></tr><?php endif; ?>
        </tbody></table>
      </div>
    </div>
  </section>
</main>
</body></html>
