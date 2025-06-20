<?php
require_once 'functions.php';

// Add comprehensive software engineering courses organized by year and semester
$courses = [
    // Freshman Year - Semester 2
    [
        'id' => 4,
        'name' => 'Object-Oriented Programming',
        'code' => 'SE103',
        'department' => 'Software Engineering',
        'semester' => '2',
        'year' => 'Freshman',
        'description' => 'Programming concepts using Java, classes, objects, inheritance, and polymorphism.',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'id' => 5,
        'name' => 'Data Structures and Algorithms',
        'code' => 'SE104',
        'department' => 'Software Engineering',
        'semester' => '2',
        'year' => 'Freshman',
        'description' => 'Advanced data structures, algorithm analysis, and problem-solving techniques.',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'id' => 6,
        'name' => 'Database Systems',
        'code' => 'SE105',
        'department' => 'Software Engineering',
        'semester' => '2',
        'year' => 'Freshman',
        'description' => 'Introduction to database design, SQL, and database management systems.',
        'created_at' => date('Y-m-d H:i:s')
    ],

    // Sophomore Year - Semester 1
    [
        'id' => 7,
        'name' => 'Software Engineering I',
        'code' => 'SE201',
        'department' => 'Software Engineering',
        'semester' => '1',
        'year' => 'Sophomore',
        'description' => 'Introduction to software development processes, methodologies, and project management.',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'id' => 8,
        'name' => 'Computer Networks',
        'code' => 'SE202',
        'department' => 'Software Engineering',
        'semester' => '1',
        'year' => 'Sophomore',
        'description' => 'Fundamentals of computer networks, protocols, and network programming.',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'id' => 9,
        'name' => 'Operating Systems',
        'code' => 'SE203',
        'department' => 'Software Engineering',
        'semester' => '1',
        'year' => 'Sophomore',
        'description' => 'Operating system concepts, process management, memory management, and file systems.',
        'created_at' => date('Y-m-d H:i:s')
    ],

    // Sophomore Year - Semester 2
    [
        'id' => 10,
        'name' => 'Software Engineering II',
        'code' => 'SE204',
        'department' => 'Software Engineering',
        'semester' => '2',
        'year' => 'Sophomore',
        'description' => 'Advanced topics in software engineering: testing, maintenance, and evolution.',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'id' => 11,
        'name' => 'Web Development',
        'code' => 'SE205',
        'department' => 'Software Engineering',
        'semester' => '2',
        'year' => 'Sophomore',
        'description' => 'Full-stack web development using modern frameworks and technologies.',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'id' => 12,
        'name' => 'Software Architecture',
        'code' => 'SE206',
        'department' => 'Software Engineering',
        'semester' => '2',
        'year' => 'Sophomore',
        'description' => 'Design patterns, architectural styles, and system integration.',
        'created_at' => date('Y-m-d H:i:s')
    ],

    // Junior Year - Semester 1
    [
        'id' => 13,
        'name' => 'Software Project Management',
        'code' => 'SE301',
        'department' => 'Software Engineering',
        'semester' => '1',
        'year' => 'Junior',
        'description' => 'Agile methodologies, project planning, risk management, and team leadership.',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'id' => 14,
        'name' => 'Mobile Application Development',
        'code' => 'SE302',
        'department' => 'Software Engineering',
        'semester' => '1',
        'year' => 'Junior',
        'description' => 'Native and cross-platform mobile app development for iOS and Android.',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'id' => 15,
        'name' => 'Cloud Computing',
        'code' => 'SE303',
        'department' => 'Software Engineering',
        'semester' => '1',
        'year' => 'Junior',
        'description' => 'Cloud platforms, services, deployment strategies, and DevOps practices.',
        'created_at' => date('Y-m-d H:i:s')
    ],

    // Junior Year - Semester 2
    [
        'id' => 16,
        'name' => 'Machine Learning',
        'code' => 'SE304',
        'department' => 'Software Engineering',
        'semester' => '2',
        'year' => 'Junior',
        'description' => 'Introduction to machine learning algorithms and data science.',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'id' => 17,
        'name' => 'Cybersecurity',
        'code' => 'SE305',
        'department' => 'Software Engineering',
        'semester' => '2',
        'year' => 'Junior',
        'description' => 'Security principles, cryptography, network security, and secure coding.',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'id' => 18,
        'name' => 'Software Testing',
        'code' => 'SE306',
        'department' => 'Software Engineering',
        'semester' => '2',
        'year' => 'Junior',
        'description' => 'Testing strategies, test automation, and quality assurance.',
        'created_at' => date('Y-m-d H:i:s')
    ],

    // Senior Year - Semester 1
    [
        'id' => 19,
        'name' => 'Capstone Project I',
        'code' => 'SE401',
        'department' => 'Software Engineering',
        'semester' => '1',
        'year' => 'Senior',
        'description' => 'Team-based software development project with real-world client.',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'id' => 20,
        'name' => 'Advanced Topics in Software Engineering',
        'code' => 'SE402',
        'department' => 'Software Engineering',
        'semester' => '1',
        'year' => 'Senior',
        'description' => 'Current trends and research in software engineering.',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'id' => 21,
        'name' => 'Enterprise Systems',
        'code' => 'SE403',
        'department' => 'Software Engineering',
        'semester' => '1',
        'year' => 'Senior',
        'description' => 'Large-scale enterprise applications and system integration.',
        'created_at' => date('Y-m-d H:i:s')
    ],

    // Senior Year - Semester 2
    [
        'id' => 22,
        'name' => 'Capstone Project II',
        'code' => 'SE404',
        'department' => 'Software Engineering',
        'semester' => '2',
        'year' => 'Senior',
        'description' => 'Continuation of Capstone Project with focus on deployment and maintenance.',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'id' => 23,
        'name' => 'Software Entrepreneurship',
        'code' => 'SE405',
        'department' => 'Software Engineering',
        'semester' => '2',
        'year' => 'Senior',
        'description' => 'Business aspects of software development and startup creation.',
        'created_at' => date('Y-m-d H:i:s')
    ],
    [
        'id' => 24,
        'name' => 'Artificial Intelligence',
        'code' => 'SE406',
        'department' => 'Software Engineering',
        'semester' => '2',
        'year' => 'Senior',
        'description' => 'Introduction to AI algorithms and applications in software engineering.',
        'created_at' => date('Y-m-d H:i:s')
    ]
];

// Write to courses file
writeJsonFile(COURSES_FILE, $courses);

echo "Software Engineering courses added successfully!\n";
?>
