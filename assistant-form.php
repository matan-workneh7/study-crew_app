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

// Get all available courses
$courses = getAllCourses();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $year = $_POST['year'];
    $email = $_POST['email'];
    $selectedCourse = $_POST['selected_course'];
    $telegram = $_POST['telegram'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $otherInfo = $_POST['other_info'] ?? '';
    
    // Save assistant application
    if (saveAssistantApplication($userId, $selectedCourse, $telegram, $phone, $otherInfo)) {
        // Set success message
        $_SESSION['success_message'] = "Your application has been submitted successfully. We'll review it and get back to you soon.";
        
        // Redirect to courses page
        header("Location: courses.php");
        exit();
    } else {
        $error = "Failed to submit your application. Please try again.";
    }
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
    <title>Student Assistant Form - Study Crew</title>
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
    <div class="container assistant-form-container">
        <div class="assistant-card">
            <h1>Student Assistant Form</h1>
            <p class="form-subtitle">Please complete your profile to join the team.</p>

            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="year">Year</label>
                    <input type="text" id="year" name="year" value="<?php echo htmlspecialchars($userData['academic_year']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="selected_course">Selected Course</label>
                    <select id="selected_course" name="selected_course" required>
                        <option value="">Select a course</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="telegram">Telegram Username (Optional)</label>
                    <div class="input-group">
                        <span class="input-icon">✈️</span>
                        <input type="text" id="telegram" name="telegram" placeholder="@yourusername">
                    </div>
                </div>

                <div class="form-group">
                    <label for="phone">Phone (Optional)</label>
                    <div class="input-group">
                        <span class="input-icon">📞</span>
                        <input type="text" id="phone" name="phone" placeholder="+251---------">
                    </div>
                </div>

                <div class="form-group">
                    <label for="other_info">Other Contact Information (Optional)</label>
                    <textarea id="other_info" name="other_info" rows="4"></textarea>
                </div>

                <button type="submit" class="submit-btn">
                    <span class="btn-icon">👤</span> Submit Application
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