<?php
require_once 'session.php';
include 'functions.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header("Location: index.php?action=login&intent=assist");
    exit();
}

// Redirect users not logged in as assistant to courses page
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'assist') {
    header("Location: courses.php");
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

// Get selected year from URL parameter or default to first available year below user's year
$selectedYear = 1;
foreach ($yearValues as $yearName => $yearNum) {
    if ($yearNum < $userYearValue) {
        $selectedYear = $yearNum;
        break;
    }
}
$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : $selectedYear;

// Ensure we have a valid year name
$selectedYearName = array_search($selectedYear, $yearValues);
if ($selectedYearName === false) {
    $selectedYear = 1;
    $selectedYearName = 'Freshman';
}

// Get selected semester from URL parameter or default to first semester
$selectedSemester = isset($_GET['semester']) ? (int)$_GET['semester'] : 1;

// Get courses for the selected year and semester
$courses = array_filter(getCoursesForAssistant($userYearValue), function($course) use ($selectedYear, $selectedSemester, $yearValues) {
    $courseYearValue = is_numeric($course['year']) ? $course['year'] : ($yearValues[$course['year']] ?? 0);
    return $courseYearValue == $selectedYear && $course['semester'] == $selectedSemester;
});

// Get courses the user is already assisting
$assistingCourses = getAssistantCourses($userId);
$assistingCourseIds = array_column($assistingCourses, 'course_id');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedCourses = $_POST['selected_courses'] ?? [];
    $telegram = trim($_POST['telegram'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $availability = trim($_POST['availability'] ?? '');
    
    // Basic validation
    $error = '';
    
    if (empty($telegram)) {
        $error = 'Telegram username is required';
    } elseif (empty($bio)) {
        $error = 'Please provide some information about yourself';
    } elseif (empty($availability)) {
        $error = 'Please provide your availability';
    } else {
        // Save the assistant's profile and course selections
        if (saveAssistantProfile($userId, $selectedCourses, $telegram, $phone, $bio, $availability)) {
            // Update user data in session
            $userData = array_merge($userData, [
                'telegram' => $telegram,
                'phone' => $phone,
                'bio' => $bio,
                'availability' => $availability
            ]);
            $_SESSION['user_data'] = $userData;
            
            // Set success message and redirect to avoid form resubmission
            $_SESSION['success_message'] = 'Your profile has been updated successfully!';
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $error = 'Failed to save profile. Please try again.';
        }
    }
}

// Check for success message
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
    <link rel="stylesheet" href="courses.css">
    <style>
        /* Form styles */
        .assistant-form {
            margin-top: 30px;
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .form-section h2 {
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
            transition: border-color 0.3s;
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        .submit-btn {
            background: #2ecc71;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 4px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            display: block;
            width: 100%;
            margin-top: 20px;
        }
        
        .submit-btn:hover {
            background: #27ae60;
        }
        
        /* Course checkboxes */
        .course-checkbox {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .course-checkbox:hover {
            background: #f8f9fa;
            border-color: #bdc3c7;
        }
        
        .course-checkbox input[type="checkbox"] {
            margin-right: 12px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .course-icon {
            margin-right: 10px;
            font-size: 1.2em;
        }
    </style>
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
                    <li><a href="assistant-dashboard.php" class="active">Assistant Dashboard</a></li>
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

    <!-- Error Message -->
    <?php if(isset($error)): ?>
        <div class="error-banner">
            <div class="container">
                <p><?php echo htmlspecialchars($error); ?></p>
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
                // Display years below user's year
                foreach ($yearValues as $yearName => $yearNum) {
                    if ($yearNum >= $userYearValue) continue;
                    $activeClass = ($yearNum == $selectedYear) ? 'active' : '';
                    echo "<li class='$activeClass'><a href='assistant-dashboard.php?year=$yearNum&semester=$selectedSemester'>$yearName</a></li>";
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
                    <a href="assistant-dashboard.php?year=<?php echo $selectedYear; ?>&semester=1" 
                       class="<?php echo $selectedSemester == 1 ? 'active' : ''; ?>">
                        First Semester
                    </a>
                    <a href="assistant-dashboard.php?year=<?php echo $selectedYear; ?>&semester=2" 
                       class="<?php echo $selectedSemester == 2 ? 'active' : ''; ?>">
                        Second Semester
                    </a>
                </div>
            </div>

            <!-- Course List -->
            <div class="course-list">
                <form method="POST" action="" class="assistant-form">
                    <div class="form-section">
                        <h3>Select courses you can assist with</h3>
                        <p class="section-description">Your academic year: <?php echo htmlspecialchars($userYear); ?></p>
                        
                        <?php if (!empty($courses)): ?>
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
                        <?php else: ?>
                            <div class="no-courses">
                                No courses available for this semester.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-section">
                        <h3>Your Profile</h3>
                        <p class="section-description">This information will be visible to students</p>
                        
                        <div class="form-group">
                            <label for="telegram">Telegram Username</label>
                            <input type="text" id="telegram" name="telegram" class="form-control" 
                                placeholder="@yourusername" value="<?php echo htmlspecialchars($userData['telegram'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number (Optional)</label>
                            <input type="text" id="phone" name="phone" class="form-control" 
                                placeholder="+251---------" value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="bio">About You</label>
                            <textarea id="bio" name="bio" class="form-control" 
                                placeholder="Share your expertise, experience, and teaching style..."><?php echo htmlspecialchars($userData['bio'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="availability">Your Availability</label>
                            <textarea id="availability" name="availability" class="form-control" 
                                placeholder="E.g., Weekdays after 4pm, Weekends 10am-6pm..."><?php echo htmlspecialchars($userData['availability'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">
                        Save Profile
                    </button>
                </form>
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
