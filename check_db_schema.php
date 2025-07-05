<?php
require_once 'config.php';

function checkAndCreateTables() {
    try {
        $conn = getDbConnection();
        
        // Check if courses table exists
        $stmt = $conn->query("SHOW TABLES LIKE 'courses'");
        if ($stmt->rowCount() === 0) {
            // Create courses table if it doesn't exist
            $sql = "CREATE TABLE IF NOT EXISTS `courses` (
                `id` varchar(10) NOT NULL,
                `name` varchar(255) NOT NULL,
                `code` varchar(20) NOT NULL,
                `credit_hours` int(11) DEFAULT 3,
                `year` varchar(20) NOT NULL,
                `semester` int(11) DEFAULT 1,
                `description` text DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
            
            $conn->exec($sql);
            echo "Created 'courses' table.\n";
            
            // Insert sample data
            $sampleData = [
                ['id' => 'CS401', 'name' => 'Software Engineering', 'code' => 'CS401', 'year' => 'Senior', 'semester' => 1, 'description' => 'Software development methodologies and practices.'],
                ['id' => 'CS402', 'name' => 'Advanced Algorithms', 'code' => 'CS402', 'year' => 'Senior', 'semester' => 1, 'description' => 'Advanced algorithms and data structures.'],
                ['id' => 'CS403', 'name' => 'Machine Learning', 'code' => 'CS403', 'year' => 'Senior', 'semester' => 2, 'description' => 'Introduction to machine learning algorithms.']
            ];
            
            $stmt = $conn->prepare("INSERT IGNORE INTO courses (id, name, code, credit_hours, year, semester, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($sampleData as $course) {
                $stmt->execute([
                    $course['id'],
                    $course['name'],
                    $course['code'],
                    $course['credit_hours'] ?? 3,
                    $course['year'],
                    $course['semester'],
                    $course['description']
                ]);
            }
            
            echo "Inserted sample course data.\n";
        } else {
            echo "'courses' table already exists.\n";
        }
        
        // Check if users table exists
        $stmt = $conn->query("SHOW TABLES LIKE 'users'");
        if ($stmt->rowCount() === 0) {
            // Create users table if it doesn't exist
            $sql = "CREATE TABLE IF NOT EXISTS `users` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `username` varchar(50) NOT NULL,
                `full_name` varchar(100) DEFAULT NULL,
                `email` varchar(100) NOT NULL,
                `password` varchar(255) NOT NULL,
                `academic_year` varchar(20) DEFAULT NULL,
                `roles` json NOT NULL DEFAULT '[]',
                `bio` text DEFAULT NULL,
                `telegram` varchar(50) DEFAULT NULL,
                `phone` varchar(20) DEFAULT NULL,
                `created_at` timestamp NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                UNIQUE KEY `username` (`username`),
                UNIQUE KEY `email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
            
            $conn->exec($sql);
            echo "Created 'users' table.\n";
        } else {
            echo "'users' table already exists.\n";
        }
        
        echo "Database schema is up to date.\n";
        
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}

// Run the check
checkAndCreateTables();

echo "\nChecking courses in the database:\n";
$conn = getDbConnection();
$stmt = $conn->query("SELECT * FROM courses");
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($courses) . " courses in the database.\n";
if (!empty($courses)) {
    echo "\nCourse List:\n";
    foreach ($courses as $course) {
        echo "- {$course['code']}: {$course['name']} ({$course['year']} Year, Semester {$course['semester']})\n";
    }
}

// Test the getCoursesByYearAndSemester function
require_once 'functions.php';

echo "\nTesting getCoursesByYearAndSemester(4, 1):\n";
$courses = getCoursesByYearAndSemester(4, 1);
echo "Found " . count($courses) . " courses for Senior Year, Semester 1.\n";
if (!empty($courses)) {
    foreach ($courses as $course) {
        echo "- {$course['code']}: {$course['name']}\n";
    }
}

echo "\nDatabase check complete.\n";
?>
