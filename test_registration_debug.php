<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Registration Debug Test</h2>\n";

// Include config and functions
require_once 'config.php';
require_once 'functions.php';

echo "<h3>1. Database Connection Test</h3>\n";
if ($conn->connect_error) {
    echo "❌ Connection failed: " . $conn->connect_error . "\n";
    exit;
} else {
    echo "✅ Database connected successfully\n";
}

echo "<h3>2. Current Users in Database</h3>\n";
$result = $conn->query("SELECT * FROM users");
if ($result) {
    echo "Users found: " . $result->num_rows . "\n";
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "- ID: " . $row['id'] . " | Username: " . $row['username'] . " | Email: " . $row['email'] . " | Roles: " . $row['roles'] . "\n";
        }
    } else {
        echo "No users found in database.\n";
    }
} else {
    echo "❌ Error fetching users: " . $conn->error . "\n";
}

echo "<h3>3. Test User Registration</h3>\n";
$testUsername = "testuser_" . time();
$testEmail = $testUsername . "@example.com";
$testPassword = "test123456";
$testYear = "Freshman";
$testRole = "assist";

echo "Attempting to register:\n";
echo "- Username: $testUsername\n";
echo "- Email: $testEmail\n";
echo "- Role: $testRole\n";
echo "- Year: $testYear\n\n";

$registrationResult = registerUser($testUsername, $testEmail, $testPassword, $testYear, $testRole);

if ($registrationResult === true) {
    echo "✅ Registration successful!\n";
    
    // Check if user was actually inserted
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $testEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "✅ User found in database after registration:\n";
        $user = $result->fetch_assoc();
        echo "- ID: " . $user['id'] . "\n";
        echo "- Username: " . $user['username'] . "\n";
        echo "- Email: " . $user['email'] . "\n";
        echo "- Roles: " . $user['roles'] . "\n";
        echo "- Academic Year: " . $user['academic_year'] . "\n";
    } else {
        echo "❌ User NOT found in database after registration!\n";
    }
} else {
    echo "❌ Registration failed: " . $registrationResult . "\n";
}

echo "<h3>4. Final User Count</h3>\n";
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$count = $result->fetch_assoc()['count'];
echo "Total users in database: $count\n";

$conn->close();
?>
