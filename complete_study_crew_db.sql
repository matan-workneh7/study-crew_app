-- Drop tables if they exist to avoid conflicts
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS connections;
DROP TABLE IF EXISTS assistant_courses;
DROP TABLE IF EXISTS assistants;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    full_name VARCHAR(255),
    bio TEXT,
    telegram VARCHAR(255),
    phone VARCHAR(50),
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    academic_year VARCHAR(50),
    roles JSON DEFAULT '["student"]',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create courses table
CREATE TABLE courses (
    id VARCHAR(10) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(20) NOT NULL,
    credit_hours INT NOT NULL,
    year VARCHAR(50) NOT NULL,
    semester INT NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create assistants table
CREATE TABLE assistants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    telegram VARCHAR(255),
    phone VARCHAR(50),
    other_info TEXT,
    visits INT DEFAULT 0,
    availability TEXT,
    bio TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create assistant_courses table
CREATE TABLE assistant_courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assistant_id INT NOT NULL,
    course_id VARCHAR(10) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assistant_id) REFERENCES assistants(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY (assistant_id, course_id)
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

-- Create messages table
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tutor_id INT NOT NULL,
    tutor_email VARCHAR(255) NOT NULL,
    sender_id INT NOT NULL,
    sender_name VARCHAR(255) NOT NULL,
    sender_email VARCHAR(255) NOT NULL,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    courses JSON,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tutor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add indexes for better performance
CREATE INDEX idx_assistants_user_id ON assistants(user_id);
CREATE INDEX idx_assistant_courses_assistant_id ON assistant_courses(assistant_id);
CREATE INDEX idx_assistant_courses_course_id ON assistant_courses(course_id);
CREATE INDEX idx_connections_user_id ON connections(user_id);
CREATE INDEX idx_connections_assistant_id ON connections(assistant_id);
CREATE INDEX idx_connections_course_id ON connections(course_id);
CREATE INDEX idx_messages_tutor_id ON messages(tutor_id);
CREATE INDEX idx_messages_sender_id ON messages(sender_id);

-- Insert sample courses
INSERT INTO courses (id, name, code, credit_hours, year, semester, description) VALUES
('CS101', 'Introduction to Computer Science', 'CS101', 3, 'Freshman', 1, 'Fundamentals of computer science and programming.'),
('MATH201', 'Calculus I', 'MATH201', 4, 'Freshman', 1, 'Differential and integral calculus.'),
('CS201', 'Data Structures', 'CS201', 3, 'Sophomore', 1, 'Study of fundamental data structures and algorithms.'),
('CS301', 'Database Systems', 'CS301', 3, 'Junior', 1, 'Introduction to database design and implementation.'),
('CS401', 'Software Engineering', 'CS401', 3, 'Senior', 1, 'Software development methodologies and practices.'),
('MATH202', 'Linear Algebra', 'MATH202', 3, 'Sophomore', 2, 'Vector spaces and linear transformations.');

-- Create a test user (password: test123)
INSERT INTO users (username, full_name, email, password, academic_year, roles) 
VALUES ('testuser', 'Test User', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sophomore', '["student"]');

-- Create a test assistant
INSERT INTO users (username, full_name, email, password, academic_year, roles) 
VALUES ('testassistant', 'Test Assistant', 'assistant@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Senior', '["assistant"]');

-- Add assistant record
INSERT INTO assistants (user_id, telegram, phone, bio, availability) 
VALUES (2, '@testassistant', '+1234567890', 'Experienced teaching assistant', 'Weekdays 9am-5pm');

-- Add assistant courses
INSERT INTO assistant_courses (assistant_id, course_id) 
VALUES (1, 'CS101'), (1, 'CS201');
