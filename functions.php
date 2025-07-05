<?php
// Include database configuration and functions
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db_functions.php';

// Import necessary functions from db_functions
if (!function_exists('getAssistantByUserId')) {
    function getAssistantByUserId($userId) {
        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT * FROM assistants WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// User authentication functions
function loginUser($email, $password) {
    $user = verifyUser($email, $password);
    
    if ($user) {
        // Start session and set user data
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        
        // Set user roles
        $roles = json_decode($user['roles'], true) ?: [];
        $_SESSION['roles'] = $roles;
        $_SESSION['user_role'] = !empty($roles) ? $roles[0] : 'student';
        
        return true;
    }
    
    return false;
}

// Function to check if user is an assistant
function isUserAssistant($userId) {
    return (bool)getAssistantByUserId($userId);
}

// Process login with intent handling (multi-role)
function processLogin($email, $password, $intent = '') {
    // Map legacy 'get' intent to 'student' for backward compatibility
    if ($intent === 'get') {
        $intent = 'student';
    }
    
    // Verify user credentials against the database
    $user = verifyUser($email, $password);
    
    if (!$user) {
        return "Invalid email or password";
    }
    
    // Get raw roles from database
    $rolesJson = $user['roles'] ?? '[]';
    
    // Debug: Log raw roles from database
    error_log('Raw roles from DB: ' . $rolesJson);
    
    // Decode JSON string to array
    $roles = json_decode($rolesJson, true);
    
    // If json_decode failed or didn't return an array, handle the error
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($roles)) {
        error_log('Error decoding roles JSON: ' . json_last_error_msg());
        // If we have a string that's not valid JSON, use it as a single role
        $roles = is_string($rolesJson) ? [$rolesJson] : ['student'];
    }
    
    // Ensure all roles are strings and trim whitespace
    $roles = array_map('trim', array_map('strval', $roles));
    
    // Debug: Log parsed roles
    error_log('Parsed roles: ' . print_r($roles, true));
    error_log('Login intent: ' . $intent);
    
    // If no roles found, set default role based on intent
    if (empty($roles)) {
        $defaultRole = $intent ?: 'student';
        $roles = [$defaultRole];
        // Update user with default role
        updateUser($user['id'], ['roles' => json_encode($roles)]);
        error_log('Assigned default role: ' . $defaultRole);
    }

    // If no intent specified, use first role
    if (empty($intent)) {
        $intent = $roles[0] ?? 'student';
        error_log('No intent specified, using: ' . $intent);
    }
    
    // Clean up the intent
    $intent = trim((string)$intent);
    
    // Debug: Log final values before comparison
    error_log('Final intent: ' . $intent);
    error_log('Final roles to check: ' . print_r($roles, true));
    
    // Check if user has the requested role (case-insensitive comparison)
    $hasRole = false;
    foreach ($roles as $role) {
        if (strtolower(trim($role)) === strtolower($intent)) {
            $hasRole = true;
            $intent = $role; // Use the original case from the database
            break;
        }
    }
    
    if (!$hasRole) {
        error_log('Login failed - User does not have role: ' . $intent);
        error_log('User roles: ' . print_r($roles, true));
        return "This account doesn't have the requested role. Please select a different role or contact support.";
    }
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['user_role'] = $intent;
    $_SESSION['roles'] = $roles;
    
    // Update last login
    updateUser($user['id'], ['last_login' => date('Y-m-d H:i:s')]);
    
    return true;
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to register new users (multi-role support)
function registerUser($username, $email, $password, $year, $user_role = 'student') {
    // Check if user already exists
    $existingUser = getUserByEmail($email);
    
    if ($existingUser) {
        // If user exists, add new role if it doesn't exist
        $roles = json_decode($existingUser['roles'], true) ?: [];
        
        if (in_array($user_role, $roles)) {
            return "You already have this role.";
        }
        
        // Add the new role
        $roles[] = $user_role;
        
        // Update user with new roles
        $success = updateUser($existingUser['id'], [
            'roles' => json_encode($roles),
            'academic_year' => $year
        ]);
        
        if (!$success) {
            return "Failed to update user roles. Please try again.";
        }
        
        // If registering as assistant, create assistant profile if it doesn't exist
        if ($user_role === 'assist') {
            $assistant = getAssistantByUserId($existingUser['id']);
            if (!$assistant) {
                saveAssistantProfile([
                    'user_id' => $existingUser['id'],
                    'telegram' => '',
                    'phone' => '',
                    'bio' => 'New assistant',
                    'availability' => 'Available during weekdays'
                ]);
            }
        }
        
        return true;
    }
    
    // Check if username is already taken
    $stmt = getDbConnection()->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        return "Username already taken.";
    }
    
    // Create new user
    $userData = [
        'username' => $username,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'academic_year' => $year,
        'roles' => json_encode([$user_role])
    ];
    
    $userId = createUser($userData);
    
    if (!$userId) {
        return "Failed to create user. Please try again.";
    }
    
    // If registering as assistant, create assistant profile
    if ($user_role === 'assist') {
        saveAssistantProfile([
            'user_id' => $userId,
            'telegram' => '',
            'phone' => '',
            'bio' => 'New assistant',
            'availability' => 'Available during weekdays'
        ]);
    }
    
    return true;
}


// Function to get user data
function getUserData($userId) {
    $stmt = getDbConnection()->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Ensure roles is always an array
        $user['roles'] = json_decode($user['roles'] ?? '[]', true);
    }
    
    return $user ?: null;
}

// Function to get courses by year and semester
function getCoursesByYearAndSemester($year, $semester) {
    $conn = getDbConnection();
    
    // Map semester name to number if needed
    $semesterMap = [
        'fall' => 1,
        'spring' => 2,
        'summer' => 3
    ];
    
    $semesterNum = is_numeric($semester) ? (int)$semester : ($semesterMap[strtolower($semester)] ?? 0);
    
    // Map numeric year to year name if needed
    $yearMap = [
        1 => 'Freshman',
        2 => 'Sophomore',
        3 => 'Junior',
        4 => 'Senior',
        5 => 'Graduate'
    ];
    
    // If year is numeric, convert to year name
    $yearName = is_numeric($year) ? ($yearMap[$year] ?? '') : $year;
    
    $sql = "
        SELECT * FROM courses 
        WHERE (year = ? OR year = ? OR year = 'all')
        AND (semester = ? OR semester = 0 OR semester IS NULL)
        ORDER BY name
    ";
    
    $stmt = $conn->prepare($sql);
    
    // Log the query and parameters for debugging
    error_log("SQL Query: $sql");
    error_log("Parameters: year=$year, yearName=$yearName, semesterNum=$semesterNum");
    
    try {
        $stmt->execute([$year, $yearName, $semesterNum]);
        error_log("Query executed successfully");
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return [];
    }
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add default category if not set
    foreach ($results as &$course) {
        if (!isset($course['category']) || empty($course['category'])) {
            // Try to determine category from course code or name
            $courseCode = strtolower($course['code'] ?? '');
            $courseName = strtolower($course['name'] ?? '');
            
            if (strpos($courseCode, 'cs') === 0 || 
                strpos($courseName, 'computer') !== false || 
                strpos($courseName, 'programming') !== false) {
                $course['category'] = 'computer';
            } elseif (strpos($courseCode, 'math') === 0 || strpos($courseName, 'math') !== false) {
                $course['category'] = 'math';
            } elseif (strpos($courseCode, 'phy') === 0 || strpos($courseName, 'physics') !== false) {
                $course['category'] = 'physics';
            } elseif (strpos($courseCode, 'chem') === 0 || strpos($courseName, 'chemistry') !== false) {
                $course['category'] = 'chemistry';
            } else {
                $course['category'] = 'default';
            }
        }
    }
    unset($course); // Break the reference
    
    // Log the results for debugging
    error_log("Found courses: " . print_r($results, true));
    
    return $results;
}

// Function to get all courses
function getAllCourses() {
    $conn = getDbConnection();
    $stmt = $conn->query("SELECT * FROM courses ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Import getCourseById from db_functions
if (!function_exists('getCourseById')) {
    function getCourseById($courseId) {
        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$courseId]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
            
        if (!$course) {
            error_log('Course not found - ID: ' . $courseId . ' (Type: ' . gettype($courseId) . ')' );
            return null;
        }
        
        return $course;
    }
}

// Function to get courses for assistant based on their academic year
function getCoursesForAssistant($userYearValue) {
    $conn = getDbConnection();
    
    // Define year values for comparison
    $yearValues = [
        'Freshman' => 1,
        'Sophomore' => 2,
        'Junior' => 3,
        'Senior' => 4,
        'Graduate' => 5
    ];
    
    // Get all courses first (without category since it doesn't exist in the database yet)
    $stmt = $conn->query("SELECT id, name, code, year, semester, credit_hours, description FROM courses ORDER BY year, semester, name");
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add a default category to each course
    foreach ($courses as &$course) {
        // Determine category based on course code or name if needed
        $courseCode = strtoupper($course['code']);
        if (strpos($courseCode, 'CS') === 0 || strpos($course['name'], 'Computer') !== false) {
            $course['category'] = 'computer';
        } elseif (strpos($courseCode, 'MATH') === 0 || strpos($course['name'], 'Math') !== false) {
            $course['category'] = 'math';
        } else {
            $course['category'] = 'default'; // Default category
        }
    }
    unset($course); // Break the reference
    
    // Filter courses based on year
    $filteredCourses = array_filter($courses, function($course) use ($userYearValue, $yearValues) {
        $courseYear = $course['year'];
        $courseYearValue = is_numeric($courseYear) ? (int)$courseYear : ($yearValues[$courseYear] ?? 0);
        
        // Assistants can only assist courses from years below their current year
        return $courseYearValue < $userYearValue;
    });
    
    // Re-index array
    return array_values($filteredCourses);
}

// Function to get courses the user is assisting
function getAssistantCourses($userId) {
    $assistant = getAssistantByUserId($userId);
    
    if (!$assistant || empty($assistant['course_ids'])) {
        return [];
    }
    
    // Get full course details for each course ID
    return getCoursesByIds($assistant['course_ids']);
}

// Function to save assistant profile with multiple courses
function saveAssistantProfile($profileData) {
    // Extract data from array
    $userId = $profileData['user_id'];
    $selectedCourses = $profileData['selected_courses'] ?? [];
    $telegram = $profileData['telegram'] ?? '';
    $phone = $profileData['phone'] ?? '';
    $bio = $profileData['bio'] ?? '';
    $availability = $profileData['availability'] ?? '';
    $conn = getDbConnection();
    
    try {
        $conn->beginTransaction();
        
        // Check if assistant profile already exists
        $assistant = getAssistantByUserId($userId);
        
        if ($assistant) {
            // Update existing assistant
            $stmt = $conn->prepare("
                UPDATE assistants 
                SET telegram = ?, phone = ?, bio = ?, availability = ?
                WHERE user_id = ?
            ");
            $stmt->execute([$telegram, $phone, $bio, $availability, $userId]);
            $assistantId = $assistant['id'];
        } else {
            // Create new assistant
            $stmt = $conn->prepare("
                INSERT INTO assistants (user_id, telegram, phone, bio, availability, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$userId, $telegram, $phone, $bio, $availability]);
            $assistantId = $conn->lastInsertId();
        }
        
        // Update assistant's courses
        // First, remove all existing course associations
        $stmt = $conn->prepare("DELETE FROM assistant_courses WHERE assistant_id = ?");
        $stmt->execute([$assistantId]);
        
        // Then add the new ones
        if (!empty($selectedCourses)) {
            $stmt = $conn->prepare("
                INSERT INTO assistant_courses (assistant_id, course_id, created_at)
                VALUES (?, ?, NOW())
            ");
            
            foreach ($selectedCourses as $courseId) {
                $stmt->execute([$assistantId, $courseId]);
            }
        }
        
        $conn->commit();
        return true;
        
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Error saving assistant profile: " . $e->getMessage());
        return false;
    }
}

// Function to get tutors by course with search
function getTutorsByCourse($courseId, $searchQuery = '') {
    $conn = getDbConnection();
    
    $params = [$courseId];
    $searchCondition = '';
    
    // Add search condition if query is provided
    if (!empty($searchQuery)) {
        $searchTerm = "%" . strtolower($searchQuery) . "%";
        $searchCondition = " AND (LOWER(u.username) LIKE ? OR LOWER(u.email) LIKE ? OR LOWER(a.bio) LIKE ?)";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
    }
    
    $sql = "
        SELECT 
            a.id, 
            a.user_id, 
            u.username, 
            u.email, 
            a.telegram, 
            a.phone, 
            a.bio, 
            a.availability, 
            a.visits, 
            a.created_at
        FROM assistants a
        JOIN users u ON a.user_id = u.id
        JOIN assistant_courses ac ON a.id = ac.assistant_id
        WHERE ac.course_id = ?
        $searchCondition
        ORDER BY a.visits DESC, u.username ASC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $result;
}

// Function to get tutor by ID
function getTutorById($tutorId) {
    $conn = getDbConnection();
    
    $stmt = $conn->prepare("
        SELECT 
            a.*, 
            u.username, 
            u.email,
            u.academic_year,
            GROUP_CONCAT(ac.course_id) as course_ids
        FROM assistants a
        JOIN users u ON a.user_id = u.id
        LEFT JOIN assistant_courses ac ON a.id = ac.assistant_id
        WHERE a.id = ?
        GROUP BY a.id
    ");
    
    $stmt->execute([$tutorId]);
    $tutor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($tutor) {
        // Convert course_ids string to array
        $tutor['course_ids'] = !empty($tutor['course_ids']) 
            ? explode(',', $tutor['course_ids']) 
            : [];
    }
    
    return $tutor ?: null;
}

// Function to save connection request
function saveConnectionRequest($userId, $tutorId, $courseId, $problemDescription, $telegram = '') {
    $conn = getDbConnection();
    
    try {
        $conn->beginTransaction();
        
        // Get tutor and course details (for validation)
        $tutor = getTutorById($tutorId);
        $course = getCourseById($courseId);
        $user = getUserData($userId);
        
        if (!$tutor || !$course || !$user) {
            return false;
        }
        
        // Create new connection
        $stmt = $conn->prepare("
            INSERT INTO connections (
                student_id, 
                assistant_id, 
                course_id, 
                problem_description, 
                telegram, 
                status, 
                created_at
            ) VALUES (?, ?, ?, ?, ?, 'pending', NOW())
        ");
        
        $success = $stmt->execute([
            $userId,
            $tutorId,
            $courseId,
            $problemDescription,
            $telegram
        ]);
        
        if ($success) {
            // Increment tutor visits
            $updateStmt = $conn->prepare("
                UPDATE assistants 
                SET visits = visits + 1 
                WHERE id = ?
            ");
            $updateStmt->execute([$tutorId]);
            
            $conn->commit();
            return true;
        }
        
        $conn->rollBack();
        return false;
        
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("Error saving connection request: " . $e->getMessage());
        return false;
    }
}

// Function to increment tutor visits
function incrementTutorVisits($tutorId) {
    $conn = getDbConnection();
    
    try {
        $stmt = $conn->prepare("
            UPDATE assistants 
            SET visits = COALESCE(visits, 0) + 1 
            WHERE id = ?
        ");
        
        return $stmt->execute([$tutorId]);
        
    } catch (Exception $e) {
        error_log("Error incrementing tutor visits: " . $e->getMessage());
        return false;
    }
}

/**
 * Save assistant application to the database
 * @param int $userId User ID applying to be an assistant
 * @param string $courseId Course ID the user wants to assist with
 * @param string $telegram Telegram username (optional)
 * @param string $phone Phone number (optional)
 * @param string $otherInfo Additional information (optional)
 * @return bool|string True on success, error message on failure
 */
function saveAssistantApplication($userId, $courseId, $telegram = '', $phone = '', $otherInfo = '') {
    $conn = getDbConnection();
    
    try {
        // Start transaction
        $conn->beginTransaction();
        
        // Check if application already exists
        $checkStmt = $conn->prepare(
            "SELECT id FROM assistant_applications 
             WHERE user_id = ? AND course_id = ?"
        );
        $checkStmt->execute([$userId, $courseId]);
        
        if ($checkStmt->fetch()) {
            $conn->rollBack();
            return "You have already applied for this course.";
        }
        
        // Create new application
        $insertStmt = $conn->prepare(
            "INSERT INTO assistant_applications (
                user_id, 
                course_id, 
                telegram, 
                phone, 
                other_info,
                status
            ) VALUES (?, ?, ?, ?, ?, 'pending')"
        );
        
        $success = $insertStmt->execute([
            $userId,
            $courseId,
            $telegram,
            $phone,
            $otherInfo
        ]);
        
        if ($success) {
            $conn->commit();
            return true;
        } else {
            $conn->rollBack();
            return "Failed to save application. Please try again.";
        }
        
    } catch (Exception $e) {
        if (isset($conn) && $conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Error saving assistant application: " . $e->getMessage());
        return "An error occurred while saving your application. Please try again later.";
    }
}

// Function to get year name
function getYearName($year) {
    $yearNames = [
        1 => 'Freshman',
        2 => 'Sophomore',
        3 => 'Junior',
        4 => 'Senior',
        5 => 'Graduate'
    ];
    
    // If year is a string that matches a name, return it as is
    if (is_string($year) && in_array(ucfirst(strtolower($year)), $yearNames)) {
        return ucfirst(strtolower($year));
    }
    
    // If year is numeric, return the corresponding name
    if (is_numeric($year)) {
        return $yearNames[(int)$year] ?? 'Unknown';
    }
    
    return 'Unknown';
}

// Function to get course icon
function getCourseIcon($category = '') {
    $icons = [
        'math' => 'fas fa-calculator',
        'science' => 'fas fa-flask',
        'computer' => 'fas fa-laptop-code',
        'engineering' => 'fas fa-cogs',
        'business' => 'fas fa-chart-line',
        'humanities' => 'fas fa-book',
        'social' => 'fas fa-users',
        'language' => 'fas fa-language',
        'cs' => 'fas fa-laptop-code',
        'default' => 'fas fa-book-open',
        'art' => 'fas fa-palette',
        'music' => 'fas fa-music',
        'health' => 'fas fa-heartbeat',
        'sports' => 'fas fa-running',
        'history' => 'fas fa-landmark',
        'geography' => 'fas fa-globe-americas',
        'physics' => 'fas fa-atom',
        'chemistry' => 'fas fa-vial',
        'biology' => 'fas fa-dna',
        'literature' => 'fas fa-book-reader',
        'philosophy' => 'fas fa-brain',
        'psychology' => 'fas fa-brain',
        'sociology' => 'fas fa-users',
        'economics' => 'fas fa-chart-line',
        'politics' => 'fas fa-landmark',
        'law' => 'fas fa-gavel',
        'medicine' => 'fas fa-heartbeat',
        'nursing' => 'fas fa-user-nurse',
        'pharmacy' => 'fas fa-pills',
        'dentistry' => 'fas fa-tooth',
        'veterinary' => 'fas fa-paw',
        'agriculture' => 'fas fa-tractor',
        'architecture' => 'fas fa-archway',
        'design' => 'fas fa-pencil-ruler',
        'fashion' => 'fas fa-tshirt',
        'film' => 'fas fa-film',
        'journalism' => 'fas fa-newspaper',
        'marketing' => 'fas fa-bullhorn',
        'photography' => 'fas fa-camera',
        'public relations' => 'fas fa-comments',
        'theater' => 'fas fa-theater-masks',
        'theology' => 'fas fa-church',
        'urban planning' => 'fas fa-city',
        'women\'s studies' => 'fas fa-female',
        'writing' => 'fas fa-pen-fancy',
    ];
    
    // If category is empty or not set, return default icon
    if (empty($category) || !is_string($category)) {
        return '<i class="' . $icons['default'] . '"></i>';
    }
    
    // Convert to lowercase for case-insensitive matching
    $category = strtolower(trim($category));
    
    // Check if the category exists in our icons array
    if (isset($icons[$category])) {
        return '<i class="' . $icons[$category] . '"></i>';
    }
    
    // Try to find a partial match
    foreach ($icons as $key => $icon) {
        if (strpos($category, $key) !== false) {
            return '<i class="' . $icon . '"></i>';
        }
    }
    
    // If no match found, return default icon
    return '<i class="' . $icons['default'] . '"></i>';
}

/**
 * Get courses by their IDs
 * @param array $courseIds Array of course IDs
 * @return array Array of course data
 */
function getCoursesByIds($courseIds) {
    if (empty($courseIds)) {
        return [];
    }
    
    // Ensure we have an array of non-empty values
    $courseIds = array_filter((array)$courseIds, 'strlen');
    if (empty($courseIds)) {
        return [];
    }
    
    // Create placeholders for the IN clause
    $placeholders = rtrim(str_repeat('?,', count($courseIds)), ',');
    
    $conn = getDbConnection();
    $stmt = $conn->prepare("
        SELECT * FROM courses 
        WHERE id IN ($placeholders)
        ORDER BY name
    ");
    
    // Execute with the original values (PDO will handle parameter binding safely)
    $stmt->execute(array_values($courseIds));
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Add categories to the courses using the same logic as getCoursesForAssistant
    foreach ($courses as &$course) {
        $courseCode = strtoupper($course['code'] ?? '');
        if (strpos($courseCode, 'CS') === 0 || stripos($course['name'] ?? '', 'computer') !== false) {
            $course['category'] = 'computer';
        } elseif (strpos($courseCode, 'MATH') === 0 || stripos($course['name'] ?? '', 'math') !== false) {
            $course['category'] = 'math';
        } elseif (strpos($courseCode, 'ENG') === 0 || stripos($course['name'] ?? '', 'english') !== false) {
            $course['category'] = 'language';
        } elseif (strpos($courseCode, 'PHYS') === 0) {
            $course['category'] = 'physics';
        } elseif (strpos($courseCode, 'CHEM') === 0) {
            $course['category'] = 'chemistry';
        } elseif (strpos($courseCode, 'BIO') === 0) {
            $course['category'] = 'biology';
        } else {
            $course['category'] = 'default';
        }
    }
    unset($course);
    
    return $courses;
}

// Initialize with sample data if database is empty
function initializeSampleData() {
    $conn = getDbConnection();
    
    try {
        // Check if users table is empty
        $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
        $userCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($userCount == 0) {
            // Insert sample users
            $sampleUsers = [
                [
                    'username' => 'admin',
                    'email' => 'admin@example.com',
                    'password' => password_hash('admin123', PASSWORD_DEFAULT),
                    'roles' => json_encode(['admin', 'student', 'assist']),
                    'academic_year' => 'Graduate',
                    'created_at' => date('Y-m-d H:i:s')
                ],
                [
                    'username' => 'student1',
                    'email' => 'student1@example.com',
                    'password' => password_hash('student123', PASSWORD_DEFAULT),
                    'roles' => json_encode(['student']),
                    'academic_year' => 'Sophomore',
                    'created_at' => date('Y-m-d H:i:s')
                ],
                [
                    'username' => 'tutor1',
                    'email' => 'tutor1@example.com',
                    'password' => password_hash('tutor123', PASSWORD_DEFAULT),
                    'roles' => json_encode(['assist', 'student']),
                    'academic_year' => 'Senior',
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ];
            
            $userStmt = $conn->prepare("
                INSERT INTO users (username, email, password, roles, academic_year, created_at)
                VALUES (:username, :email, :password, :roles, :academic_year, :created_at)
            ");
            
            foreach ($sampleUsers as $user) {
                $userStmt->execute($user);
            }
        }
        
        // Check if courses table is empty
        $stmt = $conn->query("SELECT COUNT(*) as count FROM courses");
        $courseCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($courseCount == 0) {
            // Insert sample courses
            $sampleCourses = [
                [
                    'code' => 'MATH101',
                    'name' => 'Introduction to Calculus',
                    'description' => 'Fundamentals of differential and integral calculus.',
                    'year' => 'Freshman',
                    'semester' => 1,
                    'credits' => 4,
                    'category' => 'math'
                ],
                [
                    'code' => 'CS201',
                    'name' => 'Data Structures and Algorithms',
                    'description' => 'Introduction to fundamental data structures and algorithms.',
                    'year' => 'Sophomore',
                    'semester' => 2,
                    'credits' => 3,
                    'category' => 'computer'
                ],
                [
                    'code' => 'PHYS101',
                    'name' => 'Physics I',
                    'description' => 'Classical mechanics, thermodynamics, and waves.',
                    'year' => 'Freshman',
                    'semester' => 1,
                    'credits' => 4,
                    'category' => 'science'
                ]
            ];
            
            $courseStmt = $conn->prepare("
                INSERT INTO courses (code, name, description, year, semester, credits, category)
                VALUES (:code, :name, :description, :year, :semester, :credits, :category)
            ");
            
            foreach ($sampleCourses as $course) {
                $courseStmt->execute($course);
            }
            
            // Get the tutor's user ID
            $tutorId = null;
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = 'tutor1'");
            $stmt->execute();
            $tutor = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($tutor) {
                $tutorId = $tutor['id'];
                
                // Create assistant profile
                $assistantStmt = $conn->prepare("
                    INSERT INTO assistants (
                        user_id, telegram, phone, bio, availability, visits, created_at
                    ) VALUES (
                        :user_id, :telegram, :phone, :bio, :availability, :visits, :created_at
                    )
                ");
                
                $assistantData = [
                    'user_id' => $tutorId,
                    'telegram' => '@tutor1',
                    'phone' => '123-456-7890',
                    'bio' => 'Experienced tutor in math and computer science',
                    'availability' => 'Mon-Wed-Fri, 2pm-6pm',
                    'visits' => 5,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $assistantStmt->execute($assistantData);
                $assistantId = $conn->lastInsertId();
                
                // Assign courses to assistant
                $courseStmt = $conn->prepare("
                    INSERT INTO assistant_courses (assistant_id, course_id, created_at)
                    SELECT :assistant_id, id, NOW() FROM courses WHERE code IN ('MATH101', 'CS201')
                
                ");
                $courseStmt->execute(['assistant_id' => $assistantId]);
            }
        }
        
    } catch (Exception $e) {
        error_log("Error initializing sample data: " . $e->getMessage());
    }
}

// Initialize sample data
initializeSampleData();
?>
