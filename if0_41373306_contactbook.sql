-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql301.infinityfree.com
-- Generation Time: Mar 15, 2026 at 12:49 PM
-- Server version: 11.4.10-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_41373306_contactbook`
--

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) DEFAULT '',
  `photo` varchar(255) DEFAULT '',
  `statuz` enum('active','inactive') DEFAULT 'active',
  `approval_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `owner_id` int(11) DEFAULT 0,
  `dob` varchar(10) DEFAULT '',
  `gender` varchar(10) DEFAULT '',
  `father_name` varchar(150) DEFAULT '',
  `mother_name` varchar(150) DEFAULT '',
  `Home_Town` varchar(150) DEFAULT '',
  `mo_no` varchar(20) DEFAULT '',
  `wp_no` varchar(20) DEFAULT '',
  `block_no` varchar(50) DEFAULT '',
  `address_line1` varchar(255) DEFAULT '',
  `street_address` varchar(255) DEFAULT '',
  `city` varchar(100) DEFAULT '',
  `state` varchar(100) DEFAULT '',
  `zip` int(11) DEFAULT 0,
  `country` varchar(100) DEFAULT '',
  `Vatan_vilage` varchar(150) DEFAULT '',
  `Vatan_block_no` varchar(50) DEFAULT '',
  `Vatan_Street_address` varchar(255) DEFAULT '',
  `Vatan_address_line1` varchar(255) DEFAULT '',
  `Vatan_city` varchar(100) DEFAULT '',
  `Vatan_state` varchar(100) DEFAULT '',
  `Vatan_zip` int(11) DEFAULT 0,
  `Vatan_country` varchar(100) DEFAULT '',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `first_name`, `last_name`, `photo`, `statuz`, `approval_status`, `owner_id`, `dob`, `gender`, `father_name`, `mother_name`, `Home_Town`, `mo_no`, `wp_no`, `block_no`, `address_line1`, `street_address`, `city`, `state`, `zip`, `country`, `Vatan_vilage`, `Vatan_block_no`, `Vatan_Street_address`, `Vatan_address_line1`, `Vatan_city`, `Vatan_state`, `Vatan_zip`, `Vatan_country`, `created_at`, `updated_at`) VALUES
(2, 'Gautami', 'Pandya', '', 'active', 'approved', 0, '', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', 0, '', '2026-03-15 11:35:18', '2026-03-15 11:36:58'),
(3, 'Mohit', 'Chauhan', '', 'active', 'approved', 0, '', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', 0, '', '2026-03-15 11:35:44', '2026-03-15 11:37:00'),
(4, 'Ramesh', 'Joshi', '', 'active', 'approved', 0, '', '', '', '', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', 0, '', '2026-03-15 11:35:55', '2026-03-15 11:37:03'),
(5, 'Mitesh', 'Patel', '', 'active', 'approved', 0, '', 'male', 'dfg', 'dfg', 'Ahmeadabad', '9999999999', '8888888888', '234', 'Nr.sdsdf', 'Sola', 'Ahmedabad', 'Gujrat', 383010, 'India', 'Ahmedabad', '234', 'Sola', 'Nr.sdsdf', 'Ahmedabad', 'Gujrat', 383010, 'India', '2026-03-15 11:39:08', '2026-03-15 16:33:24'),
(6, 'Mama', 'janana', '', 'active', 'approved', 0, '', 'male', 'sdf', 'sdfsdf', 'Jamla', '9999999999', '4567567678', '', 'Jamla', 'Jamla', 'Himmatnagar', 'Gujrat', 383010, 'India', 'dfgdf', '', 'dfgdfgfd', 'dfgdfg', 'dfgdfg', 'dfgdfg', 345634, 'dfgdfgfd', '2026-03-15 11:44:44', '2026-03-15 15:47:50'),
(7, 'Anand', 'Pandya', '', 'active', 'approved', 0, '1989-07-26', 'male', 'Xxxx  bhai', 'Testben', 'jamla', '9999999999', '9999999999', '383', 'Rathodwas', 'Jamla', 'Himmatnagar ', 'Gujarat ', 383010, 'India', 'Gandhinagar ', 'C703', 'pramukh Elegance ', 'Raysan ', 'Gandhinagar ', 'Gujarat ', 382421, 'India ', '2026-03-15 11:48:07', '2026-03-15 11:57:02'),
(8, 'Arvind', 'Paradva', '', 'active', 'approved', 0, '2025-10-10', 'male', '', '', '', '', '', '', '', '', '', '', 0, '', '', '', '', '', '', '', 0, '', '2026-03-15 12:15:22', '2026-03-15 15:47:54'),
(9, 'Gautami', 'Pandya', '', 'active', 'approved', 0, '', 'female', 'Anand', 'Pooja', 'Jamla', '9494949494', '', '', 'Raysan', 'Raysan', 'Gandhinagar', 'Gujrat', 382421, 'India', 'Jamla', '', 'Jamla', 'Jamla', 'Himmatnagar', 'Gujrat', 383010, 'India', '2026-03-15 15:51:19', '2026-03-15 16:30:11'),
(10, '23423', '234234', '', 'active', 'approved', 0, '', 'male', '', '', 'pdfs', '2342342343', '2342342333', '234', 'dfsdf', 'sdfsdf', 'sdfsdf', 'sdfsdf', 234234, 'sfdsfsdf', 'pdfs', '234', 'sdfsdf', 'dfsdf', 'sdfsdf', 'sdfsdf', 234234, 'sfdsfsdf', '2026-03-15 16:32:56', '2026-03-15 16:33:22');

-- --------------------------------------------------------

--
-- Table structure for table `contacts_pending`
--

CREATE TABLE `contacts_pending` (
  `id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL,
  `change_type` enum('create','update','delete') NOT NULL,
  `change_data` longtext NOT NULL,
  `requested_at` timestamp NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `review_note` varchar(255) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(20) NOT NULL,
  `last_name` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `profile_img` varchar(100) DEFAULT NULL,
  `is_admin` enum('1','0') NOT NULL DEFAULT '0',
  `is_active` enum('1','0') NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `profile_img`, `is_admin`, `is_active`) VALUES
(1, 'anand', 'pandya', 'anand.apandya89@gmail.com', '$2y$10$2qYbm6ElCIQg9mPlM6R8CunBy9Pkt.1WpADzZlviK8vQSB14bVjt.', NULL, '0', '0'),
(2, 'Ram', 'Pandya', 'ram@gmail.com', '$2y$10$WFr5D8YpeHsJbTzkl7nkRuQDV12oOU5OiOmUS.o4ZgFuMqcb2j7Ma', NULL, '0', '0');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contacts_pending`
--
ALTER TABLE `contacts_pending`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `contacts_pending`
--
ALTER TABLE `contacts_pending`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
