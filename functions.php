<?php
// File paths for data storage
define('USERS_FILE', __DIR__ . '/data/users.json');
define('COURSES_FILE', __DIR__ . '/data/courses.json');
define('ASSISTANTS_FILE', __DIR__ . '/data/assistants.json');
define('CONNECTIONS_FILE', __DIR__ . '/data/connections.json');

// Ensure data directory exists
function ensureDataDirectory() {
    $dataDir = __DIR__ . '/data';
    if (!file_exists($dataDir)) {
        mkdir($dataDir, 0755, true);
    }

    // Create empty files if they don't exist
    $files = [USERS_FILE, COURSES_FILE, ASSISTANTS_FILE, CONNECTIONS_FILE];
    foreach ($files as $file) {
        if (!file_exists($file)) {
            file_put_contents($file, json_encode([]));
        }
    }
}

// Initialize data directory
ensureDataDirectory();

// Helper function to read JSON file
function readJsonFile($filePath) {
    if (!file_exists($filePath)) {
        return [];
    }
    $content = file_get_contents($filePath);
    return json_decode($content, true) ?: [];
}

// Helper function to write JSON file
function writeJsonFile($filePath, $data) {
    return file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
}

// User authentication functions
function loginUser($email, $password) {
    $users = readJsonFile(USERS_FILE);

    foreach ($users as $user) {
        if (($user['email'] === $email || $user['username'] === $email) && 
            password_verify($password, $user['password'])) {
            // Start session and set user data
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            
            return true;
        }
    }

    return false;
}

// Function to check if user is an assistant
function isUserAssistant($userId) {
    $assistants = readJsonFile(ASSISTANTS_FILE);
    foreach ($assistants as $assistant) {
        if ($assistant['user_id'] == $userId) {
            return true;
        }
    }
    return false;
}

// Process login with intent handling (multi-role)
function processLogin($email, $password, $intent = '') {
    // Map legacy 'get' intent to 'student' for backward compatibility
    if ($intent === 'get') {
        $intent = 'student';
    }
    // First check if user exists
    $users = readJsonFile(USERS_FILE);
    $user = null;
    
    foreach ($users as $u) {
        if ($u['email'] === $email && password_verify($password, $u['password'])) {
            $user = $u;
            break;
        }
    }

    if (!$user) {
        return "Invalid email or password";
    }

    // Ensure roles is an array (backward compatibility)
    if (!isset($user['roles']) || !is_array($user['roles'])) {
        if (isset($user['user_role'])) {
            $user['roles'] = [$user['user_role']];
        } else {
            $user['roles'] = [];
        }
    }

    // If no intent specified, use first role
    if (empty($intent)) {
        $intent = isset($user['roles'][0]) ? $user['roles'][0] : '';
    }

    // Check if the user has the requested role
    if (!in_array($intent, $user['roles'])) {
        return "This account does not have the requested role.";
    }

    // Set session data
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['user_role'] = $intent; // Set role based on intent

    // Redirect based on intent
    if ($intent === 'assist') {
        header("Location: assistant-dashboard.php");
    } else {
        header("Location: courses.php");
    }
    exit();
}


// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to register new users (multi-role support)
function registerUser($username, $email, $password, $year, $user_role = 'student') {
    // Read existing users and assistants
    $users = readJsonFile(USERS_FILE);
    $assistants = readJsonFile(ASSISTANTS_FILE);

    $userFound = false;
    foreach ($users as &$user) {
        if ($user['email'] === $email) {
            $userFound = true;
            // Ensure roles is always an array
            if (!isset($user['roles']) || !is_array($user['roles'])) {
                $user['roles'] = isset($user['user_role']) ? [$user['user_role']] : [];
            }
            // If the role already exists, error
            if (in_array($user_role, $user['roles'])) {
                return "You already have this role.";
            }
            // Add the new role
            $user['roles'][] = $user_role;
            // If registering as assistant, add to assistants file if not already present
            if ($user_role === 'assist') {
                $alreadyAssistant = false;
                foreach ($assistants as $assistant) {
                    if ($assistant['user_id'] == $user['id']) {
                        $alreadyAssistant = true;
                        break;
                    }
                }
                if (!$alreadyAssistant) {
                    $newAssistant = [
                        'id' => $user['id'],
                        'user_id' => $user['id'],
                        'academic_year' => $year,
                        'courses' => [],
                        'telegram' => '',
                        'phone' => '',
                        'bio' => '',
                        'availability' => '',
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    $assistants[] = $newAssistant;
                    if (!writeJsonFile(ASSISTANTS_FILE, $assistants)) {
                        return false;
                    }
                }
            }
            // Save updated users file
            if (!writeJsonFile(USERS_FILE, $users)) {
                return false;
            }
            return true;
        }
        if ($user['username'] === $username) {
            return "Username already taken.";
        }
    }
    unset($user);

    // New user registration
    $newId = empty($users) ? 1 : max(array_column($users, 'id')) + 1;
    $newUser = [
        'id' => $newId,
        'username' => $username,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'academic_year' => $year,
        'roles' => [$user_role],
        'created_at' => date('Y-m-d H:i:s')
    ];
    $users[] = $newUser;
    if (!writeJsonFile(USERS_FILE, $users)) {
        return false;
    }
    if ($user_role === 'assist') {
        $newAssistant = [
            'id' => $newId,
            'user_id' => $newId,
            'academic_year' => $year,
            'courses' => [],
            'telegram' => '',
            'phone' => '',
            'bio' => '',
            'availability' => '',
            'created_at' => date('Y-m-d H:i:s')
        ];
        $assistants[] = $newAssistant;
        if (!writeJsonFile(ASSISTANTS_FILE, $assistants)) {
            return false;
        }
    }
    return true;
}


// Function to get user data
function getUserData($userId) {
    $users = readJsonFile(USERS_FILE);

    foreach ($users as $user) {
        if ($user['id'] == $userId) {
            return $user;
        }
    }

    return null;
}

// Function to get courses by year and semester
function getCoursesByYearAndSemester($year, $semester) {
    $courses = readJsonFile(COURSES_FILE);
    $result = [];

    foreach ($courses as $course) {
        if ($course['year'] == $year && $course['semester'] == $semester) {
            $result[] = $course;
        }
    }

    // Sort by name
    usort($result, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });

    return $result;
}

// Function to get all courses
function getAllCourses() {
    $courses = readJsonFile(COURSES_FILE);

    // Sort by year, semester, name
    usort($courses, function($a, $b) {
        if ($a['year'] != $b['year']) {
            return $a['year'] - $b['year'];
        }
        if ($a['semester'] != $b['semester']) {
            return $a['semester'] - $b['semester'];
        }
        return strcmp($a['name'], $b['name']);
    });

    return $courses;
}

// Function to get course by ID
function getCourseById($courseId) {
    $courses = readJsonFile(COURSES_FILE);

    foreach ($courses as $course) {
        if ($course['id'] == $courseId) {
            return $course;
        }
    }

    return null;
}

// Function to get courses for assistant based on their academic year
function getCoursesForAssistant($userYearValue) {
    $courses = readJsonFile(COURSES_FILE);
    $result = [];
    
    foreach ($courses as $course) {
        // Assistants can only assist courses from years below their current year
        if ($course['year'] < $userYearValue) {
            $result[] = $course;
        }
    }
    
    // Sort by year, semester, name
    usort($result, function($a, $b) {
        if ($a['year'] != $b['year']) {
            return $a['year'] - $b['year'];
        }
        if ($a['semester'] != $b['semester']) {
            return $a['semester'] - $b['semester'];
        }
        return strcmp($a['name'], $b['name']);
    });
    
    return $result;
}

// Function to get courses the user is assisting
function getAssistantCourses($userId) {
    $assistants = readJsonFile(ASSISTANTS_FILE);
    $result = [];
    
    foreach ($assistants as $assistant) {
        if ($assistant['user_id'] == $userId) {
            $result[] = $assistant;
        }
    }
    
    return $result;
}

// Function to save assistant profile with multiple courses
function saveAssistantProfile($userId, $selectedCourses, $telegram, $phone, $bio, $availability) {
    $assistants = readJsonFile(ASSISTANTS_FILE);
    $users = readJsonFile(USERS_FILE);
    
    // Update user profile data
    foreach ($users as &$user) {
        if ($user['id'] == $userId) {
            $user['telegram'] = $telegram;
            $user['phone'] = $phone;
            $user['bio'] = $bio;
            $user['availability'] = $availability;
            break;
        }
    }
    
    // Write updated user data
    writeJsonFile(USERS_FILE, $users);
    
    // Remove existing assistant entries for this user
    $assistants = array_filter($assistants, function($assistant) use ($userId) {
        return $assistant['user_id'] != $userId;
    });
    
    // Add new assistant entries for selected courses
    $maxId = 0;
    foreach ($assistants as $assistant) {
        if ($assistant['id'] > $maxId) {
            $maxId = $assistant['id'];
        }
    }
    
    foreach ($selectedCourses as $courseId) {
        $maxId++;
        $assistants[] = [
            'id' => $maxId,
            'user_id' => $userId,
            'course_id' => $courseId,
            'telegram' => $telegram,
            'phone' => $phone,
            'bio' => $bio,
            'availability' => $availability,
            'visits' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
    
    return writeJsonFile(ASSISTANTS_FILE, $assistants);
}

// Function to get tutors by course with search
function getTutorsByCourse($courseId, $searchQuery = '') {
    $assistants = readJsonFile(ASSISTANTS_FILE);
    $users = readJsonFile(USERS_FILE);
    $connections = readJsonFile(CONNECTIONS_FILE);
    $result = [];

    foreach ($assistants as $assistant) {
        if ($assistant['course_id'] == $courseId) {
            // Find user data
            $user = null;
            foreach ($users as $u) {
                if ($u['id'] == $assistant['user_id']) {
                    $user = $u;
                    break;
                }
            }
            
            if ($user) {
                // Skip if search query doesn't match
                if (!empty($searchQuery) && stripos($user['username'], $searchQuery) === false) {
                    continue;
                }
                
                // Count visits
                $visits = 0;
                foreach ($connections as $connection) {
                    if ($connection['assistant_id'] == $assistant['id']) {
                        $visits++;
                    }
                }
                
                $result[] = [
                    'id' => $assistant['id'],
                    'name' => $user['username'],
                    'year' => $user['academic_year'],
                    'bio' => $assistant['bio'] ?? $user['bio'] ?? '',
                    'visits' => $visits
                ];
            }
        }
    }

    return $result;
}

// Function to get tutor by ID
function getTutorById($tutorId) {
    $assistants = readJsonFile(ASSISTANTS_FILE);
    $users = readJsonFile(USERS_FILE);

    foreach ($assistants as $assistant) {
        if ($assistant['id'] == $tutorId) {
            // Find user data
            foreach ($users as $user) {
                if ($user['id'] == $assistant['user_id']) {
                    $assistant['name'] = $user['username'];
                    return $assistant;
                }
            }
        }
    }

    return null;
}

// Function to save connection request
function saveConnectionRequest($userId, $tutorId, $courseId, $problemDescription, $telegram = '') {
    $connections = readJsonFile(CONNECTIONS_FILE);

    // Generate new connection ID
    $newId = 1;
    if (!empty($connections)) {
        $ids = array_column($connections, 'id');
        $newId = max($ids) + 1;
    }

    // Create new connection
    $newConnection = [
        'id' => $newId,
        'user_id' => $userId,
        'assistant_id' => $tutorId,
        'course_id' => $courseId,
        'problem_description' => $problemDescription,
        'telegram' => $telegram,
        'created_at' => date('Y-m-d H:i:s')
    ];

    $connections[] = $newConnection;

    return writeJsonFile(CONNECTIONS_FILE, $connections) !== false;
}

// Function to increment tutor visits
function incrementTutorVisits($tutorId) {
    $assistants = readJsonFile(ASSISTANTS_FILE);

    foreach ($assistants as &$assistant) {
        if ($assistant['id'] == $tutorId) {
            $assistant['visits'] = ($assistant['visits'] ?? 0) + 1;
            break;
        }
    }

    return writeJsonFile(ASSISTANTS_FILE, $assistants) !== false;
}

// Function to save assistant application
function saveAssistantApplication($userId, $courseId, $telegram = '', $phone = '', $otherInfo = '') {
    $assistants = readJsonFile(ASSISTANTS_FILE);
    $existingIndex = -1;

    // Check if user already applied for this course
    foreach ($assistants as $index => $assistant) {
        if ($assistant['user_id'] == $userId && $assistant['course_id'] == $courseId) {
            $existingIndex = $index;
            break;
        }
    }

    if ($existingIndex >= 0) {
        // Update existing application
        $assistants[$existingIndex]['telegram'] = $telegram;
        $assistants[$existingIndex]['phone'] = $phone;
        $assistants[$existingIndex]['other_info'] = $otherInfo;
        $assistants[$existingIndex]['updated_at'] = date('Y-m-d H:i:s');
    } else {
        // Generate new assistant ID
        $newId = 1;
        if (!empty($assistants)) {
            $ids = array_column($assistants, 'id');
            $newId = max($ids) + 1;
        }
        
        // Create new assistant
        $newAssistant = [
            'id' => $newId,
            'user_id' => $userId,
            'course_id' => $courseId,
            'telegram' => $telegram,
            'phone' => $phone,
            'other_info' => $otherInfo,
            'visits' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $assistants[] = $newAssistant;
    }

    return writeJsonFile(ASSISTANTS_FILE, $assistants) !== false;
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
        'geography' => '🌍',
        'logic' => '🧠',
        'computer' => '🖥️'
    ];

    return $icons[$category] ?? '📚';
}

// Initialize with sample data if files are empty
function initializeSampleData() {
    // Sample users
    if (empty(readJsonFile(USERS_FILE))) {
        $users = [
            [
                'id' => 1,
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'academic_year' => 'Senior',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
        writeJsonFile(USERS_FILE, $users);
    }

    // Sample courses
    if (empty(readJsonFile(COURSES_FILE))) {
        $courses = [
            [
                'id' => 1,
                'name' => 'Discrete Mathematics',
                'year' => 1,
                'semester' => 1,
                'category' => 'math'
            ],
            [
                'id' => 2,
                'name' => 'Introduction to Programming',
                'year' => 1,
                'semester' => 1,
                'category' => 'programming'
            ],
            [
                'id' => 3,
                'name' => 'College English I',
                'year' => 1,
                'semester' => 1,
                'category' => 'language'
            ],
            [
                'id' => 4,
                'name' => 'Calculus I',
                'year' => 1,
                'semester' => 2,
                'category' => 'math'
            ],
            [
                'id' => 5,
                'name' => 'Data Structures',
                'year' => 2,
                'semester' => 1,
                'category' => 'programming'
            ],
            [
                'id' => 6,
                'name' => 'Physics I',
                'year' => 1,
                'semester' => 2,
                'category' => 'science'
            ],
            [
                'id' => 7,
                'name' => 'Database Systems',
                'year' => 2,
                'semester' => 2,
                'category' => 'programming'
            ],
            [
                'id' => 8,
                'name' => 'Linear Algebra',
                'year' => 2,
                'semester' => 1,
                'category' => 'math'
            ]
        ];
        writeJsonFile(COURSES_FILE, $courses);
    }
}

// Initialize sample data
initializeSampleData();
?>
