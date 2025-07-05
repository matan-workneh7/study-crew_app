<?php
require_once __DIR__ . '/../config.php';

// Connect to database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, null, DB_SOCKET);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create messages table
$sql = "CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tutor_id INT,
    tutor_email VARCHAR(255) NOT NULL,
    sender_id INT,
    sender_name VARCHAR(255) NOT NULL,
    sender_email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    courses TEXT COMMENT 'JSON array of course data',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tutor_id) REFERENCES assistants(id) ON DELETE SET NULL,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "Messages table created successfully\n";
    
    // Add this migration to migrations table if it doesn't exist
    $migrationName = basename(__FILE__);
    $checkSql = "SELECT COUNT(*) as count FROM migrations WHERE migration_name = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("s", $migrationName);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        $insertSql = "INSERT INTO migrations (migration_name) VALUES (?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("s", $migrationName);
        if ($insertStmt->execute()) {
            echo "Migration recorded successfully\n";
        } else {
            echo "Warning: Could not record migration: " . $insertStmt->error . "\n";
        }
        $insertStmt->close();
    } else {
        echo "Migration was already recorded\n";
    }
    
    $stmt->close();
} else {
    die("Error creating messages table: " . $conn->error . "\n");
}

$conn->close();
