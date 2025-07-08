<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<header>
    <div class="container">
        <div class="logo">
            <span class="book-icon">📚</span>STUDY CREW
        </div>
        <nav>
            <ul>
                <li><a href="index.php"<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? ' class="active"' : '' ?>>Home</a></li>
                <?php if(isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'assist'): ?>
                    <li><a href="assistant-dashboard.php"<?= basename($_SERVER['PHP_SELF']) === 'assistant-dashboard.php' ? ' class="active"' : '' ?>>Assistant Dashboard</a></li>
                <?php elseif(isset($_SESSION['user_id'])): ?>
                    <li><a href="courses.php"<?= basename($_SERVER['PHP_SELF']) === 'courses.php' ? ' class="active"' : '' ?>>Courses</a></li>
                <?php endif; ?>
                <li><a href="about.php"<?= basename($_SERVER['PHP_SELF']) === 'about.php' ? ' class="active"' : '' ?>>About</a></li>
                <li><a href="contact.php"<?= basename($_SERVER['PHP_SELF']) === 'contact.php' ? ' class="active"' : '' ?>>Contact Us</a></li>
            </ul>
        </nav>
        <?php if(isset($_SESSION['user_id'])): ?>
            <div class="user-menu">
                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="?logout=1" class="sign-in-btn">LOGOUT</a>
            </div>
        <?php else: ?>
            <a href="index.php?action=login&intent=student" class="sign-in-btn">SIGN IN</a>
        <?php endif; ?>
    </div>
</header>