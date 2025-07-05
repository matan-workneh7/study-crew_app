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
$assistantCourses = getCoursesForAssistant($userYearValue);

// Filter courses by selected year and semester
$courses = array_filter($assistantCourses, function($course) use ($selectedYear, $selectedSemester, $yearValues) {
    // Handle both numeric and string years
    $courseYear = $course['year'];
    $courseYearValue = is_numeric($courseYear) ? $courseYear : ($yearValues[$courseYear] ?? 0);
    
    return $courseYearValue == $selectedYear && $course['semester'] == $selectedSemester;
});

// Get assisting courses from session if available, otherwise from database
if (isset($_SESSION['temporary_course_selections'])) {
    $assistingCourseIds = $_SESSION['temporary_course_selections'];
} else {
    $assistingCourseIds = [];
    $assistant = getAssistantByUserId($userId);
    if ($assistant && !empty($assistant['course_ids'])) {
        $assistingCourseIds = $assistant['course_ids'];
    }
    // Store in session for consistency
    $_SESSION['temporary_course_selections'] = $assistingCourseIds;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedCourses = $_POST['selected_courses'] ?? [];
    
    // Filter out any empty values
    $selectedCourses = array_filter($selectedCourses, function($id) {
        return !empty($id);
    });
    
    // Store in session
    $_SESSION['temporary_course_selections'] = $selectedCourses;
    
    // Redirect to profile page
    header('Location: assistant-profile.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assistant Dashboard - Study Crew</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Course List */
        .course-list {
            margin-top: 20px;
        }

        .course-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: white;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.2s ease;
            cursor: pointer;
            border: 1px solid #e5e7eb;
            width: 100%;
        }

        .course-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-color: #d1d5db;
        }

        .course-item label {
            display: flex;
            align-items: center;
            width: 100%;
            margin: 0;
            cursor: pointer;
        }

        .course-item input[type="checkbox"] {
            margin-right: 15px;
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .course-icon {
            font-size: 1.5rem;
            margin-right: 15px;
            color: #4f46e5;
            width: 40px;
            text-align: center;
            flex-shrink: 0;
        }

        .course-name {
            flex: 1;
            font-weight: 500;
            color: #111827;
            margin: 0 15px 0 0;
        }

        .course-arrow {
            color: #9ca3af;
            transition: all 0.2s;
            flex-shrink: 0;
            margin-left: auto;
            padding: 0 10px;
        }

        .course-item:hover .course-arrow {
            color: #4f46e5;
            transform: translateX(4px);
        }

        /* Checkbox styling */
        .course-checkbox {
            margin-right: 15px;
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        /* Form actions */
        .form-actions {
            margin-top: 25px;
            text-align: right;
        }

        .save-btn {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            display: inline-flex;
            align-items: center;
        }

        .save-btn:hover {
            background: #4338ca;
        }

        .save-btn i {
            margin-right: 8px;
        }

        /* No courses message */
        .no-courses {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
            background: #f9fafb;
            border-radius: 8px;
            margin-top: 20px;
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
    <?php if(isset($_SESSION['success_message'])): ?>
        <div class="success-banner">
            <div class="container">
                <p><?php echo htmlspecialchars($_SESSION['success_message']); ?></p>
            </div>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if(isset($_SESSION['error_message'])): ?>
        <div class="error-banner">
            <div class="container">
                <p><?php echo htmlspecialchars($_SESSION['error_message']); ?></p>
            </div>
        </div>
        <?php unset($_SESSION['error_message']); ?>
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
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Home</a>
            <div class="courses-header">
                <h2><?php echo getYearName($selectedYear); ?> Courses You Can Assist</h2>
                
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
            <form method="POST" action="">
                <div class="course-list">
                    <?php if (!empty($courses)): ?>
                        <?php foreach ($courses as $course): ?>
                            <div class="course-item">
                                <input type="checkbox" 
                                       name="selected_courses[]" 
                                       value="<?php echo htmlspecialchars($course['id']); ?>" 
                                       id="course-<?php echo htmlspecialchars($course['id']); ?>"
                                       class="course-checkbox"
                                       <?php echo in_array($course['id'], $assistingCourseIds) ? 'checked' : ''; ?>>
                                <label for="course-<?php echo htmlspecialchars($course['id']); ?>">
                                    <div class="course-icon">
                                        <?php echo getCourseIcon($course['category']); ?>
                                    </div>
                                    <div class="course-name">
                                        <?php echo htmlspecialchars($course['name']); ?>
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="form-actions">
                            <a href="assistant-profile.php" class="btn btn-secondary">
                                <i class="fas fa-user-edit"></i> View/Edit Profile
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Course Selections
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="no-courses">
                            No courses available for this semester.
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2025 Study Crew. All rights reserved.</p>
        </div>
    </footer>
    <script>
    // Key for localStorage
    const STORAGE_KEY = 'assistant_selected_courses';

    document.addEventListener('DOMContentLoaded', function() {
        const saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
        document.querySelectorAll('input[type=checkbox][name="selected_courses[]"]').forEach(cb => {
            if (saved.includes(cb.value)) cb.checked = true;
            cb.addEventListener('change', function() {
                let current = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
                if (cb.checked) {
                    if (!current.includes(cb.value)) current.push(cb.value);
                } else {
                    current = current.filter(id => id !== cb.value);
                }
                localStorage.setItem(STORAGE_KEY, JSON.stringify(current));
            });
        });
    });

    document.querySelector('form').addEventListener('submit', function(e) {
        const saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
        document.querySelectorAll('input[name="selected_courses[]"][type=hidden]').forEach(el => el.remove());
        saved.forEach(id => {
            if (!document.querySelector('input[type=checkbox][name="selected_courses[]"][value="' + id + '"]')) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'selected_courses[]';
                hidden.value = id;
                this.appendChild(hidden);
            }
        });
        localStorage.removeItem(STORAGE_KEY);
    });
    </script>
</body>
</html>
