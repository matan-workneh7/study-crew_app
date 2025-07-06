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
            // If assistant, login automatically and redirect to assistant dashboard
            if ($user_role === 'assist') {
                $loginResult = processLogin($email, $password, 'assist');
                if ($loginResult === true) {
                    header("Location: assistant-dashboard.php");
                    exit();
                } else {
                    // fallback: show login modal with intent=assist
                    $_SESSION['success_message'] = 'Registration successful! Please log in to continue.';
                    header("Location: index.php?action=login&intent=assist");
                    exit();
                }
            } elseif ($user_role === 'student' || $user_role === 'get') {
                $loginResult = processLogin($email, $password, 'get');
                if ($loginResult === true) {
                    header("Location: courses.php");
                    exit();
                } else {
                    // fallback: show login modal with intent=get
                    $_SESSION['success_message'] = 'Registration successful! Please log in to continue.';
                    header("Location: index.php?action=login&intent=get");
                    exit();
                }
            } else {
                // fallback for any other roles
                $_SESSION['success_message'] = 'Registration successful! Please log in to continue.';
                header("Location: index.php?action=login");
                exit();
            }
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
                    <?php if(isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'assist'): ?>
                        <li><a href="assistant-dashboard.php">Assistant Dashboard</a></li>
                    <?php elseif(isset($_SESSION['user_id'])): ?>
                        <li><a href="courses.php">Courses</a></li>
                    <?php endif; ?>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                    
                </ul>
            </nav>
            <?php if(isLoggedIn()): ?>
                <div class="user-menu">
                    <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="?logout=1" class="sign-in-btn">LOGOUT</a>
                </div>
            <?php else: ?>
                
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
                <?php if(isset($_SESSION['user_id'])): ?>
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
            <?php if(!isset($_SESSION['user_id'])): ?>
                <h2>Struggling with Coursework?</h2>
                <h3>Connect with Peer Tutors or Become One</h3>
                <p class="description">
                    Study Crew bridges the gap between students seeking academic support and those ready to help.
                    Get personalized 1-on-1 assistance in challenging subjects or share your expertise with fellow students.
                    Our platform makes it easy to find the perfect study match based on courses, schedules, and learning styles.
                </p>

                <div class="cards">
                    <div class="card">
                        <div class="card-icon">🔗</div>
                        <h3>Become a Tutor</h3>
                        <p>Reinforce your knowledge by teaching others. Set your own schedule, earn recognition, and make a real impact on your peers' academic journey. Perfect for students who excel in specific subjects.</p>
                        <a href="?action=login&intent=assist" class="assist-btn">I'm here to assist others</a>
                    </div>

                    <div class="card">
                        <div class="card-icon">💡</div>
                        <h3>Find a Tutor</h3>
                        <p>Get personalized help from top students who've aced the courses you're taking. Schedule sessions at your convenience and receive targeted support for your specific challenges.</p>
                        <a href="?action=login&intent=student" class="assistance-btn">I'm looking for assistance</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="welcome-message">
                    <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
                    <p class="description">
                        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'assist'): ?>
Your expertise is in high demand! You currently have <strong>3 pending assistance requests</strong> from students in your courses. Your dashboard is ready with your schedule, upcoming sessions, and student messages. Don't forget to update your availability for next week!
                        <?php else: ?>
You're making great progress in your studies! There are <strong>5 new tutors available</strong> in your courses this week. Check out their profiles and schedule a session to get the help you need. Your next study session could be the breakthrough you've been waiting for!
                        <?php endif; ?>
                    </p>
                    <a href="<?php echo (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'assist') ? 'assistant-dashboard.php' : 'courses.php'; ?>" class="get-started-btn">
                        <?php echo (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'assist') ? 'Go to Dashboard' : 'Browse Courses'; ?>
                        <span class="arrow">→</span>
                    </a>
                </div>
            <?php endif; ?>
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
            <button type="button" onclick="window.location.href='index.php'" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Home</button>
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
            <button type="button" onclick="window.location.href='index.php'" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Home</button>
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
            <button type="button" onclick="window.location.href='index.php'" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Home</button>
        </div>
    </div>
    <?php endif; ?>

</body>
</html>