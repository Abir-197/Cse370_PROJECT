 
  <?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

$student_id = $_SESSION['userID'] ?? 'ZS22320';

$msg = '';
$err = '';

// Helper function: Validate and parse semester into a comparable integer order
function parse_semester_order($sem_str) {
    if (preg_match('/^\s*(Spring|Summer|Fall)\s+(\d{4})\s*$/i', trim($sem_str), $m)) {
        $term_num = (strcasecmp($m[1], 'Spring') === 0) ? 1 : ((strcasecmp($m[1], 'Summer') === 0) ? 2 : 3);
        return ((int)$m[2] * 10) + $term_num;
    }
    return 0; // Invalid or non-existent format
}

// Default active semester from student_info
$current_sem_default = 'Summer 2026';
$s_info_q = mysqli_query($conn, "SELECT semester FROM student_info WHERE userID = '$student_id'");
if ($s_info_q && $s_row = mysqli_fetch_assoc($s_info_q)) {
    if (!empty($s_row['semester'])) {
        $current_sem_default = trim($s_row['semester']);
    }
}

// =========================================================
// ACTION 0: UPDATE ACTIVE CURRENT SEMESTER
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_set_current_semester'])) {
    $new_sem = trim($_POST['new_current_sem'] ?? '');
    $parsed_new_order = parse_semester_order($new_sem);

    if ($parsed_new_order === 0) {
        $err = "Invalid Semester Format: Please enter a valid semester (e.g., 'Spring 2026', 'Summer 2026', 'Fall 2026').";
    } else {
        $escaped_sem = mysqli_real_escape_string($conn, $new_sem);
        $upd_sem_q = mysqli_query($conn, "UPDATE student_info SET semester = '$escaped_sem' WHERE userID = '$student_id'");
        if ($upd_sem_q) {
            $current_sem_default = $new_sem;
            $msg = "Active current semester successfully updated to: $new_sem";
        } else {
            $err = "Database Error updating semester: " . mysqli_error($conn);
        }
    }
}

// =========================================================
// ACTION 1: DELETE COURSE REVIEW
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
// ACTION 2: SUBMIT / UPDATE COURSE RATING
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
// ACTION 3: INSERT COURSE (SEMESTER & CATALOG VERIFICATION)
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_insert_course'])) {
    $target_course   = strtoupper(mysqli_real_escape_string($conn, trim($_POST['coursecode'] ?? '')));
    $semester_taken  = trim($_POST['semester_taken'] ?? '');
    $faculty_initial = strtoupper(mysqli_real_escape_string($conn, trim($_POST['faculty_initial'] ?? '')));
    $grade_letter    = strtoupper(mysqli_real_escape_string($conn, trim($_POST['grade_letter'] ?? 'I')));
    $is_completed    = ($grade_letter === 'I' || $grade_letter === 'F') ? 0 : 1;

    $grade_points_map = [
        'A'  => 4.00, 'A-' => 3.70, 'B+' => 3.30, 'B'  => 3.00, 
        'B-' => 2.70, 'C+' => 2.30, 'C'  => 2.00, 'D'  => 1.00, 
        'F'  => 0.00, 'I'  => 0.00
    ];
    $grade_point = $grade_points_map[$grade_letter] ?? 0.00;

    $entered_sem_order = parse_semester_order($semester_taken);
    $current_sem_order = parse_semester_order($current_sem_default);

    // 1. Course catalog existence check
    $course_exist_q = mysqli_query($conn, "SELECT 1 FROM course WHERE coursecode = '$target_course'");
    
    if (empty($target_course) || mysqli_num_rows($course_exist_q) === 0) {
        $err = "Invalid Course: The course '$target_course' does not exist in the official course catalog.";
    } 
    // 2. Validate semester syntax/existence
    elseif ($entered_sem_order === 0) {
        $err = "Non-existent Semester: \"$semester_taken\" is not recognized. Please format as 'Spring YYYY', 'Summer YYYY', or 'Fall YYYY'.";
    } 
    // 3. Match against current semester ceiling
    elseif ($entered_sem_order > $current_sem_order) {
        $err = "Future Semester Blocked: You cannot register courses for \"$semester_taken\" because your active semester is set to \"$current_sem_default\".";
    } 
    else {
        $sem_order = $entered_sem_order;
        $escaped_sem_taken = mysqli_real_escape_string($conn, $semester_taken);

        // 4. Duplicate Check
        $dup_chk = mysqli_query($conn, "
            SELECT 1 FROM course_student_took 
            WHERE userID = '$student_id' AND coursecode = '$target_course'
        ");

        if ($dup_chk && mysqli_num_rows($dup_chk) > 0) {
            $err = "Duplicate Record: You already have $target_course registered in your course list.";
        } else {
            // 5. 5-Course Limit Check
            $sem_count_chk = mysqli_query($conn, "
                SELECT COUNT(*) AS total_courses 
                FROM course_student_took 
                WHERE userID = '$student_id' AND semester_taken = '$escaped_sem_taken'
            ");
            $sem_row = mysqli_fetch_assoc($sem_count_chk);
            $existing_count = (int)($sem_row['total_courses'] ?? 0);

            if ($existing_count >= 5) {
                $err = "Semester Limit Exceeded: You already have $existing_count courses recorded for \"$semester_taken\". Maximum allowed is 5 courses per semester.";
            } else {
                // 6. Hard Prerequisite Check
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
                            (userID, coursecode, marks, grade, grade_letter, grade_point, faculty_initial, semester_taken, semester_order, is_completed)
                        VALUES 
                            ('$student_id', '$target_course', 0.00, 0.00, '$grade_letter', $grade_point, '$faculty_initial', '$escaped_sem_taken', $sem_order, $is_completed)
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
// ACTION 4: UPDATE COURSE GRADE & MARKS (PURE PHP)
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_update_course_grade'])) {
    $record_id    = intval($_POST['record_id'] ?? 0);
    $grade_letter = strtoupper(mysqli_real_escape_string($conn, trim($_POST['grade_letter'] ?? 'I')));
    $marks        = floatval($_POST['marks'] ?? 0.0);

    $grade_points_map = [
        'A'  => 4.00, 'A-' => 3.70, 'B+' => 3.30, 'B'  => 3.00, 
        'B-' => 2.70, 'C+' => 2.30, 'C'  => 2.00, 'D'  => 1.00, 
        'F'  => 0.00, 'I'  => 0.00
    ];
    $grade_point  = $grade_points_map[$grade_letter] ?? 0.00;
    $is_completed = ($grade_letter === 'I' || $grade_letter === 'F') ? 0 : 1;

    if ($record_id <= 0) {
        $err = "Invalid course record selected.";
    } else {
        $update_grade_sql = "
            UPDATE course_student_took 
            SET grade_letter = '$grade_letter',
                grade_point  = $grade_point,
                marks        = $marks,
                grade        = $marks,
                is_completed = $is_completed
            WHERE recordID = $record_id AND userID = '$student_id'
        ";

        if (mysqli_query($conn, $update_grade_sql)) {
            $msg = "Grade updated successfully! New Grade: $grade_letter (" . number_format($grade_point, 2) . ")";
        } else {
            $err = "Database Error: " . mysqli_error($conn);
        }
    }
}

// =========================================================
// FETCH TARGET GRADE RECORD TO EDIT (PURE PHP / GET STATE)
// =========================================================
$edit_grade_id = isset($_GET['edit_grade_id']) ? intval($_GET['edit_grade_id']) : 0;
$edit_grade_data = null;

if ($edit_grade_id > 0) {
    $eg_q = mysqli_query($conn, "
        SELECT cst.recordID, cst.coursecode, c.name AS course_name, cst.grade_letter, cst.marks
        FROM course_student_took cst
        JOIN course c ON cst.coursecode = c.coursecode
        WHERE cst.recordID = $edit_grade_id AND cst.userID = '$student_id'
        LIMIT 1
    ");
    if ($eg_q && mysqli_num_rows($eg_q) > 0) {
        $edit_grade_data = mysqli_fetch_assoc($eg_q);
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
        cst.marks,
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
    <style>
        * { box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; background: #f8fafc; margin: 0; padding: 24px; color: #1e293b; }
        .container { max-width: 1120px; margin: 0 auto; }
        
        .header { background: #0f172a; color: #ffffff; padding: 20px 24px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .header h1 { margin: 0 0 6px 0; font-size: 22px; }
        .header p { margin: 0; color: #94a3b8; font-size: 13px; }
        .nav-links { font-size: 13px; color: #cbd5e1; }
        .nav-links span { margin-right: 12px; }
        .nav-links a { color: #38bdf8; margin-left: 10px; text-decoration: none; font-weight: 500; }
        .nav-links a:hover { text-decoration: underline; }

        .alert { padding: 12px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; margin-bottom: 16px; }
        .alert-success { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .alert-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }

        .sem-config-bar { background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 14px 20px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }

        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 20px; }
        .stat-box { background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 16px 20px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .stat-box .val { font-size: 24px; font-weight: 700; color: #0f172a; }
        .stat-box .lbl { font-size: 12px; color: #64748b; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px; }

        .card, .insert-form-card, .rate-course-card, .edit-grade-card { background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .card h3, .insert-form-card h3, .rate-course-card h3, .edit-grade-card h3 { margin-top: 0; color: #0f172a; }

        .edit-grade-card { border-left: 4px solid #2563eb; background: #f8faff; }

        table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 8px; }
        th { background: #f1f5f9; padding: 12px 14px; text-align: left; border-bottom: 2px solid #cbd5e1; color: #334155; font-weight: 600; }
        td { padding: 12px 14px; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
        tr:hover { background: #f8fafc; }

        .badge-code { background: #e0f2fe; color: #0369a1; padding: 4px 8px; border-radius: 4px; font-weight: 700; font-family: monospace; font-size: 12px; }
        .badge-track { background: #f1f5f9; color: #475569; padding: 3px 6px; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .badge-completed { background: #dcfce7; color: #15803d; padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 11px; }
        .badge-progress { background: #fef3c7; color: #b45309; padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 11px; }

        .btn { padding: 7px 14px; font-size: 12px; font-weight: 600; border-radius: 5px; border: none; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; }
        .btn-primary { background: #2563eb; color: #ffffff; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-success { background: #16a34a; color: #ffffff; }
        .btn-success:hover { background: #15803d; }
        .btn-reset { background: #64748b; color: #ffffff; }
        .btn-reset:hover { background: #475569; }
        .btn-rate { background: #fef08a; color: #854d0e; padding: 4px 8px; font-size: 11px; font-weight: 600; border-radius: 4px; text-decoration: none; }
        .btn-rate:hover { background: #fde047; }
        .btn-delete { background: #fee2e2; color: #b91c1c; padding: 4px 8px; font-size: 11px; font-weight: 600; border-radius: 4px; border: 1px solid #fecaca; cursor: pointer; }
        .btn-delete:hover { background: #fca5a5; }
        .btn-edit-grade { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; padding: 3px 8px; font-size: 11px; font-weight: bold; border-radius: 4px; text-decoration: none; display: inline-block; margin-top: 4px; }
        .btn-edit-grade:hover { background: #2563eb; color: #ffffff; }

        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-top: 12px; }
        .form-label { display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px; }
        .form-control { width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 13px; color: #1e293b; background: #ffffff; }
        .form-control:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,0.15); }
    </style>
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
            <a href="Student_SeeAllCourse.php">All Courses</a>
            <a href="Student_UGPlanner.php">UG Planner</a>
            <a href="StudentSearchFaculty.php">Faculties</a>
            <a href="SwapSectionBuddy.php">Swap Buddy</a>
        </div>
    </div>

    <?php if ($msg): ?><div class="alert alert-success">✅ <?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-danger">⚠️ <?= htmlspecialchars($err) ?></div><?php endif; ?>

    <!-- DYNAMIC CURRENT SEMESTER CONFIGURATION BAR -->
    <div class="sem-config-bar">
        <div>
            <span style="font-size: 13px; color: #64748b;">Current Enrolled Semester:</span>
            <strong style="font-size: 14px; color: #0f172a; margin-left: 6px;"><?= htmlspecialchars($current_sem_default) ?></strong>
        </div>
        <form method="POST" action="" style="margin: 0; display: flex; gap: 8px; align-items: center;">
            <input type="hidden" name="action_set_current_semester" value="1">
            <label style="font-size: 12px; font-weight: bold; color: #475569;">Change Current Sem:</label>
            <input type="text" name="new_current_sem" placeholder="e.g. Summer 2026" value="<?= htmlspecialchars($current_sem_default) ?>" 
                   style="padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px;" required>
            <button type="submit" class="btn btn-primary" style="padding: 6px 14px;">Set</button>
        </form>
    </div>

    <!-- PURE PHP UPDATE GRADE PANEL (ACTIVATES ON ?edit_grade_id=) -->
    <?php if ($edit_grade_data): ?>
        <div class="edit-grade-card" id="edit-grade-box">
            <h3>✏️ Update Grade: <?= htmlspecialchars($edit_grade_data['coursecode']) ?> (<?= htmlspecialchars($edit_grade_data['course_name']) ?>)</h3>
            <p style="font-size: 13px; color: #64748b; margin-top: -6px; margin-bottom: 14px;">
                Update your recorded grade letter and numeric score. This will automatically recalculate completion status and earned credits.
            </p>

            <form method="POST" action="StudentCourses.php">
                <input type="hidden" name="action_update_course_grade" value="1">
                <input type="hidden" name="record_id" value="<?= $edit_grade_data['recordID'] ?>">

                <div class="form-grid" style="margin-bottom: 14px;">
                    <div>
                        <label class="form-label">Letter Grade:</label>
                        <select name="grade_letter" class="form-control" required>
                            <option value="A" <?= ($edit_grade_data['grade_letter'] === 'A') ? 'selected' : '' ?>>A (4.00)</option>
                            <option value="A-" <?= ($edit_grade_data['grade_letter'] === 'A-') ? 'selected' : '' ?>>A- (3.70)</option>
                            <option value="B+" <?= ($edit_grade_data['grade_letter'] === 'B+') ? 'selected' : '' ?>>B+ (3.30)</option>
                            <option value="B" <?= ($edit_grade_data['grade_letter'] === 'B') ? 'selected' : '' ?>>B (3.00)</option>
                            <option value="B-" <?= ($edit_grade_data['grade_letter'] === 'B-') ? 'selected' : '' ?>>B- (2.70)</option>
                            <option value="C+" <?= ($edit_grade_data['grade_letter'] === 'C+') ? 'selected' : '' ?>>C+ (2.30)</option>
                            <option value="C" <?= ($edit_grade_data['grade_letter'] === 'C') ? 'selected' : '' ?>>C (2.00)</option>
                            <option value="D" <?= ($edit_grade_data['grade_letter'] === 'D') ? 'selected' : '' ?>>D (1.00)</option>
                            <option value="F" <?= ($edit_grade_data['grade_letter'] === 'F') ? 'selected' : '' ?>>F (0.00)</option>
                            <option value="I" <?= ($edit_grade_data['grade_letter'] === 'I') ? 'selected' : '' ?>>I (In Progress - 0.00)</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Marks (Optional, 0 - 100):</label>
                        <input type="number" step="0.5" min="0" max="100" name="marks" class="form-control" 
                               value="<?= ($edit_grade_data['marks'] > 0) ? htmlspecialchars($edit_grade_data['marks']) : '' ?>" 
                               placeholder="e.g., 85.5">
                    </div>
                </div>

                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn btn-primary">Save Grade</button>
                    <a href="StudentCourses.php" class="btn btn-reset">Cancel</a>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- COURSE RATING CARD -->
    <?php if ($rate_target_data): ?>
        <div class="rate-course-card" id="rate-course-box">
            <h3>⭐ <?= !empty($rate_target_data['rate']) ? 'Update Your Rating' : 'Rate Completed Course' ?>: <?= htmlspecialchars($rate_target_data['coursecode']) ?> (<?= htmlspecialchars($rate_target_data['course_name']) ?>)</h3>
            <p style="font-size: 13px; color: #64748b; margin-top: -6px; margin-bottom: 14px;">
                Completed with Grade <strong><?= htmlspecialchars($rate_target_data['grade_letter']) ?></strong>.
            </p>

            <form method="POST" action="StudentCourses.php">
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
                    <a href="StudentCourses.php" class="btn btn-reset">Cancel</a>
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
                    <th style="width: 80px; text-align: center;">Grade</th>
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
                            
                            <!-- Pure PHP Update Grade Button -->
                            <td style="text-align: center;">
                                <div style="font-weight: bold;"><?= htmlspecialchars($c['grade_letter']) ?></div>
                                <a href="?edit_grade_id=<?= $c['recordID'] ?>#edit-grade-box" class="btn-edit-grade">
                                    ✏️ Update
                                </a>
                            </td>

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
                                        <div style="color: #ca8a04; font-weight: bold; margin-bottom: 4px;">★ <?= number_format($c['my_rating'], 1) ?></div>
                                        <div style="display: flex; gap: 4px; justify-content: center; align-items: center;">
                                            <a href="?rate_course=<?= urlencode($c['coursecode']) ?>#rate-course-box" class="btn btn-rate">Edit</a>
                                            <form method="POST" action="StudentCourses.php" style="margin: 0; padding: 0; display: inline;">
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
            Registration requires all <strong>HARD prerequisites</strong> to be completed first, validates that the semester is real, and blocks terms higher than <strong><?= htmlspecialchars($current_sem_default) ?></strong>.
        </p>

        <form method="POST" action="StudentCourses.php">
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
                    <label class="form-label">Semester (Direct Text Input):</label>
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
                <a href="StudentCourses.php" class="btn btn-reset">Reset</a>
            </div>
        </form>
    </div>

</div>

</body>
</html>