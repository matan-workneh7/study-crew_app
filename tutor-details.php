<?php
require_once 'session.php';
include 'functions.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header("Location: index.php?action=login&intent=get");
    exit();
}

// Get user ID from URL
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get user info
$user = getUserData($userId);

// Find assistant record for this user
$assistants = readJsonFile(ASSISTANTS_FILE);
$assistant = null;
foreach ($assistants as $a) {
    if ($a['user_id'] == $userId) {
        $assistant = $a;
        break;
    }
}

if (!$user) {
    echo '<h2>User not found.</h2>';
    exit();
}

if (!$assistant || !$user) {
    echo '<h2>Assistant not found.</h2>';
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['username'] ?? 'Tutor'); ?> - Study Crew</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="tutor-details.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <span class="book-icon">📚</span>STUDY CREW
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="courses.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'courses.php' ? 'active' : ''; ?>">Courses</a></li>
                    <li><a href="#">About</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>
            </nav>
            <div class="user-menu">
                <span><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
                <a href="?logout=1" class="sign-in-btn">LOGOUT</a>
            </div>
        </div>
    </header>
    <div class="container tutor-details-container">
        <a href="<?php echo isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : 'courses.php'; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        
        <?php if ($assistant && $user): ?>
            <div class="tutor-profile">
                <div class="tutor-header">
                    <div class="tutor-avatar">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="tutor-info">
                        <h1 class="tutor-name"><?php echo htmlspecialchars($user['username']); ?></h1>
                        <p class="tutor-title">
                            <i class="fas fa-graduation-cap"></i> 
                            Teaching Assistant
                        </p>
                        
                        <div class="tutor-meta">
                            <?php if (!empty($user['email'])): ?>
                                <div class="meta-item">
                                    <i class="fas fa-envelope"></i>
                                    <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>">
                                        <?php echo htmlspecialchars($user['email']); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($user['academic_year'])): ?>
                                <div class="meta-item">
                                    <i class="fas fa-calendar-alt"></i>
                                    <?php echo htmlspecialchars($user['academic_year']); ?> Year
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($assistant['created_at'])): ?>
                                <div class="meta-item">
                                    <i class="fas fa-clock"></i>
                                    Assistant since <?php echo date('M Y', strtotime($assistant['created_at'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($assistant['bio'])): ?>
                    <div class="tutor-bio">
                        <h3 class="section-title">
                            <i class="fas fa-user-graduate"></i> About Me
                        </h3>
                        <p><?php echo nl2br(htmlspecialchars($assistant['bio'])); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php 
                // Get courses this assistant is teaching
                $assistantCourses = [];
                $allAssistants = readJsonFile(ASSISTANTS_FILE);
                $allCourses = readJsonFile(COURSES_FILE);
                
                foreach ($allAssistants as $a) {
                    if ($a['user_id'] == $userId) {
                        foreach ($allCourses as $course) {
                            if ($course['id'] == $a['course_id']) {
                                $assistantCourses[] = $course;
                                break;
                            }
                        }
                    }
                }
                
                if (!empty($assistantCourses)): 
                ?>
                    <h3 class="section-title">
                        <i class="fas fa-book"></i> Teaching Courses
                    </h3>
                    <div class="courses-grid" id="profile-courses-list">
                        <?php foreach ($assistantCourses as $course): ?>
                            <div class="course-card selectable-course" 
                                 data-id="<?php echo htmlspecialchars($course['id']); ?>" 
                                 data-code="<?php echo htmlspecialchars($course['code']); ?>" 
                                 data-name="<?php echo htmlspecialchars($course['name']); ?>">
                                <div class="course-code">
                                    <i class="fas fa-book-open"></i>
                                    <?php echo htmlspecialchars($course['code']); ?>
                                </div>
                                <div class="course-name"><?php echo htmlspecialchars($course['name']); ?></div>
                                <div class="course-meta">
                                    <span><i class="fas fa-star"></i> <?php echo htmlspecialchars($course['credit_hours']); ?> Credits</span>
                                    <span>Year <?php echo htmlspecialchars($course['year']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <style>
                    .selectable-course.selected {
                        border: 2px solid #4a6fa5 !important;
                        box-shadow: 0 0 0 2px #b6d0f7;
                        background: #f6fbff;
                    }
                    .selectable-course {
                        cursor: pointer;
                        transition: border 0.2s, box-shadow 0.2s, background 0.2s;
                    }
                    </style>
                    <script>
                    // Default selection logic
                    document.addEventListener('DOMContentLoaded', function() {
                        const urlParams = new URLSearchParams(window.location.search);
                        const defaultCourseId = urlParams.get('course_id');
                        const courseCards = Array.from(document.querySelectorAll('.selectable-course'));
                        let selectedCourses = [];
                        // Select default course from URL or first course
                        let defaultSelected = false;
                        courseCards.forEach(function(card, idx) {
                            const courseId = card.dataset.id;
                            if ((defaultCourseId && courseId === defaultCourseId) || (!defaultCourseId && idx === 0)) {
                                card.classList.add('selected');
                                selectedCourses.push(courseId);
                                defaultSelected = true;
                            }
                            card.addEventListener('click', function() {
                                if (this.classList.contains('selected')) {
                                    this.classList.remove('selected');
                                    selectedCourses = selectedCourses.filter(id => id !== courseId);
                                } else {
                                    this.classList.add('selected');
                                    selectedCourses.push(courseId);
                                }
                            });
                        });
                        // Contact button logic
                        const contactBtn = document.querySelector('.btn.btn-primary[href="#contact"]');
                        const contactFormDiv = document.getElementById('contact');
                        const contactForm = document.getElementById('contactForm');

                        // Helper to update hidden course fields
                        function updateHiddenCourseFields() {
                            // Remove any previous hidden course fields
                            contactForm.querySelectorAll('.selected-course-field').forEach(el => el.remove());
                            // Add hidden fields for each selected course
                            selectedCourses.forEach(function(courseId) {
                                const card = courseCards.find(c => c.dataset.id === courseId);
                                if (card) {
                                    const code = card.dataset.code;
                                    const name = card.dataset.name;
                                    addHiddenField(contactForm, `courses[${courseId}][id]`, courseId);
                                    addHiddenField(contactForm, `courses[${courseId}][code]`, code);
                                    addHiddenField(contactForm, `courses[${courseId}][name]`, name);
                                }
                            });
                        }

                        // Update hidden fields whenever selection changes
                        courseCards.forEach(function(card) {
                            card.addEventListener('click', function() {
                                updateHiddenCourseFields();
                            });
                        });

                        contactBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            updateHiddenCourseFields();
                            // Show the form
                            contactFormDiv.style.display = 'block';
                            contactFormDiv.scrollIntoView({ behavior: 'smooth' });
                        });
                        function addHiddenField(form, name, value) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = name;
                            input.value = value;
                            input.classList.add('selected-course-field');
                            form.appendChild(input);
                        }
                    });
                    </script>
                <?php endif; ?>
                
                <div class="action-buttons">
                    <a href="#contact" class="btn btn-primary">
                        <i class="fas fa-envelope"></i> Contact Assistant
                    </a>
                    <a href="courses.php" class="btn btn-outline">
                        <i class="fas fa-book"></i> Browse All Courses
                    </a>
                </div>
            </div>
            
            <!-- Contact Form (initially hidden) -->
            <div id="contact" class="tutor-profile" style="display: none; margin-top: 2rem;">
                <h3 class="section-title">
                    <i class="fas fa-envelope"></i> Contact <?php echo htmlspecialchars($user['username']); ?>
                </h3>
                <?php
                // Get course details from URL
                $selectedCourseId = $_GET['course_id'] ?? '';
                $selectedCourseName = '';
                $selectedCourseCode = '';
                
                // Get all courses this tutor teaches
                $tutorCourses = [];
                $allAssistants = readJsonFile(ASSISTANTS_FILE);
                $allCourses = readJsonFile(COURSES_FILE);
                
                // Find all courses for this assistant
                foreach ($allAssistants as $asst) {
                    if ($asst['user_id'] == $assistant['user_id']) {
                        $courseId = $asst['course_id'];
                        
                        // Find course details
                        foreach ($allCourses as $course) {
                            if (isset($course['id']) && $course['id'] == $courseId) {
                                $tutorCourses[] = [
                                    'id' => $course['id'],
                                    'code' => $course['code'] ?? 'N/A',
                                    'name' => $course['name'] ?? 'Unnamed Course'
                                ];
                                
                                // If this is the selected course from URL
                                if ($course['id'] == $selectedCourseId) {
                                    $selectedCourseName = $course['name'] ?? '';
                                    $selectedCourseCode = $course['code'] ?? '';
                                }
                                break;
                            }
                        }
                    }
                }
                
                // If no course was selected but tutor has courses, select the first one
                if (empty($selectedCourseId) && !empty($tutorCourses)) {
                    $selectedCourseId = $tutorCourses[0]['id'];
                    $selectedCourseName = $tutorCourses[0]['name'];
                    $selectedCourseCode = $tutorCourses[0]['code'];
                }
                ?>
                <form id="contactForm" class="contact-form">
                    <input type="hidden" name="tutor_email" value="<?php echo htmlspecialchars($user['email']); ?>">
                    <input type="hidden" name="sender_email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>">
                    <input type="hidden" name="sender_name" value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>">
                    <input type="hidden" name="tutor_id" value="<?php echo htmlspecialchars($assistant['id']); ?>">
                    

                    
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" class="form-control" required placeholder="What's this message about?">
                    </div>
                    <div class="form-group">
                        <label for="message">Your Message</label>
                        <textarea id="message" name="message" rows="5" required
                                 placeholder="Type your message here..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>
            
            <script>
            // Toggle contact form
            document.querySelector('a[href="#contact"]')?.addEventListener('click', function(e) {
                e.preventDefault();
                const contactSection = document.getElementById('contact');
                contactSection.style.display = contactSection.style.display === 'none' ? 'block' : 'none';
                if (contactSection.style.display === 'block') {
                    contactSection.scrollIntoView({ behavior: 'smooth' });
                }
            });
            
            // Handle form submission
            document.getElementById('contactForm')?.addEventListener('submit', function(e) {
                e.preventDefault();
                const form = this;
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                const formData = new FormData(form);
                
                // Show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
                
                // Send form data via AJAX
                fetch('send-email.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reset form
                        form.reset();
                        
                        // Show success message
                        const successMsg = document.createElement('div');
                        successMsg.className = 'success-message';
                        successMsg.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
                        form.appendChild(successMsg);
                        
                        // Hide success message after 5 seconds
                        setTimeout(() => {
                            successMsg.remove();
                        }, 5000);
                    } else {
                        throw new Error(data.message || 'Failed to send message');
                    }
                })
                .catch(error => {
                    // Show error message
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'error-message';
                    errorMsg.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + (error.message || 'Failed to send message. Please try again.');
                    form.appendChild(errorMsg);
                    
                    // Hide error message after 5 seconds
                    setTimeout(() => {
                        errorMsg.remove();
                    }, 5000);
                })
                .finally(() => {
                    // Reset button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
            });
            </script>
        <?php else: ?>
            <div class="tutor-profile" style="text-align: center; padding: 3rem 2rem;">
                <div style="font-size: 4rem; color: #e9ecef; margin-bottom: 1rem;">
                    <i class="fas fa-user-slash"></i>
                </div>
                <h2 style="color: #495057; margin-bottom: 1rem;">Assistant Not Found</h2>
                <p style="color: #6c757d; margin-bottom: 1.5rem;">The requested assistant profile could not be found or is no longer available.</p>
                <a href="courses.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to Courses
                </a>
            </div>
        <?php endif; ?>
    </div>
    <footer style="background: white; padding: 2rem 0; margin-top: 3rem; border-top: 1px solid #e9ecef;">
        <div class="container" style="text-align: center; color: #6c757d; font-size: 0.9rem;">
            <p>&copy; <?php echo date('Y'); ?> Study Crew. All rights reserved.</p>
        </div>
    </footer>
    
    <style>
    /* Back button styles */
    .back-button {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #667eea;
        text-decoration: none;
        margin-bottom: 1.5rem;
        font-weight: 500;
        transition: all 0.2s ease;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
    }
    
    .back-button:hover {
        background: #f1f3f5;
        transform: translateX(-3px);
    }
    
    .back-button i {
        transition: transform 0.2s ease;
    }
    
    .back-button:hover i {
        transform: translateX(-3px);
    }
    
    /* Form styles */
    .contact-form {
        max-width: 600px;
        margin: 0 auto;
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: #495057;
        font-weight: 500;
    }
    
    .form-group input[type="text"],
    .form-group textarea {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    }
    
    .form-group textarea {
        min-height: 150px;
        resize: vertical;
    }
    
    .form-group input[type="text"]:focus,
    .form-group textarea:focus {
        border-color: #667eea;
        outline: none;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    /* Message styles */
    .success-message,
    .error-message {
        padding: 1rem;
        border-radius: 8px;
        margin-top: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        animation: fadeIn 0.3s ease;
    }
    
    .success-message {
        background: #e8f5e9;
        color: #2e7d32;
        border-left: 4px solid #4caf50;
    }
    
    .error-message {
        background: #ffebee;
        color: #c62828;
        border-left: 4px solid #f44336;
    }
    
    .success-message i,
    .error-message i {
        font-size: 1.25rem;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Button styles */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border-radius: 50px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
        font-size: 0.95rem;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    }
    
    .btn-primary {
        background: #667eea;
        color: white;
    }
    
    .btn-primary:hover {
        background: #5a67d8;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    
    .btn-outline {
        background: white;
        color: #667eea;
        border: 2px solid #667eea;
    }
    
    .btn-outline:hover {
        background: #f8f9fa;
        transform: translateY(-2px);
    }
    </style>
</body>
</html> 