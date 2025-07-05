-- Add any missing columns to the users table
ALTER TABLE users
ADD COLUMN IF NOT EXISTS username VARCHAR(255) UNIQUE AFTER id,
ADD COLUMN IF NOT EXISTS full_name VARCHAR(255) AFTER username,
ADD COLUMN IF NOT EXISTS bio TEXT AFTER full_name,
ADD COLUMN IF NOT EXISTS telegram VARCHAR(255) AFTER bio,
ADD COLUMN IF NOT EXISTS phone VARCHAR(50) AFTER telegram,
ADD COLUMN IF NOT EXISTS academic_year VARCHAR(50) AFTER password,
ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Ensure the assistants table has all required columns
ALTER TABLE assistants
ADD COLUMN IF NOT EXISTS availability TEXT AFTER other_info,
ADD COLUMN IF NOT EXISTS bio TEXT AFTER availability,
MODIFY COLUMN course_id VARCHAR(10) NULL;

-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_assistants_user_id ON assistants(user_id);
CREATE INDEX IF NOT EXISTS idx_assistant_courses_assistant_id ON assistant_courses(assistant_id);
CREATE INDEX IF NOT EXISTS idx_assistant_courses_course_id ON assistant_courses(course_id);
