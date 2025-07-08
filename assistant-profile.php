<?php
require_once 'session.php';
require_once 'functions.php';

// Check if user is logged in and is an assistant
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'assist') {
    header("Location: assistant-dashboard.php");
    exit();
}

// Initialize temporary course selections from session or database
if (!isset($_SESSION['temporary_course_selections'])) {
    $userId = $_SESSION['user_id'];
    $assistingCourses = getAssistantCourses($userId);
    $_SESSION['temporary_course_selections'] = array_column($assistingCourses, 'course_id');
}

$userId = $_SESSION['user_id'];
$userData = getUserData($userId);
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    // Get form data
    $fullName = trim($_POST['full_name'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $availability = trim($_POST['availability'] ?? '');
    $contactInfo = trim($_POST['contact_info'] ?? '');
    
    // Basic validation
    if (empty($fullName)) {
        $error = 'Full name is required';
    } else {
        try {
            $conn = getDbConnection();
            $conn->beginTransaction();
            
            // 1. Save profile information to the assistants table
            // First check if the assistant record exists
            $stmt = $conn->prepare("SELECT id FROM assistants WHERE user_id = ?");
            $stmt->execute([$userId]);
            $assistant = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($assistant) {
                // Update existing assistant
                $stmt = $conn->prepare("
                    UPDATE assistants 
                    SET bio = ?, availability = ?, phone = ?, updated_at = NOW()
                    WHERE user_id = ?
                ");
                // Using phone field to store contact info since it's available
                $stmt->execute([$bio, $availability, $contactInfo, $userId]);
            } else {
                // Insert new assistant
                $stmt = $conn->prepare("
                    INSERT INTO assistants 
                    (user_id, bio, availability, phone, created_at, updated_at)
                    VALUES (?, ?, ?, ?, NOW(), NOW())
                ");
                // Using phone field to store contact info since it's available
                $stmt->execute([$userId, $bio, $availability, $contactInfo]);
            }
            
            // 2. Save course selections to assistant_courses
            if (isset($_SESSION['temporary_course_selections']) && is_array($_SESSION['temporary_course_selections'])) {
                // Get the assistant ID if we just inserted a new record
                if (!isset($assistant)) {
                    $assistant = ['id' => $conn->lastInsertId()];
                }
                
                // First, remove all existing course assignments
                $stmt = $conn->prepare("DELETE FROM assistant_courses WHERE assistant_id = ?");
                $stmt->execute([$assistant['id']]);
                
                // Then add the new ones
                $stmt = $conn->prepare("
                    INSERT INTO assistant_courses (assistant_id, course_id, created_at) 
                    VALUES (?, ?, NOW())
                ");
                
                foreach ($_SESSION['temporary_course_selections'] as $courseId) {
                    // Validate course exists
                    $course = getCourseById($courseId);
                    if ($course) {
                        $stmt->execute([$assistant['id'], $courseId]);
                    }
                }
                
                // Clear the temporary selections after successful save
                unset($_SESSION['temporary_course_selections']);
            }
            
            $conn->commit();
            
            // Set success message and redirect
            $_SESSION['success_message'] = 'Your profile has been saved successfully!';
            header("Location: assistant-dashboard.php");
            exit();
            
        } catch (PDOException $e) {
            if (isset($conn) && $conn->inTransaction()) {
                $conn->rollBack();
            }
            $error = 'An error occurred while saving your profile: ' . $e->getMessage();
            error_log('Profile save error: ' . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Profile - Study Crew</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="assistant-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header Section -->
    <?php include __DIR__ . '/includes/header.php'; ?>

    <!-- Main Content -->
    <div class="profile-container">
        <div class="profile-card">
            <div class="profile-header">
                <h1>Complete Your Assistant Profile</h1>
                <p>Please fill in your details to complete your assistant profile</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-banner">
                    <div class="container">
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <a href="assistant-dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            
            <form method="POST" action="" class="profile-form">
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" required 
                           value="<?php echo htmlspecialchars($userData['username'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio" rows="4" 
                              placeholder="Tell students about yourself and your teaching experience"><?php echo htmlspecialchars($userData['bio'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="availability">Availability</label>
                    <input type="text" id="availability" name="availability" 
                           placeholder="e.g., Weekdays after 5 PM, Weekends"
                           value="<?php echo htmlspecialchars($userData['availability'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="contact_info">Preferred Contact Information</label>
                    <input type="text" id="contact_info" name="contact_info" 
                           placeholder="Email or phone number"
                           value="<?php echo htmlspecialchars($userData['telegram'] ?? $userData['phone'] ?? ''); ?>">
                </div>
                
                <div class="selected-courses">
                    <h3>Selected Courses</h3>
                    <?php 
                    $selectedCourses = [];
                    
                    if (isset($_SESSION['temporary_course_selections']) && is_array($_SESSION['temporary_course_selections'])) {
                        $selectedCourses = getCoursesByIds($_SESSION['temporary_course_selections']);
                    } elseif ($assistant = getAssistantByUserId($userId)) {
                        if (!empty($assistant['course_ids'])) {
                            $selectedCourses = getCoursesByIds($assistant['course_ids']);
                        }
                    }
                    
                    if (!empty($selectedCourses)): ?>
                        <div class="course-buttons">
                            <?php foreach ($selectedCourses as $course): 
                                if (!empty($course['name'])): // Only display if course has a name
                                    // Use the same icon logic as in the course list
                                    $category = strtolower($course['category'] ?? 'default');
                                    $iconHtml = getCourseIcon($category);
                        ?>
                                    <div class="course-button">
                                        <span class="icon"><?php echo $iconHtml; ?></span>
                                        <span class="course-name"><?php echo htmlspecialchars($course['name']); ?></span>
                                    </div>
                        <?php   endif;
                            endforeach;
                        else: 
                        ?>
                            <div class="no-courses">
                                <i class="far fa-folder-open"></i>
                                No courses selected yet.
                            </div>
                        <?php endif; ?>
                    </div>
                    <p class="note">
                        <i class="fas fa-arrow-left"></i>
                        To modify your course selection, please go back to the dashboard.
                    </p>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="save_profile" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Profile
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .profile-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .selected-courses {
            margin: 1.5rem 0;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }
        
        .selected-courses h3 {
            margin-top: 0;
            color: #2c3e50;
            margin-bottom: 1.2rem;
            font-size: 1.3rem;
        }
        
        .course-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 1.5rem;
        }
        
        .course-button {
            display: inline-flex;
            align-items: center;
            padding: 0.6rem 1rem;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            color: #2c3e50;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        
        .course-button:hover {
            background: #f0f4f8;
            border-color: #a5b4c0;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .course-button .icon {
            margin-right: 8px;
            color: #4a6cf7;
            font-size: 1rem;
            width: 20px;
            text-align: center;
        }
        
        .course-button .icon i {
            margin: 0;
            color: inherit;
        }
        
        .no-courses {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: #f8f9fa;
            border: 1px dashed #d1d5db;
            border-radius: 6px;
            color: #6b7280;
            font-style: italic;
        }
        
        .no-courses i {
            margin-right: 8px;
            color: #9ca3af;
        }
        
        .note {
            display: flex;
            align-items: center;
            color: #6b7280;
            font-size: 0.9em;
            margin: 1rem 0 0 0;
        }
        
        .note i {
            margin-right: 6px;
            color: #9ca3af;
        }
        
        .profile-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            padding: 2.5rem;
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .profile-header h1 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .profile-header p {
            color: #6c757d;
            font-size: 1.1rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: 500;
        }
        
        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .form-group input[type="text"]:focus,
        .form-group textarea:focus {
            border-color: #4a6cf7;
            box-shadow: 0 0 0 3px rgba(74, 108, 247, 0.1);
            outline: none;
        }
        
        .selected-courses {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 2rem 0;
        }
        
        .selected-courses h3 {
            margin-top: 0;
            color: #2c3e50;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }
        
        .course-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .course-chip {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 20px;
            padding: 0.4rem 1rem;
            font-size: 0.9rem;
            color: #2c3e50;
        }
        
        .hint {
            color: #6c757d;
            font-size: 0.9rem;
            margin: 0.5rem 0 0 0;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            font-size: 1rem;
        }
        
        .btn i {
            margin-right: 0.5rem;
        }
        
        .btn-primary {
            background: #4a6cf7;
            color: white;
        }
        
        .btn-primary:hover {
            background: #3a5ce4;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(74, 108, 247, 0.2);
        }
        
        .btn-secondary {
            background: #f1f3f5;
            color: #495057;
        }
        
        .btn-secondary:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .profile-card {
                padding: 1.5rem;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</body>
</html>
