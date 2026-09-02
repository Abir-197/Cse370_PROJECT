<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

$student_id = $_SESSION['userID'] ?? 'ZS22320';

$msg = '';
$err = '';

// Self-healing schema migration
$col_chk = mysqli_query($conn, "SHOW COLUMNS FROM `student_rates_faculty` LIKE 'coursecode'");
if ($col_chk && mysqli_num_rows($col_chk) === 0) {
    @mysqli_query($conn, "ALTER TABLE `student_rates_faculty` ADD COLUMN `coursecode` VARCHAR(10) NOT NULL AFTER `faculty_uID`");
    @mysqli_query($conn, "ALTER TABLE `student_rates_faculty` DROP PRIMARY KEY, ADD PRIMARY KEY (`student_uID`, `faculty_uID`, `coursecode`)");
}

// 1. Process Rating Submission (Pure PHP)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_submit_rating'])) {
    $target_faculty_uid = mysqli_real_escape_string($conn, trim($_POST['faculty_uid']));
    $target_coursecode  = strtoupper(mysqli_real_escape_string($conn, trim($_POST['coursecode'] ?? '')));
    $rating_val         = (int)$_POST['rating_val'];
    $review_text        = mysqli_real_escape_string($conn, trim($_POST['review_text'] ?? ''));

    if (empty($target_faculty_uid) || empty($target_coursecode)) {
        $err = "Please select a specific course taught by this faculty member.";
    } elseif ($rating_val < 1 || $rating_val > 5) {
        $err = "Please choose a rating between 1 and 5 stars.";
    } else {
        $check_dup = mysqli_query($conn, "
            SELECT 1 FROM student_rates_faculty 
            WHERE student_uID = '$student_id' 
              AND faculty_uID = '$target_faculty_uid' 
              AND coursecode = '$target_coursecode'
        ");

        if ($check_dup && mysqli_num_rows($check_dup) > 0) {
            $err = "You have already submitted a review for this faculty in $target_coursecode.";
        } else {
            $insert_sql = "
                INSERT INTO student_rates_faculty (student_uID, faculty_uID, coursecode, rating, review)
                VALUES ('$student_id', '$target_faculty_uid', '$target_coursecode', $rating_val, '$review_text')
            ";

            if (mysqli_query($conn, $insert_sql)) {
                $msg = "Review for $target_coursecode successfully recorded!";
            } else {
                $err = "Database Error: " . mysqli_error($conn);
            }
        }
    }
}

// 2. Search & Filtering Inputs
$search_name   = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$search_course = isset($_GET['search_course']) ? strtoupper(trim($_GET['search_course'])) : '';
$rating_filter = isset($_GET['rating_filter']) ? trim($_GET['rating_filter']) : 'all';
$rate_uid      = isset($_GET['rate_uid']) ? mysqli_real_escape_string($conn, trim($_GET['rate_uid'])) : '';
$view_reviews  = isset($_GET['view_reviews']) ? mysqli_real_escape_string($conn, trim($_GET['view_reviews'])) : '';

$where_clauses = ["1=1"];
if ($search_name !== '') {
    $esc_name = mysqli_real_escape_string($conn, $search_name);
    $where_clauses[] = "(u.name LIKE '%$esc_name%' OR fi.faculty_initial LIKE '%$esc_name%')";
}

$esc_course = mysqli_real_escape_string($conn, $search_course);
$having_clauses = [];

if ($search_course !== '') {
    $having_clauses[] = "courses_taught LIKE '%$esc_course%'";
    if ($rating_filter === '4_plus') {
        $having_clauses[] = "course_specific_avg >= 4.00";
    } elseif ($rating_filter === '3_plus') {
        $having_clauses[] = "course_specific_avg >= 3.00";
    } elseif ($rating_filter === 'unrated') {
        $having_clauses[] = "course_specific_avg IS NULL";
    }
} else {
    if ($rating_filter === '4_plus') {
        $having_clauses[] = "overall_avg >= 4.00";
    } elseif ($rating_filter === '3_plus') {
        $having_clauses[] = "overall_avg >= 3.00";
    } elseif ($rating_filter === 'unrated') {
        $having_clauses[] = "overall_avg IS NULL";
    }
}

$where_sql  = implode(' AND ', $where_clauses);
$having_sql = !empty($having_clauses) ? "HAVING " . implode(' AND ', $having_clauses) : "";

// 3. Faculty Query (With Independent Subqueries to prevent row multiplication)
$query = "
    SELECT 
        fi.userID,
        u.name AS faculty_name,
        fi.faculty_initial,
        fi.room_no,
        fi.designation,
        fi.Dept,
        (SELECT GROUP_CONCAT(DISTINCT fc.coursecode ORDER BY fc.coursecode SEPARATOR ', ') 
         FROM faculty_courselist fc WHERE fc.faculty_UID = fi.userID) AS courses_taught,
        (SELECT AVG(srf.rating) FROM student_rates_faculty srf WHERE srf.faculty_uID = fi.userID) AS overall_avg,
        (SELECT COUNT(srf.rating) FROM student_rates_faculty srf WHERE srf.faculty_uID = fi.userID) AS overall_reviews,
        (SELECT AVG(srf2.rating) FROM student_rates_faculty srf2 WHERE srf2.faculty_uID = fi.userID AND srf2.coursecode = '$esc_course') AS course_specific_avg,
        (SELECT COUNT(srf2.rating) FROM student_rates_faculty srf2 WHERE srf2.faculty_uID = fi.userID AND srf2.coursecode = '$esc_course') AS course_specific_reviews
    FROM faculty_info fi
    JOIN user_info u ON fi.userID = u.userID
    WHERE $where_sql
    GROUP BY fi.userID, u.name, fi.faculty_initial, fi.room_no, fi.designation, fi.Dept
    $having_sql
    ORDER BY u.name ASC
";

$faculty_res = mysqli_query($conn, $query);

// 4. Fetch Rating Target Faculty Data (if Rate button was clicked)
$target_faculty_data = null;
$courses_to_rate = [];
$reviewed_courses = [];

if ($rate_uid !== '') {
    $target_q = mysqli_query($conn, "
        SELECT fi.userID, u.name AS faculty_name, fi.faculty_initial
        FROM faculty_info fi
        JOIN user_info u ON fi.userID = u.userID
        WHERE fi.userID = '$rate_uid'
    ");

    if ($target_q && mysqli_num_rows($target_q) > 0) {
        $target_faculty_data = mysqli_fetch_assoc($target_q);

        $c_taught_q = mysqli_query($conn, "SELECT coursecode FROM faculty_courselist WHERE faculty_UID = '$rate_uid' ORDER BY coursecode ASC");
        while ($ct = mysqli_fetch_assoc($c_taught_q)) {
            $courses_to_rate[] = $ct['coursecode'];
        }

        $rev_q = mysqli_query($conn, "SELECT coursecode FROM student_rates_faculty WHERE student_uID = '$student_id' AND faculty_uID = '$rate_uid'");
        while ($rq = mysqli_fetch_assoc($rev_q)) {
            $reviewed_courses[] = $rq['coursecode'];
        }
    }
}

// 5. Fetch Reviews Data (if Read Reviews was clicked)
$reviews_faculty_data = null;
$fetched_reviews = [];

if ($view_reviews !== '') {
    $f_info_q = mysqli_query($conn, "
        SELECT fi.userID, u.name AS faculty_name, fi.faculty_initial 
        FROM faculty_info fi
        JOIN user_info u ON fi.userID = u.userID
        WHERE fi.userID = '$view_reviews'
    ");

    if ($f_info_q && mysqli_num_rows($f_info_q) > 0) {
        $reviews_faculty_data = mysqli_fetch_assoc($f_info_q);

        // Course filter isolation: only fetch reviews for this course if filtered
        $course_condition = ($search_course !== '') ? "AND srf.coursecode = '$esc_course'" : "";

        $reviews_query = "
            SELECT srf.rating, srf.review, srf.coursecode, u.name AS reviewer_name
            FROM student_rates_faculty srf
            JOIN user_info u ON srf.student_uID = u.userID
            WHERE srf.faculty_uID = '$view_reviews'
            $course_condition
            ORDER BY srf.rating DESC
        ";
        $rev_res = mysqli_query($conn, $reviews_query);
        if ($rev_res) {
            while ($r_row = mysqli_fetch_assoc($rev_res)) {
                $fetched_reviews[] = $r_row;
            }
        }
    }
}

// Helper to preserve active search parameters in links
$filter_params = "";
if ($search_name !== '') $filter_params .= "&search_name=" . urlencode($search_name);
if ($search_course !== '') $filter_params .= "&search_course=" . urlencode($search_course);
if ($rating_filter !== 'all') $filter_params .= "&rating_filter=" . urlencode($rating_filter);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Directory & Ratings — Studyverse</title>
    <link rel="stylesheet" href="StudentSearchFaculty.css">
</head>
<body>

<div class="container">

    <div class="header">
        <div>
            <h1>👨‍🏫 Faculty Directory & Ratings</h1>
            <p>Inspect desk locations, view course-specific rating stats, and inspect student reviews.</p>
        </div>
        <div class="nav-links">
            <span>Student: <strong><?= htmlspecialchars($student_id) ?></strong></span>
            <a href="student_dashboard.php">Dashboard</a>
            <a href="allcourses.php">Course Catalog</a>
            <a href="SwapSectionBuddy.php">Swap Buddy</a>
        </div>
    </div>

    <?php if ($msg): ?><div class="alert alert-success">✅ <?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-danger">⚠️ <?= htmlspecialchars($err) ?></div><?php endif; ?>

    <!-- 1. READ REVIEWS SECTION -->
    <?php if ($reviews_faculty_data): ?>
        <div class="reviews-display-card" id="reviews-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <h3 style="margin: 0; color: #0891b2;">
                    📖 Reviews for <?= htmlspecialchars($reviews_faculty_data['faculty_name']) ?> (<?= htmlspecialchars($reviews_faculty_data['faculty_initial']) ?>)
                    <?php if ($search_course !== ''): ?>
                        <span style="color: #64748b; font-size: 14px;">· Filtered for <?= htmlspecialchars($search_course) ?></span>
                    <?php else: ?>
                        <span style="color: #64748b; font-size: 14px;">· All Assigned Courses</span>
                    <?php endif; ?>
                </h3>
                <a href="?<?= ltrim($filter_params, '&') ?>" class="btn btn-reset" style="padding: 4px 10px; font-size: 12px;">Close Reviews</a>
            </div>

            <?php if (empty($fetched_reviews)): ?>
                <p style="color: #64748b; font-size: 13px;">No student reviews found for the selected criteria.</p>
            <?php else: ?>
                <?php foreach ($fetched_reviews as $rev): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <div>
                                <strong>👤 <?= htmlspecialchars($rev['reviewer_name']) ?></strong>
                                <span class="badge-course"><?= htmlspecialchars($rev['coursecode']) ?></span>
                            </div>
                            <span class="star-val">★ <?= (int)$rev['rating'] ?>.0 / 5.0</span>
                        </div>
                        <p class="review-body">
                            <?= !empty(trim($rev['review'])) ? '"' . htmlspecialchars($rev['review']) . '"' : '<span style="color:#94a3b8;">(No written comment provided)</span>' ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- 2. PURE PHP RATING FORM -->
    <?php if ($target_faculty_data): ?>
        <div class="rate-form-card" id="rate-section">
            <h3>⭐ Review Faculty: <?= htmlspecialchars($target_faculty_data['faculty_name']) ?> (<?= htmlspecialchars($target_faculty_data['faculty_initial']) ?>)</h3>
            
            <?php $unrated_available = array_diff($courses_to_rate, $reviewed_courses); ?>

            <?php if (empty($courses_to_rate)): ?>
                <p style="color:#b91c1c;">This faculty member currently has no courses assigned in the system.</p>
                <a href="?<?= ltrim($filter_params, '&') ?>" class="btn btn-reset">Close</a>
            <?php elseif (empty($unrated_available)): ?>
                <p style="color:#b91c1c; font-weight: bold;">
                    You have already reviewed <?= htmlspecialchars($target_faculty_data['faculty_initial']) ?> for all assigned courses (<?= implode(', ', $reviewed_courses) ?>).
                </p>
                <a href="?<?= ltrim($filter_params, '&') ?>" class="btn btn-reset">Close</a>
            <?php else: ?>
                <form method="POST" action="">
                    <input type="hidden" name="action_submit_rating" value="1">
                    <input type="hidden" name="faculty_uid" value="<?= htmlspecialchars($target_faculty_data['userID']) ?>">

                    <div style="display: flex; gap: 16px; margin-bottom: 12px;">
                        <div style="flex: 1;">
                            <label class="form-label">Course Taught By This Faculty:</label>
                            <select name="coursecode" class="form-control" required>
                                <option value="" disabled <?= empty($search_course) ? 'selected' : '' ?>>-- Choose Course --</option>
                                <?php foreach ($courses_to_rate as $c_code): ?>
                                    <?php 
                                        $is_done = in_array($c_code, $reviewed_courses);
                                        $is_selected = (!$is_done && $search_course === $c_code);
                                    ?>
                                    <option value="<?= htmlspecialchars($c_code) ?>" <?= $is_done ? 'disabled' : '' ?> <?= $is_selected ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c_code) ?> <?= $is_done ? '(Already Reviewed)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="flex: 1;">
                            <label class="form-label">Score (1 - 5 Stars):</label>
                            <select name="rating_val" class="form-control" required>
                                <option value="5">★★★★★ (5 - Excellent)</option>
                                <option value="4">★★★★☆ (4 - Very Good)</option>
                                <option value="3">★★★☆☆ (3 - Average)</option>
                                <option value="2">★★☆☆☆ (2 - Below Average)</option>
                                <option value="1">★☆☆☆☆ (1 - Poor)</option>
                            </select>
                        </div>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label class="form-label">Review / Feedback (Optional):</label>
                        <textarea name="review_text" class="form-control" rows="2" placeholder="Feedback on course syllabus, lectures, or grading for this course..."></textarea>
                    </div>

                    <div>
                        <button type="submit" class="btn btn-primary">Submit Review</button>
                        <a href="?<?= ltrim($filter_params, '&') ?>" class="btn btn-reset">Cancel</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- 3. SEARCH & FILTER CONTROLS -->
    <div class="card">
        <form method="GET" action="">
            <div class="filter-grid">
                <div>
                    <label class="form-label">Search Faculty / Initial</label>
                    <input type="text" name="search_name" class="form-control" placeholder="e.g. Tariq or THS..." value="<?= htmlspecialchars($search_name) ?>">
                </div>

                <div>
                    <label class="form-label">Filter by Course</label>
                    <input type="text" name="search_course" class="form-control" placeholder="e.g. CSE370, CSE110..." value="<?= htmlspecialchars($search_course) ?>">
                </div>

                <div>
                    <label class="form-label">Filter by Rating</label>
                    <select name="rating_filter" class="form-control">
                        <option value="all" <?= $rating_filter === 'all' ? 'selected' : '' ?>>All Ratings</option>
                        <option value="4_plus" <?= $rating_filter === '4_plus' ? 'selected' : '' ?>>⭐ 4.0 Stars & Above</option>
                        <option value="3_plus" <?= $rating_filter === '3_plus' ? 'selected' : '' ?>>⭐ 3.0 Stars & Above</option>
                        <option value="unrated" <?= $rating_filter === 'unrated' ? 'selected' : '' ?>>⚪ Not Rated Yet</option>
                    </select>
                </div>

                <div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>

                <?php if ($search_name !== '' || $search_course !== '' || $rating_filter !== 'all'): ?>
                    <div>
                        <a href="?" class="btn btn-reset">Reset</a>
                    </div>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- 4. FACULTY DIRECTORY TABLE -->
    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Faculty Name</th>
                    <th style="width: 80px;">Initial</th>
                    <th style="width: 110px;">Desk No</th>
                    <th>Courses Taking</th>
                    <th style="width: 200px;">
                        <?= ($search_course !== '') ? htmlspecialchars($search_course) . ' Avg Rating' : 'Overall Rating (All Courses)' ?>
                    </th>
                    <th style="width: 180px; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($faculty_res && mysqli_num_rows($faculty_res) > 0): ?>
                    <?php while ($f = mysqli_fetch_assoc($faculty_res)): ?>
                        <?php
                            $fid         = $f['userID'];
                            $courses_arr = !empty($f['courses_taught']) ? explode(', ', $f['courses_taught']) : [];
                            
                            if ($search_course !== '') {
                                $has_rating    = !empty($f['course_specific_avg']);
                                $displayed_avg = $f['course_specific_avg'];
                                $review_count  = (int)$f['course_specific_reviews'];
                                $rating_scope  = $search_course;
                            } else {
                                $has_rating    = !empty($f['overall_avg']);
                                $displayed_avg = $f['overall_avg'];
                                $review_count  = (int)$f['overall_reviews'];
                                $rating_scope  = "All Courses";
                            }
                        ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($f['faculty_name']) ?></strong>
                                <div style="font-size:11px; color:#64748b;"><?= htmlspecialchars($f['designation'] ?? 'Faculty') ?> (<?= htmlspecialchars($f['Dept'] ?? 'CSE') ?>)</div>
                            </td>

                            <td><strong style="color: #2563eb;"><?= htmlspecialchars($f['faculty_initial']) ?></strong></td>

                            <td>📍 <strong><?= htmlspecialchars($f['room_no'] ?: 'TBA') ?></strong></td>

                            <td>
                                <?php if (!empty($courses_arr)): ?>
                                    <?php foreach ($courses_arr as $c_code): ?>
                                        <span class="badge-course <?= ($search_course === $c_code) ? 'badge-highlight' : '' ?>">
                                            <?= htmlspecialchars($c_code) ?>
                                        </span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span style="color:#94a3b8; font-size:12px;">No courses assigned</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ($has_rating): ?>
                                    <span class="star-val">★ <?= number_format($displayed_avg, 2) ?></span>
                                    <div style="font-size:11px; color:#64748b;">
                                        (<?= $review_count ?> <?= $review_count === 1 ? 'review' : 'reviews' ?> · <?= htmlspecialchars($rating_scope) ?>)
                                    </div>
                                <?php else: ?>
                                    <span class="badge-unrated">Not rated yet <?= ($search_course !== '') ? 'for ' . htmlspecialchars($search_course) : '' ?></span>
                                <?php endif; ?>
                            </td>

                            <td style="text-align: center;">
                                <div style="display: flex; gap: 6px; justify-content: center;">
                                    <?php if ($review_count > 0): ?>
                                        <a href="?view_reviews=<?= urlencode($fid) ?><?= $filter_params ?>#reviews-section" class="btn btn-read">
                                            Read Reviews
                                        </a>
                                    <?php endif; ?>

                                    <a href="?rate_uid=<?= urlencode($fid) ?><?= $filter_params ?>#rate-section" class="btn btn-rate">
                                        Rate
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; color:#64748b; padding: 20px;">
                            No faculty members found matching your search and filter criteria.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>