-- Add assistant_applications table
CREATE TABLE IF NOT EXISTS assistant_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id VARCHAR(10) NOT NULL,
    telegram VARCHAR(255),
    phone VARCHAR(50),
    other_info TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, course_id)
);

-- Add index for better performance
CREATE INDEX idx_assistant_applications_user_id ON assistant_applications(user_id);
CREATE INDEX idx_assistant_applications_course_id ON assistant_applications(course_id);
CREATE INDEX idx_assistant_applications_status ON assistant_applications(status);
