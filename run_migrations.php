<?php
require_once 'config.php';

// Create migrations table if it doesn't exist
$createMigrationsTable = "
CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration_name VARCHAR(255) NOT NULL,
    run_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($createMigrationsTable) === FALSE) {
    die("Error creating migrations table: " . $conn->error);
}

// Get list of already run migrations
$result = $conn->query("SELECT migration_name FROM migrations");
$appliedMigrations = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $appliedMigrations[] = $row['migration_name'];
    }
}

// Find and run new migrations
$migrationFiles = glob('migrations/*.sql');
$migrationsRun = 0;

foreach ($migrationFiles as $migrationFile) {
    $migrationName = basename($migrationFile);
    
    if (!in_array($migrationName, $appliedMigrations)) {
        echo "Running migration: $migrationName\n";
        
        // Read and execute the migration
        $sql = file_get_contents($migrationFile);
        
        if ($conn->multi_query($sql)) {
            do {
                // Consume all results to avoid "out of sync" errors
                if ($result = $conn->store_result()) {
                    $result->free();
                }
            } while ($conn->next_result());
            
            // Record the migration as applied
            $stmt = $conn->prepare("INSERT INTO migrations (migration_name) VALUES (?)");
            $stmt->bind_param("s", $migrationName);
            $stmt->execute();
            $migrationsRun++;
            
            echo "Successfully applied migration: $migrationName\n";
        } else {
            echo "Error running migration $migrationName: " . $conn->error . "\n";
        }
    }
}

if ($migrationsRun === 0) {
    echo "No new migrations to run.\n";
} else {
    echo "\nSuccessfully ran $migrationsRun migration(s).\n";
}

$conn->close();
?>
