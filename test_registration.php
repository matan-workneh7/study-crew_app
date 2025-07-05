<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';
require_once 'functions.php';

echo "<pre>";
echo "=== Testing User Registration ===\n\n";

// Test data
$testUser = "testuser_" . time();
$testEmail = $testUser . "@example.com";
$testPass = "Test123!";
$testYear = "Freshman";

echo "Test Username: $testUser\n";
echo "Test Email: $testEmail\n";

// 1. Test registration
echo "\n1. Testing user registration...\n";
$result = registerUser($testUser, $testEmail, $testPass, $testYear, 'student');

if ($result === true) {
    echo "✅ Registration successful!\n";
    
    // 2. Verify user exists in database
    echo "\n2. Verifying user in database...\n";    
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $testUser);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo "✅ User found in database!\n";
        echo "   User ID: " . $user['id'] . "\n";
        echo "   Username: " . $user['username'] . "\n";
        echo "   Email: " . $user['email'] . "\n";
        echo "   Roles: " . $user['roles'] . "\n";
        
        // 3. Test login
        echo "\n3. Testing login...\n";
        if (loginUser($testEmail, $testPass)) {
            echo "✅ Login successful!\n";
            echo "   User ID in session: " . $_SESSION['user_id'] . "\n";
            echo "   Username in session: " . $_SESSION['username'] . "\n";
            echo "   Roles in session: " . print_r($_SESSION['roles'], true) . "\n";
        } else {
            echo "❌ Login failed!\n";
        }
    } else {
        echo "❌ User not found in database!\n";
    }
} else {
    echo "❌ Registration failed: " . $result . "\n";
    
    // Check if it's a duplicate error
    if (strpos($result, 'already exists') !== false || strpos($result, 'already taken') !== false) {
        echo "   (This might be because the test user already exists. Try running the test again.)\n";
    }
}

// 4. Clean up (remove test user)
echo "\n4. Cleaning up test user...\n";
$stmt = $conn->prepare("DELETE FROM users WHERE username = ?");
$stmt->bind_param("s", $testUser);
if ($stmt->execute()) {
    echo "✅ Cleanup complete. Removed test user.\n";
} else {
    echo "⚠️ Could not remove test user: " . $conn->error . "\n";
}

$conn->close();
echo "</pre>";
?>
