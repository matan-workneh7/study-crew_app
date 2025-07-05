-- Drop tables if they exist to avoid conflicts
DROP TABLE IF EXISTS connections;
DROP TABLE IF EXISTS assistants;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    academic_year ENUM('Freshman', 'Sophomore', 'Junior', 'Senior', 'Graduate') NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create courses table
CREATE TABLE courses (
    id VARCHAR(10) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(20) NOT NULL,
    credit_hours INT NOT NULL,
    year ENUM('Freshman', 'Sophomore', 'Junior', 'Senior', 'Graduate') NOT NULL,
    semester TINYINT NOT NULL CHECK(semester IN (1, 2)),
    description TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create assistants table
CREATE TABLE assistants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id VARCHAR(10) NOT NULL,
    telegram VARCHAR(255),
    phone VARCHAR(50),
    other_info TEXT,
    visits INT DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, course_id)
);

-- Create connections table
CREATE TABLE connections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    assistant_id INT NOT NULL,
    course_id VARCHAR(10) NOT NULL,
    problem_description TEXT NOT NULL,
    telegram VARCHAR(255),
    status ENUM('pending', 'accepted', 'rejected', 'completed') DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assistant_id) REFERENCES assistants(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Insert comprehensive course list
INSERT INTO courses (id, name, code, credit_hours, year, semester, description) VALUES
('SE101', 'Introduction to Programming', 'SE101', 3, 'Freshman', 1, 'Fundamentals of programming using a high-level language.'),
('SE102', 'Discrete Mathematics', 'SE102', 3, 'Freshman', 1, 'Mathematical foundations for computer science.'),
('SE103', 'Computer Fundamentals', 'SE103', 3, 'Freshman', 1, 'Introduction to computer systems and architecture.'),
('SE104', 'Calculus I', 'MATH101', 4, 'Freshman', 1, 'Differential and integral calculus.'),
('SE105', 'Technical Writing', 'ENG101', 2, 'Freshman', 1, 'Effective technical communication skills.'),
('SE106', 'Object-Oriented Programming', 'SE106', 3, 'Freshman', 2, 'Principles of object-oriented programming.'),
('SE107', 'Data Structures', 'SE107', 3, 'Freshman', 2, 'Fundamental data structures and algorithms.'),
('SE108', 'Digital Logic Design', 'SE108', 3, 'Freshman', 2, 'Digital systems and logic design.'),
('SE109', 'Calculus II', 'MATH102', 4, 'Freshman', 2, 'Advanced calculus topics.'),
('SE110', 'Communication Skills', 'ENG102', 2, 'Freshman', 2, 'Professional communication skills.'),
('SE201', 'Algorithms', 'SE201', 3, 'Sophomore', 1, 'Design and analysis of algorithms.'),
('SE202', 'Database Systems', 'SE202', 3, 'Sophomore', 1, 'Introduction to database design and management.'),
('SE203', 'Computer Organization', 'SE203', 3, 'Sophomore', 1, 'Computer system organization and architecture.'),
('SE204', 'Linear Algebra', 'MATH201', 3, 'Sophomore', 1, 'Matrix algebra and vector spaces.'),
('SE205', 'Web Development', 'SE205', 3, 'Sophomore', 2, 'Front-end and back-end web development.'),
('SE206', 'Operating Systems', 'SE206', 3, 'Sophomore', 2, 'Principles of operating systems.'),
('SE207', 'Software Engineering I', 'SE207', 3, 'Sophomore', 2, 'Introduction to software engineering principles.'),
('SE208', 'Probability and Statistics', 'MATH202', 3, 'Sophomore', 2, 'Probability theory and statistical methods.'),
('SE301', 'Software Requirements Engineering', 'SE301', 3, 'Junior', 1, 'Requirements elicitation and analysis.'),
('SE302', 'Computer Networks', 'SE302', 3, 'Junior', 1, 'Network architectures and protocols.'),
('SE303', 'Software Design and Architecture', 'SE303', 3, 'Junior', 1, 'Software design patterns and architectures.'),
('SE304', 'Mobile Application Development', 'SE304', 3, 'Junior', 2, 'Developing applications for mobile platforms.'),
('SE305', 'Software Testing', 'SE305', 3, 'Junior', 2, 'Software testing techniques and methodologies.'),
('SE306', 'Human-Computer Interaction', 'SE306', 3, 'Junior', 2, 'Principles of user interface design.'),
('SE401', 'Software Project Management', 'SE401', 3, 'Senior', 1, 'Managing software development projects.'),
('SE402', 'Artificial Intelligence', 'SE402', 3, 'Senior', 1, 'Fundamentals of AI and machine learning.'),
('SE403', 'Cloud Computing', 'SE403', 3, 'Senior', 2, 'Cloud architectures and services.'),
('SE404', 'Cybersecurity', 'SE404', 3, 'Senior', 2, 'Principles of information security.'),
('SE501', 'Capstone Project I', 'SE501', 3, 'Graduate', 1, 'First part of the capstone software project.'),
('SE502', 'Professional Practices', 'SE502', 2, 'Graduate', 1, 'Ethics and professional responsibilities.'),
('SE503', 'Capstone Project II', 'SE503', 3, 'Graduate', 2, 'Completion and presentation of capstone project.'),
('SE504', 'Entrepreneurship in Software', 'SE504', 2, 'Graduate', 2, 'Starting and managing a software business.'); 