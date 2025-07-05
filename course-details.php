<?php
require_once 'session.php';
include 'functions.php';

// Prefill user data for modal
$userData = [];
if (isLoggedIn()) {
    $userData = getUserData($_SESSION['user_id']);
}

// Handle assistant password-only login
$show_assistant_login_modal = false;
$assistant_login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assistant_login'])) {
    $userId = $_SESSION['user_id'] ?? null;
    $password = $_POST['login_password'] ?? '';
    $userData = $userId ? getUserData($userId) : null;
    if (!$userId || !$userData) {
        $assistant_login_error = 'Session expired. Please log in again.';
        $show_assistant_login_modal = true;
    } elseif (!isUserAssistant($userId)) {
        $assistant_login_error = 'You are not registered as an assistant.';
        $show_assistant_login_modal = true;
    } elseif (empty($password)) {
        $assistant_login_error = 'Please enter your password.';
        $show_assistant_login_modal = true;
    } elseif (!password_verify($password, $userData['password'])) {
        $assistant_login_error = 'Incorrect password.';
        $show_assistant_login_modal = true;
    } else {
        $_SESSION['user_role'] = 'assist';
        header('Location: assistant-dashboard.php');
        exit();
    }
}

// Handle assistant creation form
$show_assistant_modal = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_assistant'])) {
    $userId = $_SESSION['user_id'];
    $courseId = $_POST['course_id'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $academic_year = $_POST['academic_year'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Check if already an assistant for any course
    if (isUserAssistant($userId)) {
        $assistant_error = 'You have already created an assistant profile.';
        $show_assistant_modal = true;
    } elseif (empty($username) || empty($name) || empty($academic_year) || empty($password) || empty($confirm_password)) {
        $assistant_error = 'Please fill in all required fields.';
        $show_assistant_modal = true;
    } elseif ($password !== $confirm_password) {
        $assistant_error = 'Passwords do not match.';
        $show_assistant_modal = true;
    } elseif (strlen($password) < 6) {
        $assistant_error = 'Password must be at least 6 characters.';
        $show_assistant_modal = true;
    } else {
        // Register as assistant (multi-role)
        $registerResult = registerUser($username, $userData['email'], $password, $academic_year, 'assist');
        if ($registerResult === true) {
            $_SESSION['user_role'] = 'assist';
            header('Location: assistant-dashboard.php');
            exit();
        } else {
            $assistant_error = is_string($registerResult) ? $registerResult : 'Failed to create assistant profile.';
            $show_assistant_modal = true;
        }
    }
}


// Log request parameters using PHP's error log
if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
    error_log('Course Details - GET: ' . print_r($_GET, true));
}

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

// Log course lookup result if in debug mode
if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
    error_log("Looking up course ID: $courseId - " . ($course ? 'Found' : 'Not Found'));
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
    <title><?php echo htmlspecialchars($course['name'] ?? 'Course Details'); ?> - Study Crew</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="course-details.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
        <a href="courses.php?year=<?php echo urlencode($selectedYear); ?>&semester=<?php echo urlencode($selectedSemester); ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Courses
        </a>
        
        <div class="course-header">
            <h1><?php echo htmlspecialchars($course['name']); ?></h1>
            <div class="course-meta">
                <span><i class="fas fa-hashtag"></i> <?php echo htmlspecialchars($course['code']); ?></span>
                <span><i class="fas fa-star"></i> <?php echo htmlspecialchars($course['credit_hours']); ?> Credits</span>
                <span><i class="fas fa-calendar-alt"></i> Year <?php echo htmlspecialchars($course['year']); ?></span>
                <span><i class="fas fa-layer-group"></i> Semester <?php echo htmlspecialchars($course['semester']); ?></span>
            </div>
            <div class="course-description">
                <?php echo nl2br(htmlspecialchars($course['description'])); ?>
            </div>
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

        <div class="tutors-list">
            <div class="tutors-header">
                <div class="tutor-name">Assistant Name</div>
                <div class="tutor-year">Academic Year</div>
                <div class="tutor-visits">Visit Count</div>
            </div>

            <?php if (!empty($tutors)): ?>
                <?php foreach ($tutors as $tutor): ?>
                    <div class="tutor-item-container">
                        <a href="tutor-details.php?id=<?php echo $tutor['user_id']; ?>&course_id=<?php echo urlencode($courseId); ?>" class="tutor-item">
                            <div class="tutor-name">
                                <i class="fas fa-user-graduate" style="margin-right: 8px; color: #667eea;"></i>
                                <?php echo htmlspecialchars($tutor['name']); ?>
                            </div>
                            <div class="tutor-year">
                                <i class="fas fa-calendar" style="margin-right: 8px; color: #6c757d;"></i>
                                <?php echo htmlspecialchars($tutor['year']); ?>
                            </div>
                            <div class="tutor-visits">
                                <i class="fas fa-eye" style="margin-right: 8px; color: #6c757d;"></i>
                                <?php echo $tutor['visits']; ?> <?php echo $tutor['visits'] === 1 ? 'Visit' : 'Visits'; ?>
                            </div>
                        </a>
                        <?php if (!empty($tutor['bio'])): ?>
                            <div class="tutor-bio">
                                <p><i class="fas fa-quote-left" style="color: #adb5bd; margin-right: 8px;"></i> 
                                <?php echo nl2br(htmlspecialchars($tutor['bio'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-tutors">
                    <i class="fas fa-user-graduate" style="font-size: 2.5rem; color: #dee2e6; margin-bottom: 1rem;"></i>
                    <h3 style="color: #6c757d; margin-bottom: 0.5rem;">No Assistants Available</h3>
                    <p style="color: #adb5bd; max-width: 500px; margin: 0 auto 1.5rem;">
                        There are currently no assistants available for this course. Please check back later or consider becoming an assistant yourself!
                    </p>
                    <?php
$studentYearOrder = ['Freshman'=>1,'Sophomore'=>2,'Junior'=>3,'Senior'=>4,'Graduate'=>5];
$userAcademicYear = $userData['academic_year'] ?? '';
$courseYear = $course['year'] ?? '';


// Only show button if user is logged in, is not already an assistant, and their year is greater than the course year
if (
    isLoggedIn() &&
    isset($studentYearOrder[$userAcademicYear], $studentYearOrder[$courseYear]) &&
    $studentYearOrder[$userAcademicYear] > $studentYearOrder[$courseYear]
) {
?>
<a href="#" id="become-assistant-btn" class="btn" style="background: #667eea; color: white; text-decoration: none; padding: 0.75rem 1.5rem; border-radius: 50px; font-weight: 500; transition: all 0.2s ease; margin-left: 1rem;">
    <i class="fas fa-user-plus" style="margin-right: 8px;"></i> Become an Assistant
</a>
<?php } ?>
<!-- Modal for Become Assistant -->
<div id="assistantModal" class="modal assistant-modal" style="display:none;">
  <div class="modal-content">
    <span class="close" id="closeAssistantModal">&times;</span>
    <h2 class="modal-header">Become an Assistant for <?php echo htmlspecialchars($course['name']); ?></h2>
    <div class="modal-body">
    <?php if (!isUserAssistant($_SESSION['user_id'] ?? null)): ?>
      <form method="POST" id="assistantApplicationForm">
        <input type="hidden" name="apply_assistant" value="1">
        <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($courseId); ?>">
        <?php if (!empty($assistant_error)): ?>
          <div class="error-banner">
            <?php echo htmlspecialchars($assistant_error); ?>
          </div>
        <?php endif; ?>
        <div class="form-group">
          <label>Full Name</label>
          <div class="input-group">
            <span class="input-icon"><i class="fas fa-user"></i></span>
            <span class="input-static-value"><?php echo htmlspecialchars($userData['name'] ?? $userData['username'] ?? ''); ?></span>
            <input type="hidden" name="name" value="<?php echo htmlspecialchars($userData['name'] ?? $userData['username'] ?? ''); ?>">
          </div>
        </div>
        <div class="form-group">
          <label>Username</label>
          <div class="input-group">
            <span class="input-icon"><i class="fas fa-user-tag"></i></span>
            <span class="input-static-value"><?php echo htmlspecialchars($userData['username'] ?? ''); ?></span>
            <input type="hidden" name="username" value="<?php echo htmlspecialchars($userData['username'] ?? ''); ?>">
          </div>
        </div>
        <div class="form-group">
          <label>Academic Year</label>
          <div class="input-group">
            <span class="input-icon"><i class="fas fa-graduation-cap"></i></span>
            <span class="input-static-value"><?php echo htmlspecialchars($userData['academic_year'] ?? ''); ?></span>
            <input type="hidden" name="academic_year" value="<?php echo htmlspecialchars($userData['academic_year'] ?? ''); ?>">
          </div>
        </div>
        <div class="form-group">
          <label for="assistant-password">Password</label>
          <div class="input-group">
            <span class="input-icon"><i class="fas fa-lock"></i></span>
            <input type="password" id="assistant-password" name="password" required minlength="6">
          </div>
        </div>
        <div class="form-group">
          <label for="assistant-confirm-password">Confirm Password</label>
          <div class="input-group">
            <span class="input-icon"><i class="fas fa-lock"></i></span>
            <input type="password" id="assistant-confirm-password" name="confirm_password" required minlength="6">
          </div>
        </div>
        <button type="submit" class="submit-btn assistant-btn"><i class="fas fa-user-plus btn-icon"></i>Become Assistant</button>
      </form>
    <?php else: ?>
      <form method="POST" id="assistantLoginForm">
        <input type="hidden" name="assistant_login" value="1">
        <?php if (!empty($assistant_login_error)): ?>
          <div class="error-banner" style="background: #ef4444; color: #fff; padding: 12px 18px; border-radius: 6px; margin-bottom: 16px; text-align: center; font-weight: 500;">
            <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>
            <?php echo htmlspecialchars($assistant_login_error); ?>
          </div>
        <?php endif; ?>
        <div class="form-group">
          <label for="assistant-login-password">Password</label>
          <div class="input-group">
            <span class="input-icon"><i class="fas fa-lock"></i></span>
            <input type="password" id="assistant-login-password" name="login_password" required minlength="6" autocomplete="current-password">
          </div>
        </div>
        <button type="submit" class="submit-btn assistant-btn"><i class="fas fa-sign-in-alt btn-icon"></i>Login as Assistant</button>
      </form>
    <?php endif; ?>
    </div>
  </div>
</div>


                </div>
            <?php endif; ?>
        </div>
    </div>
    </div>

    <!-- Footer -->
        <footer style="background: white; padding: 2rem 0; margin-top: 3rem; border-top: 1px solid #e9ecef;">
        <div class="container" style="text-align: center; color: #6c757d; font-size: 0.9rem;">
            <p>&copy; <?php echo date('Y'); ?> Study Crew. All rights reserved.</p>
        </div>
    </footer>
    
    <script>
    // Auto-submit search form when typing stops
    let searchTimer;
    document.querySelector('.search-form input[name="search"]')?.addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            this.form.submit();
        }, 500);
    });
    </script>
<script>
// Modal logic for Become an Assistant
const becomeAssistantBtn = document.getElementById('become-assistant-btn');
const assistantModal = document.getElementById('assistantModal');
const closeAssistantModal = document.getElementById('closeAssistantModal');
if (becomeAssistantBtn && assistantModal && closeAssistantModal) {
  becomeAssistantBtn.addEventListener('click', function(e) {
    e.preventDefault();
    assistantModal.style.display = 'block';
  });
  closeAssistantModal.onclick = function() {
    assistantModal.style.display = 'none';
  };
  window.onclick = function(event) {
    if (event.target === assistantModal) assistantModal.style.display = 'none';
  };
}
// Auto-open assistant modal after POST error (signup or login)
<?php if ((!empty($show_assistant_modal) && $show_assistant_modal) || (!empty($show_assistant_login_modal) && $show_assistant_login_modal)): ?>
window.addEventListener('DOMContentLoaded', function() {
  if (assistantModal) assistantModal.style.display = 'block';
});
<?php endif; ?>
// Become a Student button: force logout and redirect to login with intent=get
const becomeStudentBtn = document.getElementById('become-student-btn');
if (becomeStudentBtn) {
    becomeStudentBtn.addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = 'index.php?logout=1&next=' + encodeURIComponent('index.php?action=login&intent=get');
    });
}
</script>
</body>
</html>