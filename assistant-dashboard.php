<?php
require_once 'includes/session.php';
include 'includes/functions.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header("Location: index.php?action=login&intent=assist");
    exit();
}

// Redirect non-assistants to courses page
if (!isUserAssistant($_SESSION['user_id'])) {
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
    <style>
        /* Assistant Dashboard Styles */
        .assistant-dashboard {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            margin: 2rem 0;
            overflow: hidden;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .dashboard-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            color: white;
        }

        .dashboard-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
            margin: 0;
        }

        .assistant-form {
            padding: 2rem;
        }

        .form-section {
            margin-bottom: 3rem;
            background: #f9fafb;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .form-section h2 {
            color: #1f2937;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            text-align: left;
        }

        .section-description {
            color: #6b7280;
            margin-bottom: 2rem;
        }

        .course-selection {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .year-group {
            margin-bottom: 1.5rem;
        }

        .year-group h3 {
            color: #1f2937;
            font-size: 1.3rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e5e7eb;
            text-align: left;
        }

        .semester-group {
            margin-bottom: 1.5rem;
        }

        .semester-group h4 {
            color: #4b5563;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            text-align: left;
        }

        .course-checkboxes {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
        }

        .course-checkbox {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: white;
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .course-checkbox:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .course-checkbox input[type="checkbox"] {
            margin-right: 1rem;
            width: 20px;
            height: 20px;
            accent-color: #667eea;
        }

        .course-checkbox .course-name {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            color: #1f2937;
        }

        .course-checkbox .course-icon {
            font-size: 1.5rem;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            color: white;
        }

        .no-courses-message {
            background: #f3f4f6;
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            color: #6b7280;
        }

        .field-hint {
            color: #6b7280;
            font-size: 0.9rem;
            margin-top: 0.5rem;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <?php include 'includes/header.php'; ?>

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
    <?php include 'includes/footer.php'; ?>
</body>
</html>
