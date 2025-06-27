<?php
require_once 'session.php';
include 'functions.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header("Location: index.php?action=login&intent=get");
    exit();
}

// Get user ID from URL
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get user info
$user = getUserData($userId);

// Find assistant record for this user
$assistants = readJsonFile(ASSISTANTS_FILE);
$assistant = null;
foreach ($assistants as $a) {
    if ($a['user_id'] == $userId) {
        $assistant = $a;
        break;
    }
}

if (!$user) {
    echo '<h2>User not found.</h2>';
    exit();
}

if (!$assistant || !$user) {
    echo '<h2>Assistant not found.</h2>';
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assistant Details - Study Crew</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <span class="book-icon">📚</span>STUDY CREW
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="courses.php">Courses</a></li>
                    <li><a href="#">About</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>
            </nav>
            <div class="user-menu">
                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="?logout=1" class="sign-in-btn">LOGOUT</a>
            </div>
        </div>
    </header>
    <div class="container tutor-details-container">
        <a href="javascript:history.back()" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
        <div class="tutor-details-card">
            <h1><?php echo htmlspecialchars($user['username']); ?> (Assistant)</h1>
            <p><strong>Academic Year:</strong> <?php echo htmlspecialchars($user['academic_year']); ?></p>
            <?php if (!empty($assistant['bio'])): ?>
                <p><strong>Bio:</strong> <?php echo htmlspecialchars($assistant['bio']); ?></p>
            <?php endif; ?>
            <?php if (!empty($user['telegram'])): ?>
                <p><strong>Telegram:</strong> <?php echo htmlspecialchars($user['telegram']); ?></p>
            <?php endif; ?>
            <?php if (!empty($user['phone'])): ?>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
            <?php endif; ?>
            <?php if (!empty($assistant['other_info'])): ?>
                <p><strong>Other Info:</strong> <?php echo htmlspecialchars($assistant['other_info']); ?></p>
            <?php endif; ?>
            <br>
            <a href="connect.php?tutor=<?php echo $assistant['id']; ?>" class="submit-btn">Request Help</a>
        </div>
    </div>
    <footer>
        <div class="container">
            <p>&copy; 2025 Study Crew. All rights reserved.</p>
        </div>
    </footer>
</body>
</html> 