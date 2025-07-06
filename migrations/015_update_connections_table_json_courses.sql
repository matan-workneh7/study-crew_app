-- First, create a temporary table to store the current connections
CREATE TEMPORARY TABLE temp_connections AS
SELECT * FROM connections;

-- Drop the existing connections table
DROP TABLE IF EXISTS connections;

-- Recreate the connections table with the updated schema
CREATE TABLE connections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    assistant_id INT NOT NULL,
    course_id TEXT DEFAULT NULL COMMENT 'JSON array of course IDs',
    problem_description TEXT,
    telegram VARCHAR(255) DEFAULT NULL,
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assistant_id) REFERENCES assistants(id) ON DELETE CASCADE
);

-- Copy data back from the temporary table, converting course_id to JSON array format
INSERT INTO connections (
    id, user_id, assistant_id, course_id, 
    problem_description, telegram, status, created_at, updated_at
)
SELECT 
    id, user_id, assistant_id, 
    CASE 
        WHEN course_id IS NULL OR course_id = '' OR course_id = 'null' THEN '[]'
        WHEN course_id LIKE '[%' THEN course_id  -- Already in JSON array format
        ELSE JSON_ARRAY(course_id)              -- Convert single value to array
    END as course_id,
    problem_description,
    telegram,
    status,
    created_at,
    updated_at
FROM temp_connections;

-- Drop the temporary table
DROP TEMPORARY TABLE IF EXISTS temp_connections;
