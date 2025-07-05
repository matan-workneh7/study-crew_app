<?php
require_once 'session.php';
require_once 'functions.php';

// Check if user is logged in and is an assistant
if (!isLoggedIn() || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'assist') {
    header("Location: index.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Handle form submission from dashboard
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_courses'])) {
    try {
        // Get selected courses
        $selectedCourses = $_POST['selected_courses'] ?? [];
        
        // Ensure selected_courses is an array of integers
        $selectedCourses = array_map('intval', $selectedCourses);
        $selectedCourses = array_filter($selectedCourses, function($id) {
            return $id > 0;
        });
        
        // Store in session
        $_SESSION['temporary_course_selections'] = $selectedCourses;
        
        // Set success message
        $_SESSION['success_message'] = 'Your course selections have been saved!';
        
        // Redirect to profile page
        header("Location: assistant-profile.php");
        exit();
        
    } catch (Exception $e) {
        error_log("Error saving course selections: " . $e->getMessage());
        $_SESSION['error_message'] = 'An error occurred while saving your course selections. Please try again.';
        header("Location: " . $_SERVER['HTTP_REFERER'] ?? 'assistant-dashboard.php');
        exit();
    }
} else {
    // If not a POST request, redirect back to dashboard
    header("Location: assistant-dashboard.php");
    exit();
}
?>
