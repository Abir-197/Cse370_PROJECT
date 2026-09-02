<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['userID'];

$conn->query("CREATE TABLE IF NOT EXISTS consultation_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    student_uID VARCHAR(8) NOT NULL,
    faculty_uID VARCHAR(8) NOT NULL,
    coursecode VARCHAR(10) DEFAULT NULL,
    preferred_day VARCHAR(50) NOT NULL,
    preferred_time VARCHAR(50) NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'accepted', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['request_consultation'])) {
        $faculty_uID = trim($_POST['faculty_uID'] ?? '');
        $coursecode = trim($_POST['coursecode'] ?? '');
        $preferred_day = trim($_POST['preferred_day'] ?? '');
        $preferred_time = trim($_POST['preferred_time'] ?? '');
        $reason = trim($_POST['reason'] ?? '');

        if ($faculty_uID === '' || $preferred_day === '' || $preferred_time === '' || $reason === '') {
            $errorMessage = 'Please fill in all consultation request details.';
        } else {
            $facultyCheck = $conn->prepare('SELECT userID FROM faculty_info WHERE userID = ?');
            if (!$facultyCheck) {
                $errorMessage = 'Could not validate the selected faculty.';
            } else {
                $facultyCheck->bind_param('s', $faculty_uID);
                $facultyCheck->execute();
                $facultyResult = $facultyCheck->get_result();
                if ($facultyResult->num_rows === 0) {
                    $errorMessage = 'Selected faculty was not found.';
                } else {
                    $stmt = $conn->prepare('INSERT INTO consultation_requests (student_uID, faculty_uID, coursecode, preferred_day, preferred_time, reason, status) VALUES (?, ?, ?, ?, ?, ?, "pending")');
                    if (!$stmt) {
                        $errorMessage = 'Unable to create consultation request at this time.';
                    } else {
                        $stmt->bind_param('sssss', $user_id, $faculty_uID, $coursecode, $preferred_day, $preferred_time, $reason);
                        if ($stmt->execute()) {
                            $successMessage = 'Consultation request sent successfully. The faculty will review it.';
                        } else {
                            $errorMessage = 'Failed to send consultation request: ' . $stmt->error;
                        }
                        $stmt->close();
                    }
                }
                $facultyCheck->close();
            }
        }
    }

    if (isset($_POST['update_request_status'])) {
        $request_id = (int)($_POST['request_id'] ?? 0); 
        $status = $_POST['update_request_status'] ?? '';
        $allowedStatus = ['accepted', 'rejected'];

        if ($request_id <= 0 || !in_array($status, $allowedStatus, true)) {
            $errorMessage = 'Invalid consultation status update.';
        } else {
            $checkStmt = $conn->prepare('SELECT faculty_uID FROM consultation_requests WHERE request_id = ?');
            if (!$checkStmt) {
                $errorMessage = 'Unable to update consultation status.';
            } else {
                $checkStmt->bind_param('i', $request_id);
                $checkStmt->execute();
                $requestResult = $checkStmt->get_result();
                $requestRow = $requestResult->fetch_assoc();
                $checkStmt->close();

                if (!$requestRow || $requestRow['faculty_uID'] !== $user_id) {
                    $errorMessage = 'You are not allowed to update this consultation request.';
                } else {
                    $updateStmt = $conn->prepare('UPDATE consultation_requests SET status = ? WHERE request_id = ?');
                    if (!$updateStmt) {
                        $errorMessage = 'Could not update the consultation status.';
                    } else {
                        $updateStmt->bind_param('si', $status, $request_id);
                        if ($updateStmt->execute()) {
                            $successMessage = 'Consultation request was ' . $status . '.';
                        } else {
                            $errorMessage = 'Failed to update consultation request: ' . $updateStmt->error;
                        }
                        $updateStmt->close();
                    }
                }
            }
        }
    }
}

$isFaculty = false;
$facultyCheck = $conn->prepare('SELECT userID FROM faculty_info WHERE userID = ?');
if ($facultyCheck) {
    $facultyCheck->bind_param('s', $user_id);
    $facultyCheck->execute();
    $isFaculty = $facultyCheck->get_result()->num_rows > 0;
    $facultyCheck->close();
}

$facultyList = [];
$facultyQuery = $conn->prepare('SELECT fi.userID, ui.name, fi.Dept, GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ", ") AS courses FROM faculty_info fi LEFT JOIN user_info ui ON ui.userID = fi.userID LEFT JOIN faculty_courselist fc ON fc.faculty_UID = fi.userID LEFT JOIN course c ON c.coursecode = fc.coursecode GROUP BY fi.userID, ui.name, fi.Dept ORDER BY ui.name');
if ($facultyQuery) {
    $facultyQuery->execute();
    $facultyResult = $facultyQuery->get_result();
    while ($row = $facultyResult->fetch_assoc()) {
        $facultyList[] = $row;
    }
    $facultyQuery->close();
}

$studentRequests = [];
if (!$isFaculty) {
    $studentRequestStmt = $conn->prepare('SELECT cr.request_id, cr.faculty_uID, ui.name AS faculty_name, cr.coursecode, cr.preferred_day, cr.preferred_time, cr.reason, cr.status, cr.created_at FROM consultation_requests cr LEFT JOIN user_info ui ON ui.userID = cr.faculty_uID WHERE cr.student_uID = ? ORDER BY cr.created_at DESC');
    if ($studentRequestStmt) {
        $studentRequestStmt->bind_param('s', $user_id);
        $studentRequestStmt->execute();
        $studentRequestResult = $studentRequestStmt->get_result();
        while ($row = $studentRequestResult->fetch_assoc()) {
            $studentRequests[] = $row;
        }
        $studentRequestStmt->close();
    }
}

$facultyRequests = [];
if ($isFaculty) {
    $facultyRequestStmt = $conn->prepare('SELECT cr.request_id, cr.student_uID, ui.name AS student_name, cr.coursecode, cr.preferred_day, cr.preferred_time, cr.reason, cr.status, cr.created_at FROM consultation_requests cr LEFT JOIN user_info ui ON ui.userID = cr.student_uID WHERE cr.faculty_uID = ? ORDER BY cr.created_at DESC');
    if ($facultyRequestStmt) {
        $facultyRequestStmt->bind_param('s', $user_id);
        $facultyRequestStmt->execute();
        $facultyRequestResult = $facultyRequestStmt->get_result();
        while ($row = $facultyRequestResult->fetch_assoc()) {
            $facultyRequests[] = $row;
        }
        $facultyRequestStmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultation Requests - Studyverse</title>
    <link rel="stylesheet" href="student_dash.css">
    <link rel="stylesheet" href="cnsultation_requests.css">
</head>
<body>
    <header class="navbar">
        <div class="nav-container">
            <div class="brand">Studyverse</div>
            <nav class="nav-links">
                <a href="student_dashboard.php">Dashboard</a>
                <a href="student_profile.php">Profile</a>
                <a href="mycourses.php">My Courses</a>
                <a href="mygroups.php">My Groups</a>
                <a href="findmentor.php">Find Mentor</a>
                <a href="faculties.php">Faculties</a>
                <a href="cnsultation_requests.php" class="active">Consultations</a>
            </nav>
            <div class="nav-user">
                <span class="user-name">ID: <?php echo htmlspecialchars($user_id); ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <section class="welcome-banner">
            <h2>Consultation Requests</h2>
            <p>Students can request faculty consultation slots, and faculty members can accept or reject each request.</p>
        </section>

        <section class="stats-grid">
            <div class="stat-card">
                <span class="stat-label">Faculty</span>
                <strong><?php echo count($facultyList); ?></strong>
            </div>
            <div class="stat-card">
                <span class="stat-label"><?php echo $isFaculty ? 'Pending Review' : 'My Requests'; ?></span>
                <strong><?php echo $isFaculty ? count($facultyRequests) : count($studentRequests); ?></strong>
            </div>
            <div class="stat-card">
                <span class="stat-label">Status</span>
                <strong><?php echo $isFaculty ? 'Faculty View' : 'Student View'; ?></strong>
            </div>
        </section>

        <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <?php if ($errorMessage !== ''): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <?php if (!$isFaculty): ?>
            <section class="panel-grid">
                <div class="panel">
                    <h3>Request a consultation</h3>
                    <form method="POST" class="consult-form">
                        <div class="form-grid">
                            <label>
                                Faculty
                                <select name="faculty_uID" required>
                                    <option value="">Select faculty</option>
                                    <?php foreach ($facultyList as $faculty): ?>
                                        <option value="<?php echo htmlspecialchars($faculty['userID']); ?>"><?php echo htmlspecialchars($faculty['name'] . ' (' . $faculty['Dept'] . ')'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>

                            <label>
                                Course
                                <select name="coursecode">
                                    <option value="">Select course (optional)</option>
                                    <?php
                                    $courseOptions = $conn->query("SELECT coursecode, name FROM course ORDER BY name");
                                    if ($courseOptions) {
                                        while ($course = $courseOptions->fetch_assoc()) {
                                            echo '<option value="' . htmlspecialchars($course['coursecode']) . '">' . htmlspecialchars($course['coursecode'] . ' - ' . $course['name']) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </label>

                            <div class="two-col">
                                <label>
                                    Preferred day
                                    <select name="preferred_day" required>
                                        <option value="">Select day</option>
                                        <option>Monday</option>
                                        <option>Tuesday</option>
                                        <option>Wednesday</option>
                                        <option>Thursday</option>
                                        <option>Friday</option>
                                        <option>Saturday</option>
                                        <option>Sunday</option>
                                    </select>
                                </label>

                                <label>
                                    Preferred time
                                    <input type="time" name="preferred_time" required>
                                </label>
                            </div>

                            <label>
                                Reason for consultation
                                <textarea name="reason" placeholder="Explain what you need help with..." required></textarea>
                            </label>
                        </div>

                        <button type="submit" name="request_consultation" class="primary-btn">Send request</button>
                    </form>
                </div>

                <div class="panel">
                    <h3>My consultation requests</h3>
                    <?php if (empty($studentRequests)): ?>
                        <p class="empty-state">No consultation requests yet. Choose a faculty and send your first request.</p>
                    <?php else: ?>
                        <div class="request-list">
                            <?php foreach ($studentRequests as $request): ?>
                                <div class="request-item">
                                    <div class="request-top">
                                        <h4><?php echo htmlspecialchars($request['faculty_name'] ?? 'Faculty'); ?></h4>
                                        <span class="status-badge status-<?php echo htmlspecialchars($request['status']); ?>"><?php echo ucfirst(htmlspecialchars($request['status'])); ?></span>
                                    </div>
                                    <p><strong>Course:</strong> <?php echo htmlspecialchars($request['coursecode'] ?: 'Not specified'); ?></p>
                                    <p><strong>Preferred slot:</strong> <?php echo htmlspecialchars($request['preferred_day'] . ' at ' . $request['preferred_time']); ?></p>
                                    <p><strong>Reason:</strong> <?php echo htmlspecialchars($request['reason']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php else: ?>
            <section class="panel single-panel">
                <h3>Consultation requests for your office hours</h3>
                <?php if (empty($facultyRequests)): ?>
                    <p class="empty-state">No consultation requests received yet.</p>
                <?php else: ?>
                    <div class="request-list">
                        <?php foreach ($facultyRequests as $request): ?>
                            <div class="request-item">
                                <div class="request-top">
                                    <h4><?php echo htmlspecialchars($request['student_name'] ?? 'Student'); ?></h4>
                                    <span class="status-badge status-<?php echo htmlspecialchars($request['status']); ?>"><?php echo ucfirst(htmlspecialchars($request['status'])); ?></span>
                                </div>
                                <p><strong>Course:</strong> <?php echo htmlspecialchars($request['coursecode'] ?: 'Not specified'); ?></p>
                                <p><strong>Preferred slot:</strong> <?php echo htmlspecialchars($request['preferred_day'] . ' at ' . $request['preferred_time']); ?></p>
                                <p><strong>Reason:</strong> <?php echo htmlspecialchars($request['reason']); ?></p>
                                <p><strong>Requested on:</strong> <?php echo htmlspecialchars(date('d M Y', strtotime($request['created_at']))); ?></p>

                                <?php if ($request['status'] === 'pending'): ?>
                                    <div class="action-row">
                                        <form method="POST" class="inline-form">
                                            <input type="hidden" name="request_id" value="<?php echo (int)$request['request_id']; ?>">
                                            <button type="submit" name="update_request_status" value="accepted" class="accept-btn">Accept</button>
                                        </form>
                                        <form method="POST" class="inline-form">
                                            <input type="hidden" name="request_id" value="<?php echo (int)$request['request_id']; ?>">
                                            <button type="submit" name="update_request_status" value="rejected" class="reject-btn">Reject</button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <p>© 2026 Studyverse. All rights reserved.</p>
    </footer>
</body>
</html>
