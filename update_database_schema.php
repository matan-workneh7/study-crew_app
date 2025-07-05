<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "<pre>";
echo "=== Updating Database Schema ===\n";

try {
    // Add roles column to users table if it doesn't exist
    $sql = "SHOW COLUMNS FROM users LIKE 'roles'";
    $result = $conn->query($sql);
    
    if ($result->num_rows === 0) {
        echo "Adding 'roles' column to users table...\n";
        $alterSql = "ALTER TABLE users ADD COLUMN roles JSON NOT NULL DEFAULT '[]' AFTER academic_year";
        if ($conn->query($alterSql) === TRUE) {
            echo "✅ Successfully added 'roles' column to users table\n";
        } else {
            echo "❌ Error adding 'roles' column: " . $conn->error . "\n";
        }
    } else {
        echo "✅ 'roles' column already exists in users table\n";
    }
    
    // Update existing users to have a default 'student' role if roles is empty
    $updateSql = "UPDATE users SET roles = '[]' WHERE roles = '' OR roles IS NULL";
    if ($conn->query($updateSql) === TRUE) {
        echo "✅ Updated existing users with empty roles\n";
    } else {
        echo "❌ Error updating existing users: " . $conn->error . "\n";
    }
    
    echo "\n=== Database Schema Update Complete ===\n";
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

$conn->close();
echo "</pre>";
?>
