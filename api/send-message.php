<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to log debug information
function log_debug($message, $data = null) {
    $log = "[DEBUG] [" . date('Y-m-d H:i:s') . "] " . $message;
    if ($data !== null) {
        $log .= "\n" . print_r($data, true);
    }
    error_log($log);
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../session.php';
require_once __DIR__ . '/../functions.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => ''
];

// Set JSON content type
header('Content-Type: application/json');

try {
    // Check if the request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method. Only POST is allowed.');
    }

    // Get form data
    $tutorId = isset($_POST['tutor_id']) ? (int)$_POST['tutor_id'] : 0;
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? 'New Message from Study Crew');
    $message = trim($_POST['message'] ?? '');
    $courseId = trim($_POST['course_id'] ?? '');
    $courseName = trim($_POST['course_name'] ?? '');

    // Validate required fields
    $required = [
        'tutor_id' => $tutorId,
        'name' => $name,
        'email' => $email,
        'message' => $message
    ];
    
    $missing = [];
    foreach ($required as $field => $value) {
        if (empty($value)) {
            $missing[] = str_replace('_', ' ', $field);
        }
    }
    
    if (!empty($missing)) {
        throw new Exception('Please fill in all required fields: ' . implode(', ', $missing));
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please enter a valid email address.');
    }
    
    // Get database connection
    $conn = getDbConnection();
    if (!$conn) {
        throw new Exception('Could not connect to the database.');
    }
    
    // Set PDO to throw exceptions on error
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Begin transaction
    $conn->beginTransaction();
    
    try {
        // 1. Get tutor information
        log_debug("Looking up tutor with ID: " . $tutorId);
        $sql = "SELECT id, email FROM users WHERE id = ?";
        log_debug("SQL Query: " . $sql);
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$tutorId]);
        $tutor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        log_debug("Query result: ", $tutor);
        
        if (!$tutor) {
            // Log all available users for debugging
            $allUsers = $conn->query("SELECT id, email, roles FROM users")->fetchAll(PDO::FETCH_ASSOC);
            log_debug("All users in database: ", $allUsers);
            
            throw new Exception('Recipient not found. Please check the user ID.');
        }
        
        // 2. Get sender ID (NULL for guests)
        $senderId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        
        // 3. If course ID is provided but no course name, try to get it
        if ($courseId && !$courseName) {
            $stmt = $conn->prepare("SELECT name FROM courses WHERE id = ?");
            $stmt->execute([$courseId]);
            $course = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($course) {
                $courseName = $course['name'];
            }
        }
        
        // 4. Insert the message
        $stmt = $conn->prepare("
            INSERT INTO messages (
                sender_id, sender_name, sender_email,
                tutor_id, tutor_email,
                subject, message,
                course_id, course_name
            ) VALUES (
                :sender_id, :sender_name, :sender_email,
                :tutor_id, :tutor_email,
                :subject, :message,
                :course_id, :course_name
            )
        ");
        
        $stmt->execute([
            ':sender_id' => $senderId,
            ':sender_name' => $name,
            ':sender_email' => $email,
            ':tutor_id' => $tutorId,
            ':tutor_email' => $tutor['email'],
            ':subject' => $subject,
            ':message' => $message,
            ':course_id' => $courseId ?: null,
            ':course_name' => $courseName ?: null
        ]);
        
        $messageId = $conn->lastInsertId();
        
        // 5. Prepare and send email
        $emailSubject = "New Message: " . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
        $emailBody = "You have received a new message from:\n";
        $emailBody .= "Name: " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "\n";
        $emailBody .= "Email: " . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . "\n";
        
        if ($courseName) {
            $emailBody .= "\nCourse: " . htmlspecialchars($courseName, ENT_QUOTES, 'UTF-8') . "\n";
        }
        
        $emailBody .= "\nMessage:\n" . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . "\n\n";
        $emailBody .= "---\n";
        $emailBody .= "This message was sent through the Study Crew platform.\n";
        
        // Set a default 'from' email if none is provided
        $fromEmail = !empty($email) ? $email : 'noreply@studycrew.com';
        
        $headers = [
            'From: ' . $fromEmail,
            'Reply-To: ' . $fromEmail,
            'X-Mailer: PHP/' . phpversion(),
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8'
        ];
        
        // Log email details before sending
        log_debug("Attempting to send email", [
            'to' => $tutor['email'],
            'subject' => $emailSubject,
            'headers' => $headers,
            'body_length' => strlen($emailBody)
        ]);
        
        // Send the email
        $mailSent = @mail($tutor['email'], $emailSubject, $emailBody, implode("\r\n", $headers));
        
        // Get the last error if any
        $lastError = error_get_last();
        if ($lastError) {
            log_debug("PHP error during mail() call:", $lastError);
        }
        
        if ($mailSent) {
            log_debug("Email sent successfully to " . $tutor['email']);
            
            // Update message status to read
            $stmt = $conn->prepare("UPDATE messages SET status = 'read' WHERE id = ?");
            $stmt->execute([$messageId]);
            
            $response['success'] = true;
            $response['message'] = 'Your message has been sent to the tutor!';
        } else {
            $errorMsg = 'Failed to send email notification';
            if ($lastError) {
                $errorMsg .= ': ' . $lastError['message'];
            }
            log_debug("Email sending failed", [
                'to' => $tutor['email'],
                'error' => $lastError
            ]);
            
            // Update message status to indicate email failure - using 'read' since 'error' is not a valid status
            $stmt = $conn->prepare("UPDATE messages SET status = 'read' WHERE id = ?");
            $stmt->execute([$messageId]);
            
            // Log the email failure since we can't store it in the status
            log_debug("Email sending failed but message marked as read", [
                'message_id' => $messageId,
                'error' => $errorMsg
            ]);
            
            // Still return success since the message was saved to the database
            $response['success'] = true;
            $response['message'] = 'Your message has been saved, but there was an issue sending the email notification. The tutor may contact you directly.';
        }
        
        // Commit the transaction
        $conn->commit();
        
    } catch (Exception $e) {
        // Rollback the transaction if it's still active
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        throw $e; // Re-throw the exception
    }
    
} catch (PDOException $e) {
    $errorInfo = $e->errorInfo ?? [];
    log_debug("Database error:", [
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'errorInfo' => $errorInfo
    ]);
    $response['message'] = 'Database error: ' . ($errorInfo[2] ?? $e->getMessage());
    
} catch (Exception $e) {
    log_debug("Error: " . $e->getMessage());
    $response['message'] = 'Failed to send message: ' . $e->getMessage();
}

// Return JSON response
echo json_encode($response);
?>
