<?php
require_once 'session.php';
require_once 'functions.php';

// Check if user is logged in and is an assistant
if (!isLoggedIn() || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'assist') {
    header("Location: index.php");
    exit();
}

$userId = $_SESSION['user_id'];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get selected courses
    $selectedCourses = $_POST['selected_courses'] ?? [];
    
    // Ensure selected_courses is an array
    if (!is_array($selectedCourses)) {
        $selectedCourses = [];
    }
    
    // Get existing assistant courses
    $assistants = readJsonFile(ASSISTANTS_FILE);
    $existingCourses = [];
    
    // Remove existing courses for this user
    $assistants = array_filter($assistants, function($assistant) use ($userId, &$existingCourses) {
        if ($assistant['user_id'] == $userId) {
            $existingCourses[] = $assistant;
            return false;
        }
        return true;
    });
    
    // Add new course selections
    foreach ($selectedCourses as $courseId) {
        $assistants[] = [
            'user_id' => $userId,
            'course_id' => $courseId,
            'year' => $course['year'],
            'semester' => $course['semester'],
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
    
    // Save to file
    if (writeJsonFile(ASSISTANTS_FILE, $assistants)) {
        // Store selected courses in session
        $_SESSION['assistant_courses'] = $selectedCourses;
        $_SESSION['success_message'] = 'Your course selections have been saved successfully!';
        
        // Redirect to profile page
        header("Location: assistant-profile.php");
        exit();
    } else {
        $_SESSION['error_message'] = 'There was an error saving your course selections. Please try again.';
        header("Location: assistant-dashboard.php");
        exit();
    }
} else {
    // If not a POST request, redirect back to dashboard
    header("Location: assistant-dashboard.php");
    exit();
}
?>
