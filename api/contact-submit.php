<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

// Function to log debug information
function log_debug($message, $data = null) {
    $log = "[DEBUG] [" . date('Y-m-d H:i:s') . "] " . $message . "\n";
    if ($data !== null) {
        $log .= print_r($data, true) . "\n";
    }
    // Log to PHP error log
    error_log($log);
    // Also try to write to a debug file in /tmp
    @file_put_contents('/tmp/study_crew_contact_debug.log', $log, FILE_APPEND);
}

// Set JSON content type
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => ''
];

try {
    // Check if the request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method. Only POST is allowed.');
    }

    // Get form data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? 'Contact Form Submission');
    $message = trim($_POST['message'] ?? '');
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    // Validate required fields
    if (empty($name) || empty($email) || empty($message)) {
        throw new Exception('All fields are required.');
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please enter a valid email address.');
    }

    // Get database connection
    $conn = getDbConnection();
    
    // Check if contact_messages table exists, create if not
    try {
        $tableExists = $conn->query("SHOW TABLES LIKE 'contact_messages'")->rowCount() > 0;
        
        if (!$tableExists) {
            $createTableSQL = file_get_contents(__DIR__ . '/../migrations/007_create_contact_messages_table.sql');
            $conn->exec($createTableSQL);
            log_debug("Created contact_messages table");
        }
    } catch (Exception $e) {
        log_debug("Error checking/creating contact_messages table: " . $e->getMessage());
        throw new Exception('Database configuration error. Please try again later.');
    }
    
    // Begin transaction
    $conn->beginTransaction();
    
    try {
        // Save to database
        $stmt = $conn->prepare("
            INSERT INTO contact_messages 
            (name, email, subject, message, ip_address, user_agent, status)
            VALUES (?, ?, ?, ?, ?, ?, 'unread')
        ");
        
        $stmt->execute([
            $name,
            $email,
            $subject,
            $message,
            $ipAddress,
            $userAgent
        ]);
        
        $messageId = $conn->lastInsertId();
        log_debug("Saved contact message to database", ['id' => $messageId]);
        
        // Commit the transaction
        $conn->commit();
        
        // Set recipient email
        $to = 'matan.workneh@bitscollege.edu.et';
        $emailSubject = "[Contact #$messageId] $subject";
        
        // Set email headers
        $headers = [
            'From: ' . $email,
            'Reply-To: ' . $email,
            'X-Mailer: PHP/' . phpversion(),
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8'
        ];

        // Prepare email body
        $emailBody = "You have received a new message from the contact form:\n\n";
        $emailBody .= "Name: $name\n";
        $emailBody .= "Email: $email\n";
        $emailBody .= "Subject: $subject\n\n";
        $emailBody .= "Message:\n$message\n\n";
        $emailBody .= "-- \nThis email was sent from the contact form on " . $_SERVER['HTTP_HOST'];

        // Send email
        $mailSent = @mail($to, $emailSubject, $emailBody, implode("\r\n", $headers));
        
        // Log email sending result
        log_debug("Email sending result", [
            'to' => $to,
            'subject' => $emailSubject,
            'success' => $mailSent,
            'error' => error_get_last()
        ]);
        
        // Update message status based on email sending result
        try {
            $status = $mailSent ? 'read' : 'unread';
            $stmt = $conn->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
            $stmt->execute([$status, $messageId]);
        } catch (Exception $e) {
            log_debug("Failed to update message status: " . $e->getMessage());
        }
        
        // Set success response
        $response = [
            'success' => true,
            'message' => 'Thank you for contacting us! We will get back to you soon.'
        ];
        
    } catch (Exception $e) {
        $conn->rollBack();
        log_debug("Error saving contact message: " . $e->getMessage());
        throw new Exception('Failed to process your message. Please try again later.');
    }
    
} catch (Exception $e) {
    log_debug("Contact form error: " . $e->getMessage());
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

// Return JSON response
echo json_encode($response);
?>
