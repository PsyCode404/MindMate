<?php
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
$current_page = basename($_SERVER['PHP_SELF']);
$page_title = $page_title ?? 'MindMate - Your Mental Health Companion';
$is_logged_in = $auth->isLoggedIn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/mindmate/css/style.css">
    <script src="/mindmate/js/main.js" defer></script>
    <?php echo $additional_css ?? ''; ?>
    <?php echo $additional_js ?? ''; ?>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <!-- Logo -->
            <div class="logo">
                <a href="/mindmate/index.php">
                    <i class="fas fa-brain"></i>
                    <span>MindMate</span>
                </a>
            </div>

            <!-- Desktop Navigation -->
            <div class="nav-links" id="navLinks">
                <?php if ($is_logged_in): ?>
                    <a href="/mindmate/pages/mood-tracker.php" <?php echo $current_page == 'mood-tracker.php' ? 'class="active"' : ''; ?>>Mood Tracker</a>
                    <a href="/mindmate/pages/journal.php" <?php echo $current_page == 'journal.php' ? 'class="active"' : ''; ?>>Journal</a>
                    <a href="/mindmate/pages/exercises.php" <?php echo $current_page == 'exercises.php' ? 'class="active"' : ''; ?>>Exercises</a>
                    <a href="/mindmate/pages/chat.php" <?php echo $current_page == 'chat.php' ? 'class="active"' : ''; ?>>Chat</a>
                    <div class="user-menu">
                        <span class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                        <a href="/mindmate/auth/logout.php" class="btn-outline">Log out</a>
                    </div>
                <?php elseif ($current_page !== 'index.php'): ?>
                    <a href="/mindmate/index.php" <?php echo $current_page == 'index.php' ? 'class="active"' : ''; ?>>Home</a>
                    <div class="auth-buttons">
                        <a href="/mindmate/auth/login.php" class="btn-outline">Log in</a>
                        <a href="/mindmate/auth/register.php" class="btn-primary">Sign up</a>
                    </div>
                <?php else: ?>
                    <div class="auth-buttons">
                        <a href="/mindmate/auth/login.php" class="btn-outline">Log in</a>
                        <a href="/mindmate/auth/register.php" class="btn-primary">Sign up</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Mobile menu button -->
            <div class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </nav>
