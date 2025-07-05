<?php
require_once 'config.php';

// Connect to SQLite
try {
    $sqlite = new PDO('sqlite:/opt/lampp/htdocs/study-crew_app/database.sqlite');
    $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("SQLite connection failed: " . $e->getMessage());
}

// Connect to MySQL
$mysql = new mysqli(
    'localhost',
    'root',
    '',
    'mysql',
    3306,
    '/opt/lampp/var/mysql/mysql.sock'
);
if ($mysql->connect_error) {
    die("MySQL connection failed: " . $mysql->connect_error);
}

// Create database if it doesn't exist
$mysql->query("CREATE DATABASE IF NOT EXISTS study_crew");
$mysql->select_db('study_crew');

// Create tables one by one
$statements = explode(';', file_get_contents('database.sql'));
foreach ($statements as $statement) {
    $statement = trim($statement);
    if (!empty($statement)) {
        $mysql->query($statement);
    }
}

// Migrate users
$stmt = $sqlite->query("SELECT * FROM users");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $mysql->query("INSERT INTO users (username, email, password, academic_year, created_at, updated_at) 
                  VALUES ('" . $mysql->real_escape_string($row['username']) . "', 
                          '" . $mysql->real_escape_string($row['email']) . "', 
                          '" . $mysql->real_escape_string($row['password']) . "', 
                          '" . $mysql->real_escape_string($row['academic_year']) . "', 
                          '" . $mysql->real_escape_string($row['created_at']) . "', 
                          '" . $mysql->real_escape_string($row['updated_at']) . "')");
}

// Migrate courses
$stmt = $sqlite->query("SELECT * FROM courses");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $mysql->query("INSERT IGNORE INTO courses (id, name, code, credit_hours, year, semester, description, created_at, updated_at) 
                  VALUES ('" . $mysql->real_escape_string($row['id']) . "', 
                          '" . $mysql->real_escape_string($row['name']) . "', 
                          '" . $mysql->real_escape_string($row['code']) . "', 
                          '" . $mysql->real_escape_string($row['credit_hours']) . "', 
                          '" . $mysql->real_escape_string($row['year']) . "', 
                          '" . $mysql->real_escape_string($row['semester']) . "', 
                          '" . $mysql->real_escape_string($row['description']) . "', 
                          '" . $mysql->real_escape_string($row['created_at']) . "', 
                          '" . $mysql->real_escape_string($row['updated_at']) . "')");
}

// Migrate assistants
$stmt = $sqlite->query("SELECT * FROM assistants");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $mysql->query("INSERT INTO assistants (user_id, course_id, telegram, phone, other_info, visits, created_at, updated_at) 
                  VALUES (" . $row['user_id'] . ", 
                          '" . $mysql->real_escape_string($row['course_id']) . "', 
                          '" . $mysql->real_escape_string($row['telegram']) . "', 
                          '" . $mysql->real_escape_string($row['phone']) . "', 
                          '" . $mysql->real_escape_string($row['other_info']) . "', 
                          '" . $mysql->real_escape_string($row['visits']) . "', 
                          '" . $mysql->real_escape_string($row['created_at']) . "', 
                          '" . $mysql->real_escape_string($row['updated_at']) . "')");
}

// Migrate connections
$stmt = $sqlite->query("SELECT * FROM connections");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $mysql->query("INSERT INTO connections (user_id, assistant_id, course_id, problem_description, telegram, status, created_at, updated_at) 
                  VALUES (" . $row['user_id'] . ", 
                          " . $row['assistant_id'] . ", 
                          '" . $mysql->real_escape_string($row['course_id']) . "', 
                          '" . $mysql->real_escape_string($row['problem_description']) . "', 
                          '" . $mysql->real_escape_string($row['telegram']) . "', 
                          '" . $mysql->real_escape_string($row['status']) . "', 
                          '" . $mysql->real_escape_string($row['created_at']) . "', 
                          '" . $mysql->real_escape_string($row['updated_at']) . "')");
}

echo "Migration completed successfully!\n";
