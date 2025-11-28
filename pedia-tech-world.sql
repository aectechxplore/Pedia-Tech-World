-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 28, 2025 at 07:07 AM
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
-- Database: `pediatech_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `education` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `promo_code` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'New',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `name`, `email`, `phone`, `address`, `type`, `education`, `file_path`, `promo_code`, `status`, `created_at`) VALUES
(1, 'sanjay kumar', 'aeccse202130@gmail.com', '09363534963', 'Omalur\r\n123R', 'course', 'Web development', 'uploads/id_proofs/6925694adbd5e_Beige Elegant Art and Design Training Class Participation Certificate.pdf', NULL, 'New', '2025-11-25 08:31:06'),
(2, 'sanjay kumar', 'aeccse202130@gmail.com', '09363534963', 'Omalur\r\n123R', 'achievement', 'Web development', 'uploads/id_proofs/692732e02fefe_ed-hardie-xG02JzIBf7o-unsplash.jpg', NULL, 'New', '2025-11-26 17:03:28'),
(3, 'sanjay kumar', 'aeccse202130@gmail.com', '09363534963', 'Omalur\r\n123R', 'project', 'Web development', 'uploads/id_proofs/6927365629d63_vladislav-nikonov-XF1JxGH92Js-unsplash.jpg', '001', 'New', '2025-11-26 17:18:14');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `check_in_time` time DEFAULT NULL,
  `status` enum('Present','Absent') DEFAULT 'Present'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `user_id`, `date`, `check_in_time`, `status`) VALUES
(2, 3, '2025-11-26', '19:29:23', 'Present'),
(3, 3, '2025-11-28', '06:46:44', 'Present');

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `fee` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `description`, `duration`, `fee`) VALUES
(1, 'Full Stack Web Development', 'Master the MERN stack (MongoDB, Express, React, Node.js).', '3 Months', '₹20,000'),
(2, 'Mobile App Development', 'Build iOS and Android apps using Flutter and React Native.', '4 Months', '₹25,000'),
(3, 'UI/UX Design Mastery', 'Learn Figma, Adobe XD, and user-centric design principles.', '2 Months', '₹15,000'),
(4, 'Video Editing ', 'Social media reels, corporate videos, event \r\nhighlight reels.', '2 Months', '₹30,000'),
(5, 'Audio Production ', 'Podcast editing, voice-over recording, sound \r\ndesign.', '3 month', '₹35,000');

-- --------------------------------------------------------

--
-- Table structure for table `flash_sale`
--

CREATE TABLE `flash_sale` (
  `id` int(11) NOT NULL DEFAULT 1,
  `expiry_date` datetime DEFAULT NULL,
  `description` varchar(255) DEFAULT 'Limited Time Offer!'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `flash_sale`
--

INSERT INTO `flash_sale` (`id`, `expiry_date`, `description`) VALUES
(1, '2025-11-28 11:39:00', 'Hurry! Offer Ends Soon.');

-- --------------------------------------------------------

--
-- Table structure for table `offers`
--

CREATE TABLE `offers` (
  `id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expiry_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `portfolio`
--

CREATE TABLE `portfolio` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `category` enum('project','client','achievement') DEFAULT 'project',
  `description` text DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `project_url` varchar(255) DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `image_path` text DEFAULT NULL,
  `video_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` enum('Ongoing','Completed','Pending') DEFAULT 'Ongoing',
  `progress` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `name`, `status`, `progress`) VALUES
(1, 'E-Commerce Startup', 'Ongoing', 65),
(2, 'Internal CRM System', 'Completed', 100),
(3, 'Client Mobile App', 'Pending', 50);

-- --------------------------------------------------------

--
-- Table structure for table `project_assignments`
--

CREATE TABLE `project_assignments` (
  `id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_assignments`
--

INSERT INTO `project_assignments` (`id`, `project_id`, `user_id`) VALUES
(1, 3, 3);

-- --------------------------------------------------------

--
-- Table structure for table `project_checklist`
--

CREATE TABLE `project_checklist` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `topic` varchar(255) NOT NULL,
  `status` enum('Pending','Ongoing','Completed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_checklist`
--

INSERT INTO `project_checklist` (`id`, `project_id`, `user_id`, `topic`, `status`, `created_at`) VALUES
(1, 3, 3, 'hello', 'Pending', '2025-11-26 18:14:41'),
(2, 3, 3, 'hi', 'Pending', '2025-11-26 18:14:45'),
(3, 3, 3, 'hhhhh', 'Ongoing', '2025-11-26 18:14:59'),
(4, 3, 3, 'aaaaa', 'Ongoing', '2025-11-26 18:15:07'),
(5, 3, 3, 'bbb', 'Completed', '2025-11-26 18:15:15'),
(6, 3, 3, 'nnnnnnnnn', 'Completed', '2025-11-26 18:15:24');

-- --------------------------------------------------------

--
-- Table structure for table `project_updates`
--

CREATE TABLE `project_updates` (
  `id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `update_text` text DEFAULT NULL,
  `update_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_updates`
--

INSERT INTO `project_updates` (`id`, `project_id`, `user_id`, `update_text`, `update_date`) VALUES
(1, 3, 3, 'done', '2025-11-26 18:08:06');

-- --------------------------------------------------------

--
-- Table structure for table `promo_codes`
--

CREATE TABLE `promo_codes` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount` varchar(100) NOT NULL,
  `type` enum('project','course','all') DEFAULT 'all',
  `expiry_date` date NOT NULL,
  `usage_limit` int(11) DEFAULT 1,
  `used_count` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promo_codes`
--

INSERT INTO `promo_codes` (`id`, `code`, `discount`, `type`, `expiry_date`, `usage_limit`, `used_count`, `status`, `created_at`) VALUES
(1, '001', '10%', 'all', '2025-11-26', 1, 1, 'active', '2025-11-26 17:14:33');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `price` varchar(50) NOT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `features` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `category`, `title`, `price`, `duration`, `features`, `created_at`) VALUES
(1, 'Website Development', 'Startup Package', '₹10,000-30,000', '3 Weeks', '[\"5-7 Pages\",\"Contact Form\",\"Mobile Responsive\"]', '2025-11-26 16:40:57'),
(2, 'Website Development', 'Business Pro', '₹50,000-1,00,000', '6-10 Weeks', '[\"Up to 15 Pages\",\"Custom Design & CMS & Basic SEO\"]', '2025-11-26 20:04:35'),
(3, 'Website Development', 'E-Commerce', '₹1,50,000-2,00,000+', '12-20 Weeks', '[\"50+ Products\",\"Shipping Options\",\"custom design\",\"payment gateway integration\",\"shipping options\",\"inventory management\",\"advanced SEO.\"]', '2025-11-26 20:06:07'),
(4, 'Mobile app development', 'Basic App (MVP) ', '₹1,50,000 ₹5,00,000 ', '10–16 weeks', '[\"Simple user interface\",\"essential features\",\"single \\r\\nplatform (iOS or Android).\"]', '2025-11-26 20:10:16'),
(5, 'Mobile app development', 'Standard App ', '₹8,00,000-₹15,00,000', '18–26 weeks', '[\"Custom UI\\/UX\",\"database integration\",\"user \\r\\naccounts\",\"push notifications\",\"cross-platform.\"]', '2025-11-26 20:12:02'),
(6, 'Mobile app development', 'Complex App ', '₹20,00,000+', '26+ weeks', '[\"Advanced features\",\"third-party API integration\",\"robust backend\",\"ongoing support.\"]', '2025-11-26 20:13:03');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `role` enum('admin','employee') DEFAULT 'employee',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `employee_id`, `role`, `created_at`, `profile_image`) VALUES
(1, 'Pedia Tech World', 'Pedia', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'admin', '2025-11-25 05:25:48', 'uploads/profiles/1764176255_admin_1.png'),
(3, 'sanjay kumar', 'aeccseict235@gmail.com', '$2y$10$pPrv7j1pWAKjpnmEegFnreOQ5rENvxvR1N6Qyp1ZssBbw6B6WHj8i', 'EMP0001', 'employee', '2025-11-25 15:10:07', 'uploads/profiles/1764083407_pexels-phil-mitchell-161192924-13704832.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `flash_sale`
--
ALTER TABLE `flash_sale`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `offers`
--
ALTER TABLE `offers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `portfolio`
--
ALTER TABLE `portfolio`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `project_assignments`
--
ALTER TABLE `project_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `project_checklist`
--
ALTER TABLE `project_checklist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `project_updates`
--
ALTER TABLE `project_updates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `employee_id` (`employee_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `offers`
--
ALTER TABLE `offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `portfolio`
--
ALTER TABLE `portfolio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `project_assignments`
--
ALTER TABLE `project_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `project_checklist`
--
ALTER TABLE `project_checklist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `project_updates`
--
ALTER TABLE `project_updates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `promo_codes`
--
ALTER TABLE `promo_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_assignments`
--
ALTER TABLE `project_assignments`
  ADD CONSTRAINT `project_assignments_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_assignments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_checklist`
--
ALTER TABLE `project_checklist`
  ADD CONSTRAINT `project_checklist_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_checklist_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_updates`
--
ALTER TABLE `project_updates`
  ADD CONSTRAINT `project_updates_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_updates_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
