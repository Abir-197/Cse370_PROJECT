
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

$student_id = $_SESSION['userID'] ?? 'ZS22320';

// Capture Search & Filter Inputs
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter = isset($_GET['filter']) ? trim($_GET['filter']) : 'all';

// Base Conditions
$where_clauses  = ["1=1"];
$having_clauses = [];
$order_by       = "c.coursecode ASC";

// 1. Search Bar Logic
if ($search !== '') {
    $escaped_search  = mysqli_real_escape_string($conn, $search);
    $where_clauses[] = "(c.coursecode LIKE '%$escaped_search%' OR c.name LIKE '%$escaped_search%' OR c.dept LIKE '%$escaped_search%')";
}

// 2. Dropdown Filter Routing Engine
switch ($filter) {
    case 'done':
        $where_clauses[] = "c.coursecode IN (
            SELECT coursecode FROM course_student_took WHERE userID = '$student_id' AND is_completed = 1
        )";
        break;

    case 'eligible':
        $where_clauses[] = "c.coursecode NOT IN (
            SELECT coursecode FROM course_student_took WHERE userID = '$student_id' AND is_completed = 1
        )";
        $where_clauses[] = "NOT EXISTS (
            SELECT 1 FROM course_prerequisites cp
            WHERE cp.coursecode = c.coursecode
              AND cp.prereq_type = 'HARD'
              AND cp.prereq_coursecode NOT IN (
                  SELECT coursecode FROM course_student_took WHERE userID = '$student_id' AND is_completed = 1
              )
        )";
        break;

    case 'top_rated':
        // Shows all peer-rated courses >= 4.0, ordered from highest rating
        $having_clauses[] = "AVG(src.rate) >= 4.00";
        $order_by         = "AVG(src.rate) DESC, c.coursecode ASC";
        break;

    case 'most_recommended':
        // Shows all peer-rated courses >= 4.8, ordered from highest rating
        $having_clauses[] = "AVG(src.rate) >= 4.80";
        $order_by         = "AVG(src.rate) DESC, c.coursecode ASC";
        break;

    case 'no_lab':
        $where_clauses[] = "c.has_lab = 0 AND c.has_project = 0";
        break;

    case 'continuous_lab':
        $where_clauses[] = "c.has_lab = 1 AND c.lab_assessment_type = 'CONTINUOUS_ONLY'";
        break;

    case 'track_software':
        $where_clauses[] = "c.track = 'Software'";
        break;

    case 'track_hardware':
        $where_clauses[] = "c.track = 'Hardware'";
        break;

    case 'track_theory':
        $where_clauses[] = "c.track = 'Theory'";
        break;

    case 'track_dbms':
        $where_clauses[] = "c.track = 'DBMS'";
        break;

    case 'track_cyber':
        $where_clauses[] = "c.track = 'Cybersecurity'";
        break;
}

// Assemble Query
$where_sql  = implode(' AND ', $where_clauses);
$having_sql = !empty($having_clauses) ? "HAVING " . implode(' AND ', $having_clauses) : "";

$sql = "SELECT c.*, 
        AVG(src.rate) AS dynamic_avg_rate,
        COUNT(src.rate) AS total_reviews,
        (SELECT COUNT(*) FROM repository r WHERE r.coursecode = c.coursecode) AS repo_count,
        (SELECT COUNT(DISTINCT userID) FROM course_student_took cst WHERE cst.coursecode = c.coursecode) AS total_students_enrolled,
        (SELECT COUNT(DISTINCT faculty_UID) FROM faculty_courselist fc WHERE fc.coursecode = c.coursecode) AS total_faculties_assigned
        FROM course c 
        LEFT JOIN student_review_course src ON c.coursecode = src.coursecode
        WHERE $where_sql 
        GROUP BY c.coursecode
        $having_sql
        ORDER BY $order_by";

$courses_result = mysqli_query($conn, $sql);

// Map prerequisites
$prereq_map = [];
$prereq_query = mysqli_query($conn, "SELECT coursecode, prereq_coursecode, prereq_type FROM course_prerequisites");
if ($prereq_query) {
    while ($p = mysqli_fetch_assoc($prereq_query)) {
        $prereq_map[$p['coursecode']][] = $p;
    }
}

// Map completed courses
$completed_courses = [];
$done_q = mysqli_query($conn, "SELECT coursecode FROM course_student_took WHERE userID = '$student_id' AND is_completed = 1");
if ($done_q) {
    while ($d = mysqli_fetch_assoc($done_q)) {
        $completed_courses[] = $d['coursecode'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Courses — StudyVerse Catalog</title>
    <link rel="stylesheet" href="Student_SeeAllCourse.css">
</head>
<body>

<div class="container">

    <div class="page-header">
        <div>
            <h1>📚 Course Catalog & Explorer</h1>
            <p style="margin:4px 0 0 0; color:var(--text-muted, #64748b); font-size:14px;">
                Filter, verify prerequisites, and inspect live peer ratings for all academic offerings.
            </p>
        </div>
        <div>
            <span class="badge badge-done" style="font-size:13px; padding:6px 12px;">
                User: <strong><?= htmlspecialchars($student_id) ?></strong>
            </span>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <form method="GET" action="">
        <div class="controls-card">
            <div class="search-box">
                <input type="text" name="search" class="search-input" 
                       placeholder="🔍 Search course code, name, or department (e.g. CSE370, DBMS, CSE)..." 
                       value="<?= htmlspecialchars($search) ?>">
            </div>

            <select name="filter" class="filter-select" onchange="this.form.submit()">
                <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>🌐 All Courses</option>
                <option value="eligible" <?= $filter === 'eligible' ? 'selected' : '' ?>>✅ Courses I Am Eligible To Do</option>
                <option value="done" <?= $filter === 'done' ? 'selected' : '' ?>>🎓 Courses I Have Completed</option>
                <option value="top_rated" <?= $filter === 'top_rated' ? 'selected' : '' ?>>⭐ Top Rated Courses (Peer Rate ≥ 4.0)</option>
                <option value="most_recommended" <?= $filter === 'most_recommended' ? 'selected' : '' ?>>🌟 Most Recommended (Peer Rate ≥ 4.8)</option>
                <option value="no_lab" <?= $filter === 'no_lab' ? 'selected' : '' ?>>📖 Pure Theory Courses (No Lab / No Project)</option>
                <option value="continuous_lab" <?= $filter === 'continuous_lab' ? 'selected' : '' ?>>🧪 Continuous Assessment Labs Only</option>
                <optgroup label="Filter By Department Track">
                    <option value="track_software" <?= $filter === 'track_software' ? 'selected' : '' ?>>💻 Software Track</option>
                    <option value="track_hardware" <?= $filter === 'track_hardware' ? 'selected' : '' ?>>⚡ Hardware Track</option>
                    <option value="track_dbms" <?= $filter === 'track_dbms' ? 'selected' : '' ?>>🗄️ DBMS Track</option>
                    <option value="track_theory" <?= $filter === 'track_theory' ? 'selected' : '' ?>>📐 Theory Track</option>
                    <option value="track_cyber" <?= $filter === 'track_cyber' ? 'selected' : '' ?>>🛡️ Cybersecurity Track</option>
                </optgroup>
            </select>

            <button type="submit" class="btn-submit">Apply</button>
            <?php if ($search !== '' || $filter !== 'all'): ?>
                <a href="?" class="btn-reset">Reset</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Courses Table Card -->
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th style="width: 120px;">Code</th>
                    <th>Course Title</th>
                    <th style="width: 90px;">Dept / Cr.</th>
                    <th style="width: 130px;">Track / Type</th>
                    <th>Prerequisites (🔴 Hard / 🟡 Soft)</th>
                    <th style="width: 110px;">Attributes</th>
                    <th style="width: 140px; text-align: center;">Student Rating</th>
                    <th style="width: 100px;">Stats</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($courses_result && mysqli_num_rows($courses_result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($courses_result)): 
                        $code = $row['coursecode'];
                        $is_done = in_array($code, $completed_courses);
                        $has_reviews = ((int)$row['total_reviews'] > 0 && $row['dynamic_avg_rate'] !== null);
                    ?>
                        <tr>
                            <td>
                                <a href="course_info.php?code=<?= urlencode($code) ?>" class="course-link">
                                    <?= htmlspecialchars($code) ?>
                                </a>
                                <?php if ($is_done): ?>
                                    <div><span class="badge badge-done">Done</span></div>
                                <?php endif; ?>
                            </td>

                            <td>
                                <strong><?= htmlspecialchars($row['name']) ?></strong>
                                <div style="font-size:12px; color:var(--text-muted, #64748b); margin-top:2px;">
                                    <?= htmlspecialchars(substr($row['description'], 0, 75)) ?>...
                                </div>
                            </td>

                            <td>
                                <div><strong><?= htmlspecialchars($row['dept']) ?></strong></div>
                                <div style="font-size:12px; color:var(--text-muted, #64748b);"><?= number_format($row['credit'], 1) ?> Credits</div>
                            </td>

                            <td>
                                <span class="badge badge-track"><?= htmlspecialchars($row['track']) ?></span>
                                <div><span class="badge badge-type"><?= htmlspecialchars($row['dept_type']) ?></span></div>
                            </td>

                            <td>
                                <?php if (isset($prereq_map[$code]) && !empty($prereq_map[$code])): ?>
                                    <?php foreach ($prereq_map[$code] as $p): 
                                        $badge_class = ($p['prereq_type'] === 'HARD') ? 'badge-hard' : 'badge-soft';
                                        $prefix = ($p['prereq_type'] === 'HARD') ? 'HP' : 'SP';
                                    ?>
                                        <span class="badge <?= $badge_class ?>" title="<?= $p['prereq_type'] ?> Prerequisite">
                                            <?= $prefix ?>: <?= htmlspecialchars($p['prereq_coursecode']) ?>
                                        </span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span style="color:var(--text-muted, #64748b); font-size:12px;">None (Direct Entry)</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ($row['has_lab']): ?>
                                    <span class="badge badge-lab">🧪 Lab</span>
                                <?php endif; ?>
                                <?php if ($row['has_project']): ?>
                                    <span class="badge badge-project">📂 Project</span>
                                <?php endif; ?>
                                <?php if (!$row['has_lab'] && !$row['has_project']): ?>
                                    <span class="badge badge-theory">📖 Theory</span>
                                <?php endif; ?>
                            </td>

                            <td style="text-align: center;">
                                <?php if ($has_reviews): ?>
                                    <span class="rating-star">★ <?= number_format((float)$row['dynamic_avg_rate'], 1) ?></span>
                                    <div style="font-size:11px; color:var(--text-muted, #64748b); margin-top:2px;">
                                        (<?= $row['total_reviews'] ?> <?= $row['total_reviews'] == 1 ? 'review' : 'reviews' ?>)
                                    </div>
                                <?php else: ?>
                                    <span style="color:#94a3b8; font-style:italic; font-size:12px;">Not reviewed yet</span>
                                <?php endif; ?>
                            </td>

                            <td style="font-size:12px; color:var(--text-muted, #64748b);">
                                <div>📁 <?= (int)$row['repo_count'] ?> Repos</div>
                                <div>👥 <?= (int)$row['total_students_enrolled'] ?> Students</div>
                                <div>👨‍🏫 <?= (int)$row['total_faculties_assigned'] ?> Faculty</div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="empty-state">
                            No courses match your current search and filter criteria.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>