<?php
require_once 'session.php';
require_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Crew - Academic Support Platform</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="modal-styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --white: #ffffff;
            --light-bg: #f9f9ff;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-bg);
            color: var(--text-dark);
            line-height: 1.7;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        main {
            padding: 4rem 0;
            position: relative;
            overflow: hidden;
        }
        
        main::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 300px;
            height: 300px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 50%;
            opacity: 0.1;
            z-index: -1;
        }
        
        main::after {
            content: '';
            position: absolute;
            bottom: -150px;
            left: -150px;
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 50%;
            opacity: 0.1;
            z-index: -1;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 4rem;
            position: relative;
        }
        
        .page-header h1 {
            font-size: 3rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            background-clip: text;
        }
        
        .page-header p {
            font-size: 1.2rem;
            color: white;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .section {
            background-color: var(--white);
            margin: 3rem 0;
            padding: 3rem;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .section:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        }
        
        .section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(to bottom, var(--primary), var(--secondary));
        }
        
        .section:nth-child(even) {
            direction: rtl;
        }
        
        .section:nth-child(even) > * {
            direction: ltr;
        }
        
        .section-content {
            padding: 1rem;
        }
        
        .section h2 {
            color: var(--primary);
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
            position: relative;
            display: inline-block;
        }
        
        .section h2::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            border-radius: 3px;
        }
        
        .section p {
            color: var(--text-light);
            font-size: 1.05rem;
            line-height: 1.8;
            margin-bottom: 1.5rem;
        }
        
        .illustration {
            width: 100%;
            height: 300px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .illustration::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            opacity: 0.9;
        }
        
        .illustration::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 L0,100 Z" fill="none" stroke="white" stroke-width="2" stroke-dasharray="5,5" opacity="0.2"/></svg>');
            background-size: 20px 20px;
            opacity: 0.3;
        }
        
        .icon {
            font-size: 5rem;
            z-index: 2;
            color: var(--white);
            text-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }
        
        .illustration:hover .icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 1;
            overflow: hidden;
        }
        
        .shape {
            position: absolute;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            animation: float 15s infinite linear;
        }
        
        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            left: 70%;
            animation-delay: 2s;
        }
        
        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            top: 80%;
            left: 30%;
            animation-delay: 4s;
        }
        
        .shape:nth-child(4) {
            width: 100px;
            height: 100px;
            top: 30%;
            left: 50%;
            animation-delay: 6s;
        }
        
        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
            }
            100% {
                transform: translateY(0) rotate(360deg);
            }
        }
        
        @media (max-width: 992px) {
            .section {
                grid-template-columns: 1fr;
                gap: 2rem;
                padding: 2rem;
            }
            
            .section:nth-child(even) {
                direction: ltr;
            }
            
            .illustration {
                height: 250px;
                order: -1;
            }
            
            .page-header h1 {
                font-size: 2.5rem;
            }
        }
        
        @media (max-width: 768px) {
            main {
                padding: 3rem 0;
            }
            
            .page-header h1 {
                font-size: 2rem;
            }
            
            .page-header p {
                font-size: 1rem;
            }
            
            .section h2 {
                font-size: 1.5rem;
            }
            
            .section p {
                font-size: 1rem;
            }
            
            .icon {
                font-size: 4rem;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 0 15px;
            }
            
            .section {
                padding: 1.5rem;
                margin: 2rem 0;
            }
            
            .illustration {
                height: 200px;
            }
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
                    <?php if(isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'assist'): ?>
                        <li><a href="assistant-dashboard.php">Assistant Dashboard</a></li>
                    <?php elseif(isLoggedIn()): ?>
                        <li><a href="courses.php">Courses</a></li>
                    <?php endif; ?>
                    <li><a href="about.php" class="active">About</a></li>
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

    <main class="container">
        <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Home</a>
        <div class="page-header">
            <h1>About Study Crew</h1>
            <p>Your campus connection for academic support - built by students, for students</p>
        </div>

        <section class="section">
            <div class="section-content">
                <h2>Our Story</h2>
                <p>Study Crew was born from a simple idea: learning is better together. We noticed students struggling alone with difficult concepts while others who had mastered those subjects had no way to share their knowledge. Study Crew bridges this gap, creating a vibrant academic community where students help each other succeed.</p>
                <p>What started as a small project among friends has grown into a platform serving hundreds of students across our campus.</p>
            </div>
            <div class="illustration">
                <div class="floating-shapes">
                    <div class="shape"></div>
                    <div class="shape"></div>
                    <div class="shape"></div>
                    <div class="shape"></div>
                </div>
                <span class="icon">📖</span>
            </div>
        </section>

        <section class="section">
            <div class="section-content">
                <h2>Our Mission</h2>
                <p>We believe learning is most effective when it's shared. Study Crew was created to make it easy for students to connect - whether they need academic help or want to offer their support in subjects they're confident in.</p>
                <p>Our mission is to foster a culture of collaborative learning where every student has access to peer support and the opportunity to reinforce their own knowledge by helping others.</p>
            </div>
            <div class="illustration">
                <div class="floating-shapes">
                    <div class="shape"></div>
                    <div class="shape"></div>
                    <div class="shape"></div>
                    <div class="shape"></div>
                </div>
                <span class="icon">💡</span>
            </div>
        </section>

        <section class="section">
            <div class="section-content">
                <h2>Who Can Join?</h2>
                <p>All students of our college are welcome. Whether you're just starting your degree or polishing your thesis, need help with a tricky concept in Math, Programming languages, or anything in between - Study Crew is for you.</p>
                <p>We welcome students from all departments and academic levels. The only requirements are a willingness to learn and a commitment to maintaining a positive, supportive community.</p>
            </div>
            <div class="illustration">
                <div class="floating-shapes">
                    <div class="shape"></div>
                    <div class="shape"></div>
                    <div class="shape"></div>
                    <div class="shape"></div>
                </div>
                <span class="icon">👥</span>
            </div>
        </section>

        <section class="section">
            <div class="section-content">
                <h2>How It Works</h2>
                <p>Students can sign up to join online group, after profile creation for tutor or for peer help, you can browse available student assistants by subject or even post a help request that fits your needs.</p>
                <p>If you're offering help, you can list the subjects you're comfortable assisting with and your preferred tutoring time. Once connected, you can arrange study sessions that work for both of you.</p>
            </div>
            <div class="illustration">
                <div class="floating-shapes">
                    <div class="shape"></div>
                    <div class="shape"></div>
                    <div class="shape"></div>
                    <div class="shape"></div>
                </div>
                <span class="icon">⚙️</span>
            </div>
        </section>

        <section class="section">
            <div class="section-content">
                <h2>Why Peer Support?</h2>
                <p>Students often explain things to each other in ways that are easier to understand. Peer-to-peer learning helps solidify your own grasp of the material, fosters teamwork, and creates a stronger academic community.</p>
                <p>Research shows that students who participate in peer learning programs demonstrate improved academic performance, increased retention of material, and enhanced communication skills.</p>
            </div>
            <div class="illustration">
                <div class="floating-shapes">
                    <div class="shape"></div>
                    <div class="shape"></div>
                    <div class="shape"></div>
                    <div class="shape"></div>
                </div>
                <span class="icon">🤝</span>
            </div>
        </section>

        <section class="section">
            <div class="section-content">
                <h2>Privacy and Safety</h2>
                <p>We value your trust. Your contact information is only shared to help facilitate study connections and is never made public. All users are expected to engage respectfully and use the platform strictly for academic purposes.</p>
                <p>Our team monitors the platform to ensure a safe, productive environment for all users. Any violations of our community guidelines are taken seriously and addressed promptly.</p>
            </div>
            <div class="illustration">
                <div class="floating-shapes">
                    <div class="shape"></div>
                    <div class="shape"></div>
                    <div class="shape"></div>
                    <div class="shape"></div>
                </div>
                <span class="icon">🛡️</span>
            </div>
        </section>

        <section class="section">
            <div class="section-content">
                <h2>Powered by Students</h2>
                <p>Study Crew is proudly built and maintained by a group of students who want to make for better academic support on campus. Let's grow together.</p>
                <p>Our development team is always working to improve the platform based on user feedback. Have an idea to make Study Crew better? We'd love to hear from you!</p>
            </div>
            <div class="illustration">
                <div class="floating-shapes">
                    <div class="shape"></div>
                    <div class="shape"></div>
                    <div class="shape"></div>
                    <div class="shape"></div>
                </div>
                <span class="icon">🎓</span>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2025 Study Crew. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>