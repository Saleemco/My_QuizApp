-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 24, 2025 at 11:27 PM
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
-- Database: `quiz_app`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(7) DEFAULT '#3B82F6',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `color`, `is_active`, `created_at`) VALUES
(1, 'Mathematics', 'Mathematical concepts and problem solving', '#EF4444', 1, '2025-07-24 17:06:48'),
(2, 'Science', 'General science topics', '#10B981', 1, '2025-07-24 17:06:48'),
(3, 'Computer Science', 'Programming and computer concepts', '#3B82F6', 1, '2025-07-24 17:06:48'),
(4, 'English', 'Language and literature', '#8B5CF6', 1, '2025-07-24 17:06:48'),
(5, 'History', 'Historical events and figures', '#F59E0B', 1, '2025-07-24 17:06:48'),
(6, 'General Knowledge', 'Mixed topics and trivia', '#6B7280', 1, '2025-07-24 17:06:48');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `question` text NOT NULL,
  `option_a` varchar(255) NOT NULL,
  `option_b` varchar(255) NOT NULL,
  `option_c` varchar(255) DEFAULT NULL,
  `option_d` varchar(255) DEFAULT NULL,
  `correct_option` enum('a','b','c','d') NOT NULL,
  `lecturer_id` int(11) NOT NULL,
  `explanation` text DEFAULT NULL,
  `difficulty` enum('easy','medium','hard') DEFAULT 'medium',
  `question_type` enum('multiple_choice','true_false','fill_blank') DEFAULT 'multiple_choice',
  `points` int(11) DEFAULT 1,
  `category_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`, `lecturer_id`, `explanation`, `difficulty`, `question_type`, `points`, `category_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Which of the following is the brain of the computer?', 'RAM', 'Hard Disk', 'CPU', 'Monitor', 'c', 3, NULL, 'medium', 'multiple_choice', 1, NULL, 1, '2025-08-22 10:39:37', '2025-08-22 10:59:37'),
(2, 'What does HTTP stand for?', 'HyperText Transmission Protocol', 'HyperText Transfer Protocol', 'HighText Transfer Protocol', 'Hyperlink Transmission Protocol', 'b', 3, NULL, 'medium', 'multiple_choice', 1, NULL, 1, '2025-08-22 10:40:53', '2025-08-22 10:59:42'),
(3, 'Which of the following is NOT an operating system?', 'Windows', 'Linux', 'Oracle', 'macOS', 'c', 3, NULL, 'medium', 'multiple_choice', 1, NULL, 1, '2025-08-22 10:41:31', '2025-08-22 10:59:32'),
(4, 'Which of the following is a primary storage device?', 'Hard Disk', 'CD-ROM', 'RAM', 'USB Drive', 'c', 3, NULL, 'medium', 'multiple_choice', 1, NULL, 1, '2025-08-22 10:44:26', '2025-08-22 10:59:28'),
(5, 'What does SQL stand for?', 'Structured Question Language', 'Sequential Query Language', 'Structured Query Language', 'Standard Query Language', 'c', 3, NULL, 'medium', 'multiple_choice', 1, NULL, 1, '2025-08-22 10:45:47', '2025-08-22 10:59:24'),
(6, 'Which data structure works on the principle of LIFO?', 'Queue', 'Stack', 'Linked List', 'Tree', 'b', 3, NULL, 'medium', 'multiple_choice', 1, NULL, 1, '2025-08-22 10:46:26', '2025-08-22 10:59:20'),
(7, 'Which company developed the Java programming language?', 'Microsoft', 'Sun Microsystems', 'Google', 'IBM', 'b', 3, NULL, 'medium', 'multiple_choice', 1, NULL, 1, '2025-08-22 10:47:02', '2025-08-22 10:59:16'),
(8, 'Which of the following is a non-volatile memory?', 'Cache', 'RAM', 'ROM', 'Register', 'c', 3, NULL, 'medium', 'multiple_choice', 1, NULL, 1, '2025-08-22 10:48:02', '2025-08-22 10:59:05'),
(9, 'What does IP in IP address stand for?', 'Internet Protocol', 'Internal Process', 'Internet Program', 'Interface Port', 'a', 3, NULL, 'medium', 'multiple_choice', 1, NULL, 1, '2025-08-22 10:48:47', '2025-08-22 10:58:53'),
(10, 'Which layer of the OSI model deals with routing?', 'Physical', 'Data Link', 'Network', 'Transport', 'c', 3, NULL, 'medium', 'multiple_choice', 1, NULL, 1, '2025-08-22 10:49:30', '2025-08-22 10:58:37'),
(11, 'Which sorting algorithm has the worst-case time complexity of O(n²)?', 'Merge Sort', 'Quick Sort', 'Bubble Sort', 'Heap Sort', 'c', 3, NULL, 'medium', 'multiple_choice', 1, NULL, 1, '2025-08-22 10:50:18', '2025-08-22 10:58:32'),
(12, 'Which of the following is NOT an example of a programming language?', 'Python', 'HTML', 'Java', 'C++', 'b', 3, NULL, 'medium', 'multiple_choice', 1, NULL, 1, '2025-08-22 10:50:56', '2025-08-22 10:58:27'),
(13, 'In C programming, which symbol is used for comments?', '// or /* */', '#', '%%', '--', 'a', 3, NULL, 'medium', 'multiple_choice', 1, NULL, 1, '2025-08-22 10:51:43', '2025-08-22 10:58:16'),
(14, 'What is the full form of URL?', 'Uniform Resource Locator', 'Universal Reference Locator', 'Uniform Reference Link', 'Universal Resource Link', 'a', 3, NULL, 'medium', 'multiple_choice', 1, NULL, 1, '2025-08-22 10:52:22', '2025-08-22 10:58:09'),
(15, 'Which of the following is an example of open-source software?', 'MS Word', 'Adobe Photoshop', 'Linux', 'Oracle Database', 'c', 3, NULL, 'medium', 'multiple_choice', 1, NULL, 1, '2025-08-22 10:52:57', '2025-08-22 10:58:02'),
(16, 'Which of the following is the brain of the computer?', 'RAM', 'Hard Disk', 'CPU', 'Monitor', 'c', 1, NULL, 'medium', 'multiple_choice', 1, NULL, 1, '2025-08-22 10:56:54', '2025-08-22 10:58:22');

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `time_limit` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quiz_id` int(11) DEFAULT NULL,
  `score` int(11) NOT NULL,
  `total_questions` int(11) NOT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `percentage` decimal(5,2) GENERATED ALWAYS AS (round(`score` / `total_questions` * 100,2)) STORED,
  `time_taken` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `is_completed` tinyint(1) DEFAULT 1,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_attempts`
--

INSERT INTO `quiz_attempts` (`id`, `user_id`, `quiz_id`, `score`, `total_questions`, `attempt_time`, `time_taken`, `ip_address`, `user_agent`, `is_completed`, `started_at`, `completed_at`) VALUES
(1, 4, NULL, 13, 15, '2025-08-22 11:04:54', NULL, NULL, NULL, 1, '2025-08-22 20:04:54', '2025-08-22 20:04:54');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_responses`
--

CREATE TABLE `quiz_responses` (
  `id` int(11) NOT NULL,
  `attempt_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `selected_option` enum('a','b','c','d') DEFAULT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  `time_spent` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_settings`
--

CREATE TABLE `quiz_settings` (
  `id` int(11) NOT NULL,
  `lecturer_id` int(11) NOT NULL,
  `min_questions` int(11) DEFAULT 10,
  `max_questions` int(11) DEFAULT 30
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_by`, `updated_at`) VALUES
(1, 'site_name', 'QuizMaster Pro', 'Name of the quiz application', NULL, '2025-07-24 17:06:48'),
(2, 'site_description', 'Advanced Quiz Management System', 'Description of the site', NULL, '2025-07-24 17:06:48'),
(3, 'max_quiz_attempts', '3', 'Maximum number of quiz attempts per user', NULL, '2025-07-24 17:06:48'),
(4, 'default_quiz_time', '900', 'Default quiz time in seconds (15 minutes)', NULL, '2025-07-24 17:06:48'),
(5, 'enable_notifications', '1', 'Enable system notifications', NULL, '2025-07-24 17:06:48'),
(6, 'maintenance_mode', '0', 'Enable maintenance mode', NULL, '2025-07-24 17:06:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `matric_number` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','lecturer','student') DEFAULT 'student',
  `lecturer_id` int(11) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `email_verified` tinyint(1) DEFAULT 0,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `matric_number`, `password`, `role`, `lecturer_id`, `profile_image`, `phone`, `department`, `is_active`, `email_verified`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@example.com', NULL, '$2y$12$8EQVTHKAJYQ.p245LNbDs.tuRS2molCzWUJYq54/uhwks6HOzanRq', 'admin', NULL, NULL, NULL, NULL, 1, 0, NULL, '2025-07-24 17:38:43', '2025-07-24 17:38:43'),
(3, 'Tomi Philips', 'tomi@gmail.com', '', '$2y$10$UWXoTW3cnEGHP32I6KaG7u6PakAIw4SMeuSsrNjehgyvrCepe6tyu', 'lecturer', NULL, NULL, NULL, NULL, 1, 0, NULL, '2025-07-24 17:38:43', '2025-07-24 17:38:43'),
(4, 'Adegoke Favour', 'adegokefavour240@gmail.com', 'N/CS/21/1500', '$2y$10$RkFp8DvZJosekbGSuYoRweeBmv1utaA/rtKmk4begC/JpcMA5WQSW', 'student', 3, NULL, NULL, NULL, 1, 0, NULL, '2025-08-22 10:53:57', '2025-08-22 10:53:57'),
(5, 'Adekayero Michael', 'adekayoromichael@gmail.com', 'N/CS/21/1501', '$2y$10$lRUVn8ah5RFjK80m.TEO4utB8TT6hfChVJXQjSCFFZLHt0kgQ3hpC', 'student', 3, NULL, NULL, NULL, 1, 0, NULL, '2025-08-22 15:05:14', '2025-08-22 15:05:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_name` (`name`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_read` (`is_read`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lecturer_id` (`lecturer_id`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_difficulty` (`difficulty`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_quiz` (`quiz_id`),
  ADD KEY `idx_score` (`score`),
  ADD KEY `idx_completed` (`is_completed`);

--
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `quiz_responses`
--
ALTER TABLE `quiz_responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_attempt` (`attempt_id`),
  ADD KEY `idx_question` (`question_id`);

--
-- Indexes for table `quiz_settings`
--
ALTER TABLE `quiz_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lecturer_id` (`lecturer_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `idx_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `matric_number` (`matric_number`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_matric` (`matric_number`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_lecturer` (`lecturer_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_responses`
--
ALTER TABLE `quiz_responses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz_settings`
--
ALTER TABLE `quiz_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `questions_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `quiz_attempts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_attempts_ibfk_2` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `quiz_questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_questions_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_responses`
--
ALTER TABLE `quiz_responses`
  ADD CONSTRAINT `quiz_responses_ibfk_1` FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_responses_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_settings`
--
ALTER TABLE `quiz_settings`
  ADD CONSTRAINT `quiz_settings_ibfk_1` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD CONSTRAINT `system_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
