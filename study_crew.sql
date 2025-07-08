-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 08, 2025 at 11:01 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `study_crew`
--

-- --------------------------------------------------------

--
-- Table structure for table `assistants`
--

CREATE TABLE `assistants` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `telegram` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `other_info` text DEFAULT NULL,
  `visits` int(11) DEFAULT 0,
  `availability` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assistants`
--

INSERT INTO `assistants` (`id`, `user_id`, `telegram`, `phone`, `other_info`, `visits`, `availability`, `bio`, `created_at`, `updated_at`) VALUES
(9, 17, '', '', NULL, 0, '', '', '2025-07-05 17:50:56', '2025-07-08 09:57:05');

-- --------------------------------------------------------

--
-- Table structure for table `assistant_courses`
--

CREATE TABLE `assistant_courses` (
  `id` int(11) NOT NULL,
  `assistant_id` int(11) NOT NULL,
  `course_id` varchar(10) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assistant_courses`
--

INSERT INTO `assistant_courses` (`id`, `assistant_id`, `course_id`, `created_at`) VALUES
(38, 9, 'SE104', '2025-07-08 09:57:05'),
(39, 9, 'SE103', '2025-07-08 09:57:05');

-- --------------------------------------------------------

--
-- Table structure for table `connections`
--

CREATE TABLE `connections` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `assistant_id` int(11) NOT NULL,
  `course_id` text DEFAULT NULL COMMENT 'JSON array of course IDs',
  `problem_description` text DEFAULT NULL,
  `telegram` varchar(255) DEFAULT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `connections`
--

INSERT INTO `connections` (`id`, `user_id`, `assistant_id`, `course_id`, `problem_description`, `telegram`, `status`, `created_at`, `updated_at`) VALUES
(18, 18, 9, '[\"SE103\",\"SE104\"]', 'Hello,\r\n\r\nI have a question about MATH101.', NULL, 'pending', '2025-07-08 08:58:02', '2025-07-08 08:58:15');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `status` enum('unread','read','replied','spam') NOT NULL DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `ip_address`, `user_agent`, `status`, `created_at`, `updated_at`) VALUES
(4, 'amen', 'amen@gmail.com', 'Contact Form Submission', 'm .m', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'unread', '2025-07-06 20:39:15', '2025-07-06 20:39:15'),
(5, 'amen', 'amen@gmail.com', 'Contact Form Submission', 'm m. mm ,', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'unread', '2025-07-06 20:39:42', '2025-07-06 20:39:42'),
(6, 'matan', 'matan@gmail.com', 'Contact Form Submission', 'uguguk', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'unread', '2025-07-07 14:07:34', '2025-07-07 14:07:34');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` varchar(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(20) NOT NULL,
  `credit_hours` int(11) NOT NULL,
  `year` varchar(50) NOT NULL,
  `semester` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `name`, `code`, `credit_hours`, `year`, `semester`, `description`, `created_at`, `updated_at`) VALUES
('SE101', 'Introduction to Programming', 'SE101', 3, 'Freshman', 1, 'Fundamentals of programming using a high-level language.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE102', 'Discrete Mathematics', 'SE102', 3, 'Freshman', 1, 'Mathematical foundations for computer science.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE103', 'Computer Fundamentals', 'SE103', 3, 'Freshman', 1, 'Introduction to computer systems and architecture.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE104', 'Calculus I', 'MATH101', 4, 'Freshman', 1, 'Differential and integral calculus.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE105', 'Technical Writing', 'ENG101', 2, 'Freshman', 1, 'Effective technical communication skills.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE106', 'Object-Oriented Programming', 'SE106', 3, 'Freshman', 2, 'Principles of object-oriented programming.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE107', 'Data Structures', 'SE107', 3, 'Freshman', 2, 'Fundamental data structures and algorithms.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE108', 'Digital Logic Design', 'SE108', 3, 'Freshman', 2, 'Digital systems and logic design.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE109', 'Calculus II', 'MATH102', 4, 'Freshman', 2, 'Advanced calculus topics.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE110', 'Communication Skills', 'ENG102', 2, 'Freshman', 2, 'Professional communication skills.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE201', 'Algorithms', 'SE201', 3, 'Sophomore', 1, 'Design and analysis of algorithms.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE202', 'Database Systems', 'SE202', 3, 'Sophomore', 1, 'Introduction to database design and management.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE203', 'Computer Organization', 'SE203', 3, 'Sophomore', 1, 'Computer system organization and architecture.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE204', 'Linear Algebra', 'MATH201', 3, 'Sophomore', 1, 'Matrix algebra and vector spaces.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE205', 'Web Development', 'SE205', 3, 'Sophomore', 2, 'Front-end and back-end web development.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE206', 'Operating Systems', 'SE206', 3, 'Sophomore', 2, 'Principles of operating systems.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE207', 'Software Engineering I', 'SE207', 3, 'Sophomore', 2, 'Introduction to software engineering principles.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE208', 'Probability and Statistics', 'MATH202', 3, 'Sophomore', 2, 'Probability theory and statistical methods.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE301', 'Software Requirements Engineering', 'SE301', 3, 'Junior', 1, 'Requirements elicitation and analysis.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE302', 'Computer Networks', 'SE302', 3, 'Junior', 1, 'Network architectures and protocols.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE303', 'Software Design and Architecture', 'SE303', 3, 'Junior', 1, 'Software design patterns and architectures.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE304', 'Mobile Application Development', 'SE304', 3, 'Junior', 2, 'Developing applications for mobile platforms.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE305', 'Software Testing', 'SE305', 3, 'Junior', 2, 'Software testing techniques and methodologies.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE306', 'Human-Computer Interaction', 'SE306', 3, 'Junior', 2, 'Principles of user interface design.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE402', 'Artificial Intelligence', 'SE402', 3, 'Senior', 1, 'Fundamentals of AI and machine learning.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE403', 'Cloud Computing', 'SE403', 3, 'Senior', 2, 'Cloud architectures and services.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE404', 'Cybersecurity', 'SE404', 3, 'Senior', 2, 'Principles of information security.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE501', 'Capstone Project I', 'SE501', 3, 'Graduate', 1, 'First part of the capstone software project.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE502', 'Professional Practices', 'SE502', 2, 'Graduate', 1, 'Ethics and professional responsibilities.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE503', 'Capstone Project II', 'SE503', 3, 'Graduate', 2, 'Completion and presentation of capstone project.', '2025-07-05 15:33:47', '2025-07-05 15:33:47'),
('SE504', 'Entrepreneurship in Software', 'SE504', 2, 'Graduate', 2, 'Starting and managing a software business.', '2025-07-05 15:33:47', '2025-07-05 15:33:47');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `sender_name` varchar(255) NOT NULL,
  `sender_email` varchar(255) NOT NULL,
  `tutor_id` int(11) NOT NULL,
  `tutor_email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `course_id` text DEFAULT NULL COMMENT 'JSON array of course IDs',
  `courses_json` text DEFAULT NULL COMMENT 'JSON array of course objects',
  `course_name` text DEFAULT NULL COMMENT 'JSON array of course names',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('unread','read','replied') DEFAULT 'unread'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `sender_name`, `sender_email`, `tutor_id`, `tutor_email`, `subject`, `message`, `course_id`, `courses_json`, `course_name`, `created_at`, `status`) VALUES
(24, 18, 'amen', 'amen@gmail.com', 17, 'matan@gmail.com', '[MATH101, SE102] Question about MATH101', 'Hello,\r\n\r\nI have a question about', '[\"SE104\",\"SE102\"]', NULL, '[\"Calculus I\",\"Discrete Mathematics\"]', '2025-07-06 21:24:21', 'read'),
(27, 18, 'amen', 'amen@gmail.com', 17, 'matan@gmail.com', '[MATH101, SE102, ENG101, SE101, SE103] Question about MATH101', 'Hello,\r\n\r\nI have a question about MATH101.', '[\"SE104\",\"SE102\",\"SE105\",\"SE101\",\"SE103\"]', NULL, '[\"Calculus I\",\"Discrete Mathematics\",\"Technical Writing\",\"Introduction to Programming\",\"Computer Fundamentals\"]', '2025-07-06 21:49:59', 'read'),
(28, 18, 'amen', 'amen@gmail.com', 17, 'matan@gmail.com', '[MATH101, SE101, SE103] Question about MATH101', 'Hello,\r\n\r\nI have a question about MATH101.', '[\"SE104\",\"SE101\",\"SE103\"]', NULL, '[\"Calculus I\",\"Introduction to Programming\",\"Computer Fundamentals\"]', '2025-07-06 21:59:55', 'read'),
(29, 18, 'amen', 'amen@gmail.com', 17, 'matan@gmail.com', '[MATH101, SE101, ENG101] Question about MATH101', 'Hello,\r\n\r\nI have a question about MATH101.', '[\"SE104\",\"SE101\",\"SE105\"]', NULL, '[\"Calculus I\",\"Introduction to Programming\",\"Technical Writing\"]', '2025-07-07 13:51:28', 'read'),
(30, 18, 'amen', 'amen@gmail.com', 17, 'matan@gmail.com', '[MATH101, ENG101] Question about MATH101', 'Hello,\r\n\r\nI have a question about MATH101.', '[\"SE104\",\"SE105\"]', NULL, '[\"Calculus I\",\"Technical Writing\"]', '2025-07-07 13:57:09', 'read'),
(31, 18, 'amen', 'amen@gmail.com', 17, 'matan@gmail.com', '[MATH101, SE102] Question about MATH101', 'Hello,\r\n\r\nI have a question about MATH101.', '[\"SE104\",\"SE102\"]', NULL, '[\"Calculus I\",\"Discrete Mathematics\"]', '2025-07-07 14:33:48', 'read'),
(32, 18, 'amen', 'amen@gmail.com', 17, 'matan@gmail.com', '[MATH101, ENG101] Question about MATH101', 'Hello,\r\n\r\nI have a question about MATH101.', '[\"SE104\",\"SE105\"]', NULL, '[\"Calculus I\",\"Technical Writing\"]', '2025-07-07 14:50:20', 'read'),
(33, 18, 'amen', 'amen@gmail.com', 17, 'matan@gmail.com', '[SE103, MATH101] sura', 'hello', '[\"SE103\",\"SE104\"]', NULL, '[\"Computer Fundamentals\",\"Calculus I\"]', '2025-07-08 07:07:41', 'read'),
(34, 18, 'amen', 'amen@gmail.com', 17, 'matan@gmail.com', '[MATH101, SE103] Question about MATH101', 'Hello,\r\n\r\nI have a question about MATH101.', '[\"SE104\",\"SE103\"]', NULL, '[\"Calculus I\",\"Computer Fundamentals\"]', '2025-07-08 07:11:19', 'read'),
(35, 18, 'amen', 'amen@gmail.com', 17, 'matan@gmail.com', '[MATH101, SE103] Question about MATH101', 'Hello,\r\n\r\nI have a question about MATH101.', '[\"SE104\",\"SE103\"]', NULL, '[\"Calculus I\",\"Computer Fundamentals\"]', '2025-07-08 07:33:19', 'read'),
(36, 18, 'amen', 'amen@gmail.com', 17, 'matan@gmail.com', '[MATH101, SE103] night', 'Hello,\r\n\r\nI have a question about MATH101.', '[\"SE104\",\"SE103\"]', NULL, '[\"Calculus I\",\"Computer Fundamentals\"]', '2025-07-08 08:12:57', 'read'),
(37, 18, 'amen', 'amen@gmail.com', 17, 'matan@gmail.com', '[MATH101, SE103] Question about MATH101', 'Hello,\r\n\r\nI have a question about MATH101.', '[\"SE104\",\"SE103\"]', NULL, '[\"Calculus I\",\"Computer Fundamentals\"]', '2025-07-08 08:25:37', 'read'),
(38, 18, 'Amen', 'amen@gmail.com', 17, 'matan@gmail.com', '[MATH101, PHYS101] Test Connection', 'This is a test connection request', '[1,2]', NULL, '[\"Calculus I\",\"Physics I\"]', '2025-07-08 08:30:01', 'read'),
(39, 18, 'amen', 'amen@gmail.com', 17, 'matan@gmail.com', '[MATH101, SE103] Question about MATH101', 'Hello,\r\n\r\nI have a question about MATH101.', '[\"SE104\",\"SE103\"]', NULL, '[\"Calculus I\",\"Computer Fundamentals\"]', '2025-07-08 08:30:17', 'read'),
(40, 18, 'Amen', 'amen@gmail.com', 17, 'matan@gmail.com', '[MATH101, PHYS101] Test Connection 2025-07-08 11:33:41', 'This is a test connection request', '[1,2]', NULL, '[\"Calculus I\",\"Physics I\"]', '2025-07-08 08:33:41', 'read'),
(41, 18, 'Amen', 'amen@gmail.com', 17, 'matan@gmail.com', '[MATH101, PHYS101] Test Connection 2025-07-08 11:34:24', 'This is a test connection request', '[1,2]', NULL, '[\"Calculus I\",\"Physics I\"]', '2025-07-08 08:34:24', 'read'),
(42, 18, 'amen', 'amen@gmail.com', 17, 'matan@gmail.com', '[MATH101, SE103] Question about MATH101', 'Hello,\r\n\r\nI have a question about MATH101.', '[\"SE104\",\"SE103\"]', NULL, '[\"Calculus I\",\"Computer Fundamentals\"]', '2025-07-08 08:43:55', 'read'),
(43, 18, 'amen', 'amen@gmail.com', 17, 'matan@gmail.com', '[MATH101, SE103] ayt', 'Hello,\r\n\r\nI have a question about MATH101.', '[\"SE104\",\"SE103\"]', NULL, '[\"Calculus I\",\"Computer Fundamentals\"]', '2025-07-08 08:44:30', 'read'),
(44, 18, 'amen', 'amen@gmail.com', 17, 'matan@gmail.com', '[MATH101, SE103] haloo', 'Hello,\r\n\r\nI have a question about MATH101.', '[\"SE104\",\"SE103\"]', NULL, '[\"Calculus I\",\"Computer Fundamentals\"]', '2025-07-08 08:47:48', 'read'),
(45, 18, 'amen', 'amen@gmail.com', 17, 'matan@gmail.com', '[MATH101, SE103] Question about MATH101', 'Hello,', '[\"SE104\",\"SE103\"]', NULL, '[\"Calculus I\",\"Computer Fundamentals\"]', '2025-07-08 08:48:40', 'read'),
(46, 18, 'amen', 'amen@gmail.com', 17, 'matan@gmail.com', '[SE103] Question about MATH101', 'I have a question about MATH101.', '[\"SE103\"]', NULL, '[\"Computer Fundamentals\"]', '2025-07-08 08:51:58', 'read'),
(47, 18, 'amen', 'amen@gmail.com', 17, 'matan@gmail.com', '[SE103, MATH101] Question about MATH101', 'Hello,\r\n\r\nI have a question about MATH101.', '[\"SE103\",\"SE104\"]', NULL, '[\"Computer Fundamentals\",\"Calculus I\"]', '2025-07-08 08:52:37', 'read'),
(48, 18, 'amen', 'amen@gmail.com', 17, 'matan@gmail.com', '[SE103, MATH101] hi', 'Hello,', '[\"SE103\",\"SE104\"]', NULL, '[\"Computer Fundamentals\",\"Calculus I\"]', '2025-07-08 08:57:37', 'read'),
(49, 18, 'amen', 'amen@gmail.com', 17, 'matan@gmail.com', '[SE103, MATH101] Question about MATH101', 'Hello,\r\n\r\nI have a question about MATH101.', '[\"SE103\",\"SE104\"]', NULL, '[\"Computer Fundamentals\",\"Calculus I\"]', '2025-07-08 08:58:02', 'read'),
(50, 18, 'amen', 'amen@gmail.com', 17, 'matan@gmail.com', '[MATH101] Question about MATH101', 'Hello,\r\n\r\nI have a question about MATH101.', '[\"SE104\"]', NULL, '[\"Calculus I\"]', '2025-07-08 08:58:15', 'read');

-- --------------------------------------------------------

--
-- Table structure for table `messages_backup_20240706`
--

CREATE TABLE `messages_backup_20240706` (
  `id` int(11) NOT NULL,
  `tutor_id` int(11) NOT NULL,
  `tutor_email` varchar(255) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `sender_name` varchar(255) NOT NULL,
  `sender_email` varchar(255) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `courses` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`courses`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'unread',
  `email_status` varchar(20) DEFAULT 'pending',
  `email_sent_at` timestamp NULL DEFAULT NULL,
  `email_error` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL,
  `telegram` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `academic_year` varchar(50) DEFAULT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT '["student"]' CHECK (json_valid(`roles`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `full_name`, `email`, `password`, `bio`, `telegram`, `phone`, `academic_year`, `roles`, `created_at`, `updated_at`) VALUES
(14, 'admin', NULL, 'admin@example.com', '$2y$10$w1Eu4qRr8hCPTLWS/Dvm..Ap7LLBAMypj.pgWY62ZaleBAB50Ccs.', NULL, NULL, NULL, 'Graduate', '[\"admin\",\"student\",\"assist\"]', '2025-07-05 17:49:53', '2025-07-05 17:49:53'),
(15, 'student1', NULL, 'student1@example.com', '$2y$10$lT5ruLYK2O/O72RWz/2bkuGzjvqC3m30yyvvlC1QQM3Y6w8aBaHiS', NULL, NULL, NULL, 'Sophomore', '[\"student\"]', '2025-07-05 17:49:53', '2025-07-05 17:49:53'),
(16, 'tutor1', NULL, 'tutor1@example.com', '$2y$10$1iSAHfTuvakEodYcFoGUteOPgIo/upJHOnd4XUVx.Lfq30gu.uiIu', NULL, NULL, NULL, 'Senior', '[\"assist\",\"student\"]', '2025-07-05 17:49:53', '2025-07-05 17:49:53'),
(17, 'matan', NULL, 'matan@gmail.com', '$2y$10$IhQKPYSPeaCGKBwXbeG5fu1q2h6hhV3fxPQU7xd2fP2MVejdMqvB.', NULL, NULL, NULL, 'Senior', '[\"assist\"]', '2025-07-05 17:50:56', '2025-07-05 17:50:56'),
(18, 'amen', NULL, 'amen@gmail.com', '$2y$10$6xtlbThZs2ZseLSPP0OaVOOabtzmf3Ca0e0FjQpgJ4vrJC0DWqEQu', NULL, NULL, NULL, 'Senior', '[\"student\"]', '2025-07-05 18:58:10', '2025-07-05 18:58:10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assistants`
--
ALTER TABLE `assistants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_assistants_user_id` (`user_id`);

--
-- Indexes for table `assistant_courses`
--
ALTER TABLE `assistant_courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `assistant_id` (`assistant_id`,`course_id`),
  ADD KEY `idx_assistant_courses_assistant_id` (`assistant_id`),
  ADD KEY `idx_assistant_courses_course_id` (`course_id`);

--
-- Indexes for table `connections`
--
ALTER TABLE `connections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `assistant_id` (`assistant_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tutor_id` (`tutor_id`),
  ADD KEY `idx_sender_id` (`sender_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `messages_backup_20240706`
--
ALTER TABLE `messages_backup_20240706`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_messages_tutor_id` (`tutor_id`),
  ADD KEY `idx_messages_sender_id` (`sender_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assistants`
--
ALTER TABLE `assistants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `assistant_courses`
--
ALTER TABLE `assistant_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `connections`
--
ALTER TABLE `connections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `messages_backup_20240706`
--
ALTER TABLE `messages_backup_20240706`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assistants`
--
ALTER TABLE `assistants`
  ADD CONSTRAINT `assistants_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assistant_courses`
--
ALTER TABLE `assistant_courses`
  ADD CONSTRAINT `assistant_courses_ibfk_1` FOREIGN KEY (`assistant_id`) REFERENCES `assistants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assistant_courses_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `connections`
--
ALTER TABLE `connections`
  ADD CONSTRAINT `connections_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `connections_ibfk_2` FOREIGN KEY (`assistant_id`) REFERENCES `assistants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages_backup_20240706`
--
ALTER TABLE `messages_backup_20240706`
  ADD CONSTRAINT `messages_backup_20240706_ibfk_1` FOREIGN KEY (`tutor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_backup_20240706_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
