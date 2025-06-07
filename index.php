<?php
session_start();
include 'functions.php';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'login') {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $assistIntent = $_POST['assist_intent'] ?? '';
        
        $error = processLogin($email, $password, $assistIntent);
    } elseif ($_POST['action'] === 'signup') {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $year = $_POST['year'];
        
        if (registerUser($username, $email, $password, $year)) {
            $success = "Account created successfully! Please log in.";
        } else {
            $error = "Username or email already exists.";
        }
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
    <title>Study Crew - Connect, Learn, Succeed</title>
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
                    <li><a href="courses.php">Courses</a></li>
                    <?php if(isLoggedIn()): ?>
                    <li><a href="assistant-dashboard.php">Assistant Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="#">About</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>
            </nav>
            <?php if(isLoggedIn()): ?>
                <div class="user-menu">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    <a href="?logout=1" class="sign-in-btn">LOGOUT</a>
                </div>
            <?php else: ?>
                <a href="#" class="sign-in-btn" onclick="openModal('loginModal')">SIGN IN</a>
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
                <h1>Connect, Learn, <span class="highlight">Succeed</span></h1>
                <p>Join Study Crew and connect with peer tutors who can help you excel in your courses. Get personalized assistance or share your knowledge with others.</p>
                <?php if(isLoggedIn()): ?>
                    <a href="courses.php" class="get-started-btn">
                        <span>🚀</span> Browse Courses
                    </a>
                <?php else: ?>
                    <a href="#" class="get-started-btn" onclick="openModal('signupModal')">
                        <span>🚀</span> Get Started
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Main Content Section -->
    <section class="main-content">
        <div class="container">
            <h2>How Study Crew Works</h2>
            <h3>Choose your path to academic success</h3>
            <p class="description">
                Whether you need help with challenging courses or want to share your expertise, 
                Study Crew connects you with the right people at the right time.
            </p>

            <div class="cards">
                <div class="card">
                    <div class="card-icon">🎯</div>
                    <h3>Get Assistance</h3>
                    <p>Connect with experienced peer tutors who have mastered the courses you're struggling with. Get personalized help and guidance.</p>
                    <?php if(isLoggedIn()): ?>
                        <a href="courses.php" class="assistance-btn">I need assistance</a>
                    <?php else: ?>
                        <a href="#" class="assistance-btn" onclick="openModal('loginModal', 'get')">I need assistance</a>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <div class="card-icon">🔗</div>
                    <h3>Assist Others</h3>
                    <p>Share your knowledge, reinforce your understanding, and help fellow students succeed. Become a tutor and make a positive impact on your academic community.</p>
                    <?php if(isLoggedIn()): ?>
                        <a href="assistant-dashboard.php" class="assist-btn">I'm here to assist others</a>
                    <?php else: ?>
                        <a href="#" class="assist-btn" onclick="openModal('loginModal', 'assist')">I'm here to assist others</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Section -->
    <section class="why-choose">
        <div class="container">
            <h2>Why Choose Study Crew?</h2>
            <p class="subtitle">We make peer-to-peer learning simple, effective, and accessible for everyone</p>

            <div class="features">
                <div class="feature">
                    <div class="feature-icon blue">👥</div>
                    <h3>Peer-to-Peer Learning</h3>
                    <p>Learn from students who have recently mastered the same material you're studying. Get insights and tips that textbooks can't provide.</p>
                </div>

                <div class="feature">
                    <div class="feature-icon green">⚡</div>
                    <h3>Quick Connections</h3>
                    <p>Find the right tutor for your needs in minutes. Our platform makes it easy to connect with available peer tutors instantly.</p>
                </div>

                <div class="feature">
                    <div class="feature-icon purple">🎓</div>
                    <h3>Academic Excellence</h3>
                    <p>Improve your grades and understanding while building lasting study relationships. Success is better when shared.</p>
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
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('loginModal')">&times;</span>
            <div class="modal-header">
                <div class="modal-logo">📚 STUDY CREW</div>
                <p class="modal-subtitle">Connect, Learn, Succeed</p>
            </div>
            <div class="modal-body">
                <h2>Welcome Back!</h2>
                
                <?php if(isset($error) && $_POST['action'] === 'login'): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="action" value="login">
                    <input type="hidden" name="assist_intent" id="assistIntent" value="">
                    
                    <div class="form-group">
                        <label for="loginEmail">Email or Username</label>
                        <div class="input-group">
                            <span class="input-icon">👤</span>
                            <input type="text" id="loginEmail" name="email" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="loginPassword">Password</label>
                        <div class="input-group">
                            <span class="input-icon">🔒</span>
                            <input type="password" id="loginPassword" name="password" required>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox"> Remember me
                        </label>
                        <a href="#" class="forgot-password">Forgot Password?</a>
                    </div>

                    <button type="submit" class="modal-btn login-btn">
                        <span>🚀</span> Sign In
                    </button>
                </form>

                <p class="switch-form">
                    Don't have an account? <a href="#" onclick="switchModal('loginModal', 'signupModal')">Sign up here</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Signup Modal -->
    <div id="signupModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('signupModal')">&times;</span>
            <div class="modal-header">
                <div class="modal-logo">📚 STUDY CREW</div>
                <p class="modal-subtitle">Connect, Learn, Succeed</p>
            </div>
            <div class="modal-body">
                <h2>Join Study Crew</h2>
                
                <?php if(isset($error) && $_POST['action'] === 'signup'): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if(isset($success)): ?>
                    <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="action" value="signup">
                    
                    <div class="form-group">
                        <label for="signupUsername">Username</label>
                        <div class="input-group">
                            <span class="input-icon">👤</span>
                            <input type="text" id="signupUsername" name="username" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="signupEmail">Email</label>
                        <div class="input-group">
                            <span class="input-icon">📧</span>
                            <input type="email" id="signupEmail" name="email" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="signupPassword">Password</label>
                        <div class="input-group">
                            <span class="input-icon">🔒</span>
                            <input type="password" id="signupPassword" name="password" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="academicYear">Academic Year</label>
                        <div class="input-group">
                            <span class="input-icon">🎓</span>
                            <select id="academicYear" name="year" required>
                                <option value="">Select your year</option>
                                <option value="Freshman">Freshman</option>
                                <option value="Sophomore">Sophomore</option>
                                <option value="Junior">Junior</option>
                                <option value="Senior">Senior</option>
                                <option value="Graduate">Graduate</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="modal-btn signup-btn">
                        <span>✨</span> Create Account
                    </button>
                </form>

                <p class="switch-form">
                    Already have an account? <a href="#" onclick="switchModal('signupModal', 'loginModal')">Sign in here</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function openModal(modalId, intent = '') {
            document.getElementById(modalId).style.display = 'block';
            if (intent && modalId === 'loginModal') {
                document.getElementById('assistIntent').value = intent;
            }
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function switchModal(currentModal, targetModal) {
            closeModal(currentModal);
            openModal(targetModal);
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }

        // Auto-open modals based on URL parameters
        <?php if(isset($_GET['action']) && $_GET['action'] === 'login'): ?>
            openModal('loginModal', '<?php echo isset($_GET['intent']) ? $_GET['intent'] : ''; ?>');
        <?php elseif(isset($_GET['action']) && $_GET['action'] === 'signup'): ?>
            openModal('signupModal');
        <?php endif; ?>

        // Show signup modal after successful account creation
        <?php if(isset($success)): ?>
            setTimeout(() => {
                closeModal('signupModal');
                openModal('loginModal');
            }, 2000);
        <?php endif; ?>
    </script>
</body>
</html>
