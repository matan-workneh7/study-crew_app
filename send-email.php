<?php
header('Content-Type: application/json');
require_once 'functions.php';

// Function to send JSON response and exit
function sendJsonResponse($success, $message = '', $debug = []) {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    // Add debug info if in development
    if (!empty($debug)) {
        $response['debug'] = $debug;
    }
    
    $json = json_encode($response);
    if ($json === false) {
        // Fallback response if json_encode fails
        $response = [
            'success' => false,
            'message' => 'Error encoding response: ' . json_last_error_msg(),
            'debug' => []
        ];
        echo json_encode($response);
    } else {
        echo $json;
    }
    exit();
}

try {
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // Check if required fields are set
    if (!isset($_POST['tutor_email'], $_POST['subject'], $_POST['message'])) {
        throw new Exception('Missing required fields.');
    }
    
    // Sanitize and validate input
    $tutorEmail = filter_var($_POST['tutor_email'], FILTER_VALIDATE_EMAIL);
    // Get selected courses (multi-select)
    $selectedCourses = [];
    if (!empty($_POST['courses']) && is_array($_POST['courses'])) {
        foreach ($_POST['courses'] as $courseId => $courseData) {
            if (is_array($courseData) && isset($courseData['id'], $courseData['code'], $courseData['name'])) {
                $selectedCourses[] = [
                    'id' => filter_var($courseData['id'], FILTER_SANITIZE_STRING),
                    'code' => filter_var($courseData['code'], FILTER_SANITIZE_STRING),
                    'name' => filter_var($courseData['name'], FILTER_SANITIZE_STRING)
                ];
            }
        }
    }
    
    // Get sender details from form data
    $fromName = trim($_POST['sender_name'] ?? 'Study Crew User');
    $fromEmail = trim($_POST['sender_email'] ?? '');
    
    // Debug info
    $debug = [
        'sender_name' => $fromName,
        'sender_email' => $fromEmail,
        'tutor_email' => $tutorEmail,
        'has_course_info' => !empty($selectedCourses)
    ];
    
    // Sanitize subject and message
    $subject = trim(filter_var($_POST['subject'] ?? '', FILTER_SANITIZE_STRING));
    $message = trim($_POST['message'] ?? '');

    // Validate required fields
    if (!$tutorEmail) {
        throw new Exception('Invalid tutor email address.');
    }
    
    if (empty($subject) || empty($message)) {
        throw new Exception('Subject and message are required.');
    }
    
    if (empty($fromEmail) || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('A valid sender email is required.');
    }

    // Prepare email content
    $email_subject = "[Study Crew] " . trim(filter_var($_POST['subject'], FILTER_SANITIZE_STRING));
    
    // Build course list HTML
    $courseListHtml = '';
    if (!empty($selectedCourses)) {
        $courseListHtml .= "<div class='course-info'>";
        $courseListHtml .= "<h3>Selected Courses</h3>";
        $courseListHtml .= "<ul style='padding-left: 20px; margin: 10px 0;'>";
        
        foreach ($selectedCourses as $course) {
            $courseCode = htmlspecialchars($course['code']);
            $courseName = htmlspecialchars($course['name']);
            $courseListHtml .= "<li><strong>$courseCode</strong> - $courseName</li>";
        }
        
        $courseListHtml .= "</ul></div>";
    }
    
    $email_body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #4a6fa5; color: white; padding: 15px; text-align: center; border-radius: 5px 5px 0 0; }
            .content { padding: 20px; background-color: #f9f9f9; border: 1px solid #ddd; border-top: none; border-radius: 0 0 5px 5px; }
            .footer { margin-top: 20px; font-size: 0.9em; color: #777; text-align: center; }
            .button { display: inline-block; padding: 10px 20px; background-color: #4a6fa5; color: white; text-decoration: none; border-radius: 4px; margin-top: 15px; }
            .course-info { background-color: #e9f0f9; padding: 15px; border-radius: 4px; margin: 15px 0; }
            .course-info h3 { margin-top: 0; color: #2c3e50; }
            .course-info ul { margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h2>New Message from $fromName</h2>
        </div>
        <div class='content'>
            <p><strong>Message:</strong></p>
            <p>" . nl2br(htmlspecialchars($_POST['message'])) . "</p>
            
            $courseListHtml
            
            <div style='margin-top: 20px;'>
                <p><strong>Student Contact Information:</strong></p>
                <p>Name: $fromName</p>
                <p>Email: $fromEmail</p>
            </div>
            
            <p style='margin-top: 20px;'>
                <a href='mailto:$fromEmail' class='button'>Reply to Student</a>
            </p>
        </div>
        <div class='footer'>
            <p>This message was sent via Study Crew platform. Please do not reply to this email directly.</p>
        </div>
    </body>
    </html>
    ";

    // Set email headers
    $headers = [
        'From: ' . $fromName . ' <' . $fromEmail . '>',
        'Reply-To: ' . $fromEmail,
        'X-Mailer: PHP/' . phpversion(),
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8'
    ];

    // For development, save to file instead of sending
    $logDir = __DIR__ . '/logs';
    if (!file_exists($logDir)) {
        if (!mkdir($logDir, 0777, true)) {
            throw new Exception('Failed to create logs directory.');
        }
    }
    
    $logFile = $logDir . '/email_' . date('Y-m-d_His') . '.log';
    $logContent = "To: $tutorEmail\n";
    $logContent .= "Subject: Study Crew: " . $subject . "\n";
    $logContent .= "Headers: " . print_r($headers, true) . "\n\n";
    $logContent .= $email_body;
    
    if (!file_put_contents($logFile, $logContent)) {
        throw new Exception('Failed to write email to log file.');
    }
    
    // In production, you would use:
    // $mailSent = mail($to, $subject, $message, $headers);
    $mailSent = true; // For development

    if ($mailSent) {
        sendJsonResponse(true, 'Your message has been sent successfully!', [
            'log_file' => $logFile,
            'email_sent' => $mailSent
        ]);
    } else {
        throw new Exception('Failed to send email. Please try again later.');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Return JSON response
echo json_encode($response);
?>
