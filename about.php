<?php
require_once 'includes/session.php';
include 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Crew - Academic Support Platform</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        /* Main Content Styles */
        main {
            padding: 2rem 0;
        }
        .section {
            background-color: white;
            margin: 2rem 0;
            padding: 3rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
        }
        .section:nth-child(even) {
            direction: rtl;
        }
        .section:nth-child(even) > * {
            direction: ltr;
        }
        .section h2 {
            color: #2c5aa0;
            font-size: 1.8rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        .section p {
            color: #666;
            font-size: 1rem;
            line-height: 1.7;
        }
        .illustration {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            position: relative;
            overflow: hidden;
        }
        .illustration::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
        }
        /* Specific illustration styles */
        .study-illustration {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 50%, #fecfef 100%);
        }
        .mission-illustration {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        }
        .join-illustration {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
        }
        .works-illustration {
            background: linear-gradient(135deg, #e0c3fc 0%, #9bb5ff 100%);
        }
        .support-illustration {
            background: linear-gradient(135deg, #ffd89b 0%, #19547b 100%);
        }
        .safety-illustration {
            background: linear-gradient(135deg, #89f7fe 0%, #66a6ff 100%);
        }
        .students-illustration {
            background: linear-gradient(135deg, #fdbb2d 0%, #22c1c3 100%);
        }
        /* Icon styles */
        .icon {
            font-size: 3rem;
            z-index: 1;
            position: relative;
        }
        /* Responsive Design */
        @media (max-width: 768px) {
            .section {
                grid-template-columns: 1fr;
                gap: 2rem;
                padding: 2rem;
            }
            .section:nth-child(even) {
                direction: ltr;
            }
            .illustration {
                height: 150px;
            }
            .section h2 {
                font-size: 1.5rem;
            }
        }
        @media (max-width: 480px) {
            .container {
                padding: 0 15px;
            }
            .section {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <section class="section">
            <div>
                <h2>About Study Crew</h2>
                <p>Study Crew is your campus connection for academic support - built by students, for students. Whether you're looking to help with a subject you're ready to assist your peers, Study Crew provides a trusted platform to share knowledge, grow academically, and support one another through collaboration.</p>
            </div>
            <div class="illustration study-illustration">
                <span class="icon">📖</span>
            </div>
        </section>

        <section class="section">
            <div>
                <h2>Our Mission</h2>
                <p>We believe learning is most effective when it's shared. Study Crew was created to make it easy for students to connect - whether they need academic help or want to offer their support in subjects they're confident in.</p>
            </div>
            <div class="illustration mission-illustration">
                <span class="icon">💡</span>
            </div>
        </section>

        <section class="section">
            <div>
                <h2>Who Can Join?</h2>
                <p>All students of our college are welcome. Whether you're just starting your degree or polishing your thesis, need help with a tricky concept in Math, Programming languages, or anything in between - Study Crew is for you.</p>
            </div>
            <div class="illustration join-illustration">
                <span class="icon">👥</span>
            </div>
        </section>

        <section class="section">
            <div>
                <h2>How It Works</h2>
                <p>Students can sign up to join online group, after profile creation for tutor or for peer help, you can browse available student assistants by subject or even post a help request that fits your needs. If you're offering help, you can list the subjects you're comfortable assisting with and your preferred tutoring time. Once connected, you can arrange study sessions that work for both of you.</p>
            </div>
            <div class="illustration works-illustration">
                <span class="icon">⚙️</span>
            </div>
        </section>

        <section class="section">
            <div>
                <h2>Why Peer Support?</h2>
                <p>Students often explain things to each other in ways that are easier to understand. Peer-to-peer learning helps solidify your own grasp of the material, fosters teamwork, and creates a stronger academic community.</p>
            </div>
            <div class="illustration support-illustration">
                <span class="icon">🤝</span>
            </div>
        </section>

        <section class="section">
            <div>
                <h2>Privacy and Safety</h2>
                <p>We value your trust. Your contact information is only shared to help facilitate study connections and is never made public. All users are expected to engage respectfully and use the platform strictly for academic purposes.</p>
            </div>
            <div class="illustration safety-illustration">
                <span class="icon">🛡️</span>
            </div>
        </section>

        <section class="section">
            <div>
                <h2>Powered by Students</h2>
                <p>Study Crew is proudly built and maintained by a group of students who want to make for better academic support on campus. Let's grow together.</p>
            </div>
            <div class="illustration students-illustration">
                <span class="icon">🎓</span>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

</body>
</html>