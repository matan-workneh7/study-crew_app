<?php
require_once 'session.php';
include 'functions.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header("Location: index.php?action=login&intent=get");
    exit();
}

// Redirect users logged in as assistant to their dashboard
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'assist') {
    header("Location: assistant-dashboard.php");
    exit();
}

// Get user data
$userId = $_SESSION['user_id'];
$userData = getUserData($userId);
$userYear = $userData['academic_year'];

// Convert academic year to numeric value for comparison
$yearValues = [
    'Freshman' => 1,
    'Sophomore' => 2,
    'Junior' => 3,
    'Senior' => 4,
    'Graduate' => 5
];

$userYearValue = $yearValues[$userYear] ?? 1;

// Get selected year from URL parameter or default to user's year
$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : $userYearValue;
$selectedYearName = array_search($selectedYear, $yearValues);

// Ensure we have a valid year name
if ($selectedYearName === false) {
    // If we can't find the year name, use the user's year
    $selectedYear = $userYearValue;
    $selectedYearName = array_search($selectedYear, $yearValues) ?: 'Freshman';
}

// Get selected semester from URL parameter or default to first semester
$selectedSemester = isset($_GET['semester']) ? (int)$_GET['semester'] : 1;

// Get courses for the selected year and semester
$courses = getCoursesByYearAndSemester($selectedYear, $selectedSemester);

// Handle logout
if(isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Check for success messages
if(isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - Study Crew</title>
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
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                </ul>
            </nav>
            <div class="user-menu">
                <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="?logout=1" class="sign-in-btn">LOGOUT</a>
            </div>
        </div>
    </header>

    <!-- Success Message -->
    <?php if(isset($success_message)): ?>
        <div class="success-banner">
            <div class="container">
                <p><?php echo htmlspecialchars($success_message); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="courses-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h3>Academic Year</h3>
            <ul class="year-list">
                <?php
                // Display years up to and including user's year
                foreach ($yearValues as $yearName => $yearNum) {
                    if ($yearNum > $userYearValue) continue;
                    $activeClass = ($yearNum == $selectedYear) ? 'active' : '';
                    echo "<li class='$activeClass'><a href='courses.php?year=$yearNum&semester=$selectedSemester'>$yearName</a></li>";
                }
                ?>
            </ul>
        </div>

        <!-- Main Content Area -->
        <div class="main-content">
            <div class="courses-header">
                <h2><?php echo getYearName($selectedYear); ?> Courses</h2>
                
                <!-- Semester Toggle -->
                <div class="semester-toggle">
                    <a href="courses.php?year=<?php echo $selectedYear; ?>&semester=1" 
                       class="<?php echo $selectedSemester == 1 ? 'active' : ''; ?>">
                        First Semester
                    </a>
                    <a href="courses.php?year=<?php echo $selectedYear; ?>&semester=2" 
                       class="<?php echo $selectedSemester == 2 ? 'active' : ''; ?>">
                        Second Semester
                    </a>
                </div>
            </div>

            <!-- Course List -->
            <div class="course-list">
                <?php foreach ($courses as $course): ?>
                    <a href="course-details.php?id=<?php echo $course['id']; ?>&year=<?php echo $course['year']; ?>&semester=<?php echo $course['semester']; ?>" class="course-item">
                        <div class="course-icon">
                            <?php echo getCourseIcon($course['category']); ?>
                        </div>
                        <div class="course-name">
                            <?php echo htmlspecialchars($course['name']); ?>
                        </div>
                        <div class="course-arrow">
                            &rsaquo;
                        </div>
                    </a>
                <?php endforeach; ?>
                
                <?php if (empty($courses)): ?>
                    <div class="no-courses">
                        No courses available for this semester.
                    </div>
                <?php endif; ?>
            </div>
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