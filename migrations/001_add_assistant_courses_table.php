<?php
// Migration script to add assistant_courses table and migrate data
require_once __DIR__ . '/../config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to log messages with timestamp
function logMessage($message) {
    echo "[" . date('Y-m-d H:i:s') . "] $message\n";
}

// Verify database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

logMessage("Starting migration...");

// Get database name from config
$dbName = DB_NAME;

// Check if migration has already been run
$migrationCheck = "SELECT 1 FROM information_schema.tables WHERE table_schema = '$dbName' AND table_name = 'migrations' LIMIT 1";
$result = $conn->query($migrationCheck);
$hasMigrationsTable = $result && $result->num_rows > 0;

// Create migrations table if it doesn't exist
if (!$hasMigrationsTable) {
    logMessage("Creating migrations table...");
    $createMigrationsTable = "CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL,
        batch INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (!$conn->query($createMigrationsTable)) {
        die("Error creating migrations table: " . $conn->error);
    }
    
    logMessage("Migrations table created successfully");
}

// Check if this migration has already run
$migrationName = '001_add_assistant_courses_table';
$checkMigration = "SELECT id FROM migrations WHERE migration = '$migrationName' LIMIT 1";
$result = $conn->query($checkMigration);

if ($result && $result->num_rows > 0) {
    logMessage("Migration $migrationName has already been run. Exiting...");
    exit(0);
}

// Start transaction
logMessage("Starting transaction...");
$conn->begin_transaction();

try {
    logMessage("Starting migration: $migrationName");
    
    // 1. Check if assistant_courses table already exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'assistant_courses'");
    if ($tableCheck && $tableCheck->num_rows > 0) {
        logMessage("Table 'assistant_courses' already exists. Dropping it first...");
        if (!$conn->query("DROP TABLE IF EXISTS assistant_courses")) {
            throw new Exception("Failed to drop existing assistant_courses table: " . $conn->error);
        }
    }
    
    // 2. First, let's check if the assistant_courses table exists and drop it if it does
    $tableCheck = $conn->query("SHOW TABLES LIKE 'assistant_courses'");
    if ($tableCheck && $tableCheck->num_rows > 0) {
        logMessage("Dropping existing assistant_courses table...");
        if (!$conn->query("DROP TABLE IF EXISTS assistant_courses")) {
            throw new Exception("Failed to drop existing assistant_courses table: " . $conn->error);
        }
    }
    
    // 3. Create the assistant_courses table with matching character set and collation as courses table
    logMessage("Creating assistant_courses table...");
    $createTable = "CREATE TABLE assistant_courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        assistant_id INT NOT NULL,
        course_id VARCHAR(10) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_assistant_course (assistant_id, course_id),
        FOREIGN KEY (assistant_id) REFERENCES assistants(id) ON DELETE CASCADE,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    logMessage("SQL: " . $createTable);
    
    if (!$conn->query($createTable)) {
        throw new Exception("Error creating assistant_courses table: " . $conn->error . "\n" . 
                          "Make sure the courses table has a primary key on the 'id' column and the data types match.");
    }
    
    logMessage("Table 'assistant_courses' created successfully");
    
    // 3. Check if course_id column exists in assistants table
    $columnCheck = $conn->query("SHOW COLUMNS FROM assistants LIKE 'course_id'");
    $hasCourseIdColumn = $columnCheck && $columnCheck->num_rows > 0;
    
    if ($hasCourseIdColumn) {
        // 4. Migrate existing data from assistants.course_id to assistant_courses
        logMessage("Migrating existing course assignments...");
        
        // First, ensure all course codes exist in the courses table
        $updateCourseCodes = "UPDATE courses c 
                            INNER JOIN assistants a ON c.id = a.course_id
                            SET c.code = c.id";
        if (!$conn->query($updateCourseCodes)) {
            logMessage("Warning: Could not update course codes: " . $conn->error);
        }
        
        // Now migrate the data
        $migrateData = "INSERT IGNORE INTO assistant_courses (assistant_id, course_id)
                       SELECT a.id, a.course_id 
                       FROM assistants a
                       WHERE a.course_id IS NOT NULL AND a.course_id != ''";
        
        if (!$conn->query($migrateData)) {
            throw new Exception("Error migrating data: " . $conn->error);
        }
        
        $migratedCount = $conn->affected_rows;
        logMessage("Migrated $migratedCount course assignments");
    } else {
        logMessage("No course_id column found in assistants table. Skipping data migration.");
    }
    
    // 5. Mark migration as complete
    logMessage("Marking migration as complete...");
    $markComplete = "INSERT INTO migrations (migration, batch) VALUES ('$migrationName', 1)";
    if (!$conn->query($markComplete)) {
        throw new Exception("Failed to mark migration as complete: " . $conn->error);
    }
    
    // 6. Commit the transaction
    $conn->commit();
    logMessage("Migration completed successfully!");
    
    if ($hasCourseIdColumn) {
        logMessage("\nNext steps:");
        logMessage("1. Verify the data in the assistant_courses table");
        logMessage("2. Once verified, you can safely remove the course_id column from the assistants table using:");
        logMessage("   ALTER TABLE assistants DROP COLUMN course_id;\n");
    }
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    logMessage("ERROR: " . $e->getMessage());
    logMessage("Migration failed and has been rolled back.");
    exit(1);
}

$conn->close();
?>
