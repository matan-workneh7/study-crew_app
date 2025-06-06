<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Crew - Your Campus Connection</title>
    <link rel="stylesheet" href="style.css">
    <?php 
    session_start();
    include 'functions.php'; 
    
    // Handle login form submission
    if(isset($_POST['login_submit'])) {
        $email = $_POST['login_email'];
        $password = $_POST['login_password'];
        
        if(loginUser($email, $password)) {
            $login_success = true;
        } else {
            $login_error = "Invalid email or password";
        }
    }
    
    // Handle signup form submission
    if(isset($_POST['signup_submit'])) {
        $username = $_POST['signup_username'];
        $email = $_POST['signup_email'];
        $year = $_POST['signup_year'];
        $password = $_POST['signup_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if($password === $confirm_password) {
            if(registerUser($username, $email, $password, $year)) {
                $signup_success = true;
            } else {
                $signup_error = "Registration failed. Email may already exist.";
            }
        } else {
            $signup_error = "Passwords do not match";
        }
    }
    ?>
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
                    <li><a href="#">Home</a></li>
                    <li><a href="#">Courses</a></li>
                    <li><a href="#">About</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>
            </nav>
            <?php if(isLoggedIn()): ?>
                <button class="sign-in-btn" onclick="logout()">LOGOUT</button>
            <?php else: ?>
                <button class="sign-in-btn" onclick="openLoginModal()">SIGN IN</button>
            <?php endif; ?>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Welcome to <span class="highlight">Study Crew</span>!</h1>
                <p>Your campus connection for academic support.</p>
                <?php if(isLoggedIn()): ?>
                    <button class="get-started-btn">Continue Learning <span class="arrow">→</span></button>
                <?php else: ?>
                    <button class="get-started-btn" onclick="openSignupModal()">Get Started <span class="arrow">→</span></button>
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
                        <button class="assist-btn">I'm here to assist others</button>
                    <?php else: ?>
                        <button class="assist-btn" onclick="openLoginModal()">I'm here to assist others</button>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <div class="card-icon">💡</div>
                    <h3>Get Assistance</h3>
                    <p>Find knowledgeable peers to help you understand challenging concepts, prepare for exams, and improve your grades.</p>
                    <?php if(isLoggedIn()): ?>
                        <button class="assistance-btn">I'm looking for assistance</button>
                    <?php else: ?>
                        <button class="assistance-btn" onclick="openLoginModal()">I'm looking for assistance</button>
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
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeLoginModal()">&times;</span>
            <div class="modal-header">
                <div class="modal-logo">
                    <span class="book-icon">📚</span>STUDY CREW
                </div>
            </div>
            <div class="modal-body">
                <h2>Welcome Back!</h2>
                <p class="modal-subtitle">Sign in to continue your learning journey.</p>
                
                <?php if(isset($login_error)): ?>
                    <div class="error-message"><?php echo $login_error; ?></div>
                <?php endif; ?>
                
                <?php if(isset($login_success)): ?>
                    <div class="success-message">Login successful! Redirecting...</div>
                    <script>
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    </script>
                <?php endif; ?>

                <form method="POST" action="">
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

                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember_me">
                            <span class="checkmark"></span>
                            Remember Me
                        </label>
                        <a href="#" class="forgot-password">Forgot Password?</a>
                    </div>

                    <button type="submit" name="login_submit" class="modal-btn login-btn">
                        <span class="btn-icon">→</span> LOG IN
                    </button>
                </form>

                <p class="switch-form">
                    Don't have an account? <a href="#" onclick="switchToSignup()">Create Account</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Signup Modal -->
    <div id="signupModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeSignupModal()">&times;</span>
            <div class="modal-header">
                <div class="modal-logo">
                    <span class="book-icon">📚</span>STUDY CREW
                </div>
                <p class="modal-subtitle">Join our academic support community!</p>
            </div>
            <div class="modal-body">
                <h2>Create Your Account</h2>
                
                <?php if(isset($signup_error)): ?>
                    <div class="error-message"><?php echo $signup_error; ?></div>
                <?php endif; ?>
                
                <?php if(isset($signup_success)): ?>
                    <div class="success-message">Account created successfully! You can now log in.</div>
                    <script>
                        setTimeout(function() {
                            closeSignupModal();
                            openLoginModal();
                        }, 2000);
                    </script>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label>Username</label>
                        <div class="input-group">
                            <span class="input-icon">👤</span>
                            <input type="text" name="signup_username" placeholder="Enter your username" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <div class="input-group">
                            <span class="input-icon">📧</span>
                            <input type="email" name="signup_email" placeholder="test@example.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Year</label>
                        <div class="input-group">
                            <span class="input-icon">📅</span>
                            <select name="signup_year" required>
                                <option value="">Select your academic year</option>
                                <option value="Freshman">Freshman</option>
                                <option value="Sophomore">Sophomore</option>
                                <option value="Junior">Junior</option>
                                <option value="Senior">Senior</option>
                                <option value="Graduate">Graduate</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <div class="input-group">
                            <span class="input-icon">🔒</span>
                            <input type="password" name="signup_password" placeholder="Enter your password" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Confirm Password</label>
                        <div class="input-group">
                            <span class="input-icon">🔒</span>
                            <input type="password" name="confirm_password" placeholder="Enter your password again" required>
                        </div>
                    </div>

                    <button type="submit" name="signup_submit" class="modal-btn signup-btn">
                        <span class="btn-icon">⭐</span> Sign Up
                    </button>
                </form>

                <p class="switch-form">
                    Already have an account? <a href="#" onclick="switchToLogin()">Back to login</a>
                </p>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>