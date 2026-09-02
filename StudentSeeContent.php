<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

$student_id = $_SESSION['userID'] ?? 'ZS22320';

$msg = '';
$err = '';

// Auto-seed sample academic resources into course_resouce if currently empty[cite: 1]
$check_empty = mysqli_query($conn, "SELECT 1 FROM course_resouce LIMIT 1");
if ($check_empty && mysqli_num_rows($check_empty) === 0) {
    mysqli_query($conn, "
        INSERT INTO `course_resouce` (`course_resourceID`, `student_UID`, `coursecode`, `resource_type`, `content`, `name`, `content_category`, `file_path`, `view_count`, `download_count`) VALUES
        ('RES101', 'ZS22320', 'CSE110', 'PDF Document', 'Complete lecture summary on syntax, control structures, and loops.', 'CSE110 Comprehensive Lecture Notes', 'NOTE', 'uploads/cse110_notes.pdf', 45, 12),
        ('RES102', 'S21102', 'CSE220', 'Code Archive', 'Standard implementations of Linked Lists, Stacks, Queues, and BST.', 'CSE220 Essential Data Structures Repo', 'NOTE', 'uploads/cse220_dsa.zip', 98, 34),
        ('RES103', 'S20018', 'CSE370', 'Question Bank', 'Previous 4 semesters midterm and final exams with solved answers.', 'CSE370 Solved Past Papers', 'PREV_QUESTION', 'uploads/cse370_mid_final.pdf', 120, 50),
        ('RES104', 'ZS22320', 'CSE321', 'Slide Deck', 'Operating Systems Process Synchronization and Semaphore deck.', 'CSE321 Semaphore & Deadlock Slides', 'PREV_SLIDE', 'uploads/cse321_slides.pdf', 67, 19),
        ('RES105', 'S22210', 'CSE230', 'Handout', 'Discrete mathematics truth tables, logic proofs, and graph theory summaries.', 'CSE230 Discrete Proof Handouts', 'NOTE', 'uploads/cse230_proofs.pdf', 38, 9),
        ('RES106', 'S21088', 'CSE260', 'Simulation Files', 'Digital logic circuit implementations, Boolean simplification K-maps.', 'CSE260 Digital Logic Lab Circuits', 'OTHER', 'uploads/cse260_circuits.zip', 54, 15)
    ");

    mysqli_query($conn, "
        INSERT INTO `student_reviews_resources` (`resourceID`, `student_UID`, `rate`, `review`) VALUES
        ('RES101', 'S21102', 4.5, 'Super clear explanations of nested loops!'),
        ('RES102', 'ZS22320', 5.0, 'Incredible BST and doubly linked list samples.'),
        ('RES103', 'S22104', 4.0, 'Very helpful for final revision, clear SQL queries.')
    ");
}

// =========================================================
// ACTION: SUBMIT OR UPDATE RESOURCE REVIEW & RATING
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_rate_resource'])) {
    $resource_id = mysqli_real_escape_string($conn, trim($_POST['resource_id'] ?? ''));
    $rating      = floatval($_POST['rating'] ?? 0);
    $review_text = mysqli_real_escape_string($conn, trim($_POST['review_text'] ?? ''));

    if (empty($resource_id) || $rating < 1 || $rating > 5) {
        $err = "Please select a valid rating between 1 and 5 stars.";
    } else {
        // Upsert user review into student_reviews_resources[cite: 1]
        $upsert_sql = "
            INSERT INTO student_reviews_resources (resourceID, student_UID, rate, review) 
            VALUES ('$resource_id', '$student_id', $rating, '$review_text')
            ON DUPLICATE KEY UPDATE rate = $rating, review = '$review_text'
        ";

        if (mysqli_query($conn, $upsert_sql)) {
            $msg = "Your rating and review have been saved!";
        } else {
            $err = "Database Error: " . mysqli_error($conn);
        }
    }
}

// =========================================================
// FILTERS & SEARCH PARAMETERS
// =========================================================
$search_query   = isset($_GET['search']) ? trim($_GET['search']) : '';
$course_filter  = isset($_GET['course']) ? trim($_GET['course']) : 'ALL';
$rating_filter  = isset($_GET['rating']) ? trim($_GET['rating']) : 'ALL';

$where_clauses  = ["1=1"];
$having_clauses = [];

// Keyword Search
if ($search_query !== '') {
    $escaped_search  = mysqli_real_escape_string($conn, $search_query);
    $where_clauses[] = "(cr.name LIKE '%$escaped_search%' OR cr.content LIKE '%$escaped_search%' OR cr.coursecode LIKE '%$escaped_search%')";
}

// Course Filter
if ($course_filter !== 'ALL' && !empty($course_filter)) {
    $escaped_course  = mysqli_real_escape_string($conn, $course_filter);
    $where_clauses[] = "cr.coursecode = '$escaped_course'";
}

// Rating Filter
if ($rating_filter === '4_plus') {
    $having_clauses[] = "AVG(srr.rate) >= 4.0";
} elseif ($rating_filter === '3_plus') {
    $having_clauses[] = "AVG(srr.rate) >= 3.0";
} elseif ($rating_filter === 'not_reviewed') {
    $having_clauses[] = "COUNT(srr.rate) = 0";
}

$where_sql  = implode(' AND ', $where_clauses);
$having_sql = !empty($having_clauses) ? "HAVING " . implode(' AND ', $having_clauses) : "";

// Fetch distinct courses for the filter dropdown[cite: 1]
$courses_list_q = mysqli_query($conn, "SELECT DISTINCT coursecode FROM course_resouce ORDER BY coursecode ASC");

// Master Content Query with real dynamic average rating and review counts[cite: 1]
$content_sql = "
    SELECT 
        cr.*,
        u.name AS uploader_name,
        AVG(srr.rate) AS avg_rate,
        COUNT(srr.rate) AS total_reviews,
        (SELECT srr2.rate FROM student_reviews_resources srr2 WHERE srr2.resourceID = cr.course_resourceID AND srr2.student_UID = '$student_id') AS my_rate,
        (SELECT srr2.review FROM student_reviews_resources srr2 WHERE srr2.resourceID = cr.course_resourceID AND srr2.student_UID = '$student_id') AS my_review
    FROM course_resouce cr
    LEFT JOIN user_info u ON cr.student_UID = u.userID
    LEFT JOIN student_reviews_resources srr ON cr.course_resourceID = srr.resourceID
    WHERE $where_sql
    GROUP BY cr.course_resourceID
    $having_sql
    ORDER BY cr.coursecode ASC, avg_rate DESC
";
$content_res = mysqli_query($conn, $content_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Content & Resources — Studyverse</title>
    <link rel="stylesheet" href="StudentSeeContent.css">
</head>
<body>

<div class="container">

    <div class="header">
        <div>
            <h1>📁 Course Content & Learning Resources</h1>
            <p>Explore academic materials, filter by subject or rating, and share reviews with peers.</p>
        </div>
        <div class="nav-links">
            <span>Student: <strong><?= htmlspecialchars($student_id) ?></strong></span>
            <a href="student_dashboard.php">Dashboard</a>
            <a href="Student_SeeAllCourse.php">All Courses</a>
            <a href="StudentCourses.php">My Courses</a>
            <a href="StudentMentor.php">Mentor Hub</a>
        </div>
    </div>

    <?php if ($msg): ?><div class="alert alert-success">✅ <?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-danger">⚠️ <?= htmlspecialchars($err) ?></div><?php endif; ?>

    <!-- Search & Filter Controls -->
    <div class="filter-card">
        <form method="GET" action="SeeAllContent.php">
            <div class="filter-grid">
                <div>
                    <label class="form-label">Search Content:</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="🔍 Search title, description, or course..." 
                           value="<?= htmlspecialchars($search_query) ?>">
                </div>

                <div>
                    <label class="form-label">Filter by Course:</label>
                    <select name="course" class="form-control" onchange="this.form.submit()">
                        <option value="ALL" <?= ($course_filter === 'ALL') ? 'selected' : '' ?>>All Courses</option>
                        <?php 
                        if ($courses_list_q && mysqli_num_rows($courses_list_q) > 0) {
                            mysqli_data_seek($courses_list_q, 0);
                            while ($c = mysqli_fetch_assoc($courses_list_q)) {
                                $c_val = $c['coursecode'];
                                $sel = ($course_filter === $c_val) ? 'selected' : '';
                                echo "<option value=\"" . htmlspecialchars($c_val) . "\" $sel>" . htmlspecialchars($c_val) . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label class="form-label">Filter by Rating:</label>
                    <select name="rating" class="form-control" onchange="this.form.submit()">
                        <option value="ALL" <?= ($rating_filter === 'ALL') ? 'selected' : '' ?>>All Ratings</option>
                        <option value="4_plus" <?= ($rating_filter === '4_plus') ? 'selected' : '' ?>>⭐ 4.0 Stars & Above</option>
                        <option value="3_plus" <?= ($rating_filter === '3_plus') ? 'selected' : '' ?>>⭐ 3.0 Stars & Above</option>
                        <option value="not_reviewed" <?= ($rating_filter === 'not_reviewed') ? 'selected' : '' ?>>Not Reviewed Yet</option>
                    </select>
                </div>

                <div>
                    <button type="submit" class="btn btn-primary">Apply</button>
                    <?php if ($search_query !== '' || $course_filter !== 'ALL' || $rating_filter !== 'ALL'): ?>
                        <a href="SeeAllContent.php" class="btn btn-reset">Reset</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <!-- Content Items Grid -->
    <div class="content-grid">
        <?php if ($content_res && mysqli_num_rows($content_res) > 0): ?>
            <?php while ($item = mysqli_fetch_assoc($content_res)): ?>
                <?php
                    $res_id      = $item['course_resourceID'];
                    $has_reviews = ((int)$item['total_reviews'] > 0 && $item['avg_rate'] !== null);
                    $my_rated    = !empty($item['my_rate']);

                    // Fetch up to 2 latest community reviews for this resource[cite: 1]
                    $comm_reviews_q = mysqli_query($conn, "
                        SELECT srr.rate, srr.review, u.name 
                        FROM student_reviews_resources srr
                        JOIN user_info u ON srr.student_UID = u.userID
                        WHERE srr.resourceID = '$res_id' AND srr.review != ''
                        ORDER BY srr.rate DESC LIMIT 2
                    ");
                ?>
                <div class="content-card">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                            <span class="badge-course"><?= htmlspecialchars($item['coursecode']) ?></span>
                            <span class="badge-category"><?= htmlspecialchars($item['content_category']) ?></span>
                        </div>

                        <div class="content-title"><?= htmlspecialchars($item['name']) ?></div>
                        <p style="font-size: 13px; color: #475569; margin: 0 0 10px 0; line-height: 1.4;">
                            <?= htmlspecialchars($item['content']) ?>
                        </p>

                        <div style="font-size: 12px; color: #64748b; margin-bottom: 8px;">
                            <div>👤 Uploader: <strong><?= htmlspecialchars($item['uploader_name'] ?: $item['student_UID']) ?></strong></div>
                            <div>👁️ <?= (int)$item['view_count'] ?> Views · ⬇️ <?= (int)$item['download_count'] ?> Downloads</div>
                        </div>

                        <div>
                            <?php if ($has_reviews): ?>
                                <span class="star-rating">★ <?= number_format((float)$item['avg_rate'], 1) ?></span>
                                <span style="font-size: 12px; color: #64748b;">(<?= $item['total_reviews'] ?> <?= $item['total_reviews'] == 1 ? 'review' : 'reviews' ?>)</span>
                            <?php else: ?>
                                <span class="not-reviewed">Not reviewed yet</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Reviews & Rating Section -->
                    <div class="review-box">
                        <details <?= $my_rated ? 'open' : '' ?>>
                            <summary><?= $my_rated ? '✏️ Edit Your Rating & Review' : '⭐ Rate & Review This Resource' ?></summary>
                            
                            <form method="POST" action="" class="review-form">
                                <input type="hidden" name="action_rate_resource" value="1">
                                <input type="hidden" name="resource_id" value="<?= htmlspecialchars($res_id) ?>">

                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <label style="font-size: 12px; font-weight: bold;">Rating:</label>
                                    <select name="rating" class="form-control" style="width: auto; padding: 4px 8px;" required>
                                        <option value="" disabled <?= !$my_rated ? 'selected' : '' ?>>Select Score</option>
                                        <option value="5" <?= ($my_rated && (int)$item['my_rate'] === 5) ? 'selected' : '' ?>>★★★★★ (5 - Excellent)</option>
                                        <option value="4" <?= ($my_rated && (int)$item['my_rate'] === 4) ? 'selected' : '' ?>>★★★★☆ (4 - Good)</option>
                                        <option value="3" <?= ($my_rated && (int)$item['my_rate'] === 3) ? 'selected' : '' ?>>★★★☆☆ (3 - Average)</option>
                                        <option value="2" <?= ($my_rated && (int)$item['my_rate'] === 2) ? 'selected' : '' ?>>★★☆☆☆ (2 - Poor)</option>
                                        <option value="1" <?= ($my_rated && (int)$item['my_rate'] === 1) ? 'selected' : '' ?>>★☆☆☆☆ (1 - Terrible)</option>
                                    </select>
                                </div>

                                <textarea name="review_text" class="form-control" rows="2" 
                                          placeholder="Write your feedback..."><?= htmlspecialchars($item['my_review'] ?? '') ?></textarea>

                                <button type="submit" class="btn-submit-review">
                                    <?= $my_rated ? 'Update Review' : 'Submit Review' ?>
                                </button>
                            </form>
                        </details>

                        <?php if ($comm_reviews_q && mysqli_num_rows($comm_reviews_q) > 0): ?>
                            <div class="recent-reviews">
                                <strong style="color: #334155;">Recent Feedback:</strong>
                                <?php while ($cr = mysqli_fetch_assoc($comm_reviews_q)): ?>
                                    <div class="review-item">
                                        <span class="star-rating" style="font-size: 11px;">★ <?= $cr['rate'] ?></span>
                                        <span style="color: #64748b;">— <?= htmlspecialchars($cr['name']) ?>:</span>
                                        "<?= htmlspecialchars($cr['review']) ?>"
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="color: #64748b; font-size: 14px; grid-column: 1 / -1; text-align: center; padding: 30px;">
                No learning content found matching your search and filter criteria.
            </p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>