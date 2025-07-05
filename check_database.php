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
echo "<pre>";
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

echo "✅ Connected to database successfully\n\n";

// Check if users table exists and has correct structure
echo "=== Checking users table structure ===\n";
$result = $conn->query("SHOW COLUMNS FROM users");
if ($result) {
    echo "Columns in users table:\n";
    while($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "❌ Error checking users table: " . $conn->error . "\n";
}

echo "\n=== Checking current users ===\n";
$result = $conn->query("SELECT * FROM users");
if ($result) {
    echo "Number of users: " . $result->num_rows . "\n";
    if ($result->num_rows > 0) {
        echo "\nUsers in database:\n";
        while($row = $result->fetch_assoc()) {
            echo "ID: " . $row['id'] . 
                 " | Username: " . $row['username'] . 
                 " | Email: " . $row['email'] . 
                 " | Roles: " . $row['roles'] . "\n";
        }
    } else {
        echo "No users found in database.\n";
    }
} else {
    echo "❌ Error fetching users: " . $conn->error . "\n";
}

echo "\n=== Testing Insert ===\n";
$testUser = "testuser_" . time();
$testEmail = $testUser . "@example.com";
$testPass = "Test123!";
$testYear = "Freshman";

// Try inserting a test user
$hashedPass = password_hash($testPass, PASSWORD_DEFAULT);
$roles = json_encode(["student"]);

$stmt = $conn->prepare("INSERT INTO users (username, email, password, academic_year, roles) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $testUser, $testEmail, $hashedPass, $testYear, $roles);

if ($stmt->execute()) {
    echo "✅ Test user inserted successfully!\n";
    echo "   User ID: " . $conn->insert_id . "\n";
    echo "   Username: " . $testUser . "\n";
    echo "   Email: " . $testEmail . "\n";
    
    // Verify the insert
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $testUser);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "✅ User found in database!\n";
        $user = $result->fetch_assoc();
        print_r($user);
    } else {
        echo "❌ User not found in database after insert!\n";
    }
} else {
    echo "❌ Insert failed: " . $conn->error . "\n";
}

// Clean up test user
$stmt = $conn->prepare("DELETE FROM users WHERE username = ?");
$stmt->bind_param("s", $testUser);
$stmt->execute();

$conn->close();
?>
