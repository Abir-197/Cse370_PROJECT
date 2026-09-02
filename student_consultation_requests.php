<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['userID'];

$successMessage = '';
$errorMessage = '';

$ensureConsultationSchema = function () use ($conn) {
    $conn->query("CREATE TABLE IF NOT EXISTS consultation_availability (
        availability_id INT AUTO_INCREMENT PRIMARY KEY,
        faculty_uID VARCHAR(8) NOT NULL,
        day_name VARCHAR(20) NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        notes VARCHAR(255) DEFAULT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $conn->query("CREATE TABLE IF NOT EXISTS consultation_requests (
        request_id INT AUTO_INCREMENT PRIMARY KEY,
        student_uID VARCHAR(8) NOT NULL,
        faculty_uID VARCHAR(8) NOT NULL,
        availability_id INT NULL,
        reason TEXT NULL,
        status ENUM('pending', 'accepted', 'rejected') NOT NULL DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    $columns = [];
    $result = $conn->query('SHOW COLUMNS FROM consultation_requests');
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
    }

    $missingColumns = [
        'availability_id' => "ALTER TABLE consultation_requests ADD COLUMN availability_id INT NULL AFTER faculty_uID",
        'reason' => "ALTER TABLE consultation_requests ADD COLUMN reason TEXT NULL AFTER availability_id",
        'status' => "ALTER TABLE consultation_requests ADD COLUMN status ENUM('pending', 'accepted', 'rejected') NOT NULL DEFAULT 'pending' AFTER reason"
    ];

    foreach ($missingColumns as $columnName => $sql) {
        if (!in_array($columnName, $columns, true)) {
            $conn->query($sql);
        }
    }
};

$ensureConsultationSchema();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_consultation'])) {
    $faculty_uID = trim($_POST['faculty_uID'] ?? '');
    $availability_id = (int)($_POST['availability_id'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');

    if ($faculty_uID === '' || $availability_id <= 0 || $reason === '') {
        $errorMessage = 'Please select a faculty slot and provide a reason for the consultation.';
    } else {
        $slotCheck = $conn->prepare('SELECT availability_id, faculty_uID, day_name, start_time, end_time
            FROM consultation_availability
            WHERE availability_id = ? AND faculty_uID = ? AND is_active = 1
            AND (SELECT COUNT(*) FROM consultation_requests WHERE availability_id = consultation_availability.availability_id AND status = "accepted") < 2');
        if (!$slotCheck) {
            $errorMessage = 'Could not validate the consultation slot.';
        } else {
            $slotCheck->bind_param('is', $availability_id, $faculty_uID);
            $slotCheck->execute();
            $slotRow = $slotCheck->get_result()->fetch_assoc();
            $slotCheck->close();

            if (!$slotRow) {
                $errorMessage = 'That consultation slot is no longer available.';
            } else {
                $requestExist = $conn->prepare('SELECT request_id FROM consultation_requests WHERE student_uID = ? AND faculty_uID = ? AND availability_id = ? AND status = "pending"');
                if (!$requestExist) {
                    $errorMessage = 'Unable to create consultation request right now.';
                } else {
                    $requestExist->bind_param('sis', $user_id, $faculty_uID, $availability_id);
                    $requestExist->execute();
                    $requestExists = $requestExist->get_result()->num_rows > 0;
                    $requestExist->close();

                    if ($requestExists) {
                        $errorMessage = 'You already sent a pending request for this slot.';
                    } else {
                        $stmt = $conn->prepare('INSERT INTO consultation_requests (student_uID, faculty_uID, availability_id, reason, status) VALUES (?, ?, ?, ?, "pending")');
                        if (!$stmt) {
                            $errorMessage = 'Could not submit consultation request.';
                        } else {
                           $stmt->bind_param('ssis', $user_id, $faculty_uID, $availability_id, $reason);
                            if ($stmt->execute()) {
                                $successMessage = 'Consultation request sent successfully. The faculty will review it.';
                            } else {
                                $errorMessage = 'Failed to send consultation request: ' . $stmt->error;
                            }
                            $stmt->close();
                        }
                    }
                }
            }
        }
    }
}

$facultyList = [];
$facultyQuery = $conn->prepare('SELECT fi.userID, ui.name, fi.Dept FROM faculty_info fi LEFT JOIN user_info ui ON ui.userID = fi.userID ORDER BY ui.name');
if ($facultyQuery) {
    $facultyQuery->execute();
    $facultyResult = $facultyQuery->get_result();
    while ($row = $facultyResult->fetch_assoc()) {
        $facultyList[] = $row;
    }
    $facultyQuery->close();
}

$availableSlots = [];
$slotQuery = $conn->prepare('SELECT ca.availability_id, ca.faculty_uID, ui.name AS faculty_name, fi.Dept, ca.day_name, ca.start_time, ca.end_time, ca.notes
    FROM consultation_availability ca
    LEFT JOIN user_info ui ON ui.userID = ca.faculty_uID
    LEFT JOIN faculty_info fi ON fi.userID = ca.faculty_uID
    LEFT JOIN (
        SELECT availability_id, COUNT(*) AS accepted_count
        FROM consultation_requests
        WHERE status = "accepted"
        GROUP BY availability_id
    ) accepted ON accepted.availability_id = ca.availability_id
    WHERE ca.is_active = 1 AND COALESCE(accepted.accepted_count, 0) < 2
    ORDER BY ui.name, FIELD(ca.day_name, "Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"), ca.start_time');
if ($slotQuery) {
    $slotQuery->execute();
    $slotResult = $slotQuery->get_result();
    while ($row = $slotResult->fetch_assoc()) {
        $availableSlots[] = $row;
    }
    $slotQuery->close();
}

$studentRequests = [];
$studentRequestStmt = $conn->prepare('SELECT cr.request_id, ui.name AS faculty_name, ca.day_name, ca.start_time, ca.end_time, cr.reason, cr.status, cr.created_at FROM consultation_requests cr LEFT JOIN user_info ui ON ui.userID = cr.faculty_uID LEFT JOIN consultation_availability ca ON ca.availability_id = cr.availability_id WHERE cr.student_uID = ? ORDER BY cr.created_at DESC');
if ($studentRequestStmt) {
    $studentRequestStmt->bind_param('s', $user_id);
    $studentRequestStmt->execute();
    $studentResult = $studentRequestStmt->get_result();
    while ($row = $studentResult->fetch_assoc()) {
        $studentRequests[] = $row;
    }
    $studentRequestStmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Consultation Requests - Studyverse</title>
    <link rel="stylesheet" href="student_dash.css">
    <link rel="stylesheet" href="student_consultation_requests.css">
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
                <a href="student_consultation_requests.php" class="active">Consultations</a>
            </nav>
            <div class="nav-user">
                <span class="user-name">ID: <?php echo htmlspecialchars($user_id); ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <section class="welcome-banner">
            <h2>Student Consultation Requests</h2>
            <p>Choose a faculty office hour, request a consultation, and wait for approval or rejection.</p>
        </section>

        <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <?php if ($errorMessage !== ''): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <section class="panel-grid">
            <div class="panel">
                <h3>Request a consultation</h3>
                <form method="POST" class="consult-form">
                    <div class="form-grid">
                        <label>
                            Faculty
                            <select name="faculty_uID" id="facultySelect" required>
                                <option value="">Select faculty</option>
                                <?php foreach ($facultyList as $faculty): ?>
                                    <option value="<?php echo htmlspecialchars($faculty['userID']); ?>"><?php echo htmlspecialchars($faculty['name'] . ' (' . $faculty['Dept'] . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label>
                            Available time slot
                            <select name="availability_id" id="slotSelect" required>
                                <option value="">Select a slot</option>
                                <?php foreach ($availableSlots as $slot): ?>
                                    <option value="<?php echo (int)$slot['availability_id']; ?>" data-faculty="<?php echo htmlspecialchars($slot['faculty_uID']); ?>">
                                        <?php echo htmlspecialchars($slot['day_name'] . ' ' . substr($slot['start_time'], 0, 5) . ' - ' . substr($slot['end_time'], 0, 5)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label>
                            Reason for consultation
                            <textarea name="reason" placeholder="Explain the topic or issue you need help with" required></textarea>
                        </label>
                    </div>

                    <button type="submit" name="request_consultation" class="primary-btn">Send request</button>
                </form>
            </div>

            <div class="panel">
                <h3>My consultation history</h3>
                <?php if (empty($studentRequests)): ?>
                    <p class="empty-state">No consultation requests yet.</p>
                <?php else: ?>
                    <div class="request-list">
                        <?php foreach ($studentRequests as $request): ?>
                            <div class="request-item">
                                <div class="request-top">
                                    <h4><?php echo htmlspecialchars($request['faculty_name'] ?? 'Faculty'); ?></h4>
                                    <span class="status-badge status-<?php echo htmlspecialchars($request['status']); ?>"><?php echo ucfirst(htmlspecialchars($request['status'])); ?></span>
                                </div>
                                <p><strong>Time:</strong> <?php echo htmlspecialchars($request['day_name'] . ' ' . substr($request['start_time'], 0, 5) . ' - ' . substr($request['end_time'], 0, 5)); ?></p>
                                <p><strong>Reason:</strong> <?php echo htmlspecialchars($request['reason']); ?></p>
                                <p><strong>Requested on:</strong> <?php echo htmlspecialchars(date('d M Y', strtotime($request['created_at']))); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer class="footer">
        <p>© 2026 Studyverse. All rights reserved.</p>
    </footer>
    <script>
        const facultySelect = document.getElementById('facultySelect');
        const slotSelect = document.getElementById('slotSelect');
        const slotOptions = Array.from(slotSelect.querySelectorAll('option[data-faculty]'));

        function filterFacultySlots() {
            const facultyId = facultySelect.value;
            slotSelect.value = '';
            slotOptions.forEach(function (option) {
                option.hidden = facultyId === '' || option.dataset.faculty !== facultyId;
            });
        }

        facultySelect.addEventListener('change', filterFacultySlots);
        filterFacultySlots();
    </script>
</body>
</html>
