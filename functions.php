<?php
// Database connection
function connectDB() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "study_crew";
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// User authentication functions
function loginUser($email, $password) {
    $conn = connectDB();
    
    // Sanitize inputs
    $email = $conn->real_escape_string($email);
    
    // Query database
    $sql = "SELECT id, username, email, password FROM users WHERE email = '$email' OR username = '$email'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Start session and set user data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            
            return true;
        }
    }
    
    return false;
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to register new users
function registerUser($username, $email, $password, $year) {
    $conn = connectDB();
    
    // Sanitize inputs
    $username = $conn->real_escape_string($username);
    $email = $conn->real_escape_string($email);
    $year = $conn->real_escape_string($year);
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if email or username already exists
    $checkSql = "SELECT id FROM users WHERE email = '$email' OR username = '$username'";
    $result = $conn->query($checkSql);
    
    if ($result->num_rows > 0) {
        return false; // Email or username already exists
    }
    
    // Insert new user
    $sql = "INSERT INTO users (username, email, password, academic_year, created_at) VALUES ('$username', '$email', '$hashedPassword', '$year', NOW())";
    
    if ($conn->query($sql) === TRUE) {
        return true;
    } else {
        return false;
    }
}

// Function to get assistance requests
function getAssistanceRequests() {
    $conn = connectDB();
    
    $sql = "SELECT * FROM assistance_requests ORDER BY created_at DESC";
    $result = $conn->query($sql);
    
    $requests = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
    }
    
    return $requests;
}

// Function to submit assistance request
function submitAssistanceRequest($userId, $subject, $description) {
    $conn = connectDB();
    
    // Sanitize inputs
    $userId = (int)$userId;
    $subject = $conn->real_escape_string($subject);
    $description = $conn->real_escape_string($description);
    
    $sql = "INSERT INTO assistance_requests (user_id, subject, description, created_at) 
            VALUES ($userId, '$subject', '$description', NOW())";
    
    return $conn->query($sql) === TRUE;
}
?>