-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    academic_year TEXT NOT NULL CHECK(academic_year IN ('Freshman', 'Sophomore', 'Junior', 'Senior', 'Graduate')),
    created_at DATETIME NOT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Create courses table
CREATE TABLE IF NOT EXISTS courses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    year INTEGER NOT NULL,
    semester INTEGER NOT NULL,
    category TEXT NOT NULL CHECK(category IN ('math', 'programming', 'language', 'science', 'humanities', 'business', 'engineering', 'arts', 'geography')),
    created_at DATETIME NOT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Create assistants table
CREATE TABLE IF NOT EXISTS assistants (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    course_id INTEGER NOT NULL,
    telegram TEXT,
    phone TEXT,
    other_info TEXT,
    visits INTEGER DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE(user_id, course_id)
);

-- Create connections table
CREATE TABLE IF NOT EXISTS connections (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    assistant_id INTEGER NOT NULL,
    course_id INTEGER NOT NULL,
    problem_description TEXT NOT NULL,
    telegram TEXT,
    status TEXT DEFAULT 'pending' CHECK(status IN ('pending', 'accepted', 'rejected', 'completed')),
    created_at DATETIME NOT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assistant_id) REFERENCES assistants(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Insert sample courses
INSERT INTO courses (name, year, semester, category, created_at) VALUES
('Introduction to Programming', 1, 1, 'programming', datetime('now')),
('Calculus I', 1, 1, 'math', datetime('now')),
('English Composition', 1, 1, 'language', datetime('now')),
('General Physics', 1, 2, 'science', datetime('now')),
('Data Structures', 2, 1, 'programming', datetime('now')),
('Linear Algebra', 2, 1, 'math', datetime('now')),
('Database Systems', 2, 2, 'programming', datetime('now')),
('Web Development', 2, 2, 'programming', datetime('now')),
('Operating Systems', 3, 1, 'programming', datetime('now')),
('Software Engineering', 3, 2, 'programming', datetime('now')),
('Artificial Intelligence', 4, 1, 'programming', datetime('now')),
('Computer Networks', 4, 2, 'programming', datetime('now')); 