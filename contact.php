<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/functions.php'; // Include functions if needed

// Get tutor ID from URL if available
$tutorId = isset($_GET['tutor_id']) ? (int)$_GET['tutor_id'] : null;
$courseId = isset($_GET['course_id']) ? $_GET['course_id'] : null;

// If we have a tutor ID, get tutor details
$tutor = null;
if ($tutorId) {
    try {
        $conn = getDbConnection();
        $stmt = $conn->prepare(
            "SELECT a.id, u.username, u.email 
            FROM assistants a 
            JOIN users u ON a.user_id = u.id 
            WHERE a.id = ?"
        );
        $stmt->execute([$tutorId]);
        $tutor = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Error getting tutor details: ' . $e->getMessage());
    }
}

// Set default subject if we have tutor info
$defaultSubject = $tutor ? "Question about tutoring" : "Contact Form Submission";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Study Crew</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="modal-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%,rgb(71, 87, 159) 100%);
            color: #ffffff;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
            line-height: 1.6;
            font-weight: 300;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(102, 126, 234, 0.1);
            z-index: -1;
            filter: blur(60px);
        }
        
        body::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -80px;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: rgba(118, 75, 162, 0.1);
            z-index: -1;
            filter: blur(60px);
        }

        .container {
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            position: relative;
            padding: 0 20px;
        }
        
        h1 {
            text-align: center;
            margin: 40px 0 25px;
            font-size: 2.8rem;
            font-weight: 600;
            background: linear-gradient(135deg, #ffffff 0%, #d1d1d1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }
        
        .subtitle {
            text-align: center;
            margin-bottom: 50px;
            line-height: 1.8;
            font-weight: 300;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            opacity: 0.9;
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.85);
        }
        
        hr {
            border: none;
            height: 1px;
            background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.2), transparent);
            margin: 50px auto;
            width: 80%;
        }
        
        .contact-container {
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
            position: relative;
            margin-bottom: 80px;
        }
        
        .contact-info {
            flex: 1;
            min-width: 300px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .contact-info:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .contact-info h2 {
            margin-bottom: 30px;
            font-size: 1.8rem;
            font-weight: 500;
            position: relative;
            padding-bottom: 15px;
            color: #ffffff;
        }
        
        .contact-info h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(to right, #667eea, rgba(102, 126, 234, 0));
            border-radius: 3px;
        }
        
        .info-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 30px;
            transition: transform 0.3s;
        }
        
        .info-item:hover {
            transform: translateX(8px);
        }
        
        .info-icon {
            font-size: 1.3rem;
            margin-right: 20px;
            color: white;
            min-width: 50px;
            height: 50px;
            background: rgba(102, 126, 234, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
        }
        
        .info-item:hover .info-icon {
            background: rgba(102, 126, 234, 0.3);
            transform: scale(1.1);
        }
        
        .info-content h3 {
            margin-bottom: 8px;
            font-size: 1.2rem;
            font-weight: 500;
            color: #ffffff;
        }
        
        .info-content p {
            text-align: left;
            margin-bottom: 0;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 300;
            margin-left: 0;
            font-size: 0.95rem;
        }
        
        .contact-form {
            flex: 1;
            min-width: 300px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 40px;
            color: #333;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
            transform-style: preserve-3d;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .contact-form:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .contact-form::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(102, 126, 234, 0.05), transparent);
            transform: rotate(45deg);
            z-index: 0;
            animation: shimmer 8s infinite linear;
        }
        
        @keyframes shimmer {
            0% { transform: rotate(45deg) translate(-50%, -50%); }
            100% { transform: rotate(45deg) translate(50%, 50%); }
        }
        
        .contact-form > * {
            position: relative;
            z-index: 1;
        }
        
        .contact-form h2 {
            margin-bottom: 30px;
            font-size: 1.8rem;
            color: #667eea;
            font-weight: 500;
            position: relative;
            padding-bottom: 15px;
        }
        
        .contact-form h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(to right, #667eea, rgba(102, 126, 234, 0));
            border-radius: 3px;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: #555;
            font-size: 0.95rem;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 15px 20px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
            background: rgba(245, 245, 245, 0.9);
            font-family: 'Poppins', sans-serif;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
            background: white;
        }
        
        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .send-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 16px 40px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            display: inline-block;
            position: relative;
            overflow: hidden;
            width: 100%;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        
        .send-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        }
        
        .send-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.3) 0%, transparent 100%);
            transform: translateX(-100%);
            transition: transform 0.4s;
        }
        
        .send-btn:hover::after {
            transform: translateX(0);
        }
        
        /* Status messages */
        .status-message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 500;
            text-align: center;
            animation: fadeIn 0.5s ease;
        }
        
        .error-message {
            background-color: rgba(255, 99, 71, 0.2);
            border: 1px solid rgba(255, 99, 71, 0.3);
            color: #ffcccb;
        }
        
        .success-message {
            background-color: rgba(46, 213, 115, 0.2);
            border: 1px solid rgba(46, 213, 115, 0.3);
            color: #96f7d2;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Social Media Links */
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 40px;
        }

        /* Form Message Styles */
        .form-message {
            margin-top: 15px;
            padding: 12px 15px;
            border-radius: 6px;
            font-size: 14px;
            line-height: 1.5;
            display: none;
        }

        .form-message.success {
            display: block;
            background-color: #e6f7e6;
            color: #2e7d32;
            border: 1px solid #a5d6a7;
        }

        .form-message.error {
            display: block;
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }

        /* Form Group Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
        }

        /* Button Styles */
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-primary {
            background-color: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background-color: #5a67d8;
            transform: translateY(-1px);
        }

        .btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none !important;
        }

        /* Form Info */
        .form-info {
            background-color: #f0f5ff;
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }

        .form-info p {
            margin: 0;
            color: #2c5282;
            font-size: 14px;
        }
        
        .social-link {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .social-link:hover {
            background: rgba(102, 126, 234, 0.3);
            transform: translateY(-3px);
        }
        
        @media (max-width: 768px) {
            .contact-container {
                flex-direction: column;
            }
            
            .contact-info, .contact-form {
                width: 100%;
            }
            
            h1 {
                font-size: 2.2rem;
                margin-top: 30px;
            }
            
            body::before, body::after {
                display: none;
            }
            
            .subtitle {
                font-size: 1rem;
                padding: 0 15px;
            }
        }

        /* Footer Styles */
        footer {
            background: rgba(0, 0, 0, 0.25);
            padding: 30px 0;
            margin-top: 60px;
            width: 100%;
            text-align: center;
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        footer .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        footer p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin: 0;
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
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'assist'): ?>
                            <li><a href="assistant-dashboard.php">Assistant Dashboard</a></li>
                        <?php else: ?>
                            <li><a href="courses.php">Courses</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php" class="active">Contact Us</a></li>
                </ul>
            </nav>
           <?php if (isset($_SESSION['user_id'])): ?>
                <a href="?logout=1" class="sign-in-btn">LOGOUT</a>
            <?php else: ?>
                <a href="?action=login" class="sign-in-btn">SIGN IN</a>
            <?php endif; ?>
        </div>
    </header>
    
    <div class="container">
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Home</a>
        <h1>Get In Touch</h1>
        <p class="subtitle">We'd love to hear from you! Whether you have questions about our services, need technical support, or want to provide feedback, our team is ready to assist you. Reach out through the form or contact us directly, and we'll respond as quickly as possible.</p>
        
        <hr>
        
        <?php if($error): ?>
            <div class="status-message error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="status-message success-message">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <div class="contact-container">
            <div class="contact-info">
                <h2>Our Contact Details</h2>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="info-content">
                        <h3>Our Location</h3>
                        <p>2QGW+8J6, Unnamed Road, Addis Ababa, Ethiopia</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <div class="info-content">
                        <h3>Phone Number</h3>
                        <p>+251 507-XXX-XXXX</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="info-content">
                        <h3>Email Address</h3>
                        <p>info@bitscollege.edu.et</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="info-content">
                        <h3>Working Hours</h3>
                        <p>Monday - Friday: 8:00 AM - 5:00 PM<br>Saturday: 9:00 AM - 1:00 PM</p>
                    </div>
                </div>
                
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            
            <div class="contact-container">
                <form id="contactForm" class="contact-form" action="/study-crew_app/api/send-message.php" method="POST">
                    <h2>Send us a message</h2>
                    <?php if ($tutor): ?>
                        <div class="form-info">
                            <p>You're sending a message to: <strong><?php echo htmlspecialchars($tutor['username']); ?></strong></p>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="name">Your Name *</label>
                        <input type="text" id="name" name="name" required 
                            value="<?php echo isset($_SESSION['user_id']) ? htmlspecialchars($_SESSION['username'] ?? '') : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Your Email *</label>
                        <input type="email" id="email" name="email" required
                            value="<?php echo isset($_SESSION['user_id']) && isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject *</label>
                        <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($defaultSubject); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Your Message *</label>
                        <textarea id="message" name="message" rows="5" required></textarea>
                    </div>
                    <?php if ($tutorId): ?>
                        <input type="hidden" name="tutor_id" value="<?php echo $tutorId; ?>">
                    <?php endif; ?>
                    <?php if ($courseId): ?>
                        <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($courseId); ?>">
                    <?php endif; ?>
                    <input type="hidden" name="contact_submit" value="1">
                    <button type="submit" class="btn btn-primary">Send Message</button>
                    <div id="formMessage" class="form-message"></div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2025 Study Crew. All rights reserved.</p>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const contactForm = document.getElementById('contactForm');
        const formMessage = document.getElementById('formMessage');

        if (contactForm) {
            contactForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Disable submit button
                const submitBtn = contactForm.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Sending...';
                
                // Clear previous messages
                formMessage.textContent = '';
                formMessage.className = 'form-message';
                
                // Get form data
                const formData = new FormData(contactForm);
                
                // Send AJAX request
                fetch('/study-crew_app/api/send-message.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        formMessage.textContent = data.message;
                        formMessage.className = 'form-message success';
                        contactForm.reset();
                    } else {
                        throw new Error(data.message || 'Failed to send message');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    formMessage.textContent = error.message || 'An error occurred. Please try again.';
                    formMessage.className = 'form-message error';
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Send Message';
                    
                    // Scroll to message
                    formMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
            });
        }
    });
    </script>
</body>
</html>