CREATE TABLE IF NOT EXISTS `assistant_courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assistant_id` int(11) NOT NULL,
  `course_id` varchar(10) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_assistant_course` (`assistant_id`, `course_id`),
  CONSTRAINT `fk_assistant` FOREIGN KEY (`assistant_id`) REFERENCES `assistants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
