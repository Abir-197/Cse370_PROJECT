<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['userID'];

$conn->query("CREATE TABLE IF NOT EXISTS student_work_submission (
    submission_id INT AUTO_INCREMENT PRIMARY KEY,
    student_uID VARCHAR(8) NOT NULL,
    title VARCHAR(150) NOT NULL,
    work_type ENUM('Project','Paper','Journal') NOT NULL,
    description TEXT DEFAULT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS faculty_work_reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    faculty_uID VARCHAR(8) NOT NULL,
    rating TINYINT NOT NULL,
    review TEXT NOT NULL,
    feedback TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_review (submission_id, faculty_uID)
)");

$successMessage = '';
$errorMessage = '';
$studentSearch = trim($_GET['student_name'] ?? $_POST['student_name'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $submissionId = (int)($_POST['submission_id'] ?? 0);
    $rating = (int)($_POST['rating'] ?? 0);
    $review = trim($_POST['review'] ?? '');
    $feedback = trim($_POST['feedback'] ?? '');

    if ($submissionId <= 0 || $rating < 1 || $rating > 5 || $review === '') {
        $errorMessage = 'Please provide a valid rating and review for the submission.';
    } else {
        $checkSubmission = $conn->prepare('SELECT submission_id FROM student_work_submission WHERE submission_id = ?');
        if (!$checkSubmission) {
            $errorMessage = 'Unable to validate the submission.';
        } else {
            $checkSubmission->bind_param('i', $submissionId);
            $checkSubmission->execute();
            $submissionExists = $checkSubmission->get_result()->num_rows > 0;
            $checkSubmission->close();

            if (!$submissionExists) {
                $errorMessage = 'Selected submission was not found.';
            } else {
                $stmt = $conn->prepare('INSERT INTO faculty_work_reviews (submission_id, faculty_uID, rating, review, feedback) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE rating = VALUES(rating), review = VALUES(review), feedback = VALUES(feedback), created_at = CURRENT_TIMESTAMP');
                if (!$stmt) {
                    $errorMessage = 'Could not submit your review.';
                } else {
                    $stmt->bind_param('isiss', $submissionId, $user_id, $rating, $review, $feedback);
                    if ($stmt->execute()) {
                        $successMessage = 'Your rating and review were submitted successfully.';
                    } else {
                        $errorMessage = 'Failed to save the review: ' . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        }
    }
}

$submissions = [];
$submissionQuery = $conn->prepare('SELECT s.submission_id, s.student_uID, ui.name AS student_name, s.title, s.work_type, s.description, s.file_name, s.file_path, s.uploaded_at
    FROM student_work_submission s
    LEFT JOIN user_info ui ON ui.userID = s.student_uID
    WHERE ui.name LIKE ?
    ORDER BY s.uploaded_at DESC');
if ($submissionQuery) {
    $studentSearchPattern = '%' . $studentSearch . '%';
    $submissionQuery->bind_param('s', $studentSearchPattern);
    $submissionQuery->execute();
    $submissionResult = $submissionQuery->get_result();
    while ($row = $submissionResult->fetch_assoc()) {
        $row['reviews'] = [];
        $row['current_faculty_review'] = null;
        $reviewStmt = $conn->prepare('SELECT r.faculty_uID, r.rating, r.review, r.feedback, ui.name AS faculty_name FROM faculty_work_reviews r LEFT JOIN user_info ui ON ui.userID = r.faculty_uID WHERE r.submission_id = ? ORDER BY r.created_at DESC');
        if ($reviewStmt) {
            $reviewStmt->bind_param('i', $row['submission_id']);
            $reviewStmt->execute();
            $reviewResult = $reviewStmt->get_result();
            while ($reviewRow = $reviewResult->fetch_assoc()) {
                $row['reviews'][] = $reviewRow;
                if ($reviewRow['faculty_uID'] === $user_id) {
                    $row['current_faculty_review'] = $reviewRow;
                }
            }
            $reviewStmt->close();
        }
        $submissions[] = $row;
    }
    $submissionQuery->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects & Papers - Studyverse</title>
    <link rel="stylesheet" href="student_dash.css">
    <link rel="stylesheet" href="rate_projects.css">
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
                <a href="faculty_consultation_requests.php">Consultations</a>
                <a href="rate_projects.php" class="active">Projects & Papers</a>
            </nav>
            <div class="nav-user">
                <span class="user-name">ID: <?php echo htmlspecialchars($user_id); ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <section class="welcome-banner">
            <h2>Project and Paper Review</h2>
            <p>Review student submissions, rate their work, and leave feedback to guide their progress.</p>
        </section>

        <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <?php if ($errorMessage !== ''): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <form method="GET" class="search-panel">
            <label for="studentSearch">Find work by student name</label>
            <div class="search-row">
                <input type="search" id="studentSearch" name="student_name" value="<?php echo htmlspecialchars($studentSearch); ?>" placeholder="Enter student name">
                <button type="submit" class="primary-btn search-btn">Search</button>
                <?php if ($studentSearch !== ''): ?>
                    <a href="rate_projects.php" class="clear-search">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if (empty($submissions)): ?>
            <div class="panel empty-panel">
                <p class="empty-state"><?php echo $studentSearch !== '' ? 'No student work matched that name.' : 'No student work has been uploaded yet.'; ?></p>
            </div>
        <?php else: ?>
            <div class="submission-list">
                <?php foreach ($submissions as $submission): ?>
                    <div class="panel submission-card">
                        <div class="submission-head">
                            <div>
                                <span class="badge"><?php echo htmlspecialchars($submission['work_type']); ?></span>
                                <h3><?php echo htmlspecialchars($submission['title']); ?></h3>
                            </div>
                            <div class="status-group">
                                <span class="status-badge <?php echo !empty($submission['reviews']) ? 'reviewed' : 'pending'; ?>"><?php echo !empty($submission['reviews']) ? 'Reviewed' : 'Pending'; ?></span>
                                <span class="uploader">Uploaded by: <?php echo htmlspecialchars($submission['student_name'] ?? 'Unknown Student'); ?></span>
                            </div>
                        </div>

                        <p class="meta-line"><?php echo htmlspecialchars(date('d M Y', strtotime($submission['uploaded_at']))); ?></p>

                        <?php if (!empty($submission['description'])): ?>
                            <p class="description"><?php echo htmlspecialchars($submission['description']); ?></p>
                        <?php endif; ?>

                        <div class="file-box">
                            <strong>Submitted work:</strong>
                            <a href="<?php echo htmlspecialchars($submission['file_path']); ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars($submission['file_name']); ?></a>
                        </div>

                        <?php if (!empty($submission['reviews'])): ?>
                            <div class="review-block">
                                <h4>Existing reviews</h4>
                                <?php foreach ($submission['reviews'] as $review): ?>
                                    <div class="review-item <?php echo ($submission['current_faculty_review'] && $submission['current_faculty_review']['faculty_uID'] == $review['faculty_uID']) ? 'current-review' : ''; ?>">
                                        <p><strong><?php echo htmlspecialchars($review['faculty_name'] ?? 'Faculty'); ?></strong> rated it <?php echo (int)$review['rating']; ?>/5</p>
                                        <p><?php echo htmlspecialchars($review['review']); ?></p>
                                        <?php if (!empty($review['feedback'])): ?>
                                            <p class="feedback"><strong>Feedback:</strong> <?php echo htmlspecialchars($review['feedback']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="review-form">
                            <input type="hidden" name="submission_id" value="<?php echo (int)$submission['submission_id']; ?>">
                            <input type="hidden" name="student_name" value="<?php echo htmlspecialchars($studentSearch); ?>">
                            <div class="form-grid">
                                <label>
                                    Rate and review
                                    <select name="rating" required>
                                        <option value="">Select rating</option>
                                        <option value="5" <?php echo (!empty($submission['current_faculty_review']) && (int)$submission['current_faculty_review']['rating'] === 5) ? 'selected' : ''; ?>>5 - Excellent</option>
                                        <option value="4" <?php echo (!empty($submission['current_faculty_review']) && (int)$submission['current_faculty_review']['rating'] === 4) ? 'selected' : ''; ?>>4 - Very Good</option>
                                        <option value="3" <?php echo (!empty($submission['current_faculty_review']) && (int)$submission['current_faculty_review']['rating'] === 3) ? 'selected' : ''; ?>>3 - Good</option>
                                        <option value="2" <?php echo (!empty($submission['current_faculty_review']) && (int)$submission['current_faculty_review']['rating'] === 2) ? 'selected' : ''; ?>>2 - Fair</option>
                                        <option value="1" <?php echo (!empty($submission['current_faculty_review']) && (int)$submission['current_faculty_review']['rating'] === 1) ? 'selected' : ''; ?>>1 - Needs Improvement</option>
                                    </select>
                                </label>

                                <label>
                                    Review
                                    <textarea name="review" placeholder="Describe the strengths and areas for improvement" required><?php echo htmlspecialchars(!empty($submission['current_faculty_review']) ? $submission['current_faculty_review']['review'] : ''); ?></textarea>
                                </label>

                                <label>
                                    Feedback (optional)
                                    <textarea name="feedback" placeholder="Optional short feedback for the student"><?php echo htmlspecialchars(!empty($submission['current_faculty_review']) ? ($submission['current_faculty_review']['feedback'] ?? '') : ''); ?></textarea>
                                </label>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="submit_review" class="primary-btn"><?php echo !empty($submission['current_faculty_review']) ? 'Update Review' : 'Rate and Review'; ?></button>
                                <?php if (!empty($submission['current_faculty_review'])): ?>
                                    <span class="mini-label">Your review is saved and editable.</span>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <p>© 2026 Studyverse. All rights reserved.</p>
    </footer>
</body>
</html>
