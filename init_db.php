<?php
require_once 'config.php';

try {
    $db = new SQLite3(SQLITE_DB_PATH);
    
    // Read and execute the SQL file
    $sql = file_get_contents('database.sqlite.sql');
    $result = $db->exec($sql);
    
    if ($result) {
        echo "Database initialized successfully!\n";
        echo "Tables created: users, courses, assistants, connections\n";
        echo "Sample course data has been inserted.\n";
    } else {
        echo "Error initializing database: " . $db->lastErrorMsg() . "\n";
    }
    
    $db->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 