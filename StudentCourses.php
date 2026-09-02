 <?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

$student_id = $_SESSION['userID'] ?? 'ZS22320';

$msg = '';
$err = '';

// Default active semester from student_info
$current_sem_default = 'Summer 2026';
$s_info_q = mysqli_query($conn, "SELECT semester FROM student_info WHERE userID = '$student_id'");
if ($s_info_q && $s_row = mysqli_fetch_assoc($s_info_q)) {
    if (!empty($s_row['semester'])) {
        $current_sem_default = $s_row['semester'];
    }
}

// =========================================================
// ACTION 1: DELETE COURSE REVIEW (PURE PHP POST)
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_delete_course_review'])) {
    $del_code = strtoupper(mysqli_real_escape_string($conn, trim($_POST['del_coursecode'] ?? '')));

    if (!empty($del_code)) {
        $del_sql = "DELETE FROM student_review_course WHERE student_uID = '$student_id' AND coursecode = '$del_code'";
        if (mysqli_query($conn, $del_sql)) {
            $msg = "Review for course $del_code has been successfully deleted.";
        } else {
            $err = "Database Error: " . mysqli_error($conn);
        }
    }
}

// =========================================================
// ACTION 2: SUBMIT / UPDATE COURSE RATING (COMPLETED COURSES ONLY)
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_submit_course_rating'])) {
    $rate_course_code = strtoupper(mysqli_real_escape_string($conn, trim($_POST['rate_coursecode'] ?? '')));
    $rate_val         = floatval($_POST['rating_val'] ?? 0);
    $review_text      = mysqli_real_escape_string($conn, trim($_POST['review_text'] ?? ''));

    if (empty($rate_course_code) || $rate_val < 1 || $rate_val > 5) {
        $err = "Please select a valid rating between 1.0 and 5.0 stars.";
    } else {
        $complete_chk = mysqli_query($conn, "
            SELECT 1 FROM course_student_took 
            WHERE userID = '$student_id' 
              AND coursecode = '$rate_course_code' 
              AND is_completed = 1 
              AND grade_letter NOT IN ('F', 'I')
        ");

        if (!$complete_chk || mysqli_num_rows($complete_chk) === 0) {
            $err = "Course Rating Blocked: You can only rate courses you have already completed with a passing grade.";
        } else {
            $existing_rev_q = mysqli_query($conn, "
                SELECT reviewID FROM student_review_course 
                WHERE student_uID = '$student_id' AND coursecode = '$rate_course_code'
            ");

            if ($existing_rev_q && mysqli_num_rows($existing_rev_q) > 0) {
                $update_sql = "
                    UPDATE student_review_course 
                    SET rate = $rate_val, review = '$review_text' 
                    WHERE student_uID = '$student_id' AND coursecode = '$rate_course_code'
                ";
                if (mysqli_query($conn, $update_sql)) {
                    $msg = "Rating for $rate_course_code updated successfully!";
                } else {
                    $err = "Database Error: " . mysqli_error($conn);
                }
            } else {
                $insert_rev_sql = "
                    INSERT INTO student_review_course (student_uID, coursecode, rate, review) 
                    VALUES ('$student_id', '$rate_course_code', $rate_val, '$review_text')
                ";
                if (mysqli_query($conn, $insert_rev_sql)) {
                    $msg = "Your review for $rate_course_code has been submitted!";
                } else {
                    $err = "Database Error: " . mysqli_error($conn);
                }
            }
        }
    }
}

// =========================================================
// ACTION 3: INSERT COURSE (DYNAMIC SEMESTER & PREREQUISITE CHECK)
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_insert_course'])) {
    $target_course   = strtoupper(mysqli_real_escape_string($conn, trim($_POST['coursecode'] ?? '')));
    $semester_taken  = mysqli_real_escape_string($conn, trim($_POST['semester_taken'] ?? ''));
    $faculty_initial = strtoupper(mysqli_real_escape_string($conn, trim($_POST['faculty_initial'] ?? '')));
    $grade_letter    = strtoupper(mysqli_real_escape_string($conn, trim($_POST['grade_letter'] ?? 'I')));
    $is_completed    = ($grade_letter === 'I' || $grade_letter === 'F') ? 0 : 1;

    $grade_points_map = [
        'A'  => 4.00, 'A-' => 3.70, 'B+' => 3.30, 'B'  => 3.00, 
        'B-' => 2.70, 'C+' => 2.30, 'C'  => 2.00, 'D'  => 1.00, 
        'F'  => 0.00, 'I'  => 0.00
    ];
    $grade_point = $grade_points_map[$grade_letter] ?? 0.00;

    $sem_order = 20262;
    if (preg_match('/(Spring|Summer|Fall)\s*(\d{4})/i', $semester_taken, $matches)) {
        $term_num = strcasecmp($matches[1], 'Spring') === 0 ? 1 : (strcasecmp($matches[1], 'Summer') === 0 ? 2 : 3);
        $sem_order = ((int)$matches[2] * 10) + $term_num;
    }

    if (empty($target_course) || empty($semester_taken)) {
        $err = "Please enter both a course code and a semester.";
    } else {
        $dup_chk = mysqli_query($conn, "
            SELECT 1 FROM course_student_took 
            WHERE userID = '$student_id' AND coursecode = '$target_course'
        ");

        if ($dup_chk && mysqli_num_rows($dup_chk) > 0) {
            $err = "Duplicate Record: You already have $target_course registered in your course list.";
        } else {
            // 5-Course Limit Check
            $sem_count_chk = mysqli_query($conn, "
                SELECT COUNT(*) AS total_courses 
                FROM course_student_took 
                WHERE userID = '$student_id' AND semester_taken = '$semester_taken'
            ");
            $sem_row = mysqli_fetch_assoc($sem_count_chk);
            $existing_count = (int)($sem_row['total_courses'] ?? 0);

            if ($existing_count >= 5) {
                $err = "Semester Limit Exceeded: You already have $existing_count courses recorded for \"$semester_taken\". Maximum allowed is 5 courses per semester.";
            } else {
                // Hard Prerequisite Check
                $prereq_chk_sql = "
                    SELECT cp.prereq_coursecode 
                    FROM course_prerequisites cp
                    WHERE cp.coursecode = '$target_course' 
                      AND cp.prereq_type = 'HARD'
                      AND cp.prereq_coursecode NOT IN (
                          SELECT cst.coursecode 
                          FROM course_student_took cst 
                          WHERE cst.userID = '$student_id' 
                            AND cst.is_completed = 1 
                            AND cst.grade_letter NOT IN ('F', 'I')
                      )
                ";
                $prereq_res = mysqli_query($conn, $prereq_chk_sql);

                if ($prereq_res && mysqli_num_rows($prereq_res) > 0) {
                    $missing = [];
                    while ($p = mysqli_fetch_assoc($prereq_res)) {
                        $missing[] = $p['prereq_coursecode'];
                    }
                    $err = "Prerequisite Block: Cannot add $target_course. Missing completed HARD prerequisite(s): " . implode(', ', $missing) . ".";
                } else {
                    $insert_sql = "
                        INSERT INTO course_student_took 
                            (userID, coursecode, marks, grade_letter, grade_point, faculty_initial, semester_taken, semester_order, is_completed)
                        VALUES 
                            ('$student_id', '$target_course', 0.00, '$grade_letter', $grade_point, '$faculty_initial', '$semester_taken', $sem_order, $is_completed)
                    ";

                    if (mysqli_query($conn, $insert_sql)) {
                        $msg = "Course $target_course successfully added to $semester_taken! ($semester_taken total: " . ($existing_count + 1) . "/5)";
                    } else {
                        $err = "Database Error: " . mysqli_error($conn);
                    }
                }
            }
        }
    }
}

// =========================================================
// FETCH COURSES TAKEN BY THIS STUDENT
// =========================================================
$my_courses_sql = "
    SELECT 
        cst.recordID,
        cst.coursecode,
        c.name AS course_name,
        c.credit,
        c.dept_type,
        c.track,
        cst.faculty_initial,
        cst.semester_taken,
        cst.grade_letter,
        cst.grade_point,
        cst.is_completed,
        src.rate AS my_rating,
        src.review AS my_review
    FROM course_student_took cst
    JOIN course c ON cst.coursecode = c.coursecode
    LEFT JOIN student_review_course src ON src.student_uID = cst.userID AND src.coursecode = cst.coursecode
    WHERE cst.userID = '$student_id'
    ORDER BY cst.semester_order DESC, cst.coursecode ASC
";
$my_courses_res = mysqli_query($conn, $my_courses_sql);

$total_registered_courses = 0;
$total_credits_completed  = 0;

if ($my_courses_res) {
    while ($row = mysqli_fetch_assoc($my_courses_res)) {
        $total_registered_courses++;
        if ($row['is_completed'] == 1 && $row['grade_letter'] !== 'F') {
            $total_credits_completed += (float)$row['credit'];
        }
    }
    mysqli_data_seek($my_courses_res, 0);
}

// Fetch Target Course for Rating Form
$rate_target_code = isset($_GET['rate_course']) ? strtoupper(mysqli_real_escape_string($conn, trim($_GET['rate_course']))) : '';
$rate_target_data = null;

if ($rate_target_code !== '') {
    $target_q = mysqli_query($conn, "
        SELECT cst.coursecode, c.name AS course_name, cst.grade_letter, src.rate, src.review
        FROM course_student_took cst
        JOIN course c ON cst.coursecode = c.coursecode
        LEFT JOIN student_review_course src ON src.student_uID = cst.userID AND src.coursecode = cst.coursecode
        WHERE cst.userID = '$student_id' 
          AND cst.coursecode = '$rate_target_code' 
          AND cst.is_completed = 1 
          AND cst.grade_letter NOT IN ('F', 'I')
    ");
    if ($target_q && mysqli_num_rows($target_q) > 0) {
        $rate_target_data = mysqli_fetch_assoc($target_q);
    }
}

$available_courses_q = mysqli_query($conn, "SELECT coursecode, name, credit FROM course ORDER BY coursecode ASC");
$all_faculties_q     = mysqli_query($conn, "SELECT DISTINCT faculty_initial FROM faculty_info WHERE faculty_initial IS NOT NULL ORDER BY faculty_initial ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses & Ratings — Studyverse</title>
    <link rel="stylesheet" href="StudentCourses.css">
</head>
<body>

<div class="container">

    <div class="header">
        <div>
            <h1>📖 My Academic Course History & Ratings</h1>
            <p>Active Semester: <strong><?= htmlspecialchars($current_sem_default) ?></strong> · Monitor completed courses, audit credits, and manage course feedback.</p>
        </div>
        <div class="nav-links">
            <span>Student: <strong><?= htmlspecialchars($student_id) ?></strong></span>
            <a href="student_dashboard.php">Dashboard</a>
            <a href="allcourses.php">All Courses</a>
            <a href="Student_UGPlanner.php">UG Planner</a>
            <a href="StudentSearchFaculty.php">Faculties</a>
            <a href="SwapSectionBuddy.php">Swap Buddy</a>
        </div>
    </div>

    <?php if ($msg): ?><div class="alert alert-success">✅ <?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-danger">⚠️ <?= htmlspecialchars($err) ?></div><?php endif; ?>

    <!-- COURSE RATING CARD -->
    <?php if ($rate_target_data): ?>
        <div class="rate-course-card" id="rate-course-box">
            <h3>⭐ <?= !empty($rate_target_data['rate']) ? 'Update Your Rating' : 'Rate Completed Course' ?>: <?= htmlspecialchars($rate_target_data['coursecode']) ?> (<?= htmlspecialchars($rate_target_data['course_name']) ?>)</h3>
            <p style="font-size: 13px; color: #64748b; margin-top: -6px; margin-bottom: 14px;">
                Completed with Grade <strong><?= htmlspecialchars($rate_target_data['grade_letter']) ?></strong>.
            </p>

            <form method="POST" action="">
                <input type="hidden" name="action_submit_course_rating" value="1">
                <input type="hidden" name="rate_coursecode" value="<?= htmlspecialchars($rate_target_data['coursecode']) ?>">

                <div style="display: flex; gap: 16px; margin-bottom: 12px;">
                    <div style="flex: 1;">
                        <label class="form-label">Course Evaluation (1 to 5 Stars):</label>
                        <select name="rating_val" class="form-control" required>
                            <option value="5" <?= ($rate_target_data['rate'] == 5) ? 'selected' : '' ?>>★★★★★ (5.0 - Excellent)</option>
                            <option value="4" <?= ($rate_target_data['rate'] == 4) ? 'selected' : '' ?>>★★★★☆ (4.0 - Good)</option>
                            <option value="3" <?= ($rate_target_data['rate'] == 3) ? 'selected' : '' ?>>★★★☆☆ (3.0 - Moderate)</option>
                            <option value="2" <?= ($rate_target_data['rate'] == 2) ? 'selected' : '' ?>>★★☆☆☆ (2.0 - Heavy Workload)</option>
                            <option value="1" <?= ($rate_target_data['rate'] == 1) ? 'selected' : '' ?>>★☆☆☆☆ (1.0 - Poorly Structured)</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 14px;">
                    <label class="form-label">Review / Feedback (Optional):</label>
                    <textarea name="review_text" class="form-control" rows="2" placeholder="e.g., Key focus areas, exam difficulty, assignments..."><?= htmlspecialchars($rate_target_data['review'] ?? '') ?></textarea>
                </div>

                <div>
                    <button type="submit" class="btn btn-primary">Save Review</button>
                    <a href="SudentCourses.php" class="btn btn-reset">Cancel</a>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Summary Stats Grid -->
    <div class="stats-grid">
        <div class="stat-box">
            <div class="val"><?= $total_registered_courses ?></div>
            <div class="lbl">Total Courses Taken</div>
        </div>
        <div class="stat-box">
            <div class="val"><?= $total_credits_completed ?> / 136</div>
            <div class="lbl">Credits Earned</div>
        </div>
        <div class="stat-box">
            <div class="val">5 Max</div>
            <div class="lbl">Course Limit Per Sem</div>
        </div>
    </div>

    <!-- Courses Table -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <h3 style="margin: 0; color: #1e293b;">📋 Academic Course Record</h3>
            <a href="#insert-course-box" class="btn btn-primary">➕ Enroll / Insert Course</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 90px;">Course Code</th>
                    <th>Course Title</th>
                    <th style="width: 50px; text-align: center;">Credit</th>
                    <th style="width: 80px;">Track</th>
                    <th style="width: 60px; text-align: center;">Faculty</th>
                    <th style="width: 110px;">Semester</th>
                    <th style="width: 50px; text-align: center;">Grade</th>
                    <th style="width: 90px; text-align: center;">Status</th>
                    <th style="width: 160px; text-align: center;">Course Review</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($my_courses_res && mysqli_num_rows($my_courses_res) > 0): ?>
                    <?php while ($c = mysqli_fetch_assoc($my_courses_res)): ?>
                        <?php 
                            $is_eligible_to_rate = ($c['is_completed'] == 1 && $c['grade_letter'] !== 'F');
                            $has_user_rating     = !empty($c['my_rating']);
                        ?>
                        <tr>
                            <td><span class="badge-code"><?= htmlspecialchars($c['coursecode']) ?></span></td>
                            <td>
                                <strong><?= htmlspecialchars($c['course_name']) ?></strong>
                                <span style="font-size: 11px; color: #64748b; margin-left: 4px;">(<?= htmlspecialchars($c['dept_type']) ?>)</span>
                            </td>
                            <td style="text-align: center;"><?= number_format($c['credit'], 1) ?></td>
                            <td><span class="badge-track"><?= htmlspecialchars($c['track']) ?></span></td>
                            <td style="text-align: center;"><strong><?= htmlspecialchars($c['faculty_initial'] ?: 'TBA') ?></strong></td>
                            <td><?= htmlspecialchars($c['semester_taken']) ?></td>
                            <td style="text-align: center; font-weight: bold;"><?= htmlspecialchars($c['grade_letter']) ?></td>
                            <td style="text-align: center;">
                                <?php if ($is_eligible_to_rate): ?>
                                    <span class="badge-completed">Completed</span>
                                <?php else: ?>
                                    <span class="badge-progress">In Progress</span>
                                <?php endif; ?>
                            </td>

                            <!-- Course Review Actions -->
                            <td style="text-align: center;">
                                <?php if ($is_eligible_to_rate): ?>
                                    <?php if ($has_user_rating): ?>
                                        <div class="star-val" style="margin-bottom: 4px;">★ <?= number_format($c['my_rating'], 1) ?></div>
                                        <div style="display: flex; gap: 4px; justify-content: center; align-items: center;">
                                            <a href="?rate_course=<?= urlencode($c['coursecode']) ?>#rate-course-box" class="btn btn-rate">Edit</a>
                                            <form method="POST" action="" style="margin: 0; padding: 0; display: inline;">
                                                <input type="hidden" name="action_delete_course_review" value="1">
                                                <input type="hidden" name="del_coursecode" value="<?= htmlspecialchars($c['coursecode']) ?>">
                                                <button type="submit" class="btn btn-delete">Delete</button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <a href="?rate_course=<?= urlencode($c['coursecode']) ?>#rate-course-box" class="btn btn-rate">⭐ Rate Course</a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color: #94a3b8; font-size: 11px; font-style: italic;">Complete first</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center; color: #64748b; padding: 24px;">
                            No course records found. Use the form below to insert your courses.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Insert Course Section -->
    <div class="insert-form-card" id="insert-course-box">
        <h3>➕ Insert Course into Academic Record</h3>
        <p style="font-size: 13px; color: #64748b; margin-top: -6px; margin-bottom: 16px;">
            Registration requires all <strong>HARD prerequisites</strong> to be completed first and blocks inserting more than <strong>5 courses</strong> for any single semester[cite: 1].
        </p>

        <form method="POST" action="">
            <input type="hidden" name="action_insert_course" value="1">

            <div class="form-grid">
                <div>
                    <label class="form-label">Course to Insert:</label>
                    <select name="coursecode" class="form-control" required>
                        <option value="" disabled selected>-- Select Course --</option>
                        <?php while ($co = mysqli_fetch_assoc($available_courses_q)): ?>
                            <option value="<?= htmlspecialchars($co['coursecode']) ?>">
                                <?= htmlspecialchars($co['coursecode']) ?>: <?= htmlspecialchars($co['name']) ?> (<?= $co['credit'] ?> cr)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div>
                    <label class="form-label">Semester (Input Directly):</label>
                    <input type="text" name="semester_taken" class="form-control" 
                           placeholder="e.g., Summer 2026" 
                           value="<?= htmlspecialchars($_POST['semester_taken'] ?? $current_sem_default) ?>" 
                           required>
                </div>

                <div>
                    <label class="form-label">Faculty Initial (Optional):</label>
                    <select name="faculty_initial" class="form-control">
                        <option value="">-- TBA / Optional --</option>
                        <?php while ($fa = mysqli_fetch_assoc($all_faculties_q)): ?>
                            <option value="<?= htmlspecialchars($fa['faculty_initial']) ?>">
                                <?= htmlspecialchars($fa['faculty_initial']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div>
                    <label class="form-label">Grade / Status:</label>
                    <select name="grade_letter" class="form-control" required>
                        <option value="I">I (In Progress)</option>
                        <option value="A">A</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B">B</option>
                        <option value="B-">B-</option>
                        <option value="C+">C+</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                        <option value="F">F</option>
                    </select>
                </div>
            </div>

            <div style="margin-top: 16px;">
                <button type="submit" class="btn btn-success">Check & Insert Course</button>
                <a href="SudentCourses.php" class="btn btn-reset">Reset</a>
            </div>
        </form>
    </div>

</div>

</body>
</html>