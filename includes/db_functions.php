<?php
/**
 * Database Functions
 * 
 * Contains all database-related functions for the Study Crew application
 */

/**
 * Get user by ID
 */
function getUserById($userId) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get user by email
 */
function getUserByEmail($email) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Create a new user
 */
function createUser($userData) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("
        INSERT INTO users (username, full_name, email, password, academic_year, roles, bio, telegram, phone)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    // Handle roles - ensure it's a valid JSON array
    $roles = '["student"]'; // Default role
    if (isset($userData['roles'])) {
        if (is_array($userData['roles'])) {
            // If it's already an array, encode it properly
            $roles = json_encode($userData['roles']);
        } elseif (is_string($userData['roles']) && json_decode($userData['roles']) !== null) {
            // If it's a valid JSON string, use it as is
            $roles = $userData['roles'];
        } else {
            // If it's a simple string, wrap it in an array and encode
            $roles = json_encode([$userData['roles']]);
        }
    }
    
    $success = $stmt->execute([
        $userData['username'],
        $userData['full_name'] ?? null,
        $userData['email'],
        $userData['password'],
        $userData['academic_year'] ?? null,
        $roles,
        $userData['bio'] ?? null,
        $userData['telegram'] ?? null,
        $userData['phone'] ?? null
    ]);
    
    return $success ? $conn->lastInsertId() : false;
}

/**
 * Update user data
 */
function updateUser($userId, $userData) {
    $conn = getDbConnection();
    $updates = [];
    $params = [];
    
    $allowedFields = ['username', 'full_name', 'email', 'password', 'academic_year', 'bio', 'telegram', 'phone', 'roles'];
    
    foreach ($userData as $field => $value) {
        if (in_array($field, $allowedFields)) {
            // Special handling for roles to ensure proper JSON format
            if ($field === 'roles') {
                if (is_array($value)) {
                    $value = json_encode($value);
                } elseif (is_string($value) && json_decode($value) === null) {
                    // If it's a string but not valid JSON, wrap it in an array
                    $value = json_encode([$value]);
                }
                // If it's already valid JSON, use it as is
            }
            
            $updates[] = "$field = ?";
            $params[] = $value;
        }
    }
    
    if (empty($updates)) {
        return false;
    }
    
    $params[] = $userId;
    $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    return $stmt->execute($params);
}

/**
 * Verify user credentials
 */
function verifyUser($email, $password) {
    $user = getUserByEmail($email);
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

/**
 * Get assistant by user ID
 */
function getAssistantByUserId($userId) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM assistants WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get assistant profile by user ID
 * 
 * @param int $userId The ID of the user
 * @return array|null The assistant data or null if not found
 */
function getAssistantProfile($userId) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("
        SELECT a.*, u.username, u.email, u.full_name 
        FROM assistants a
        JOIN users u ON a.user_id = u.id
        WHERE a.user_id = ?
    ");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get courses by filters
 */
function getCourses($filters = []) {
    $conn = getDbConnection();
    $where = [];
    $params = [];
    
    if (!empty($filters['year'])) {
        $where[] = "year = ?";
        $params[] = $filters['year'];
    }
    
    if (!empty($filters['semester'])) {
        $where[] = "semester = ?";
        $params[] = $filters['semester'];
    }
    
    if (!empty($filters['search'])) {
        $where[] = "(name LIKE ? OR code LIKE ?)";
        $searchTerm = "%{$filters['search']}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $sql = "SELECT * FROM courses";
    if (!empty($where)) {
        $sql .= " WHERE " . implode(' AND ', $where);
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get course by ID
 */
/**
 * Get course by ID
 * 
 * @param string|int $courseId The ID of the course to retrieve
 * @return array|null The course data or null if not found
 */
function getCourseById($courseId) {
    // Ensure consistent string comparison
    $courseId = is_string($courseId) ? trim($courseId) : $courseId;
    
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$courseId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Update assistant's course assignments
 * 
 * @param int $assistantId The ID of the assistant
 * @param array $courseIds Array of course IDs to assign to the assistant
 * @return bool True on success, false on failure
 */
function updateAssistantCourses($assistantId, $courseIds) {
    $conn = getDbConnection();
    
    try {
        $conn->beginTransaction();
        
        // Remove existing courses
        $stmt = $conn->prepare("DELETE FROM assistant_courses WHERE assistant_id = ?");
        $stmt->execute([$assistantId]);
        
        // Add new courses
        if (!empty($courseIds)) {
            $values = [];
            $params = [];
            foreach ($courseIds as $courseId) {
                $values[] = "(?, ?)";
                $params[] = $assistantId;
                $params[] = $courseId;
            }
            
            $sql = "INSERT INTO assistant_courses (assistant_id, course_id) VALUES " . implode(', ', $values);
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
        }
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Error updating assistant courses: " . $e->getMessage());
        return false;
    }
}

/**
 * Get assistants by course
 */
function getAssistantsByCourse($courseId, $filters = []) {
    $conn = getDbConnection();
    $params = [$courseId];
    
    $sql = "
        SELECT u.*, a.*, GROUP_CONCAT(DISTINCT c.name) as course_names
        FROM users u
        JOIN assistants a ON u.id = a.user_id
        JOIN assistant_courses ac ON a.id = ac.assistant_id
        JOIN courses c ON ac.course_id = c.id
        WHERE ac.course_id = ?
    ";
    
    if (!empty($filters['search'])) {
        $sql .= " AND (u.full_name LIKE ? OR u.bio LIKE ?)";
        $searchTerm = "%{$filters['search']}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $sql .= " GROUP BY u.id";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Create a connection request
 */
function createConnectionRequest($data) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("
        INSERT INTO connections (user_id, assistant_id, course_id, problem_description, telegram, status)
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");
    
    return $stmt->execute([
        $data['user_id'],
        $data['assistant_id'],
        $data['course_id'],
        $data['problem_description'],
        $data['telegram'] ?? null
    ]);
}

/**
 * Get user's connections
 */
function getUserConnections($userId, $role = 'student') {
    $conn = getDbConnection();
    
    if ($role === 'student') {
        $sql = "
            SELECT c.*, u.full_name as assistant_name, crs.name as course_name, crs.code as course_code
            FROM connections c
            JOIN users u ON c.assistant_id = u.id
            JOIN courses crs ON c.course_id = crs.id
            WHERE c.user_id = ?
            ORDER BY c.created_at DESC
        ";
    } else {
        $sql = "
            SELECT c.*, u.full_name as student_name, crs.name as course_name, crs.code as course_code
            FROM connections c
            JOIN users u ON c.user_id = u.id
            JOIN courses crs ON c.course_id = crs.id
            WHERE c.assistant_id = ?
            ORDER BY c.created_at DESC
        ";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Update connection status
 */
function updateConnectionStatus($connectionId, $status, $assistantId = null) {
    $conn = getDbConnection();
    $sql = "UPDATE connections SET status = ? WHERE id = ?";
    $params = [$status, $connectionId];
    
    if ($assistantId) {
        $sql .= " AND assistant_id = ?";
        $params[] = $assistantId;
    }
    
    $stmt = $conn->prepare($sql);
    return $stmt->execute($params);
}
