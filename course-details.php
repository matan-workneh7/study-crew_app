<?php
session_start();
include 'functions.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header("Location: index.php?action=login&intent=get");
    exit();
}

// Get course ID from URL
$courseId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get course details
$course = getCourseById($courseId);
if (!$course) {
    header("Location: courses.php");
    exit();
}

// Get search query
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get tutors for this course
$tutors = getTutorsByCourse($courseId, $searchQuery);

// Sort options
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'visits';
$sortOrder = isset($_GET['order']) ? $_GET['order'] : 'desc';

// Sort tutors based on selected option
if ($sortBy === 'name') {
    usort($tutors, function($a, $b) use ($sortOrder) {
        return $sortOrder === 'asc' ? 
            strcmp($a['name'], $b['name']) : 
            strcmp($b['name'], $a['name']);
    });
} else {
    usort($tutors, function($a, $b) use ($sortOrder) {
        return $sortOrder === 'asc' ? 
            $a['visits'] - $b['visits'] : 
            $b['visits'] - $a['visits'];
    });
}

// Handle logout
if(isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['name']); ?> - Study Crew</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="courses.css">
</head>
<body>
    <!-- Header Section -->
    <header>
        <div class="container">
            <div class="logo">
                <span class="book-icon">📚</span>STUDY CREW
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="courses.php" class="active">Courses</a></li>
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

    <!-- Main Content -->
    <div class="container course-details-container">
        <div class="course-header">
            <h2>Course Selected:</h2>
            <h1><?php echo htmlspecialchars($course['name']); ?></h1>
        </div>

        <div class="search-container">
            <form method="GET" action="" class="search-box">
                <input type="hidden" name="id" value="<?php echo $courseId; ?>">
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sortBy); ?>">
                <input type="hidden" name="order" value="<?php echo htmlspecialchars($sortOrder); ?>">
                <input type="text" name="search" placeholder="Search by Name" value="<?php echo htmlspecialchars($searchQuery); ?>">
                <button type="submit" class="search-btn">Search</button>
            </form>
        </div>

        <div class="tutors-list">
            <div class="tutors-header">
                <div class="tutor-name">
                    Student Name
                    <a href="?id=<?php echo $courseId; ?>&sort=name&order=asc&search=<?php echo urlencode($searchQuery); ?>" class="sort-arrow">↑</a>
                    <a href="?id=<?php echo $courseId; ?>&sort=name&order=desc&search=<?php echo urlencode($searchQuery); ?>" class="sort-arrow">↓</a>
                </div>
                <div class="tutor-visits">
                    Visit Count
                    <a href="?id=<?php echo $courseId; ?>&sort=visits&order=asc&search=<?php echo urlencode($searchQuery); ?>" class="sort-arrow">↑</a>
                    <a href="?id=<?php echo $courseId; ?>&sort=visits&order=desc&search=<?php echo urlencode($searchQuery); ?>" class="sort-arrow">↓</a>
                </div>
            </div>

            <?php foreach ($tutors as $tutor): ?>
                <a href="connect.php?course=<?php echo $courseId; ?>&tutor=<?php echo $tutor['id']; ?>" class="tutor-item">
                    <div class="tutor-name"><?php echo htmlspecialchars($tutor['name']); ?></div>
                    <div class="tutor-visits"><?php echo $tutor['visits']; ?> Visits</div>
                </a>
            <?php endforeach; ?>

            <?php if (empty($tutors)): ?>
                <div class="no-tutors">
                    <?php if($searchQuery): ?>
                        No tutors found matching "<?php echo htmlspecialchars($searchQuery); ?>".
                        <br><a href="?id=<?php echo $courseId; ?>">Show all tutors</a>
                    <?php else: ?>
                        No tutors available for this course yet.
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2025 Study Crew. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>