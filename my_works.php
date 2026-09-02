<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['userID'];
$uploadDir = __DIR__ . '/uploads/works';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

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

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_work'])) {
    $title = trim($_POST['title'] ?? '');
    $workType = trim($_POST['work_type'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title === '' || $workType === '') {
        $errorMessage = 'Please enter a title and select the work type.';
    } elseif (!isset($_FILES['work_file']) || $_FILES['work_file']['error'] !== UPLOAD_ERR_OK) {
        $errorMessage = 'Please choose a valid file to upload.';
    } else {
        $allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'zip', 'rar', 'png', 'jpg', 'jpeg'];
        $fileName = basename($_FILES['work_file']['name']);
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed, true)) {
            $errorMessage = 'Unsupported file type. Upload PDF, Word, PowerPoint, text, ZIP, image, or archive files.';
        } else {
            $safeBase = preg_replace('/[^A-Za-z0-9_-]+/', '_', pathinfo($fileName, PATHINFO_FILENAME));
            $storedName = $safeBase . '_' . time() . '_' . uniqid() . '.' . $ext;
            $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $storedName;

            if (!move_uploaded_file($_FILES['work_file']['tmp_name'], $targetPath)) {
                $errorMessage = 'Failed to upload the file. Please try again.';
            } else {
                $stmt = $conn->prepare('INSERT INTO student_work_submission (student_uID, title, work_type, description, file_name, file_path) VALUES (?, ?, ?, ?, ?, ?)');
                if (!$stmt) {
                    $errorMessage = 'Could not save your work for review.';
                    unlink($targetPath);
                } else {
                    $relativePath = 'uploads/works/' . $storedName;
                    $stmt->bind_param('ssssss', $user_id, $title, $workType, $description, $fileName, $relativePath);
                    if ($stmt->execute()) {
                        $successMessage = 'Your work was uploaded successfully and is ready for faculty review.';
                    } else {
                        $errorMessage = 'Failed to save your work: ' . $stmt->error;
                        unlink($targetPath);
                    }
                    $stmt->close();
                }
            }
        }
    }
}

$myWorks = [];
$workStmt = $conn->prepare('SELECT submission_id, title, work_type, description, file_name, file_path, uploaded_at FROM student_work_submission WHERE student_uID = ? ORDER BY uploaded_at DESC');
if ($workStmt) {
    $workStmt->bind_param('s', $user_id);
    $workStmt->execute();
    $workResult = $workStmt->get_result();
    while ($row = $workResult->fetch_assoc()) {
        $row['reviews'] = [];
        $reviewStmt = $conn->prepare('SELECT r.rating, r.review, r.feedback, ui.name AS faculty_name FROM faculty_work_reviews r LEFT JOIN user_info ui ON ui.userID = r.faculty_uID WHERE r.submission_id = ? ORDER BY r.created_at DESC');
        if ($reviewStmt) {
            $reviewStmt->bind_param('i', $row['submission_id']);
            $reviewStmt->execute();
            $reviewResult = $reviewStmt->get_result();
            while ($reviewRow = $reviewResult->fetch_assoc()) {
                $row['reviews'][] = $reviewRow;
            }
            $reviewStmt->close();
        }
        $myWorks[] = $row;
    }
    $workStmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Works - Studyverse</title>
    <link rel="stylesheet" href="student_dash.css">
    <link rel="stylesheet" href="my_works.css">
</head>
<body>
    <header class="navbar">
        <div class="nav-container">
            <div class="brand">Studyverse</div>
            <nav class="nav-links">
                <a href="student_dashboard.php">Dashboard</a>
                <a href="student_profile.php">Profile</a>
                <a href="mycourses.php">My Courses</a>
                <a href="my_works.php" class="active">My Works</a>
                <a href="mygroups.php">My Groups</a>
                <a href="findmentor.php">Find Mentor</a>
                <a href="faculties.php">Faculties</a>
            </nav>
            <div class="nav-user">
                <span class="user-name">ID: <?php echo htmlspecialchars($user_id); ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <section class="welcome-banner">
            <h2>My Works</h2>
            <p>Upload project work, papers, and journals so faculty can review, rate, and provide feedback.</p>
        </section>

        <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <?php if ($errorMessage !== ''): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <section class="panel-grid">
            <div class="panel">
                <h3>Upload a new work</h3>
                <form method="POST" enctype="multipart/form-data" class="work-form">
                    <div class="form-grid">
                        <label>
                            Title
                            <input type="text" name="title" placeholder="e.g. Data Mining Project Report" required>
                        </label>

                        <label>
                            Work type
                            <select name="work_type" required>
                                <option value="">Select type</option>
                                <option value="Project">Project</option>
                                <option value="Paper">Paper</option>
                                <option value="Journal">Journal</option>
                            </select>
                        </label>

                        <label>
                            Description
                            <textarea name="description" placeholder="Briefly describe your project or paper"></textarea>
                        </label>

                        <label>
                            Upload file
                            <input type="file" name="work_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.zip,.rar,.png,.jpg,.jpeg" required>
                        </label>
                    </div>

                    <button type="submit" name="upload_work" class="primary-btn">Upload work</button>
                </form>
            </div>

            <div class="panel">
                <h3>Uploaded works</h3>
                <?php if (empty($myWorks)): ?>
                    <p class="empty-state">No uploaded work yet. Add your project, paper, or journal to begin the review process.</p>
                <?php else: ?>
                    <div class="work-list">
                        <?php foreach ($myWorks as $work): ?>
                            <div class="work-item">
                                <div class="work-meta">
                                    <span class="badge"><?php echo htmlspecialchars($work['work_type']); ?></span>
                                    <span class="status-badge <?php echo !empty($work['reviews']) ? 'reviewed' : 'pending'; ?>"><?php echo !empty($work['reviews']) ? 'Reviewed' : 'Pending'; ?></span>
                                    <span><?php echo htmlspecialchars(date('d M Y', strtotime($work['uploaded_at']))); ?></span>
                                </div>
                                <h4><?php echo htmlspecialchars($work['title']); ?></h4>
                                <?php if (!empty($work['description'])): ?>
                                    <p><?php echo htmlspecialchars($work['description']); ?></p>
                                <?php endif; ?>
                                <a class="download-btn" href="<?php echo htmlspecialchars($work['file_path']); ?>" target="_blank" rel="noopener">Open / Download file</a>

                                <?php if (!empty($work['reviews'])): ?>
                                    <div class="review-panel">
                                        <h5>Faculty review</h5>
                                        <?php foreach ($work['reviews'] as $review): ?>
                                            <div class="faculty-review-item">
                                                <p><strong><?php echo htmlspecialchars($review['faculty_name'] ?? 'Faculty'); ?></strong> rated it <?php echo (int)$review['rating']; ?>/5</p>
                                                <p><?php echo htmlspecialchars($review['review']); ?></p>
                                                <?php if (!empty($review['feedback'])): ?>
                                                    <p class="feedback"><strong>Feedback:</strong> <?php echo htmlspecialchars($review['feedback']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
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
