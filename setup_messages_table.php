<?php
require_once __DIR__ . '/config.php';

// Connect to database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, null, DB_SOCKET);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create migrations table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "Migrations table created successfully\n";
} else {
    die("Error creating migrations table: " . $conn->error);
}

// Check if messages table already exists
$result = $conn->query("SHOW TABLES LIKE 'messages'");
if ($result->num_rows > 0) {
    echo "Messages table already exists. No changes made.\n";
} else {
    // Create messages table
    $sql = "CREATE TABLE messages (
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
        
        // Record this migration
        $migrationName = 'create_messages_table';
        $stmt = $conn->prepare("INSERT IGNORE INTO migrations (name) VALUES (?)");
        $stmt->bind_param("s", $migrationName);
        if ($stmt->execute()) {
            echo "Migration recorded successfully\n";
        } else {
            echo "Warning: Could not record migration: " . $stmt->error . "\n";
        }
        $stmt->close();
    } else {
        echo "Error creating messages table: " . $conn->error . "\n";
    }
}

$conn->close();

echo "Setup completed. Check the output above for any errors.\n";
