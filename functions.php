<?php
// Add these functions to your existing functions.php file

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

// Function to get assistant details for display to students
function getAssistantDetails($assistantId) {
    $assistants = readJsonFile(ASSISTANTS_FILE);
    $users = readJsonFile(USERS_FILE);
    
    foreach ($assistants as $assistant) {
        if ($assistant['id'] == $assistantId) {
            // Find user data
            foreach ($users as $user) {
                if ($user['id'] == $assistant['user_id']) {
                    return [
                        'id' => $assistant['id'],
                        'name' => $user['username'],
                        'year' => $user['academic_year'],
                        'telegram' => $assistant['telegram'] ?? $user['telegram'] ?? '',
                        'phone' => $assistant['phone'] ?? $user['phone'] ?? '',
                        'bio' => $assistant['bio'] ?? $user['bio'] ?? '',
                        'availability' => $assistant['availability'] ?? $user['availability'] ?? '',
                        'visits' => $assistant['visits'] ?? 0
                    ];
                }
            }
        }
    }
    
    return null;
}

// Update the getTutorsByCourse function to include more details
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
?>