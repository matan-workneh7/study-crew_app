<?php
/**
 * Main Configuration File
 * 
 * This file contains all the configuration settings for the Study Crew application.
 * It includes database connection settings, application paths, and other configurations.
 */

// Debug mode - set to false in production
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', true);
}

// Error reporting settings
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/error.log');
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'study_crew');

// Application URLs
define('BASE_URL', 'http://localhost/study-crew_app');
define('ASSISTANT_DASHBOARD_URL', BASE_URL . '/assistant-dashboard.php');

// Email configuration
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your-email@gmail.com'); // Replace with your email
define('MAIL_PASSWORD', 'your-email-password'); // Replace with your email password
define('MAIL_FROM_ADDRESS', 'noreply@studycrew.com');
define('MAIL_FROM_NAME', 'Study Crew');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Time zone
date_default_timezone_set('Asia/Jerusalem');

/**
 * Get a PDO database connection
 * 
 * @return PDO Database connection object
 * @throws Exception If connection fails
 */
function getDbConnection() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            // For XAMPP, use the socket path
            $socket = '/opt/lampp/var/mysql/mysql.sock';
            $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";unix_socket=$socket;charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => true,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci'
            ];
            
            $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed. Please try again later.");
        }
    }
    
    return $conn;
}

// Initialize database connection
try {
    $conn = getDbConnection();
} catch (Exception $e) {
    die($e->getMessage());
}

/**
 * Send an email
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $body Email body
 * @param bool $isHtml Whether the email is in HTML format
 * @return bool True on success, false on failure
 */
function sendEmail($to, $subject, $body, $isHtml = true) {
    $headers = [];
    $headers[] = 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM_ADDRESS . '>';
    $headers[] = 'Reply-To: ' . MAIL_FROM_ADDRESS;
    
    if ($isHtml) {
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=utf-8';
    }
    
    return mail($to, $subject, $body, implode("\r\n", $headers));
}