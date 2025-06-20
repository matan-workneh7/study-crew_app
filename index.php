<?php
require_once 'session.php';
require_once 'functions.php';

// Initialize variables
// DATA RESET: This is a fresh start after data wipe. All users, assistants, courses, and connections have been cleared.
$error = '';
$success = '';
$show_login_modal = false;
$show_signup_modal = false;
$assist_intent = $_GET['intent'] ?? 'get';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $intent = $_POST['intent'] ?? 'get';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
        $show_login_modal = true;
    } else {
        $loginResult = processLogin($email, $password, $intent);
        if ($loginResult !== true) {
            $error = $loginResult;
            $show_login_modal = true;
        } else {
            // Redirect based on selected intent (role)
            if ($_SESSION['user_role'] === 'assist') {
                header("Location: assistant-dashboard.php");
            } else {
                header("Location: courses.php");
            }
            exit();
        }
    }
}

// Handle signup form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup_submit'])) {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $academic_year = $_POST['academic_year'] ?? '';
    $user_role = $_POST['user_role'] ?? 'student';
    if ($user_role === 'get') {
        $user_role = 'student';
    }
    
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($academic_year)) {
        $error = 'Please fill in all fields';
        $show_signup_modal = true;
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
        $show_signup_modal = true;
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
        $show_signup_modal = true;
    } else {
        $registrationResult = registerUser($username, $email, $password, $academic_year, $user_role);
        if ($registrationResult === true) {
            // Redirect to login with success message
            // Ensure user_role is consistent: 'assist' for assistants, 'student' for students
$login_intent = ($user_role === 'assist') ? 'assist' : 'get';
            $_SESSION['success_message'] = 'Registration successful! Please log in to continue.';
            header("Location: index.php?action=login&intent=" . $login_intent);
            exit();
        } else {
            $error = $registrationResult;
            $show_signup_modal = true;
        }
    }
}

// Handle modal display requests
if(isset($_GET['action'])) {
    if($_GET['action'] === 'login') {
        $show_login_modal = true;
        $assist_intent = $_GET['intent'] ?? '';
    } elseif($_GET['action'] === 'signup') {
        $show_signup_modal = true;
        $assist_intent = $_GET['intent'] ?? '';
    }
}

// Handle logout
if(isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Check for success messages
if(isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Crew - Your Campus Connection</title>
    <link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="modal-styles.css">
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
                    <li><a href="index.php" class="active">Home</a></li>
                    <?php if(isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'assist'): ?>
                        <li><a href="assistant-dashboard.php">Assistant Dashboard</a></li>
                    <?php elseif(isLoggedIn()): ?>
                        <li><a href="courses.php">Courses</a></li>
                    <?php endif; ?>
                    <li><a href="#">About</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                    
                </ul>
            </nav>
            <?php if(isLoggedIn()): ?>
                <a href="?logout=1" class="sign-in-btn">LOGOUT</a>
            <?php else: ?>
                <a href="?action=login" class="sign-in-btn">SIGN IN</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Success Message -->
    <?php if(isset($success_message)): ?>
        <div class="success-banner">
            <div class="container">
                <p><?php echo htmlspecialchars($success_message); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Welcome to <span class="highlight">Study Crew</span>!</h1>
                <p>Your campus connection for academic support.</p>
                <?php if(isLoggedIn()): ?>
                    <a href="courses.php" class="get-started-btn">Continue Learning <span class="arrow">→</span></a>
                <?php else: ?>
                    <a href="?action=signup&intent=get" class="get-started-btn">Get Started <span class="arrow">→</span></a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Main Content Section -->
    <section class="main-content">
        <div class="container">
            <h2>Need help with a tough subject?</h2>
            <h3>Want to offer help and make a difference?</h3>
            <p class="description">
                Study Crew connects students who are looking for academic assistance with peers who are 
                ready to help. Whether you're stuck in calculus or fluent in Java, this is your space to learn and 
                support each other.
            </p>

            <div class="cards">
                <div class="card">
                    <div class="card-icon">🔗</div>
                    <h3>Assist Others</h3>
                    <p>Share your knowledge, reinforce your understanding, and help fellow students succeed. Become a tutor and make a positive impact.</p>
                    <?php if(isLoggedIn()): ?>
                        <?php if(isUserAssistant($_SESSION['user_id'])): ?>
                            <a href="assistant-dashboard.php" class="assist-btn">Go to Assistant Dashboard</a>
                        <?php else: ?>
                            <a href="assistant-form.php" class="assist-btn">Become an Assistant</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="?action=login&intent=assist" class="assist-btn">I'm here to assist others</a>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <div class="card-icon">💡</div>
                    <h3>Get Assistance</h3>
                    <p>Find knowledgeable peers to help you understand challenging concepts, prepare for exams, and improve your grades.</p>
                    <?php if(isLoggedIn()): ?>
                        <a href="courses.php" class="assistance-btn">Browse Courses</a>
                    <?php else: ?>
                        <a href="?action=login&intent=student" class="assistance-btn">I'm looking for assistance</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Section -->
    <section class="why-choose">
        <div class="container">
            <h2>Why Choose Study Crew?</h2>
            <p class="subtitle">We provide a collaborative and supportive environment for students to thrive academically.</p>

            <div class="features">
                <div class="feature">
                    <div class="feature-icon blue">👥</div>
                    <h3>Peer-to-Peer Learning</h3>
                    <p>Connect with students who understand your coursework and challenges.</p>
                </div>

                <div class="feature">
                    <div class="feature-icon green">⏰</div>
                    <h3>Flexible Scheduling</h3>
                    <p>Find help or offer assistance at times that work for you.</p>
                </div>

                <div class="feature">
                    <div class="feature-icon purple">🛡️</div>
                    <h3>Safe & Trusted</h3>
                    <p>A secure platform connecting students from your own campus community.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2025 Study Crew. All rights reserved.</p>
        </div>
    </footer>

    <!-- Student Login Modal -->
    <?php if(isset($show_login_modal) && $show_login_modal && ($assist_intent === 'student' || $assist_intent === 'get')): ?>
    <div class="modal student-modal" style="display: block;">
        <div class="modal-content">
            <a href="index.php" class="close">&times;</a>
            <div class="modal-header">
                <div class="modal-logo">
                    <span class="book-icon">📚</span>STUDY CREW
                </div>
            </div>
            <div class="modal-body">
                <h2>Student Login</h2>
                <p class="modal-subtitle">Sign in to get the help you need</p>
                
                <?php if(isset($login_error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($login_error); ?></div>
                <?php endif; ?>

                <?php if (isset($error) && $show_login_modal): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="intent" value="student">
                    
                    <div class="form-group">
                        <label>Email</label>
                        <div class="input-group">
                            <span class="input-icon">📧</span>
                            <input type="email" name="email" placeholder="Enter your student email" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <div class="input-group">
                            <span class="input-icon">🔒</span>
                            <input type="password" name="password" placeholder="Enter your password" required>
                        </div>
                    </div>

                    <button type="submit" name="login_submit" class="submit-btn student-btn">
                        <span class="btn-icon">🔑</span> Sign In as Student
                    </button>

                    <p class="signup-link">
                        Don't have an account? <a href="?action=signup&intent=student">Sign up as Student</a>
                    </p>
                    <p class="switch-link">
                        <a href="?action=login&intent=assist">Switch to Assistant Login</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Assistant Login Modal -->
    <?php if(isset($show_login_modal) && $show_login_modal && $assist_intent === 'assist'): ?>
    <div class="modal assistant-modal" style="display: block;">
        <div class="modal-content">
            <a href="index.php" class="close">&times;</a>
            <div class="modal-header">
                <div class="modal-logo">
                    <span class="book-icon">📚</span>STUDY CREW
                </div>
            </div>
            <div class="modal-body">
                <h2>Assistant Login</h2>
                <p class="modal-subtitle">Sign in to start helping others</p>
                
                <?php if(isset($login_error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($login_error); ?></div>
                <?php endif; ?>

                <?php if (isset($error) && $show_login_modal): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="intent" value="assist">
                    
                    <div class="form-group">
                        <label>Email</label>
                        <div class="input-group">
                            <span class="input-icon">📧</span>
                            <input type="email" name="email" placeholder="Enter your assistant email" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <div class="input-group">
                            <span class="input-icon">🔒</span>
                            <input type="password" name="password" placeholder="Enter your password" required>
                        </div>
                    </div>

                    <button type="submit" name="login_submit" class="submit-btn assistant-btn">
                        <span class="btn-icon">🔑</span> Sign In as Assistant
                    </button>

                    <p class="signup-link">
                        Don't have an account? <a href="?action=signup&intent=assist">Sign up as Assistant</a>
                    </p>
                    <p class="switch-link">
                        <a href="?action=login&intent=get">Switch to Student Login</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Signup Modal -->
    <?php if(isset($show_signup_modal) && $show_signup_modal): ?>
    <div class="modal" style="display: block;">
        <div class="modal-content">
            <a href="index.php" class="close">&times;</a>
            <div class="modal-header">
                <div class="modal-logo">
                    <span class="book-icon">📚</span>STUDY CREW
                </div>
            </div>
            <div class="modal-body">
                <h2>Join Study Crew</h2>
                <p class="modal-subtitle">Create your account to <?php echo $assist_intent === 'assist' ? 'start helping others' : 'get the help you need'; ?>.</p>
                
                <?php if(isset($signup_error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($signup_error); ?></div>
                <?php endif; ?>

                <?php if (isset($error) && $show_signup_modal): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="user_role" value="<?php echo ($assist_intent === 'assist') ? 'assist' : 'student'; ?>">
                    
                    <div class="form-group">
                        <label>Username</label>
                        <div class="input-group">
                            <span class="input-icon">👤</span>
                            <input type="text" name="username" placeholder="Choose a username" required>
                        </div>
                    </div>


                    <div class="form-group">
                        <label>Email</label>
                        <div class="input-group">
                            <span class="input-icon">📧</span>
                            <input type="email" name="email" placeholder="Enter your email" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Academic Year</label>
                        <select class="form-select" name="academic_year" required>
                            <option value="">Select your academic year</option>
                            <option value="Freshman">Freshman (1st Year)</option>
                            <option value="Sophomore">Sophomore (2nd Year)</option>
                            <option value="Junior">Junior (3rd Year)</option>
                            <option value="Senior">Senior (4th Year)</option>
                            <option value="Graduate">Graduate</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Password (min 6 characters)</label>
                        <div class="input-group">
                            <span class="input-icon">🔒</span>
                            <input type="password" name="password" placeholder="Create a password" minlength="6" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Confirm Password</label>
                        <div class="input-group">
                            <span class="input-icon">🔒</span>
                            <input type="password" name="confirm_password" placeholder="Confirm your password" minlength="6" required>
                        </div>
                    </div>

                    <button type="submit" name="signup_submit" class="submit-btn">
                        <span class="btn-icon">📝</span> Create Account
                    </button>

                    <p class="login-link">
                        Already have an account? <a href="?action=login&intent=student">Sign in</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

</body>
</html>