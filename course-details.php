<?php
require_once 'session.php';
include 'functions.php';

// Debug: Log request parameters to a file
$debugLog = __DIR__ . '/debug.log';
$debugMsg = '[' . date('Y-m-d H:i:s') . '] Course Details - GET: ' . print_r($_GET, true) . "\n";
file_put_contents($debugLog, $debugMsg, FILE_APPEND);

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header("Location: index.php?action=login&intent=get");
    exit();
}

// Get course ID and context from URL
$courseId = $_GET['id'] ?? '';  // Keep as string to match the data format
$yearValues = [
    'Freshman' => 1,
    'Sophomore' => 2,
    'Junior' => 3,
    'Senior' => 4,
    'Graduate' => 5
];
$selectedYear = 1;
if (isset($_GET['year'])) {
    if (is_numeric($_GET['year'])) {
        $selectedYear = (int)$_GET['year'];
    } elseif (isset($yearValues[$_GET['year']])) {
        $selectedYear = $yearValues[$_GET['year']];
    }
}
$selectedSemester = isset($_GET['semester']) ? (int)$_GET['semester'] : 1;

// Debug: Log the course ID and its type
error_log('Course ID: ' . $courseId . ', Type: ' . gettype($courseId));

// Get course details
$course = getCourseById($courseId);

// If course not found, show error and exit
if (!$course) {
    // Debug: Log available courses for troubleshooting
    $courses = readJsonFile(COURSES_FILE);
    $courseIds = array_column($courses, 'id');
    error_log('Available course IDs: ' . print_r($courseIds, true));
    
    echo '<h2>Course not found (ID: ' . htmlspecialchars($courseId) . '). Please go back to the <a href="courses.php">courses list</a>.</h2>';
    exit();
}

// Debug: Log course lookup
$debugMsg = '[' . date('Y-m-d H:i:s') . "] Looking up course ID: $courseId, Found: " . ($course ? 'Yes' : 'No') . "\n";
file_put_contents($debugLog, $debugMsg, FILE_APPEND);

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
                <input type="hidden" name="year" value="<?php echo $selectedYear; ?>">
                <input type="hidden" name="semester" value="<?php echo $selectedSemester; ?>">
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sortBy); ?>">
                <input type="hidden" name="order" value="<?php echo htmlspecialchars($sortOrder); ?>">
                <input type="text" name="search" placeholder="Search by Name" value="<?php echo htmlspecialchars($searchQuery); ?>">
                <button type="submit" class="search-btn">Search</button>
            </form>
        </div>

        <a href="courses.php?year=<?php echo urlencode($selectedYear); ?>&semester=<?php echo urlencode($selectedSemester); ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Courses</a>

        <div class="tutors-list">
            <div class="tutors-header">
                <div class="tutor-name">Assistant Name</div>
                <div class="tutor-year">Academic Year</div>
                <div class="tutor-visits">Visit Count</div>
            </div>

            <?php if (!empty($tutors)): ?>
                <?php foreach ($tutors as $tutor): ?>
                    <div class="tutor-item-container">
                        <a href="tutor-details.php?id=<?php echo $tutor['user_id']; ?>" class="tutor-item">
                            <div class="tutor-name"><?php echo htmlspecialchars($tutor['name']); ?></div>
                            <div class="tutor-year"><?php echo htmlspecialchars($tutor['year']); ?></div>
                            <div class="tutor-visits"><?php echo $tutor['visits']; ?> Visits</div>
                        </a>
                        <?php if (!empty($tutor['bio'])): ?>
                            <div class="tutor-bio">
                                <p><?php echo htmlspecialchars($tutor['bio']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-tutors" style="text-align:center; padding:2em; color:#888;">
                    <strong>No assistants are currently available for this course.</strong><br>
                    <span>Check back soon, or consider becoming the first assistant for this course!</span>
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