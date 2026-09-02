<
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

$student_id = $_SESSION['userID'] ?? 'ZS22320';

$msg = '';
$err = '';

// Self-healing check: Ensure 'status' column exists in student_consult_mentor
$col_chk = mysqli_query($conn, "SHOW COLUMNS FROM `student_consult_mentor` LIKE 'status'");
if ($col_chk && mysqli_num_rows($col_chk) === 0) {
    @mysqli_query($conn, "ALTER TABLE `student_consult_mentor` ADD COLUMN `status` ENUM('PENDING','ACCEPTED','REJECTED','CANCELLED') NOT NULL DEFAULT 'PENDING'");
}

// =========================================================
// ACTION 1: REGISTER OR UPDATE MENTOR SUBJECT
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_mentor_register'])) {
    $chosen_subject = strtoupper(mysqli_real_escape_string($conn, trim($_POST['mentor_subject'] ?? '')));

    if (empty($chosen_subject)) {
        $err = "Please select a course to mentor.";
    } else {
        // Enforce: Course MUST be completed with at least A- (grade_point >= 3.70)
        $grade_chk_sql = "
            SELECT grade_letter, grade_point 
            FROM course_student_took 
            WHERE userID = '$student_id' 
              AND coursecode = '$chosen_subject' 
              AND is_completed = 1 
              AND (grade_letter IN ('A', 'A-') OR grade_point >= 3.70)
        ";
        $grade_res = mysqli_query($conn, $grade_chk_sql);

        if (!$grade_res || mysqli_num_rows($grade_res) === 0) {
            $err = "Eligibility Block: You cannot mentor $chosen_subject. You must have completed the course with at least an A- grade.";
        } else {
            $m_chk = mysqli_query($conn, "SELECT * FROM mentor WHERE userID = '$student_id'");

            if ($m_chk && mysqli_num_rows($m_chk) > 0) {
                // Update subject and reactivate
                $update_sql = "UPDATE mentor SET Subject = '$chosen_subject', is_active = 1 WHERE userID = '$student_id'";
                if (mysqli_query($conn, $update_sql)) {
                    $msg = "Mentorship updated! You are now mentoring $chosen_subject.";
                } else {
                    $err = "Database Error: " . mysqli_error($conn);
                }
            } else {
                $gen_mentor_id = 'M_' . substr(preg_replace('/[^0-9]/', '', $student_id) ?: '01', 0, 7);
                $ins_mentor_sql = "INSERT INTO mentor (userID, MentorID, Subject, is_active) VALUES ('$student_id', '$gen_mentor_id', '$chosen_subject', 1)";
                if (mysqli_query($conn, $ins_mentor_sql)) {
                    $msg = "Congratulations! You are now an active peer mentor for $chosen_subject.";
                } else {
                    $err = "Database Error: " . mysqli_error($conn);
                }
            }
        }
    }
}

// =========================================================
// ACTION 2: SOFT DELETE MENTOR PROFILE
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_delete_mentor_profile'])) {
    $soft_del_sql = "UPDATE mentor SET is_active = 0 WHERE userID = '$student_id'";
    if (mysqli_query($conn, $soft_del_sql)) {
        $msg = "Your mentor profile has been deactivated. Past consultation records remain intact.";
    } else {
        $err = "Database Error: " . mysqli_error($conn);
    }
}

// =========================================================
// ACTION 3: SEND CONSULTATION REQUEST (WITH CROSS-SCHEDULE CHECKS)
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_book_mentor'])) {
    $target_mentor_uid = mysqli_real_escape_string($conn, trim($_POST['mentor_uid'] ?? ''));
    $target_mentor_id  = mysqli_real_escape_string($conn, trim($_POST['mentor_id'] ?? ''));
    $book_date         = mysqli_real_escape_string($conn, trim($_POST['booking_date'] ?? ''));
    $book_time         = mysqli_real_escape_string($conn, trim($_POST['booking_time'] ?? ''));

    // Check 1: User cannot book himself
    if ($target_mentor_uid === $student_id) {
        $err = "Invalid Booking: You cannot book yourself as a mentor.";
    } elseif (empty($target_mentor_uid) || empty($book_date) || empty($book_time)) {
        $err = "Please select both a date and a time slot.";
    } else {
        $timeschedule = "$book_date $book_time";

        // Check 2: Student already booked with ANOTHER mentor OR scheduled to CONDUCT a session as mentor
        $mentor_conflict_chk = mysqli_query($conn, "
            SELECT 1 FROM student_consult_mentor 
            WHERE (student_uID = '$student_id' OR mentor_studentID = '$student_id')
              AND timeschedule = '$timeschedule'
              AND status IN ('ACCEPTED', 'PENDING')
        ");

        if ($mentor_conflict_chk && mysqli_num_rows($mentor_conflict_chk) > 0) {
            $err = "Time Conflict: You already have a peer consultation session scheduled or pending at $timeschedule.";
        } else {
            // Check 3: Cross-check with student_faculty_consultation (student cannot overlap with faculty session)
            $student_faculty_clash = mysqli_query($conn, "
                SELECT 1 FROM student_faculty_consultation 
                WHERE student_uID = '$student_id' 
                  AND consult_date = '$book_date' 
                  AND status IN ('ACCEPTED', 'PENDING')
                  AND (
                      ('$book_time' >= start_time AND '$book_time' < end_time)
                      OR start_time = '$book_time'
                  )
            ");

            if ($student_faculty_clash && mysqli_num_rows($student_faculty_clash) > 0) {
                $err = "Faculty Schedule Conflict: You already have an active faculty consultation booked or pending on $book_date around " . date('h:i A', strtotime($book_time)) . ".";
            } else {
                // Check 4: Cross-check if the MENTOR has a faculty consultation at that time
                $mentor_faculty_clash = mysqli_query($conn, "
                    SELECT 1 FROM student_faculty_consultation 
                    WHERE student_uID = '$target_mentor_uid' 
                      AND consult_date = '$book_date' 
                      AND status IN ('ACCEPTED', 'PENDING')
                      AND (
                          ('$book_time' >= start_time AND '$book_time' < end_time)
                          OR start_time = '$book_time'
                      )
                ");

                if ($mentor_faculty_clash && mysqli_num_rows($mentor_faculty_clash) > 0) {
                    $err = "Mentor Unavailable: This mentor is attending a faculty consultation on $book_date at " . date('h:i A', strtotime($book_time)) . ".";
                } else {
                    // Check 5: Mentor daily limit (Max 3 accepted sessions on that date)
                    $mentor_day_chk = mysqli_query($conn, "
                        SELECT COUNT(*) AS total_day_bookings 
                        FROM student_consult_mentor 
                        WHERE mentor_studentID = '$target_mentor_uid' 
                          AND DATE(timeschedule) = '$book_date'
                          AND status = 'ACCEPTED'
                    ");
                    $day_row = mysqli_fetch_assoc($mentor_day_chk);
                    $current_day_bookings = (int)($day_row['total_day_bookings'] ?? 0);

                    if ($current_day_bookings >= 3) {
                        $err = "Daily Limit Reached: This mentor has already reached their maximum of 3 confirmed bookings on $book_date. Please choose another date.";
                    } else {
                        // Check 6: Mentor collision check (Another student already booked this mentor at this exact slot)
                        $mentor_slot_chk = mysqli_query($conn, "
                            SELECT 1 FROM student_consult_mentor 
                            WHERE mentor_studentID = '$target_mentor_uid' 
                              AND timeschedule = '$timeschedule'
                              AND status = 'ACCEPTED'
                        ");

                        if ($mentor_slot_chk && mysqli_num_rows($mentor_slot_chk) > 0) {
                            $err = "Slot Unavailable: This mentor already has a confirmed session at $timeschedule. Please choose a different time slot.";
                        } else {
                            // All cross-checks passed -> Insert as PENDING
                            $ins_book_sql = "
                                INSERT INTO student_consult_mentor (student_uID, mentor_studentID, mentorID, timeschedule, status)
                                VALUES ('$student_id', '$target_mentor_uid', '$target_mentor_id', '$timeschedule', 'PENDING')
                            ";

                            if (mysqli_query($conn, $ins_book_sql)) {
                                $msg = "Consultation request sent! The session will appear on both schedules once the mentor accepts.";
                            } else {
                                $err = "Database Error: " . mysqli_error($conn);
                            }
                        }
                    }
                }
            }
        }
    }
}

// =========================================================
// ACTION 4: MENTOR ACCEPTS OR REJECTS INCOMING REQUEST
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_mentor_decision'])) {
    $req_student_uid = mysqli_real_escape_string($conn, trim($_POST['req_student_uid'] ?? ''));
    $req_timesched   = mysqli_real_escape_string($conn, trim($_POST['req_timeschedule'] ?? ''));
    $decision        = strtoupper(trim($_POST['decision'] ?? ''));

    if ($decision === 'ACCEPT') {
        $req_date = date('Y-m-d', strtotime($req_timesched));

        // Mentor daily capacity verification
        $day_cap_q = mysqli_query($conn, "
            SELECT COUNT(*) AS total_accepted 
            FROM student_consult_mentor 
            WHERE mentor_studentID = '$student_id' 
              AND DATE(timeschedule) = '$req_date' 
              AND status = 'ACCEPTED'
        ");
        $day_cap = mysqli_fetch_assoc($day_cap_q);

        if ((int)($day_cap['total_accepted'] ?? 0) >= 3) {
            $err = "Acceptance Blocked: You already have 3 accepted bookings on $req_date (maximum allowed per day).";
        } else {
            // Mentor time collision verification
            $col_chk_q = mysqli_query($conn, "
                SELECT 1 FROM student_consult_mentor 
                WHERE mentor_studentID = '$student_id' 
                  AND timeschedule = '$req_timesched' 
                  AND status = 'ACCEPTED'
            ");

            if ($col_chk_q && mysqli_num_rows($col_chk_q) > 0) {
                $err = "Collision Blocked: You already have an accepted session at $req_timesched.";
            } else {
                $accept_sql = "
                    UPDATE student_consult_mentor 
                    SET status = 'ACCEPTED' 
                    WHERE student_uID = '$req_student_uid' 
                      AND mentor_studentID = '$student_id' 
                      AND timeschedule = '$req_timesched'
                ";
                if (mysqli_query($conn, $accept_sql)) {
                    $msg = "Consultation accepted! This session is now confirmed on both your mentor schedule and the mentee's schedule.";
                } else {
                    $err = "Database Error: " . mysqli_error($conn);
                }
            }
        }
    } elseif ($decision === 'REJECT') {
        $reject_sql = "
            UPDATE student_consult_mentor 
            SET status = 'REJECTED' 
            WHERE student_uID = '$req_student_uid' 
              AND mentor_studentID = '$student_id' 
              AND timeschedule = '$req_timesched'
        ";
        if (mysqli_query($conn, $reject_sql)) {
            $msg = "Consultation request rejected.";
        } else {
            $err = "Database Error: " . mysqli_error($conn);
        }
    }
}

// =========================================================
// DATA RETRIEVAL
// =========================================================

// 1. Current student's active mentor status
$my_mentor_profile = null;
$my_m_q = mysqli_query($conn, "SELECT * FROM mentor WHERE userID = '$student_id' AND is_active = 1");
if ($my_m_q && mysqli_num_rows($my_m_q) > 0) {
    $my_mentor_profile = mysqli_fetch_assoc($my_m_q);
}

// 2. Courses eligible for mentorship (Grade >= A-)
$eligible_courses_q = mysqli_query($conn, "
    SELECT cst.coursecode, c.name, cst.grade_letter 
    FROM course_student_took cst
    JOIN course c ON cst.coursecode = c.coursecode
    WHERE cst.userID = '$student_id' 
      AND cst.is_completed = 1 
      AND (cst.grade_letter IN ('A', 'A-') OR cst.grade_point >= 3.70)
    ORDER BY cst.coursecode ASC
");

// 3. Incoming Requests Inbox
$incoming_requests = [];
if ($my_mentor_profile) {
    $inbox_q = mysqli_query($conn, "
        SELECT scm.student_uID, scm.timeschedule, scm.status, u.name AS student_name, si.dept, si.cgpa
        FROM student_consult_mentor scm
        JOIN user_info u ON scm.student_uID = u.userID
        JOIN student_info si ON scm.student_uID = si.userID
        WHERE scm.mentor_studentID = '$student_id' 
          AND scm.status = 'PENDING'
        ORDER BY scm.timeschedule ASC
    ");
    while ($req = mysqli_fetch_assoc($inbox_q)) {
        $incoming_requests[] = $req;
    }
}

// 4. Mentor's Confirmed Teaching Schedule
$my_teaching_schedule = [];
if ($my_mentor_profile) {
    $teach_q = mysqli_query($conn, "
        SELECT scm.student_uID, scm.timeschedule, u.name AS student_name, si.dept
        FROM student_consult_mentor scm
        JOIN user_info u ON scm.student_uID = u.userID
        JOIN student_info si ON scm.student_uID = si.userID
        WHERE scm.mentor_studentID = '$student_id' 
          AND scm.status = 'ACCEPTED'
        ORDER BY scm.timeschedule ASC
    ");
    while ($ts = mysqli_fetch_assoc($teach_q)) {
        $my_teaching_schedule[] = $ts;
    }
}

// 5. Course Filter & Active Mentors Directory (Excludes the logged-in mentor)
$search_course = isset($_GET['search_course']) ? trim($_GET['search_course']) : 'ALL';
$course_filter_sql = "";
if (!empty($search_course) && $search_course !== 'ALL') {
    $safe_course = mysqli_real_escape_string($conn, $search_course);
    $course_filter_sql = " AND m.Subject = '$safe_course'";
}

// Fetch all distinct subjects currently offered by other active mentors for filter dropdown
$distinct_subjects_q = mysqli_query($conn, "
    SELECT DISTINCT Subject 
    FROM mentor 
    WHERE is_active = 1 
      AND userID != '$student_id' 
    ORDER BY Subject ASC
");

$all_mentors_sql = "
    SELECT 
        m.userID,
        m.MentorID,
        m.Subject,
        u.name AS mentor_name,
        si.cgpa,
        si.dept,
        (SELECT AVG(srm.rate) FROM student_rates_mentor srm WHERE srm.mentor_studentID = m.userID) AS avg_mentor_rate,
        (SELECT COUNT(srm.rate) FROM student_rates_mentor srm WHERE srm.mentor_studentID = m.userID) AS total_mentor_reviews
    FROM mentor m
    JOIN user_info u ON m.userID = u.userID
    JOIN student_info si ON m.userID = si.userID
    WHERE m.is_active = 1
      AND m.userID != '$student_id'
      $course_filter_sql
    ORDER BY m.Subject ASC, u.name ASC
";
$all_mentors_res = mysqli_query($conn, $all_mentors_sql);

// 6. Selected Mentor for Booking Form
$target_booking_uid = isset($_GET['book_uid']) ? mysqli_real_escape_string($conn, trim($_GET['book_uid'])) : '';
$target_mentor_data = null;
if ($target_booking_uid !== '') {
    if ($target_booking_uid !== $student_id) {
        $t_q = mysqli_query($conn, "
            SELECT m.userID, m.MentorID, m.Subject, u.name AS mentor_name
            FROM mentor m
            JOIN user_info u ON m.userID = u.userID
            WHERE m.userID = '$target_booking_uid' AND m.is_active = 1
        ");
        if ($t_q && mysqli_num_rows($t_q) > 0) {
            $target_mentor_data = mysqli_fetch_assoc($t_q);
        }
    }
}

// 7. Student's Consultations & History
$my_consultations_sql = "
    SELECT 
        scm.timeschedule,
        scm.mentor_studentID,
        scm.mentorID,
        scm.status,
        u.name AS mentor_name,
        m.Subject AS current_subject,
        m.is_active AS mentor_is_active
    FROM student_consult_mentor scm
    LEFT JOIN user_info u ON scm.mentor_studentID = u.userID
    LEFT JOIN mentor m ON scm.mentor_studentID = m.userID
    WHERE scm.student_uID = '$student_id'
    ORDER BY scm.timeschedule DESC
";
$my_consultations_res = mysqli_query($conn, $my_consultations_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peer Mentorship Hub — Studyverse</title>
    <link rel="stylesheet" href="StudentMentor.css">
</head>
<body>

<div class="container">

    <div class="header">
        <div>
            <h1>🎓 Peer Mentorship Hub</h1>
            <p>Connect with mentors, accept consultation requests, and keep synchronized schedules.</p>
        </div>
        <div class="nav-links">
            <span>Student: <strong><?= htmlspecialchars($student_id) ?></strong></span>
            <a href="student_dashboard.php">Dashboard</a>
            <a href="StudentCourses.php">My Courses</a>
            <a href="Student_UGPlanner.php">UG Planner</a>
            <a href="StudentSearchFaculty.php">Faculties</a>
            <a href="SwapSectionBuddy.php">Swap Buddy</a>
        </div>
    </div>

    <?php if ($msg): ?><div class="alert alert-success">✅ <?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-danger">⚠️ <?= htmlspecialchars($err) ?></div><?php endif; ?>

    <!-- 1. MENTOR ROLE MANAGEMENT -->
    <div class="mentor-role-card">
        <?php if ($my_mentor_profile): ?>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin: 0; color: #1e293b;">🎖️ You Are an Active Peer Mentor</h3>
                    <p style="margin: 4px 0 10px 0; font-size: 13px; color: #475569;">
                        Currently Mentoring: <span class="badge-subject"><?= htmlspecialchars($my_mentor_profile['Subject']) ?></span>
                        · Mentor ID: <span class="badge-id"><?= htmlspecialchars($my_mentor_profile['MentorID']) ?></span>
                    </p>
                </div>
                <form method="POST" action="" style="margin: 0;">
                    <input type="hidden" name="action_delete_mentor_profile" value="1">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to resign as mentor? Past consultations remain preserved.');">
                        Delete Mentor ID
                    </button>
                </form>
            </div>

            <!-- Switch Course -->
            <form method="POST" action="" style="margin-top: 10px; border-top: 1px solid #cbd5e1; padding-top: 12px;">
                <input type="hidden" name="action_mentor_register" value="1">
                <div style="display: flex; gap: 10px; align-items: flex-end;">
                    <div style="flex: 1;">
                        <label class="form-label">Switch Mentoring Course (Grade &ge; A- required):</label>
                        <select name="mentor_subject" class="form-control" required>
                            <option value="" disabled selected>-- Select Eligible Course --</option>
                            <?php 
                            if ($eligible_courses_q) {
                                mysqli_data_seek($eligible_courses_q, 0);
                                while ($ec = mysqli_fetch_assoc($eligible_courses_q)) {
                                    $is_curr = ($ec['coursecode'] === $my_mentor_profile['Subject']);
                                    echo '<option value="' . htmlspecialchars($ec['coursecode']) . '" ' . ($is_curr ? 'selected' : '') . '>';
                                    echo htmlspecialchars($ec['coursecode']) . ': ' . htmlspecialchars($ec['name']) . ' (Grade: ' . $ec['grade_letter'] . ')' . ($is_curr ? ' [Current]' : '');
                                    echo '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">Update Course</button>
                    </div>
                </div>
            </form>

        <?php else: ?>
            <h3 style="margin-top: 0;">🌟 Wanna Become a Peer Mentor?</h3>
            <p style="font-size: 13px; color: #475569; margin-top: -6px; margin-bottom: 12px;">
                Share your mastery with peers. You must have completed the course with at least an <strong>A-</strong> grade[cite: 1]. Mentors can guide <strong>one course</strong> at a time[cite: 1].
            </p>

            <?php if ($eligible_courses_q && mysqli_num_rows($eligible_courses_q) > 0): ?>
                <form method="POST" action="">
                    <input type="hidden" name="action_mentor_register" value="1">
                    <div style="display: flex; gap: 10px; align-items: flex-end;">
                        <div style="flex: 1;">
                            <label class="form-label">Select Course to Mentor (Completed with &ge; A-):</label>
                            <select name="mentor_subject" class="form-control" required>
                                <option value="" disabled selected>-- Select Course --</option>
                                <?php 
                                mysqli_data_seek($eligible_courses_q, 0);
                                while ($ec = mysqli_fetch_assoc($eligible_courses_q)): ?>
                                    <option value="<?= htmlspecialchars($ec['coursecode']) ?>">
                                        <?= htmlspecialchars($ec['coursecode']) ?>: <?= htmlspecialchars($ec['name']) ?> (Your Grade: <?= htmlspecialchars($ec['grade_letter']) ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-success">Register as Mentor</button>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <p style="color: #b91c1c; font-size: 13px; margin: 0;">
                    You do not currently have any completed courses with a grade of A- or higher in your academic record.
                </p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- 2. INCOMING BOOKING REQUESTS -->
    <?php if ($my_mentor_profile && !empty($incoming_requests)): ?>
        <div class="inbox-card">
            <h3 style="margin: 0 0 10px 0; color: #854d0e;">📥 Incoming Consultation Requests (Requires Your Acceptance)</h3>
            <p style="font-size: 13px; color: #713f12; margin-top: -6px; margin-bottom: 12px;">
                Once you click <strong>Accept</strong>, this booking becomes confirmed and gets placed onto both your mentor schedule and the student's schedule.
            </p>

            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Student ID</th>
                        <th>Dept & CGPA</th>
                        <th>Requested Time</th>
                        <th style="text-align: center; width: 180px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($incoming_requests as $req): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($req['student_name']) ?></strong></td>
                            <td><span class="badge-id"><?= htmlspecialchars($req['student_uID']) ?></span></td>
                            <td><?= htmlspecialchars($req['dept'] ?: 'CSE') ?> (CGPA: <?= number_format((float)$req['cgpa'], 2) ?>)</td>
                            <td>📅 <strong><?= date('D, M d, Y - h:i A', strtotime($req['timeschedule'])) ?></strong></td>
                            <td style="text-align: center;">
                                <form method="POST" action="" style="display: inline; margin-right: 4px;">
                                    <input type="hidden" name="action_mentor_decision" value="1">
                                    <input type="hidden" name="req_student_uid" value="<?= htmlspecialchars($req['student_uID']) ?>">
                                    <input type="hidden" name="req_timeschedule" value="<?= htmlspecialchars($req['timeschedule']) ?>">
                                    <input type="hidden" name="decision" value="ACCEPT">
                                    <button type="submit" class="btn-accept">Accept</button>
                                </form>

                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="action_mentor_decision" value="1">
                                    <input type="hidden" name="req_student_uid" value="<?= htmlspecialchars($req['student_uID']) ?>">
                                    <input type="hidden" name="req_timeschedule" value="<?= htmlspecialchars($req['timeschedule']) ?>">
                                    <input type="hidden" name="decision" value="REJECT">
                                    <button type="submit" class="btn-reject">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- 3. MENTOR'S CONFIRMED TEACHING SCHEDULE -->
    <?php if ($my_mentor_profile): ?>
        <div class="card">
            <h3 style="margin-top: 0; color: #1e293b;">🗓️ My Mentor Schedule (Sessions I Am Conducting)</h3>
            <?php if (!empty($my_teaching_schedule)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Mentee Name</th>
                            <th>Student ID</th>
                            <th>Confirmed Timeslot</th>
                            <th style="text-align: center;">Session Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($my_teaching_schedule as $ts): ?>
                            <?php 
                                $is_upcoming = (strtotime($ts['timeschedule']) >= time());
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($ts['student_name']) ?></strong> (<?= htmlspecialchars($ts['dept'] ?: 'CSE') ?>)</td>
                                <td><span class="badge-id"><?= htmlspecialchars($ts['student_uID']) ?></span></td>
                                <td>📅 <strong><?= date('D, M d, Y - h:i A', strtotime($ts['timeschedule'])) ?></strong></td>
                                <td style="text-align: center;">
                                    <?php if ($is_upcoming): ?>
                                        <span class="badge-accepted">Upcoming Session</span>
                                    <?php else: ?>
                                        <span style="background:#f1f5f9; color:#475569; padding:3px 8px; border-radius:4px; font-size:11px;">Completed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #64748b; font-size: 13px; margin: 0;">No confirmed consultations on your schedule yet.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- 4. BOOKING FORM -->
    <?php if ($target_mentor_data): ?>
        <div class="booking-card" id="booking-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h3 style="margin: 0; color: #065f46;">
                    🗓️ Request Consultation with <?= htmlspecialchars($target_mentor_data['mentor_name']) ?> (<?= htmlspecialchars($target_mentor_data['Subject']) ?>)
                </h3>
                <a href="StudentMentor.php" class="btn btn-reset" style="padding: 4px 10px; font-size: 12px;">Cancel</a>
            </div>

            <form method="POST" action="">
                <input type="hidden" name="action_book_mentor" value="1">
                <input type="hidden" name="mentor_uid" value="<?= htmlspecialchars($target_mentor_data['userID']) ?>">
                <input type="hidden" name="mentor_id" value="<?= htmlspecialchars($target_mentor_data['MentorID']) ?>">

                <div class="form-grid">
                    <div>
                        <label class="form-label">Consultation Date:</label>
                        <input type="date" name="booking_date" class="form-control" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div>
                        <label class="form-label">Time Slot:</label>
                        <select name="booking_time" class="form-control" required>
                            <option value="09:30:00">09:30 AM - 10:30 AM</option>
                            <option value="11:00:00">11:00 AM - 12:00 PM</option>
                            <option value="14:00:00">02:00 PM - 03:00 PM</option>
                            <option value="15:30:00">03:30 PM - 04:30 PM</option>
                            <option value="17:00:00">05:00 PM - 06:00 PM</option>
                        </select>
                    </div>

                    <div>
                        <button type="submit" class="btn btn-success" style="width: 100%;">Send Request</button>
                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- 5. ACTIVE PEER MENTORS DIRECTORY -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
            <div>
                <h3 style="margin: 0; color: #1e293b;">👥 Active Peer Mentors Directory</h3>
                <p style="font-size: 13px; color: #64748b; margin: 4px 0 0 0;">
                    Connect with peer mentors. Cross-checked against faculty and peer consultation schedules[cite: 1].
                </p>
            </div>

            <!-- Course Search / Filter Bar -->
            <form method="GET" action="StudentMentor.php" style="display: flex; gap: 8px; align-items: center; margin: 0;">
                <label for="search_course" style="font-size: 13px; font-weight: bold; color: #475569;">Filter Course:</label>
                <select name="search_course" id="search_course" class="form-control" style="width: auto; min-width: 170px;" onchange="this.form.submit()">
                    <option value="ALL" <?= ($search_course === 'ALL' || empty($search_course)) ? 'selected' : '' ?>>All Courses</option>
                    <?php 
                    if ($distinct_subjects_q && mysqli_num_rows($distinct_subjects_q) > 0) {
                        mysqli_data_seek($distinct_subjects_q, 0);
                        while ($sub = mysqli_fetch_assoc($distinct_subjects_q)) {
                            $s_val = $sub['Subject'];
                            $selected = ($search_course === $s_val) ? 'selected' : '';
                            echo "<option value=\"" . htmlspecialchars($s_val) . "\" $selected>" . htmlspecialchars($s_val) . "</option>";
                        }
                    }
                    ?>
                </select>
                <?php if ($search_course !== 'ALL' && !empty($search_course)): ?>
                    <a href="StudentMentor.php" class="btn btn-reset" style="padding: 7px 12px; font-size: 12px;">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="mentor-grid">
            <?php if ($all_mentors_res && mysqli_num_rows($all_mentors_res) > 0): ?>
                <?php while ($m = mysqli_fetch_assoc($all_mentors_res)): ?>
                    <?php 
                        $has_rating = !empty($m['avg_mentor_rate']);
                    ?>
                    <div class="mentor-card">
                        <div>
                            <h4><?= htmlspecialchars($m['mentor_name']) ?></h4>
                            <div style="margin-bottom: 8px;">
                                <span class="badge-subject"><?= htmlspecialchars($m['Subject']) ?></span>
                                <span class="badge-id"><?= htmlspecialchars($m['MentorID']) ?></span>
                            </div>
                            <div style="font-size: 12px; color: #64748b; line-height: 1.5;">
                                <div>🎓 Dept: <strong><?= htmlspecialchars($m['dept'] ?: 'CSE') ?></strong> · CGPA: <strong><?= number_format((float)$m['cgpa'], 2) ?></strong></div>
                                <div>
                                    <?php if ($has_rating): ?>
                                        <span class="star-val">★ <?= number_format((float)$m['avg_mentor_rate'], 1) ?></span> (<?= $m['total_mentor_reviews'] ?> reviews)
                                    <?php else: ?>
                                        <span style="color:#94a3b8; font-style:italic;">No ratings yet</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div>
                            <a href="?search_course=<?= urlencode($search_course) ?>&book_uid=<?= urlencode($m['userID']) ?>#booking-section" class="btn btn-book" style="display: block;">
                                📅 Book Consultation
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: #64748b; font-size: 13px; grid-column: 1 / -1;">
                    <?= ($search_course !== 'ALL' && !empty($search_course)) 
                        ? 'No other mentors currently registered for <strong>' . htmlspecialchars($search_course) . '</strong>.' 
                        : 'No other active mentors currently available in the directory.' ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- 6. STUDENT'S BOOKINGS & CONSULTATION HISTORY -->
    <div class="card">
        <h3 style="margin-top: 0; color: #1e293b;">📋 My Bookings & Mentors History</h3>
        <p style="font-size: 13px; color: #64748b; margin-top: -6px; margin-bottom: 12px;">
            Shows requested and confirmed sessions. Confirmed sessions are placed onto both your schedule and your mentor's schedule.
        </p>

        <table>
            <thead>
                <tr>
                    <th>Mentor Name</th>
                    <th>Mentor ID</th>
                    <th>Subject</th>
                    <th>Consultation Schedule</th>
                    <th style="text-align: center;">Booking Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($my_consultations_res && mysqli_num_rows($my_consultations_res) > 0): ?>
                    <?php while ($c = mysqli_fetch_assoc($my_consultations_res)): ?>
                        <?php 
                            $sched_timestamp = strtotime($c['timeschedule']);
                            $b_status = $c['status'] ?? 'PENDING';
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($c['mentor_name'] ?: 'Archived Mentor') ?></strong></td>
                            <td><span class="badge-id"><?= htmlspecialchars($c['mentorID']) ?></span></td>
                            <td><span class="badge-subject"><?= htmlspecialchars($c['current_subject'] ?: 'Subject Recorded') ?></span></td>
                            <td>📅 <?= date('D, M d, Y - h:i A', $sched_timestamp) ?></td>
                            <td style="text-align: center;">
                                <?php if ($b_status === 'ACCEPTED'): ?>
                                    <span class="badge-accepted">Confirmed on Schedule</span>
                                <?php elseif ($b_status === 'PENDING'): ?>
                                    <span class="badge-pending">Pending Mentor Acceptance</span>
                                <?php elseif ($b_status === 'REJECTED'): ?>
                                    <span class="badge-rejected">Declined</span>
                                <?php else: ?>
                                    <span class="badge-id"><?= htmlspecialchars($b_status) ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: #64748b; padding: 20px;">
                            You have not requested any consultations yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>