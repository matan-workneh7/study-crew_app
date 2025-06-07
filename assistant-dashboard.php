<?php
session_start();
include 'functions.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header("Location: index.php?action=login&intent=assist");
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

// Get all courses for years below the user's year
$availableCourses = getCoursesForAssistant($userYearValue);

// Group courses by year and semester
$groupedCourses = [];
foreach ($availableCourses as $course) {
    $yearName = getYearName($course['year']);
    $semesterName = $course['semester'] == 1 ? 'First Semester' : 'Second Semester';
    
    if (!isset($groupedCourses[$yearName])) {
        $groupedCourses[$yearName] = [];
    }
    
    if (!isset($groupedCourses[$yearName][$semesterName])) {
        $groupedCourses[$yearName][$semesterName] = [];
    }
    
    $groupedCourses[$yearName][$semesterName][] = $course;
}

// Get courses the user is already assisting
$assistingCourses = getAssistantCourses($userId);
$assistingCourseIds = array_column($assistingCourses, 'course_id');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedCourses = $_POST['selected_courses'] ?? [];
    $telegram = $_POST['telegram'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $bio = $_POST['bio'] ?? '';
    $availability = $_POST['availability'] ?? '';
    
    // Save assistant profile
    if (saveAssistantProfile($userId, $selectedCourses, $telegram, $phone, $bio, $availability)) {
        $_SESSION['success_message'] = "Your assistant profile has been updated successfully!";
        header("Location: assistant-dashboard.php");
        exit();
    } else {
        $error = "Failed to update your assistant profile. Please try again.";
    }
}

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
    <title>Assistant Dashboard - Study Crew</title>
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
                    <li><a href="courses.php">Courses</a></li>
                    <li><a href="assistant-dashboard.php" class="active">Assistant Dashboard</a></li>
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

    <!-- Success Message -->
    <?php if(isset($success_message)): ?>
        <div class="success-banner">
            <div class="container">
                <p><?php echo htmlspecialchars($success_message); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <div class="container">
        <div class="assistant-dashboard">
            <div class="dashboard-header">
                <h1>Assistant Dashboard</h1>
                <p class="dashboard-subtitle">Select courses you'd like to assist with and complete your profile</p>
            </div>

            <?php if(isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="assistant-form">
                <div class="form-section">
                    <h2>Available Courses</h2>
                    <p class="section-description">Select the courses you can assist with (based on your academic year: <?php echo htmlspecialchars($userYear); ?>)</p>
                    
                    <div class="course-selection">
                        <?php foreach ($groupedCourses as $yearName => $semesters): ?>
                            <div class="year-group">
                                <h3><?php echo htmlspecialchars($yearName); ?> Courses</h3>
                                
                                <?php foreach ($semesters as $semesterName => $courses): ?>
                                    <div class="semester-group">
                                        <h4><?php echo htmlspecialchars($semesterName); ?></h4>
                                        
                                        <div class="course-checkboxes">
                                            <?php foreach ($courses as $course): ?>
                                                <label class="course-checkbox">
                                                    <input type="checkbox" name="selected_courses[]" value="<?php echo $course['id']; ?>" 
                                                        <?php echo in_array($course['id'], $assistingCourseIds) ? 'checked' : ''; ?>>
                                                    <span class="course-name">
                                                        <span class="course-icon"><?php echo getCourseIcon($course['category']); ?></span>
                                                        <?php echo htmlspecialchars($course['name']); ?>
                                                    </span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty($groupedCourses)): ?>
                            <div class="no-courses-message">
                                No courses available for you to assist with. As a <?php echo htmlspecialchars($userYear); ?>, 
                                you can only assist with courses from years below your current academic year.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Your Profile</h2>
                    <p class="section-description">This information will be visible to students seeking assistance</p>
                    
                    <div class="form-group">
                        <label for="telegram">Telegram Username</label>
                        <div class="input-group">
                            <span class="input-icon">✈️</span>
                            <input type="text" id="telegram" name="telegram" placeholder="@yourusername" 
                                value="<?php echo htmlspecialchars($userData['telegram'] ?? ''); ?>">
                        </div>
                        <p class="field-hint">This is the preferred contact method for students</p>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number (Optional)</label>
                        <div class="input-group">
                            <span class="input-icon">📞</span>
                            <input type="text" id="phone" name="phone" placeholder="+251---------" 
                                value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="bio">About You</label>
                        <textarea id="bio" name="bio" rows="4" placeholder="Share your expertise, experience, and teaching style..."><?php echo htmlspecialchars($userData['bio'] ?? ''); ?></textarea>
                        <p class="field-hint">Help students understand why they should choose you as their assistant</p>
                    </div>

                    <div class="form-group">
                        <label for="availability">Your Availability</label>
                        <textarea id="availability" name="availability" rows="3" placeholder="E.g., Weekdays after 4pm, Weekends 10am-6pm..."><?php echo htmlspecialchars($userData['availability'] ?? ''); ?></textarea>
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    <span class="btn-icon">✅</span> Save Assistant Profile
                </button>
            </form>
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