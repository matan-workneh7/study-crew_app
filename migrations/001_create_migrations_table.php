<?php
require_once __DIR__ . '/../config.php';

// Connect to database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, null, DB_SOCKET);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create migrations table
$sql = "CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration_name VARCHAR(255) NOT NULL UNIQUE,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "Migrations table created successfully\n";
} else {
    die("Error creating migrations table: " . $conn->error);
}

$conn->close();
