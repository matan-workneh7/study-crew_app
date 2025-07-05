<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'study_crew');
define('DB_SOCKET', '/opt/lampp/var/mysql/mysql.sock');

// Create MySQL connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, null, DB_SOCKET);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Test data
$testUser = "testuser_" . time();
$testEmail = $testUser . "@example.com";
$testPass = "Test123!";
$testYear = "Freshman";

// Test registration query
$hashedPass = password_hash($testPass, PASSWORD_DEFAULT);
$roles = json_encode(["student"]);

$stmt = $conn->prepare("INSERT INTO users (username, email, password, academic_year, roles) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $testUser, $testEmail, $hashedPass, $testYear, $roles);

if ($stmt->execute()) {
    echo "✅ User registered successfully!\n";
    echo "   User ID: " . $conn->insert_id . "\n";
    echo "   Username: " . $testUser . "\n";
    echo "   Email: " . $testEmail . "\n";
    
    // Verify user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $testUser);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "✅ User found in database!\n";
        $user = $result->fetch_assoc();
        print_r($user);
    } else {
        echo "❌ User not found in database!\n";
    }
} else {
    echo "❌ Registration failed: " . $conn->error . "\n";
}

// Clean up
$stmt = $conn->prepare("DELETE FROM users WHERE username = ?");
$stmt->bind_param("s", $testUser);
$stmt->execute();

$conn->close();
?>
