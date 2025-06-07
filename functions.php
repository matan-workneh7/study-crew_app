<?php
require_once 'config.php';

// Database connection
function getDBConnection() {
    $conn = new SQLite3(SQLITE_DB_PATH);
    if (!$conn) {
        die("Connection failed: " . $conn->lastErrorMsg());
    }
    return $conn;
}

// User authentication functions
function loginUser($username, $password) {
    $conn = getDBConnection();
    $stmt = $conn->prepare('SELECT id, username, password FROM users WHERE username = :username');
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        return true;
    }
    return false;
}

function registerUser($username, $email, $password, $academic_year) {
    $conn = getDBConnection();
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('INSERT INTO users (username, email, password, academic_year, created_at) VALUES (:username, :email, :password, :academic_year, datetime("now"))');
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $stmt->bindValue(':email', $email, SQLITE3_TEXT);
    $stmt->bindValue(':password', $hashed_password, SQLITE3_TEXT);
    $stmt->bindValue(':academic_year', $academic_year, SQLITE3_TEXT);
    return $stmt->execute() !== false;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    $conn = getDBConnection();
    $user_id = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare('SELECT id, username, email, academic_year FROM users WHERE id = :id');
    $stmt->bindValue(':id', $user_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    return $result ? $result->fetchArray(SQLITE3_ASSOC) : null;
}

// Course functions
function getAllCourses() {
    $conn = getDBConnection();
    $query = "SELECT * FROM courses ORDER BY year, semester, name";
    $result = $conn->query($query);
    $courses = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $courses[] = $row;
    }
    return $courses;
}

function getCourseById($course_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare('SELECT * FROM courses WHERE id = :id');
    $stmt->bindValue(':id', $course_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    return $result ? $result->fetchArray(SQLITE3_ASSOC) : null;
}

// Assistant functions
function registerAsAssistant($user_id, $course_id, $telegram, $phone, $other_info) {
    $conn = getDBConnection();
    $stmt = $conn->prepare('INSERT INTO assistants (user_id, course_id, telegram, phone, other_info, created_at) VALUES (:user_id, :course_id, :telegram, :phone, :other_info, datetime("now"))');
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $stmt->bindValue(':course_id', $course_id, SQLITE3_INTEGER);
    $stmt->bindValue(':telegram', $telegram, SQLITE3_TEXT);
    $stmt->bindValue(':phone', $phone, SQLITE3_TEXT);
    $stmt->bindValue(':other_info', $other_info, SQLITE3_TEXT);
    return $stmt->execute() !== false;
}

function getAssistantsForCourse($course_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare('SELECT a.*, u.username, u.academic_year FROM assistants a JOIN users u ON a.user_id = u.id WHERE a.course_id = :course_id ORDER BY a.visits DESC');
    $stmt->bindValue(':course_id', $course_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $assistants = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $assistants[] = $row;
    }
    return $assistants;
}

// Connection functions
function createConnection($user_id, $assistant_id, $course_id, $problem_description, $telegram) {
    $conn = getDBConnection();
    $stmt = $conn->prepare('INSERT INTO connections (user_id, assistant_id, course_id, problem_description, telegram, created_at) VALUES (:user_id, :assistant_id, :course_id, :problem_description, :telegram, datetime("now"))');
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $stmt->bindValue(':assistant_id', $assistant_id, SQLITE3_INTEGER);
    $stmt->bindValue(':course_id', $course_id, SQLITE3_INTEGER);
    $stmt->bindValue(':problem_description', $problem_description, SQLITE3_TEXT);
    $stmt->bindValue(':telegram', $telegram, SQLITE3_TEXT);
    return $stmt->execute() !== false;
}

function updateConnectionStatus($connection_id, $status) {
    $conn = getDBConnection();
    $stmt = $conn->prepare('UPDATE connections SET status = :status WHERE id = :id');
    $stmt->bindValue(':status', $status, SQLITE3_TEXT);
    $stmt->bindValue(':id', $connection_id, SQLITE3_INTEGER);
    return $stmt->execute() !== false;
}

function getUserConnections($user_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare('SELECT c.*, co.name as course_name, u.username as assistant_name FROM connections c JOIN courses co ON c.course_id = co.id JOIN assistants a ON c.assistant_id = a.id JOIN users u ON a.user_id = u.id WHERE c.user_id = :user_id ORDER BY c.created_at DESC');
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $connections = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $connections[] = $row;
    }
    return $connections;
}

function getAssistantConnections($assistant_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare('SELECT c.*, co.name as course_name, u.username as student_name FROM connections c JOIN courses co ON c.course_id = co.id JOIN users u ON c.user_id = u.id WHERE c.assistant_id = :assistant_id ORDER BY c.created_at DESC');
    $stmt->bindValue(':assistant_id', $assistant_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $connections = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $connections[] = $row;
    }
    return $connections;
}

// Process login with intent handling
function processLogin($email, $password, $assistIntent = '') {
    if(loginUser($email, $password)) {
        if($assistIntent === 'assist') {
            header("Location: assistant-form.php");
            exit();
        } else if($assistIntent === 'get') {
            header("Location: courses.php");
            exit();
        } else {
            header("Location: index.php");
            exit();
        }
    } else {
        return "Invalid email or password";
    }
}

// Function to get user data
function getUserData($userId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare('SELECT * FROM users WHERE id = :id');
    $stmt->bindValue(':id', $userId, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $userData = $result ? $result->fetchArray(SQLITE3_ASSOC) : null;
    return $userData;
}

// Function to get courses by year and semester
function getCoursesByYearAndSemester($year, $semester) {
    $conn = getDBConnection();
    $stmt = $conn->prepare('SELECT * FROM courses WHERE year = :year AND semester = :semester ORDER BY name');
    $stmt->bindValue(':year', $year, SQLITE3_INTEGER);
    $stmt->bindValue(':semester', $semester, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $courses = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $courses[] = $row;
    }
    return $courses;
}

// Function to get tutors by course with search
function getTutorsByCourse($courseId, $searchQuery = '') {
    $conn = getDBConnection();
    $sql = 'SELECT a.id, u.username as name, COALESCE(COUNT(c.id), 0) as visits FROM assistants a JOIN users u ON a.user_id = u.id LEFT JOIN connections c ON a.id = c.assistant_id WHERE a.course_id = :courseId';
    if (!empty($searchQuery)) {
        $sql .= ' AND u.username LIKE :searchQuery';
    }
    $sql .= ' GROUP BY a.id, u.username';
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':courseId', $courseId, SQLITE3_INTEGER);
    if (!empty($searchQuery)) {
        $stmt->bindValue(':searchQuery', "%" . $searchQuery . "%", SQLITE3_TEXT);
    }
    $result = $stmt->execute();
    $tutors = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $tutors[] = $row;
    }
    return $tutors;
}

// Function to get tutor by ID
function getTutorById($tutorId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare('SELECT a.*, u.username as name FROM assistants a JOIN users u ON a.user_id = u.id WHERE a.id = :id');
    $stmt->bindValue(':id', $tutorId, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $tutor = $result ? $result->fetchArray(SQLITE3_ASSOC) : null;
    return $tutor;
}

// Function to save connection request
function saveConnectionRequest($userId, $tutorId, $courseId, $problemDescription, $telegram = '') {
    $conn = getDBConnection();
    $stmt = $conn->prepare('INSERT INTO connections (user_id, assistant_id, course_id, problem_description, telegram, created_at) VALUES (:user_id, :assistant_id, :course_id, :problem_description, :telegram, datetime("now"))');
    $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
    $stmt->bindValue(':assistant_id', $tutorId, SQLITE3_INTEGER);
    $stmt->bindValue(':course_id', $courseId, SQLITE3_INTEGER);
    $stmt->bindValue(':problem_description', $problemDescription, SQLITE3_TEXT);
    $stmt->bindValue(':telegram', $telegram, SQLITE3_TEXT);
    $success = $stmt->execute() !== false;
    return $success;
}

// Function to increment tutor visits
function incrementTutorVisits($tutorId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare('UPDATE assistants SET visits = visits + 1 WHERE id = :id');
    $stmt->bindValue(':id', $tutorId, SQLITE3_INTEGER);
    $success = $stmt->execute() !== false;
    return $success;
}

// Function to save assistant application
function saveAssistantApplication($userId, $courseId, $telegram = '', $phone = '', $otherInfo = '') {
    $conn = getDBConnection();
    // Check if user already applied for this course
    $check_stmt = $conn->prepare('SELECT id FROM assistants WHERE user_id = :user_id AND course_id = :course_id');
    $check_stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
    $check_stmt->bindValue(':course_id', $courseId, SQLITE3_INTEGER);
    $result = $check_stmt->execute();
    if ($result->fetchArray(SQLITE3_ASSOC)) {
        // Update existing application
        $stmt = $conn->prepare('UPDATE assistants SET telegram = :telegram, phone = :phone, other_info = :otherInfo, updated_at = datetime("now") WHERE user_id = :user_id AND course_id = :course_id');
        $stmt->bindValue(':telegram', $telegram, SQLITE3_TEXT);
        $stmt->bindValue(':phone', $phone, SQLITE3_TEXT);
        $stmt->bindValue(':otherInfo', $otherInfo, SQLITE3_TEXT);
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $stmt->bindValue(':course_id', $courseId, SQLITE3_INTEGER);
    } else {
        // Insert new application
        $stmt = $conn->prepare('INSERT INTO assistants (user_id, course_id, telegram, phone, other_info, visits, created_at) VALUES (:user_id, :course_id, :telegram, :phone, :otherInfo, 0, datetime("now"))');
        $stmt->bindValue(':user_id', $userId, SQLITE3_INTEGER);
        $stmt->bindValue(':course_id', $courseId, SQLITE3_INTEGER);
        $stmt->bindValue(':telegram', $telegram, SQLITE3_TEXT);
        $stmt->bindValue(':phone', $phone, SQLITE3_TEXT);
        $stmt->bindValue(':otherInfo', $otherInfo, SQLITE3_TEXT);
    }
    $success = $stmt->execute() !== false;
    return $success;
}

// Function to get year name
function getYearName($year) {
    $yearNames = [
        1 => 'First Year',
        2 => 'Second Year',
        3 => 'Third Year',
        4 => 'Fourth Year',
        5 => 'Fifth Year'
    ];
    
    return $yearNames[$year] ?? 'Unknown Year';
}

// Function to get course icon
function getCourseIcon($category) {
    $icons = [
        'math' => '📊',
        'programming' => '💻',
        'language' => '📝',
        'science' => '🔬',
        'humanities' => '📚',
        'business' => '💼',
        'engineering' => '⚙️',
        'arts' => '🎨',
        'geography' => '🌍'
    ];
    
    return $icons[$category] ?? '📚';
}
?>