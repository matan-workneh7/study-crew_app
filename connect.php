<?php
include 'functions.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?action=login&intent=get");
    exit();
}

// Get course, tutor IDs and context from URL
$courseId = isset($_GET['course']) ? (int)$_GET['course'] : 0;
$tutorId = isset($_GET['tutor']) ? (int)$_GET['tutor'] : 0;
$selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : 1;
$selectedSemester = isset($_GET['semester']) ? (int)$_GET['semester'] : 1;

// Get course and tutor details
$course = getCourseById($courseId);
$tutor = getTutorById($tutorId);

if (!$course || !$tutor) {
    header("Location: courses.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentName = $_POST['student_name'];
    $email = $_POST['email'];
    $telegram = $_POST['telegram'] ?? '';
    $problemDescription = $_POST['problem_description'];
    
    // Save connection request
    if (saveConnectionRequest($_SESSION['user_id'], $tutorId, $courseId, $problemDescription, $telegram)) {
        // Increment tutor visit count
        incrementTutorVisits($tutorId);
        
        // Set success message
        $_SESSION['success_message'] = "Your request has been sent to {$tutor['name']}. They will contact you soon.";
        
        // Redirect back to course details with context
        header("Location: course-details.php?id=$courseId&year=$selectedYear&semester=$selectedSemester");
        exit();
    } else {
        $error = "Failed to send your request. Please try again.";
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
    <title>Connect with Assistant - Study Crew</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="courses.css">
</head>
<body>
    <!-- Header Section -->
    <?php include __DIR__ . '/includes/header.php'; ?>

    <!-- Main Content -->
    <div class="container connect-container">
        <a href="javascript:history.back()" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
        <div class="connect-card">
            <h1>Connect with Your Assistant</h1>
            <p class="connect-info">
                You've selected <span class="highlight"><?php echo htmlspecialchars($course['name']); ?></span> 
                and your chosen assistant is <span class="highlight"><?php echo htmlspecialchars($tutor['name']); ?></span>.
                Please fill out the form below to get in touch.
            </p>

            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
    <input type="hidden" name="year" value="<?php echo $selectedYear; ?>">
    <input type="hidden" name="semester" value="<?php echo $selectedSemester; ?>">
    <input type="hidden" name="course" value="<?php echo $courseId; ?>">
    <input type="hidden" name="tutor" value="<?php echo $tutorId; ?>">
                <div class="form-group">
                    <label for="student_name">Student Name</label>
                    <input type="text" id="student_name" name="student_name" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="telegram">Telegram Username (Optional)</label>
                    <div class="input-group">
                        <span class="input-icon">✈️</span>
                        <input type="text" id="telegram" name="telegram" placeholder="@yourusername">
                    </div>
                </div>

                <div class="form-group">
                    <label for="problem_description">Problem Description</label>
                    <textarea id="problem_description" name="problem_description" rows="5" placeholder="Please elaborate on the problems or chapters you're facing difficulties with. Be as specific as possible." required></textarea>
                </div>

                <p class="form-note">This will help your assistant prepare for your session.</p>

                <button type="submit" class="connect-btn">
                    <span class="btn-icon">✉️</span> Get in Touch
                </button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
</body>
</html>