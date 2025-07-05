<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "<pre>";
echo "=== Database Connection Test ===\n";

// Test connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "✅ Connected to database successfully\n";

// Test if users table exists
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows > 0) {
    echo "✅ Users table exists\n";
    
    // Show current users
    $result = $conn->query("SELECT * FROM users");
    echo "\n=== Current Users ===\n";
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "ID: " . $row['id'] . " | ";
            echo "Username: " . $row['username'] . " | ";
            echo "Email: " . $row['email'] . " | ";
            echo "Roles: " . $row['roles'] . "\n";
        }
    } else {
        echo "No users found in the database.\n";
    }
} else {
    echo "❌ Users table does NOT exist\n";
}

// Test insert
$testEmail = "test_" . time() . "@example.com";
$testUser = "testuser_" . time();
$hashedPass = password_hash("test123", PASSWORD_DEFAULT);

echo "\n=== Testing User Insert ===\n";

try {
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, academic_year, roles) VALUES (?, ?, ?, 'Freshman', ?)");
    $roles = json_encode(["student"]);
    $stmt->bind_param("ssss", $testUser, $testEmail, $hashedPass, $roles);
    
    if ($stmt->execute()) {
        $newId = $conn->insert_id;
        echo "✅ Test user inserted successfully\n";
        echo "   - New user ID: $newId\n";
        echo "   - Username: $testUser\n";
        echo "   - Email: $testEmail\n";
    } else {
        echo "❌ Error inserting test user: " . $stmt->error . "\n";
    }
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

// Show final user count
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$count = $result->fetch_assoc()['count'];
echo "\n=== Final User Count: $count ===\n";

$conn->close();
echo "</pre>";
?>
