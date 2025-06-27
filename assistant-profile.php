<?php
require_once 'session.php';
require_once 'functions.php';

// Check if user is logged in, is an assistant, and has selected courses
if (!isLoggedIn() || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'assist' || !isset($_SESSION['assistant_courses'])) {
    header("Location: assistant-dashboard.php");
    exit();
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
        // TODO: Save profile to database
        // This is where you would typically save to a database
        // For now, we'll just store in session
        $_SESSION['assistant_profile'] = [
            'full_name' => $fullName,
            'bio' => $bio,
            'availability' => $availability,
            'contact_info' => $contactInfo,
            'courses' => $_SESSION['assistant_courses']
        ];
        
        // Clear the courses from session after saving profile
        unset($_SESSION['assistant_courses']);
        
        // Redirect to dashboard with success message
        $_SESSION['success_message'] = 'Your profile has been saved successfully!';
        header("Location: assistant-dashboard.php");
        exit();
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
    <header>
        <div class="container">
            <div class="logo">
                <span class="book-icon">📚</span>STUDY CREW
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="assistant-dashboard.php">Assistant Dashboard</a></li>
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
                           value="<?php echo htmlspecialchars($userData['full_name'] ?? ''); ?>">
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
                           value="<?php echo htmlspecialchars($userData['contact_info'] ?? ''); ?>">
                </div>
                
                <div class="selected-courses">
                    <h3>Selected Courses</h3>
                    <div class="course-chips">
                        <?php 
                        $courses = isset($_SESSION['assistant_courses']) ? getCoursesByIds($_SESSION['assistant_courses']) : [];
                        foreach ($courses as $course): 
                        ?>
                            <span class="course-chip">
                                <?php echo htmlspecialchars($course['name']); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                    <p class="hint">To modify your course selection, please go back to the dashboard.</p>
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
