<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['userID'];

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
    availability_id INT DEFAULT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'accepted', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY faculty_uID (faculty_uID),
    KEY student_uID (student_uID),
    KEY availability_id (availability_id)
)");

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_slot'])) {
        $day = trim($_POST['day_name'] ?? '');
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';
        $notes = trim($_POST['notes'] ?? '');

        if ($day === '' || $start_time === '' || $end_time === '') {
            $errorMessage = 'Please provide day and time for the consultation slot.';
        } elseif ($start_time >= $end_time) {
            $errorMessage = 'End time must be later than start time.';
        } else {
            $slotStmt = $conn->prepare('INSERT INTO consultation_availability (faculty_uID, day_name, start_time, end_time, notes, is_active) VALUES (?, ?, ?, ?, ?, 1)');
            if (!$slotStmt) {
                $errorMessage = 'Could not add consultation slot.';
            } else {
                $slotStmt->bind_param('sssss', $user_id, $day, $start_time, $end_time, $notes);
                if ($slotStmt->execute()) {
                    $successMessage = 'Consultation slot added successfully.';
                } else {
                    $errorMessage = 'Failed to save slot: ' . $slotStmt->error;
                }
                $slotStmt->close();
            }
        }
    }

    if (isset($_POST['update_request_status'])) {
        $request_id = (int)($_POST['request_id'] ?? 0);
        $status = $_POST['update_request_status'] ?? '';
        $allowed = ['accepted', 'rejected'];

        if ($request_id <= 0 || !in_array($status, $allowed, true)) {
            $errorMessage = 'Invalid consultation request status.';
        } else {
            $check = $conn->prepare('SELECT faculty_uID FROM consultation_requests WHERE request_id = ?');
            if (!$check) {
                $errorMessage = 'Could not validate consultation request.';
            } else {
                $check->bind_param('i', $request_id);
                $check->execute();
                $row = $check->get_result()->fetch_assoc();
                $check->close();

                if (!$row || $row['faculty_uID'] !== $user_id) {
                    $errorMessage = 'You are not allowed to update this request.';
                } else {
                    $requestInfo = $conn->prepare('SELECT availability_id, status FROM consultation_requests WHERE request_id = ? AND faculty_uID = ?');
                    if (!$requestInfo) {
                        $errorMessage = 'Unable to validate consultation request.';
                    } else {
                        $requestInfo->bind_param('is', $request_id, $user_id);
                        $requestInfo->execute();
                        $requestData = $requestInfo->get_result()->fetch_assoc();
                        $requestInfo->close();

                        if (!$requestData) {
                            $errorMessage = 'Consultation request was not found.';
                        } elseif ($status === 'accepted' && $requestData['status'] !== 'pending') {
                            $errorMessage = 'Only pending requests can be accepted.';
                        } elseif ($status === 'accepted') {
                            $conn->begin_transaction();
                            $slotLock = $conn->prepare('SELECT availability_id FROM consultation_availability WHERE availability_id = ? AND faculty_uID = ? FOR UPDATE');
                            if (!$slotLock) {
                                $conn->rollback();
                                $errorMessage = 'Unable to check slot capacity.';
                            } else {
                                $slotLock->bind_param('is', $requestData['availability_id'], $user_id);
                                $slotLock->execute();
                                $slotExists = $slotLock->get_result()->num_rows > 0;
                                $slotLock->close();

                                if (!$slotExists) {
                                    $conn->rollback();
                                    $errorMessage = 'The consultation slot is no longer available.';
                                    $acceptedCount = 2;
                                } else {
                                    $capacityCheck = $conn->prepare('SELECT COUNT(*) AS accepted_count FROM consultation_requests WHERE availability_id = ? AND status = "accepted"');
                                    if (!$capacityCheck) {
                                        $conn->rollback();
                                        $errorMessage = 'Unable to check slot capacity.';
                                        $acceptedCount = 2;
                                    } else {
                                        $capacityCheck->bind_param('i', $requestData['availability_id']);
                                        $capacityCheck->execute();
                                        $acceptedCount = (int)$capacityCheck->get_result()->fetch_assoc()['accepted_count'];
                                        $capacityCheck->close();
                                    }
                                }

                                if ($slotExists && $acceptedCount >= 2) {
                                    $rejectPending = $conn->prepare('UPDATE consultation_requests SET status = "rejected" WHERE availability_id = ? AND status = "pending"');
                                    if ($rejectPending) {
                                        $rejectPending->bind_param('i', $requestData['availability_id']);
                                        $rejected = $rejectPending->execute();
                                        $rejectPending->close();
                                        if (!$rejected) {
                                            $conn->rollback();
                                            $errorMessage = 'Unable to reject the remaining consultation requests.';
                                        }
                                    } else {
                                        $conn->rollback();
                                        $errorMessage = 'Unable to reject the remaining consultation requests.';
                                    }
                                    if ($errorMessage === '') {
                                        $conn->commit();
                                        $errorMessage = 'This slot already has two accepted students. Remaining pending requests were rejected.';
                                    }
                                } else {
                                    $update = $conn->prepare('UPDATE consultation_requests SET status = "accepted" WHERE request_id = ? AND status = "pending"');
                                    if (!$update) {
                                        $conn->rollback();
                                        $errorMessage = 'Unable to update consultation request.';
                                    } else {
                                        $update->bind_param('i', $request_id);
                                        $updated = $update->execute();
                                        $update->close();
                                        if (!$updated) {
                                            $conn->rollback();
                                            $errorMessage = 'Failed to accept consultation request.';
                                        } else {
                                            if ($acceptedCount === 1) {
                                                $rejectPending = $conn->prepare('UPDATE consultation_requests SET status = "rejected" WHERE availability_id = ? AND status = "pending"');
                                                if (!$rejectPending) {
                                                    $conn->rollback();
                                                    $errorMessage = 'Unable to update remaining consultation requests.';
                                                } else {
                                                    $rejectPending->bind_param('i', $requestData['availability_id']);
                                                    $rejected = $rejectPending->execute();
                                                    $rejectPending->close();
                                                    if (!$rejected) {
                                                        $conn->rollback();
                                                        $errorMessage = 'Unable to reject the remaining consultation requests.';
                                                    } else {
                                                        $conn->commit();
                                                        $successMessage = 'Consultation request accepted. The slot is now full and remaining requests were rejected.';
                                                    }
                                                }
                                            } else {
                                                $conn->commit();
                                                $successMessage = 'Consultation request accepted.';
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            $update = $conn->prepare('UPDATE consultation_requests SET status = "rejected" WHERE request_id = ? AND status = "pending"');
                            if (!$update) {
                                $errorMessage = 'Unable to update consultation request.';
                            } else {
                                $update->bind_param('i', $request_id);
                                if ($update->execute()) {
                                    $successMessage = 'Consultation request rejected.';
                                } else {
                                    $errorMessage = 'Failed to reject consultation request: ' . $update->error;
                                }
                                $update->close();
                            }
                        }
                    }
                }
            }
        }
    }

    if (isset($_POST['delete_slot'])) {
        $slot_id = (int)($_POST['slot_id'] ?? 0);

        if ($slot_id <= 0) {
            $errorMessage = 'Please select a valid slot to delete.';
        } else {
            $slotCheck = $conn->prepare('SELECT faculty_uID FROM consultation_availability WHERE availability_id = ?');
            if (!$slotCheck) {
                $errorMessage = 'Could not validate the consultation slot.';
            } else {
                $slotCheck->bind_param('i', $slot_id);
                $slotCheck->execute();
                $slotRow = $slotCheck->get_result()->fetch_assoc();
                $slotCheck->close();

                if (!$slotRow || $slotRow['faculty_uID'] !== $user_id) {
                    $errorMessage = 'You are not allowed to delete this consultation slot.';
                } else {
                    $deleteRequests = $conn->prepare('DELETE FROM consultation_requests WHERE availability_id = ?');
                    if ($deleteRequests) {
                        $deleteRequests->bind_param('i', $slot_id);
                        $deleteRequests->execute();
                        $deleteRequests->close();
                    }

                    $deleteSlot = $conn->prepare('DELETE FROM consultation_availability WHERE availability_id = ?');
                    if (!$deleteSlot) {
                        $errorMessage = 'Unable to delete the consultation slot.';
                    } else {
                        $deleteSlot->bind_param('i', $slot_id);
                        if ($deleteSlot->execute()) {
                            $successMessage = 'Consultation slot deleted successfully.';
                        } else {
                            $errorMessage = 'Failed to delete the slot: ' . $deleteSlot->error;
                        }
                        $deleteSlot->close();
                    }
                }
            }
        }
    }
}

$facultySlots = [];
$slotQuery = $conn->prepare('SELECT availability_id, day_name, start_time, end_time, notes FROM consultation_availability WHERE faculty_uID = ? AND is_active = 1 ORDER BY FIELD(day_name, "Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"), start_time');
if ($slotQuery) {
    $slotQuery->bind_param('s', $user_id);
    $slotQuery->execute();
    $slotResult = $slotQuery->get_result();
    while ($row = $slotResult->fetch_assoc()) {
        $facultySlots[] = $row;
    }
    $slotQuery->close();
}

$requests = [];
$requestStmt = $conn->prepare('SELECT cr.request_id, cr.student_uID, ui.name AS student_name, cr.reason, cr.status, cr.created_at, ca.day_name, ca.start_time, ca.end_time
    FROM consultation_requests cr
    LEFT JOIN user_info ui ON ui.userID = cr.student_uID
    LEFT JOIN consultation_availability ca ON ca.availability_id = cr.availability_id
    LEFT JOIN (
        SELECT availability_id, COUNT(*) AS accepted_count
        FROM consultation_requests
        WHERE status = "accepted"
        GROUP BY availability_id
    ) accepted ON accepted.availability_id = cr.availability_id
    WHERE cr.faculty_uID = ? AND NOT (cr.status = "pending" AND COALESCE(accepted.accepted_count, 0) >= 2)
    ORDER BY cr.created_at DESC');
if ($requestStmt) {
    $requestStmt->bind_param('s', $user_id);
    $requestStmt->execute();
    $requestResult = $requestStmt->get_result();
    while ($row = $requestResult->fetch_assoc()) {
        $requests[] = $row;
    }
    $requestStmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Consultation Requests - Studyverse</title>
    <link rel="stylesheet" href="student_dash.css">
    <link rel="stylesheet" href="faculty_consultation_requests.css">
</head>
<body>
    <header class="navbar">
        <div class="nav-container">
            <div class="brand">Studyverse</div>
            <nav class="nav-links">
                <a href="faculty_dashboard.php">Dashboard</a>
                <a href="faculty_profile.php">Profile</a>
                <a href="mycourses.php">My Courses</a>
                <a href="resources.php">Resources</a>
                <a href="faculty_consultation_requests.php" class="active">Consultations</a>
                <a href="rate_projects.php">Projects & Papers</a>
            </nav>
            <div class="nav-user">
                <span class="user-name">ID: <?php echo htmlspecialchars($user_id); ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <section class="welcome-banner">
            <h2>Faculty Consultation Requests</h2>
            <p>Add your office hours and review each student consultation request. Accept or reject based on your availability.</p>
        </section>

        <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <?php if ($errorMessage !== ''): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <section class="panel-grid">
            <div class="panel">
                <h3>Add consultation slot</h3>
                <form method="POST" class="consult-form">
                    <div class="form-grid">
                        <label>
                            Day
                            <select name="day_name" required>
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

                        <div class="two-col">
                            <label>
                                Start time
                                <input type="time" name="start_time" required>
                            </label>

                            <label>
                                End time
                                <input type="time" name="end_time" required>
                            </label>
                        </div>

                        <label>
                            Notes (optional)
                            <input type="text" name="notes" placeholder="Example: For CSE 110 and CSE 111 guidance">
                        </label>
                    </div>

                    <button type="submit" name="add_slot" class="primary-btn">Add slot</button>
                </form>

                <div class="slot-list-wrap">
                    <h4>My available slots</h4>
                    <?php if (empty($facultySlots)): ?>
                        <p class="empty-state">No consultation slots added yet.</p>
                    <?php else: ?>
                        <div class="slot-list">
                            <?php foreach ($facultySlots as $slot): ?>
                                <div class="slot-item">
                                    <div class="slot-main">
                                        <div>
                                            <strong><?php echo htmlspecialchars($slot['day_name']); ?></strong>
                                            <span><?php echo htmlspecialchars(substr($slot['start_time'], 0, 5) . ' - ' . substr($slot['end_time'], 0, 5)); ?></span>
                                            <?php if (!empty($slot['notes'])): ?>
                                                <small><?php echo htmlspecialchars($slot['notes']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <form method="POST" class="slot-delete-form">
                                            <input type="hidden" name="slot_id" value="<?php echo (int)$slot['availability_id']; ?>">
                                            <button type="submit" name="delete_slot" class="delete-btn" onclick="return confirm('Delete this consultation slot?');">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="panel">
                <h3>Student consultation requests</h3>
                <?php if (empty($requests)): ?>
                    <p class="empty-state">No consultation requests yet.</p>
                <?php else: ?>
                    <div class="request-list">
                        <?php foreach ($requests as $request): ?>
                            <div class="request-item">
                                <div class="request-top">
                                    <h4><?php echo htmlspecialchars($request['student_name'] ?? 'Student'); ?></h4>
                                    <span class="status-badge status-<?php echo htmlspecialchars($request['status']); ?>"><?php echo ucfirst(htmlspecialchars($request['status'])); ?></span>
                                </div>
                                <p><strong>Time:</strong> <?php echo htmlspecialchars($request['day_name'] . ' ' . substr($request['start_time'], 0, 5) . ' - ' . substr($request['end_time'], 0, 5)); ?></p>
                                <p><strong>Reason:</strong> <?php echo htmlspecialchars($request['reason']); ?></p>
                                <p><strong>Requested:</strong> <?php echo htmlspecialchars(date('d M Y', strtotime($request['created_at']))); ?></p>

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
            </div>
        </section>
    </main>

    <footer class="footer">
        <p>© 2026 Studyverse. All rights reserved.</p>
    </footer>
</body>
</html>
