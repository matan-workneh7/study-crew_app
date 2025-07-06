-- Modify existing columns to store JSON arrays
ALTER TABLE messages 
MODIFY COLUMN course_id TEXT DEFAULT NULL COMMENT 'JSON array of course IDs',
MODIFY COLUMN course_name TEXT DEFAULT NULL COMMENT 'JSON array of course names';

-- Create a temporary table to store the course data for existing messages
CREATE TEMPORARY TABLE temp_course_mapping AS
SELECT 
    m.id AS message_id,
    m.course_id,
    m.course_name,
    c.code AS course_code
FROM messages m
LEFT JOIN courses c ON m.course_id = c.id;

-- Update existing messages to use the new JSON array format
UPDATE messages m
JOIN temp_course_mapping t ON m.id = t.message_id
SET 
    m.course_id = JSON_ARRAY(IFNULL(t.course_id, '')),
    m.course_name = JSON_ARRAY(IFNULL(t.course_name, ''));

-- Drop the temporary table
DROP TEMPORARY TABLE IF EXISTS temp_course_mapping;
