<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to log debug information
function log_debug($message, $data = null) {
    $log = "[DEBUG] [" . date('Y-m-d H:i:s') . "] " . $message . "\n";
    if ($data !== null) {
        $log .= print_r($data, true) . "\n";
    }
    // Log to PHP error log
    error_log($log);
    // Also try to write to a debug file in /tmp
    @file_put_contents('/tmp/study_crew_debug.log', $log, FILE_APPEND);
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
    
    // Handle multiple course selections
    $courses = [];
    if (!empty($_POST['courses']) && is_array($_POST['courses'])) {
        foreach ($_POST['courses'] as $courseId => $courseData) {
            $courses[] = [
                'id' => $courseId,
                'code' => $courseData['code'] ?? '',
                'name' => $courseData['name'] ?? ''
            ];
        }
    }
    
    // For backward compatibility with single course selection
    $courseId = $_POST['course_id'] ?? ($courses[0]['id'] ?? '');
    $courseName = $courses[0]['name'] ?? '';

    // Validate required fields
    $required = [
        'tutor_id' => $tutorId,
        'name' => $name,
        'email' => $email,
        'message' => $message,
        'at_least_one_course' => !empty($courses) ? 1 : 0
    ];
    
    $missing = [];
    foreach ($required as $field => $value) {
        if (empty($value)) {
            $missing[] = str_replace('_', ' ', $field);
        }
    }
    
    if (!empty($missing)) {
        // Replace the at_least_one_course key with a user-friendly message
        $missingFields = array_map(function($field) {
            return $field === 'at_least_one_course' ? 'at least one course' : $field;
        }, $missing);
        
        throw new Exception('Please fill in all required fields: ' . implode(', ', $missingFields));
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
        
        // 4. Prepare course data for storage
        $courseIds = [];
        $courseNames = [];
        $courseCodes = [];
        
        // If no courses are selected, use the single course_id for backward compatibility
        if (empty($courses) && !empty($courseId)) {
            $courses[] = [
                'id' => $courseId,
                'name' => $courseName,
                'code' => ''
            ];
        }
        
        // Prepare course data arrays
        foreach ($courses as $course) {
            if (!empty($course['id'])) {
                $courseIds[] = $course['id'];
                $courseNames[] = $course['name'] ?? '';
                if (!empty($course['code'])) {
                    $courseCodes[] = $course['code'];
                }
            }
        }
        
        // If there are multiple courses, include them in the subject
        $emailSubject = $subject;
        if (count($courseCodes) > 1) {
            $emailSubject = '[' . implode(', ', $courseCodes) . '] ' . $subject;
        } elseif (count($courseCodes) === 1) {
            $emailSubject = '[' . $courseCodes[0] . '] ' . $subject;
        }
        
        // Insert a single message with all courses
        $insertStmt = $conn->prepare("
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
        
        $insertStmt->execute([
            ':sender_id' => $senderId,
            ':sender_name' => $name,
            ':sender_email' => $email,
            ':tutor_id' => $tutorId,
            ':tutor_email' => $tutor['email'],
            ':subject' => $emailSubject,
            ':message' => $message,
            ':course_id' => !empty($courseIds) ? json_encode($courseIds) : null,
            ':course_name' => !empty($courseNames) ? json_encode($courseNames) : null
        ]);
        
        $messageId = $conn->lastInsertId();
        
        // 5. Create connection record
        try {
            // Log all available data
            $debugInfo = [
                'senderId' => $senderId,
                'tutorId' => $tutorId,
                'courseId' => $courseId,
                'hasSession' => isset($_SESSION['user_id']) ? 'yes' : 'no',
                'sessionUserId' => $_SESSION['user_id'] ?? 'not set',
                'messageLength' => strlen($message),
                'isConnected' => $conn ? 'yes' : 'no',
                'dbName' => $conn ? $conn->query('SELECT DATABASE()')->fetchColumn() : 'no connection'
            ];
            
            log_debug("=== ATTEMPTING TO CREATE CONNECTION ===", $debugInfo);
            
            // Log current database tables
            if ($conn) {
                $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                log_debug("Available tables in database:", $tables);
                
                // Log connections table structure
                try {
                    $columns = $conn->query("DESCRIBE connections")->fetchAll(PDO::FETCH_ASSOC);
                    log_debug("Connections table structure:", $columns);
                } catch (Exception $e) {
                    log_debug("Failed to get connections table structure: " . $e->getMessage());
                }
            }

            // Ensure we have required values
            if (empty($senderId)) {
                throw new Exception('Sender ID is required but not provided');
            }

            // Get the assistant ID for this tutor (user_id)
            $checkStmt = $conn->prepare("SELECT id FROM assistants WHERE user_id = ?");
            $checkStmt->execute([$tutorId]);
            $assistant = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$assistant) {
                log_debug("No assistant found for user ID $tutorId");
                // Don't throw an error, just log and skip connection creation
                return;
            }
            
            $assistantId = $assistant['id'];

            // Prepare course IDs array for JSON storage
            $courseIds = [];
            foreach ($courses as $course) {
                if (!empty($course['id'])) {
                    $courseIds[] = $course['id'];
                }
            }
            
            // If no valid course IDs, use empty array
            $coursesJson = !empty($courseIds) ? json_encode($courseIds) : '[]';
            
            // Check if connection already exists
            $checkConnStmt = $conn->prepare("SELECT id, course_id FROM connections WHERE user_id = ? AND assistant_id = ?");
            $checkConnStmt->execute([$senderId, $assistantId]);
            $existingConn = $checkConnStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingConn) {
                // Update existing connection with merged course IDs
                $existingCourseIds = [];
                if (!empty($existingConn['course_id']) && $existingConn['course_id'] !== '[]') {
                    $existingCourseIds = json_decode($existingConn['course_id'], true) ?: [];
                }
                
                // Merge existing and new course IDs, remove duplicates
                $allCourseIds = array_unique(array_merge($existingCourseIds, $courseIds));
                $mergedCoursesJson = json_encode(array_values($allCourseIds));
                
                // Update the connection with merged courses
                $updateStmt = $conn->prepare("UPDATE connections SET course_id = ?, updated_at = NOW() WHERE id = ?");
                $result = $updateStmt->execute([$mergedCoursesJson, $existingConn['id']]);
                
                if ($result) {
                    log_debug("Successfully updated connection with ID: " . $existingConn['id']);
                } else {
                    $errorInfo = $updateStmt->errorInfo();
                    throw new Exception("Failed to update connection: " . ($errorInfo[2] ?? 'Unknown error'));
                }
            } else {
                // Create new connection with all course IDs
                $sql = "INSERT INTO connections (
                            user_id, 
                            assistant_id, 
                            course_id, 
                            problem_description, 
                            status,
                            created_at,
                            updated_at
                        ) VALUES (
                            :user_id,
                            :assistant_id,
                            :course_id,
                            :problem_description,
                            'pending',
                            NOW(),
                            NOW()
                        )";
                
                log_debug("Executing SQL: " . $sql);
                
                $stmt = $conn->prepare($sql);
                $result = $stmt->execute([
                    ':user_id' => $senderId,
                    ':assistant_id' => $assistantId,
                    ':course_id' => $coursesJson,
                    ':problem_description' => substr($message, 0, 500) // Limit length to prevent issues
                ]);
                
                if ($result) {
                    log_debug("Successfully created connection with ID: " . $conn->lastInsertId());
                } else {
                    $errorInfo = $stmt->errorInfo();
                    throw new Exception("Failed to create connection: " . ($errorInfo[2] ?? 'Unknown error'));
                }
            }
            
        } catch (Exception $e) {
            log_debug("CRITICAL ERROR in connection creation: " . $e->getMessage());
            log_debug("Full error info: " . print_r($e, true));
            // Don't rethrow to prevent breaking the message sending
        }
        
        // 6. Prepare email content
        $emailBody = "You have received a new message from $name ($email)\n\n";
        
        // Add course information
        if (!empty($courses)) {
            $emailBody .= "Courses (" . count($courses) . "):\n";
            foreach ($courses as $course) {
                $courseLine = "- ";
                if (!empty($course['code'])) {
                    $courseLine .= "[{$course['code']}] ";
                }
                if (!empty($course['name'])) {
                    $courseLine .= $course['name'];
                }
                $emailBody .= $courseLine . "\n";
            }
            $emailBody .= "\n";
        }
        
        $emailBody .= "Message:\n$message\n\n";
        $emailBody .= "---\n";
        $emailBody .= "This message was sent through the Study Crew platform.\n";
        $emailBody .= "Please do not reply directly to this email.";
        
        // Email headers
        $headers = [
            'From: Study Crew <noreply@studycrew.com>',
            'Reply-To: ' . $email,
            'X-Mailer: PHP/' . phpversion(),
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=utf-8'
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
// After sending the message, increment assistant's visits
if (isset($_POST['tutor_id'])) {
    $tutorId = (int)$_POST['tutor_id'];
    require_once __DIR__ . '/../includes/db_functions.php'; // or wherever getDbConnection() is defined
    $conn = getDbConnection();
    // Get assistant record by user_id
    $stmt = $conn->prepare("SELECT id FROM assistants WHERE user_id = ?");
    $stmt->execute([$tutorId]);
    $assistant = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($assistant) {
        $stmt = $conn->prepare("UPDATE assistants SET visits = visits + 1 WHERE id = ?");
        $stmt->execute([$assistant['id']]);
    }
}

// Return JSON response
echo json_encode($response);
?>
