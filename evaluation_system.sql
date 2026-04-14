-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 23, 2026 at 03:31 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `evaluation_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_settings`
--

CREATE TABLE `academic_settings` (
  `id` int(11) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `semester` varchar(30) NOT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `evaluation`
--

CREATE TABLE `evaluation` (
  `id` int(11) NOT NULL,
  `teacher_name` varchar(100) NOT NULL,
  `rating` decimal(4,2) NOT NULL,
  `feedback` text NOT NULL,
  `rating_comm` tinyint(1) NOT NULL,
  `rating_mastery` tinyint(1) NOT NULL,
  `rating_punctual` tinyint(1) NOT NULL,
  `department` varchar(20) NOT NULL,
  `year_level` varchar(20) DEFAULT '',
  `semester` varchar(20) DEFAULT '',
  `term` varchar(20) DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `department` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `name`, `department`, `created_at`) VALUES
(1, 'Mr. Jerick Barnatia', 'BSIT', '2026-03-23 14:30:45'),
(2, 'Mr. Elizor Villanueva', 'BSIT', '2026-03-23 14:30:45'),
(3, 'Mr. Diosdado Reyes', 'BSIT', '2026-03-23 14:30:45'),
(4, 'Mr. Vladimir Figueroa', 'BSIT', '2026-03-23 14:30:45'),
(5, 'Mr. Joel Altura', 'BSIT', '2026-03-23 14:30:45'),
(6, 'Mr. Jonathan Alberto', 'BSIT', '2026-03-23 14:30:45'),
(7, 'Donabel Tangunan', 'BSEDUC', '2026-03-23 14:30:45'),
(8, 'Reynaldo Millan', 'BSEDUC', '2026-03-23 14:30:45'),
(9, 'Camille Rose Gasmino', 'BSEDUC', '2026-03-23 14:30:45'),
(10, 'Rose Ann Jade Gloria', 'BSEDUC', '2026-03-23 14:30:45'),
(11, 'Arleene Ambrocio', 'BSEDUC', '2026-03-23 14:30:45'),
(12, 'Nilbert Lorlucass', 'BSCRIM', '2026-03-23 14:30:45'),
(13, 'Kyla Melissa Pascual', 'BSCRIM', '2026-03-23 14:30:45'),
(14, 'Pauline Joyce Tomas', 'BSCRIM', '2026-03-23 14:30:45'),
(15, 'Eliza Jacinto', 'BSCRIM', '2026-03-23 14:30:45'),
(16, 'Jan Marco Dancel', 'BSCRIM', '2026-03-23 14:30:45'),
(17, 'Grezelle Ignacio', 'BSHM', '2026-03-23 14:30:45'),
(18, 'Nicole Anne Barangan', 'BSHM', '2026-03-23 14:30:45'),
(19, 'Jhermae Anne Castro', 'BSHM', '2026-03-23 14:30:45'),
(20, 'Cerele Jean Cristobal', 'BSHM', '2026-03-23 14:30:45'),
(21, 'Desiree Monje', 'BSHM', '2026-03-23 14:30:45'),
(22, 'Ronnie Dandi', 'BSOAD', '2026-03-23 14:30:45'),
(23, 'Nicole Anne Barangan', 'BSOAD', '2026-03-23 14:30:45'),
(24, 'Joseph Agustin', 'BSOAD', '2026-03-23 14:30:45'),
(25, 'Tuzsha Mae Bondoc', 'BSOAD', '2026-03-23 14:30:45'),
(26, 'Willmarie Tomas', 'BSOAD', '2026-03-23 14:30:45'),
(27, 'Rowena Villanueva', 'BSOAD', '2026-03-23 14:30:45'),
(28, 'Jericho Martinez', 'BSOAD', '2026-03-23 14:30:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(32) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_settings`
--
ALTER TABLE `academic_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `evaluation`
--
ALTER TABLE `evaluation`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_settings`
--
ALTER TABLE `academic_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `evaluation`
--
ALTER TABLE `evaluation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
