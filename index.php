<?php
require_once 'session.php';
include 'functions.php';

// Handle login form submission
if(isset($_POST['login_submit'])) {
    $email = $_POST['login_email'];
    $password = $_POST['login_password'];
    $assistIntent = $_POST['assist_intent'] ?? '';
    
    if(empty($email) || empty($password)) {
        $login_error = "Please enter both email and password";
        $show_login_modal = true;
    } else {
        $loginResult = processLogin($email, $password, $assistIntent);
        if($loginResult !== true) {
            $login_error = $loginResult;
            $show_login_modal = true;
        }
    }
}

// Handle signup form submission
if(isset($_POST['signup_submit'])) {
    $username = $_POST['signup_username'];
    $email = $_POST['signup_email'];
    $year = $_POST['signup_year'];
    $password = $_POST['signup_password'];
    $confirm_password = $_POST['confirm_password'];
    $user_role = $_POST['user_role'] ?? '';
    
    if($password === $confirm_password) {
        if(registerUser($username, $email, $password, $year, $user_role)) {
            // Set success message in session
            $_SESSION['success_message'] = "Registration successful! Please log in to continue.";
            
            // Redirect to login with the selected role
            header("Location: index.php?action=login&intent=" . $user_role);
            exit();
        } else {
            $signup_error = "Registration failed. Email may already exist.";
            $show_signup_modal = true;
        }
    } else {
        $signup_error = "Passwords do not match";
        $show_signup_modal = true;
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
                    <?php if(isLoggedIn()): ?>
                        <?php if(isUserAssistant($_SESSION['user_id'])): ?>
                            <li><a href="assistant-dashboard.php">Assistant Dashboard</a></li>
                        <?php else: ?>
                            <li><a href="courses.php">Courses</a></li>
                        <?php endif; ?>
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
                        <a href="?action=login&intent=get" class="assistance-btn">I'm looking for assistance</a>
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

    <!-- Login Modal -->
    <?php if(isset($show_login_modal) && $show_login_modal): ?>
    <div class="modal" style="display: block;">
        <div class="modal-content">
            <a href="index.php" class="close">&times;</a>
            <div class="modal-header">
                <div class="modal-logo">
                    <span class="book-icon">📚</span>STUDY CREW
                </div>
            </div>
            <div class="modal-body">
                <h2><?php echo $assist_intent === 'assist' ? 'Assistant Login' : 'Student Login'; ?></h2>
                <p class="modal-subtitle">Sign in to <?php echo $assist_intent === 'assist' ? 'start helping others' : 'get the help you need'; ?>.</p>
                
                <?php if(isset($login_error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($login_error); ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <input type="hidden" name="assist_intent" value="<?php echo htmlspecialchars($assist_intent ?? ''); ?>">
                    
                    <div class="form-group">
                        <label>Username or Email</label>
                        <div class="input-group">
                            <span class="input-icon">👤</span>
                            <input type="text" name="login_email" placeholder="Enter your username or email" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <div class="input-group">
                            <span class="input-icon">🔒</span>
                            <input type="password" name="login_password" placeholder="Enter your password" required>
                        </div>
                    </div>

                    <button type="submit" name="login_submit" class="submit-btn">
                        <span class="btn-icon">🔑</span> Sign In
                    </button>

                    <p class="signup-link">
                        Don't have an account? <a href="?action=signup&intent=<?php echo htmlspecialchars($assist_intent ?? 'get'); ?>">Sign up</a>
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

                <form method="POST" action="">
                    <input type="hidden" name="user_role" value="<?php echo htmlspecialchars($assist_intent ?? 'get'); ?>">
                    
                    <div class="form-group">
                        <label>Username</label>
                        <div class="input-group">
                            <span class="input-icon">👤</span>
                            <input type="text" name="signup_username" placeholder="Choose a username" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <div class="input-group">
                            <span class="input-icon">📧</span>
                            <input type="email" name="signup_email" placeholder="Enter your email" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Academic Year</label>
                        <div class="input-group">
                            <span class="input-icon">🎓</span>
                            <input type="text" name="signup_year" placeholder="e.g., 3rd Year" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <div class="input-group">
                            <span class="input-icon">🔒</span>
                            <input type="password" name="signup_password" placeholder="Create a password" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Confirm Password</label>
                        <div class="input-group">
                            <span class="input-icon">🔒</span>
                            <input type="password" name="confirm_password" placeholder="Confirm your password" required>
                        </div>
                    </div>

                    <button type="submit" name="signup_submit" class="submit-btn">
                        <span class="btn-icon">📝</span> Create Account
                    </button>

                    <p class="login-link">
                        Already have an account? <a href="?action=login&intent=<?php echo htmlspecialchars($assist_intent ?? 'get'); ?>">Sign in</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>