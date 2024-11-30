-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 30, 2024 at 01:28 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `litdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `courseId` int(11) NOT NULL AUTO_INCREMENT,
  `courseName` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `language` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `level` enum('Basics','Intermediate','Advanced') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `description` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`courseId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- --------------------------------------------------------

--
-- Table structure for table `quiz_scores`
--

CREATE TABLE `quiz_scores` (
  `scoreId` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `courseId` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `dateTaken` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`scoreId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstName` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `lastName` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `email` varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `password` varchar(225) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `role` int(11) NOT NULL DEFAULT 2,
  `joinDate` date NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstName`, `lastName`, `email`, `password`, `role`, `joinDate`) VALUES
(1, 'trial', 'test', 'nana.marge@home.com', '$2y$10$33s8q.GmM.LSzsR.uDmcbePksMGKzjY7WBtUCqfeLmTGRarHxXMXu', 2, '2024-11-30'),
(2, 'Marge', 'Hagan', 'margenana@home.com', '$2y$10$DQWj8ucgDbBEpZCqRZOMWOutJS53JVyyQqKdqwVRdvbUaMULoxEQW', 2, '2024-11-30');

-- --------------------------------------------------------

--
-- Table structure for table `user_progress`
--

CREATE TABLE `user_progress` (
  `progressId` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `courseId` int(11) NOT NULL,
  `wordsLearned` int(11) DEFAULT 0,
  `quizzesTaken` int(11) DEFAULT 0,
  `totalScore` int(11) DEFAULT 0,
  `lastAccessed` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`progressId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `words`
--

CREATE TABLE `words` (
  `wordId` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `sourceLanguage` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `targetLanguage` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `pronunciation` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `translation` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `courseId` int(11) NULL,
  `category` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `difficulty` enum('easy','medium','hard') DEFAULT 'medium',
  `createdAt` date NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`wordId`),
  INDEX `category_index` (`category`),
  INDEX `difficulty_index` (`difficulty`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Table structure for table `word_of_day`
--

CREATE TABLE IF NOT EXISTS `word_of_day` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `wordId` INT NOT NULL,
    `dateShown` DATE NOT NULL DEFAULT CURRENT_DATE,
    FOREIGN KEY (`wordId`) REFERENCES `words`(`wordId`),
    UNIQUE KEY `unique_date` (`dateShown`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_enrollments`
--

CREATE TABLE `user_enrollments` (
  `enrollmentId` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `courseId` int(11) NOT NULL,
  `enrollmentDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','completed','dropped') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`enrollmentId`),
  UNIQUE KEY `unique_enrollment` (`userId`, `courseId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--


-- Constraints for dumped tables
--

--
-- Constraints for table `quiz_scores`
--
ALTER TABLE user_progress 
ADD INDEX idx_user_course (userId, courseId);

ALTER TABLE words 
ADD INDEX idx_course_word (courseId, word);

ALTER TABLE user_enrollments 
ADD INDEX idx_user_status (userId, status);

ALTER TABLE `quiz_scores`
  ADD CONSTRAINT `quiz_scores_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `quiz_scores_ibfk_2` FOREIGN KEY (`courseId`) REFERENCES `courses` (`courseId`),
  ADD CONSTRAINT `valid_score` CHECK (`score` >= 0 AND `score` <= 100);

--
-- Constraints for table `user_progress`
--
ALTER TABLE `user_progress`
  ADD CONSTRAINT `user_progress_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_progress_ibfk_2` FOREIGN KEY (`courseId`) REFERENCES `courses` (`courseId`);

--
-- Constraints for table `words`
--
ALTER TABLE `words`
  ADD CONSTRAINT `words_ibfk_1` FOREIGN KEY (`courseId`) REFERENCES `courses` (`courseId`)
  ON DELETE SET NULL;


-- Add index for email as it's likely used for login
ALTER TABLE `users` 
  ADD UNIQUE INDEX `email_index` (`email`);

-- Add index for common joins and filters
ALTER TABLE `words` 
  ADD INDEX `language_index` (`sourceLanguage`, `targetLanguage`);

-- Constraints for table `user_enrollments`
--
ALTER TABLE `user_enrollments`
  ADD CONSTRAINT `user_enrollments_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_enrollments_ibfk_2` FOREIGN KEY (`courseId`) REFERENCES `courses` (`courseId`);

-- These tables will store API responses for caching purposes
CREATE TABLE IF NOT EXISTS `translations` (
    `translationId` INT NOT NULL AUTO_INCREMENT,
    `wordId` INT NOT NULL,
    `translation` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `context` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
    `createdAt` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    `expiresAt` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`translationId`),
    KEY `wordId` (`wordId`),
    FOREIGN KEY (`wordId`) REFERENCES `words`(`wordId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `word_examples` (
  `exampleId` int(11) NOT NULL AUTO_INCREMENT,
  `wordId` int(11) NOT NULL,
  `original` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `translation` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `expiresAt` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`exampleId`),
  KEY `wordId` (`wordId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `learned_words` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `word_id` INT NOT NULL,
    `learned_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
    FOREIGN KEY (`word_id`) REFERENCES `words`(`wordId`),
    INDEX `idx_user_word` (`user_id`, `word_id`),
    INDEX `idx_learned_date` (`learned_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add index for faster lookups of learned words
-- ALTER TABLE learned_words
-- ADD INDEX idx_user_word (user_id, word_id),
-- ADD INDEX idx_learned_date (learned_date);

-- Add missing indexes if they don't exist
CREATE INDEX IF NOT EXISTS `idx_word_course` ON `words` (`courseId`);
CREATE INDEX IF NOT EXISTS `idx_word_languages` ON `words` (`sourceLanguage`, `targetLanguage`);


-- Insert just French and Spanish basic courses
INSERT INTO `courses` (`courseName`, `language`, `level`, `description`) VALUES
('French', 'French', 'Basics', 'Learn the fundamentals of French language including basic vocabulary and phrases'),
('Spanish', 'Spanish', 'Basics', 'Learn the fundamentals of Spanish language including basic vocabulary and phrases');




/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
