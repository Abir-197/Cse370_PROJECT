<?php
session_start();
$isLoggedIn = isset($_SESSION['uid']);
$userName = $isLoggedIn ? htmlspecialchars($_SESSION['name'] ?? 'User') : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Studyverse - Your Study Partner</title>
    <link rel="stylesheet" href="intro.css">
</head>
<body>

    <!-- Header Navigation -->
    <header class="header">
        <a href="intro.php" class="brand-title">Studyverse</a>
        
        <nav class="auth-nav">
            <?php if ($isLoggedIn): ?>
                <span class="user-greeting">Welcome, <?= $userName; ?>!</span>
                <a href="dashboard.php" class="btn-nav-register">Dashboard</a>
                <a href="logout.php" class="btn-nav-logout">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn-nav-login">Login</a>
                <a href="register.php" class="btn-nav-register">Register</a>
            <?php endif; ?>
        </nav>
    </header>

    <!-- Center Hero Section -->
    <main class="hero-container">
        <h1 class="hero-heading">Studyverse</h1>
        <p class="hero-subhead">Your Study Partner. Connect with peers, share resources, and collaborate seamlessly.</p>
        
        <div class="cta-group">
            <?php if ($isLoggedIn): ?>
                <a href="dashboard.php" class="btn-cta-primary">Go to Dashboard</a>
            <?php else: ?>
                <a href="register.php" class="btn-cta-primary">Get Started</a>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>