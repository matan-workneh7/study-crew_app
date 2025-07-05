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
    
    try {
        $conn = getDbConnection();
        
        // Start transaction
        $conn->beginTransaction();
        
        // First, remove all existing course assignments for this user
        $stmt = $conn->prepare("DELETE FROM assistants WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // Add new course selections
        $stmt = $conn->prepare("INSERT INTO assistants (user_id, course_id, created_at) VALUES (?, ?, NOW())");
        
        foreach ($selectedCourses as $courseId) {
            // Validate course exists
            $course = getCourseById($courseId);
            if ($course) {
                $stmt->execute([$userId, $courseId]);
            }
        }
        
        // Commit transaction
        $conn->commit();
        $success = true;
        
    } catch (PDOException $e) {
        // Rollback transaction on error
        if (isset($conn) && $conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Error saving assistant courses: " . $e->getMessage());
        $error = "An error occurred while saving your course selections. Please try again.";
    }
    
    // If we reach here, the database operation was successful
    // Store selected courses in session
    $_SESSION['assistant_courses'] = $selectedCourses;
    $_SESSION['success_message'] = 'Your course selections have been saved successfully!';
    
    // Redirect to profile page
    header("Location: assistant-profile.php");
    exit();
} else {
    // If not a POST request, redirect back to dashboard
    header("Location: assistant-dashboard.php");
    exit();
}
?>
