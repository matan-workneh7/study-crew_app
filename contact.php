<?php
require_once "session.php";
require_once "functions.php";
// Contact form handler
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if (!$name || !$email || !$message) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $to = 'info@bitscollege.edu.et'; // Change this to your desired recipient
        $subject = 'Study Crew Contact Form Submission';
        $body = "Name: $name\nEmail: $email\nMessage:\n$message";
        $headers = "From: $email\r\nReply-To: $email";
        if (mail($to, $subject, $body, $headers)) {
            $success = 'Thank you for contacting us! We will get back to you soon.';
            // Clear form fields after success
            $_POST = [];
        } else {
            $error = 'Sorry, there was a problem sending your message. Please try again later.';
        }
    }
}
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
                    <?php if(isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'assist'): ?>
                        <li><a href="assistant-dashboard.php">Assistant Dashboard</a></li>
                    <?php elseif(isLoggedIn()): ?>
                        <li><a href="courses.php">Courses</a></li>
                    <?php endif; ?>
                    <li><a href="#">About</a></li>
                    <li><a href="contact.php" class="active">Contact Us</a></li>
                </ul>
            </nav>
            <?php if(isLoggedIn()): ?>
                <a href="index.php?logout=1" class="sign-in-btn">LOGOUT</a>
            <?php else: ?>
                <a href="index.php?action=login" class="sign-in-btn">SIGN IN</a>
            <?php endif; ?>
        </div>
    </header>
    <main>
        <div class="container" style="margin-top: 40px; max-width: 1200px;">
            <h1>Contact Us</h1>
            <p>Welcome to the Study Crew Contact Page! Have questions, feedback, or need support? Reach out via the contact form or email us, and we’ll respond promptly. Thank you for choosing Study Crew!</p>
            <hr>
            <div class="contact-container">
                <div class="contact-info">
                    <h2>Contact Information</h2>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-content">
                            <h3>Address</h3>
                            <p>2QGW+8J6, Unnamed Road, Addis Ababa</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div class="info-content">
                            <h3>Phone</h3>
                            <p>+251 911 123 456</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-content">
                            <h3>Email</h3>
                            <p>info@bitscollege.edu.et</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="info-content">
                            <h3>Working Hours</h3>
                            <p>Monday - Friday: 8:00 - 17:00<br>Saturday: 9:00 - 13:00</p>
                        </div>
                    </div>
                </div>
                <div class="contact-form">
                    <h2>Send Message</h2>
                    <form method="POST" action="">
    <?php if(isset($error) && $error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if(isset($success) && $success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="message">Type your Message...</label>
                            <textarea id="message" name="message" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>
                        <button type="submit" class="send-btn" name="contact_submit">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
