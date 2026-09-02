<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['userID'];
$uploadDir = __DIR__ . '/uploads/resources';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_resource'])) {
    $coursecode = trim($_POST['coursecode'] ?? '');
    $resourceType = trim($_POST['resource_type'] ?? '');
    $resourceName = trim($_POST['resource_name'] ?? '');
    $resourceDescription = trim($_POST['resource_description'] ?? '');

    if ($coursecode === '' || $resourceType === '') {
        $errorMessage = 'Please select a course and a resource type.';
    } elseif (!isset($_FILES['resource_file']) || $_FILES['resource_file']['error'] !== UPLOAD_ERR_OK) {
        $errorMessage = 'Please choose a valid file to upload.';
    } else {
        $allowedExtensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'zip', 'rar', 'png', 'jpg', 'jpeg'];
        $fileName = basename($_FILES['resource_file']['name']);
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions, true)) {
            $errorMessage = 'Unsupported file type. Please upload PDF, Word, PowerPoint, text, ZIP, image, or archive files.';
        } else {
            $safeBaseName = preg_replace('/[^A-Za-z0-9_-]+/', '_', pathinfo($fileName, PATHINFO_FILENAME));
            $storedName = $safeBaseName . '_' . time() . '_' . uniqid() . '.' . $extension;
            $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $storedName;

            if (!move_uploaded_file($_FILES['resource_file']['tmp_name'], $targetPath)) {
                $errorMessage = 'Failed to upload the file. Please try again.';
            } else {
                $relativePath = 'uploads/resources/' . $storedName;
                $dataToStore = $resourceDescription !== '' ? $resourceDescription . ' | ' . $relativePath : $relativePath;
                $resourceID = 'RES' . substr(uniqid('', true), -7);
                $displayName = $resourceName !== '' ? $resourceName : $fileName;

                $insertQuery = $conn->prepare('INSERT INTO course_resouce (course_resourceID, student_UID, coursecode, resource_type, content, name) VALUES (?, ?, ?, ?, ?, ?)');
                if ($insertQuery === false) {
                    $errorMessage = 'Database insert error: ' . $conn->error;
                    unlink($targetPath);
                } else {
                    $insertQuery->bind_param('ssssss', $resourceID, $user_id, $coursecode, $resourceType, $dataToStore, $displayName);
                    if ($insertQuery->execute()) {
                        $successMessage = 'Resource uploaded successfully.';
                    } else {
                        $errorMessage = 'Upload failed: ' . $insertQuery->error;
                        unlink($targetPath);
                    }
                    $insertQuery->close();
                }
            }
        }
    }
}

$courses = [];
$courseQuery = $conn->prepare('SELECT c.coursecode, c.name FROM faculty_courselist fc JOIN course c ON fc.coursecode = c.coursecode WHERE fc.faculty_UID = ? ORDER BY c.name');
if ($courseQuery) {
    $courseQuery->bind_param('s', $user_id);
    $courseQuery->execute();
    $courseResult = $courseQuery->get_result();
    while ($row = $courseResult->fetch_assoc()) {
        $courses[] = $row;
    }
    $courseQuery->close();
}

$resources = [];
$resourceQuery = $conn->prepare('SELECT cr.course_resourceID, cr.coursecode, cr.resource_type, cr.name, cr.content, c.name AS course_name FROM course_resouce cr LEFT JOIN course c ON c.coursecode = cr.coursecode WHERE cr.student_UID = ? ORDER BY cr.course_resourceID DESC');
if ($resourceQuery) {
    $resourceQuery->bind_param('s', $user_id);
    $resourceQuery->execute();
    $resourceResult = $resourceQuery->get_result();
    while ($row = $resourceResult->fetch_assoc()) {
        $resources[] = $row;
    }
    $resourceQuery->close();
}

$uploadedResourceCount = count($resources);
$courseCount = count($courses);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Resources - Studyverse</title>
    <link rel="stylesheet" href="student_dash.css">
    <link rel="stylesheet" href="resources.css">
</head>
<body>
    <header class="navbar">
        <div class="nav-container">
            <div class="brand">Studyverse</div>
            <nav class="nav-links">
                <a href="faculty_dashboard.php">Dashboard</a>
                <a href="faculty_profile.php">Profile</a>
                <a href="mycourses.php">My Courses</a>
                <a href="resources.php" class="active">Resources</a>
                <a href="faculty_consultation_requests.php">Consultations</a>
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
            <h2>Faculty Resource Hub</h2>
            <p>Upload course content, class notes, previous quiz questions, assignments, and other study materials for your classes.</p>
        </section>

        <section class="stats-grid">
            <div class="stat-card">
                <span class="stat-label">Courses</span>
                <strong><?php echo htmlspecialchars($courseCount); ?></strong>
            </div>
            <div class="stat-card">
                <span class="stat-label">Uploaded</span>
                <strong><?php echo htmlspecialchars($uploadedResourceCount); ?></strong>
            </div>
            <div class="stat-card">
                <span class="stat-label">Status</span>
                <strong>Ready</strong>
            </div>
        </section>

        <section class="resource-layout">
            <div class="panel">
                <h3>Upload new resource</h3>

                <?php if ($successMessage !== ''): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
                <?php endif; ?>

                <?php if ($errorMessage !== ''): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage); ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <label>
                            Course
                            <select name="coursecode" required>
                                <option value="">Select a course</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo htmlspecialchars($course['coursecode']); ?>"><?php echo htmlspecialchars($course['coursecode'] . ' - ' . $course['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label>
                            Resource type
                            <select name="resource_type" required>
                                <option value="">Select a type</option>
                                <option value="Course Content">Course Content</option>
                                <option value="Class Notes">Class Notes</option>
                                <option value="Previous Quiz Questions">Previous Quiz Questions</option>
                                <option value="Assignments">Assignments</option>
                                <option value="Slides">Slides</option>
                                <option value="Reference Material">Reference Material</option>
                            </select>
                        </label>

                        <label>
                            Resource title
                            <input type="text" name="resource_name" placeholder="e.g. Week 4 Lecture Notes">
                        </label>

                        <label>
                            Short description
                            <textarea name="resource_description" placeholder="Brief description of the material"></textarea>
                        </label>

                        <label>
                            Upload file
                            <input type="file" name="resource_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.txt,.zip,.rar,.png,.jpg,.jpeg" required>
                        </label>
                    </div>

                    <button type="submit" name="upload_resource" class="submit-btn">Upload resource</button>
                </form>
            </div>

            <div class="panel">
                <h3>Uploaded resources</h3>

                <?php if (empty($resources)): ?>
                    <p class="empty-state">No resources uploaded yet. Add your first file using the form.</p>
                <?php else: ?>
                    <div class="resource-list">
                        <?php foreach ($resources as $resource): ?>
                            <?php
                            $resourceContent = $resource['content'] ?? '';
                            $description = $resourceContent;
                            $downloadPath = '';

                            if (strpos($resourceContent, ' | ') !== false) {
                                [$description, $downloadPath] = explode(' | ', $resourceContent, 2);
                            } elseif (strpos($resourceContent, 'uploads/') === 0) {
                                $downloadPath = $resourceContent;
                                $description = 'Uploaded file';
                            }
                            ?>
                            <div class="resource-item">
                                <div class="resource-meta">
                                    <span class="resource-badge"><?php echo htmlspecialchars($resource['resource_type']); ?></span>
                                    <span><?php echo htmlspecialchars($resource['course_name'] ?: $resource['coursecode']); ?></span>
                                </div>
                                <h4><?php echo htmlspecialchars($resource['name']); ?></h4>
                                <p><?php echo htmlspecialchars($description); ?></p>
                                <?php if ($downloadPath !== ''): ?>
                                    <a class="download-btn" href="<?php echo htmlspecialchars($downloadPath); ?>" target="_blank" rel="noopener">Open / Download</a>
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
