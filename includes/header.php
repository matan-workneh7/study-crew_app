<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
// Initialize variables if not set
$currentPage = $currentPage ?? basename($_SERVER['PHP_SELF'], '.php');
$isLoggedIn = $isLoggedIn ?? isset($_SESSION['user_id']);
$username = $username ?? ($_SESSION['username'] ?? null);
$isAssistant = $isAssistant ?? (isset($_SESSION['user_id']) ? isUserAssistant($_SESSION['user_id']) : false);
?>
<!-- Header Section -->
<header>
    <div class="container">
        <div class="logo">
            <span class="book-icon">📚</span>STUDY CREW
        </div>
        <nav>
            <ul>
                <li><a href="index.php" <?php echo ($currentPage === 'index') ? 'class="active"' : ''; ?>>Home</a></li>
                
                <?php if ($isLoggedIn): ?>
                    <li><a href="courses.php" <?php echo ($currentPage === 'courses') ? 'class="active"' : ''; ?>>Courses</a></li>
                    <li><a href="<?php echo $isAssistant ? 'assistant-dashboard.php' : 'assistant-form.php'; ?>" 
                        <?php echo (in_array($currentPage, ['assistant-dashboard', 'assistant-form'])) ? 'class="active"' : ''; ?>>
                        <?php echo $isAssistant ? 'Assistant Dashboard' : 'Become an Assistant'; ?>
                    </a></li>
                <?php endif; ?>
                <li><a href="about.php" <?php echo ($currentPage === 'about') ? 'class="active"' : ''; ?>>About</a></li>
                <li><a href="contact.php" <?php echo ($currentPage === 'contact') ? 'class="active"' : ''; ?>>Contact Us</a></li>
            </ul>
        </nav>
        <div class="user-menu">
            <?php if ($isLoggedIn && $username): ?>
                <span><?php echo htmlspecialchars($username); ?></span>
                <a href="?logout=1" class="sign-in-btn">LOGOUT</a>
            <?php else: ?>
                <a href="index.php?action=login" class="sign-in-btn">SIGN IN</a>
            <?php endif; ?>
        </div>
    </div>
</header>
</body>
</html>