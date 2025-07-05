-- Add availability column to assistants table
ALTER TABLE assistants
ADD COLUMN availability TEXT,
ADD COLUMN bio TEXT,
MODIFY COLUMN course_id VARCHAR(10) NULL;

-- Create assistant_courses table for many-to-many relationship
CREATE TABLE IF NOT EXISTS assistant_courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assistant_id INT NOT NULL,
    course_id VARCHAR(10) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assistant_id) REFERENCES assistants(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY (assistant_id, course_id)
);

-- Add roles column to users table
ALTER TABLE users 
ADD COLUMN roles JSON DEFAULT '["student"]',
ADD COLUMN last_login DATETIME NULL;

-- Update existing assistants to have the 'assist' role
UPDATE users u
JOIN assistants a ON u.id = a.user_id
SET u.roles = JSON_ARRAY('assist')
WHERE JSON_CONTAINS(u.roles, '"student"') = 0;
