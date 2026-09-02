-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 26, 2026 at 08:42 AM
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
-- Database: `studyverse`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_info`
--

CREATE TABLE `admin_info` (
  `AdminID` varchar(8) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_info`
--

INSERT INTO `admin_info` (`AdminID`, `name`, `email`, `phone`) VALUES
('A1', 'Human', 'Human@mail.com', '01000000');

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `coursecode` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(500) NOT NULL,
  `credit` float NOT NULL,
  `total_mark` decimal(3,0) NOT NULL,
  `admin` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_resouce`
--

CREATE TABLE `course_resouce` (
  `course_resourceID` varchar(10) NOT NULL,
  `student_UID` varchar(8) NOT NULL,
  `coursecode` varchar(10) NOT NULL,
  `resource_type` varchar(100) NOT NULL,
  `content` varchar(500) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_student_took`
--

CREATE TABLE `course_student_took` (
  `userID` varchar(8) NOT NULL,
  `coursecode` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faculty_courselist`
--

CREATE TABLE `faculty_courselist` (
  `faculty_UID` varchar(8) NOT NULL,
  `coursecode` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faculty_info`
--

CREATE TABLE `faculty_info` (
  `userID` varchar(8) NOT NULL,
  `FID` int(10) DEFAULT NULL,
  `Initial` varchar(10) DEFAULT NULL,
  `Dept` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `group_consult_faculty`
--

CREATE TABLE `group_consult_faculty` (
  `groupID` varchar(10) NOT NULL,
  `faculty_uID` varchar(8) NOT NULL,
  `schedule` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `group_info`
--

CREATE TABLE `group_info` (
  `groupID` varchar(10) NOT NULL,
  `group_creator_uid` varchar(8) NOT NULL,
  `type` varchar(100) NOT NULL,
  `targeted_subjects` varchar(500) NOT NULL,
  `adminID` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `group_rated_by_student`
--

CREATE TABLE `group_rated_by_student` (
  `groupID` varchar(10) NOT NULL,
  `student_UID` varchar(8) NOT NULL,
  `rate` float NOT NULL,
  `review` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mentor`
--

CREATE TABLE `mentor` (
  `userID` varchar(8) NOT NULL,
  `MentorID` varchar(10) NOT NULL,
  `Subject` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `repository`
--

CREATE TABLE `repository` (
  `repoID` varchar(10) NOT NULL,
  `coursecode` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `adminID` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resource_uploaded_in_repo`
--

CREATE TABLE `resource_uploaded_in_repo` (
  `repoID` varchar(10) NOT NULL,
  `resourceID` varchar(10) NOT NULL,
  `upload_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_consult_mentor`
--

CREATE TABLE `student_consult_mentor` (
  `student_uID` varchar(8) NOT NULL,
  `mentor_studentID` varchar(8) NOT NULL,
  `mentorID` varchar(10) NOT NULL,
  `timeschedule` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_faculty_consultation`
--

CREATE TABLE `student_faculty_consultation` (
  `student_uID` varchar(8) NOT NULL,
  `faculty_uID` varchar(8) NOT NULL,
  `time_schedule` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_info`
--

CREATE TABLE `student_info` (
  `userID` varchar(8) NOT NULL,
  `SID` int(8) DEFAULT NULL,
  `cgpa` decimal(3,2) DEFAULT NULL,
  `semester` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_info`
--

INSERT INTO `student_info` (`userID`, `SID`, `cgpa`, `semester`) VALUES
('ZS22320', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_joins_group`
--

CREATE TABLE `student_joins_group` (
  `groupID` varchar(10) NOT NULL,
  `student_UID` varchar(8) NOT NULL,
  `joindate` date NOT NULL,
  `leaveDate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_rates_faculty`
--

CREATE TABLE `student_rates_faculty` (
  `student_uID` varchar(8) NOT NULL,
  `faculty_uID` varchar(8) NOT NULL,
  `rating` decimal(10,0) NOT NULL,
  `review` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_rates_mentor`
--

CREATE TABLE `student_rates_mentor` (
  `student_uID` varchar(8) NOT NULL,
  `mentor_studentID` varchar(8) NOT NULL,
  `mentorID` varchar(10) NOT NULL,
  `rate` float NOT NULL,
  `review` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_reviews_resources`
--

CREATE TABLE `student_reviews_resources` (
  `resourceID` varchar(10) NOT NULL,
  `student_UID` varchar(8) NOT NULL,
  `rate` float NOT NULL,
  `review` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_review_course`
--

CREATE TABLE `student_review_course` (
  `student_uID` varchar(8) NOT NULL,
  `coursecode` varchar(10) NOT NULL,
  `rate` float NOT NULL,
  `review` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `userphone`
--

CREATE TABLE `userphone` (
  `userID` varchar(8) NOT NULL,
  `phone` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `userphone`
--

INSERT INTO `userphone` (`userID`, `phone`) VALUES
('ZS22320', '017111111banai');

-- --------------------------------------------------------

--
-- Table structure for table `user_info`
--

CREATE TABLE `user_info` (
  `userID` varchar(8) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `road` varchar(100) NOT NULL,
  `area` varchar(100) NOT NULL,
  `AdminID` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_info`
--

INSERT INTO `user_info` (`userID`, `name`, `email`, `password`, `road`, `area`, `AdminID`) VALUES
('ZS22320', 'ZAREEN SUBAH', 'zareen.subah1@g.bracu.ac.bd', 'zareensubah', 'road11', 'banani', 'A1');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_info`
--
ALTER TABLE `admin_info`
  ADD PRIMARY KEY (`AdminID`);

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`coursecode`),
  ADD KEY `admin` (`admin`);

--
-- Indexes for table `course_resouce`
--
ALTER TABLE `course_resouce`
  ADD PRIMARY KEY (`course_resourceID`),
  ADD KEY `student_UID` (`student_UID`),
  ADD KEY `coursecode` (`coursecode`);

--
-- Indexes for table `course_student_took`
--
ALTER TABLE `course_student_took`
  ADD PRIMARY KEY (`userID`,`coursecode`),
  ADD KEY `coursecode` (`coursecode`);

--
-- Indexes for table `faculty_courselist`
--
ALTER TABLE `faculty_courselist`
  ADD PRIMARY KEY (`faculty_UID`,`coursecode`),
  ADD KEY `coursecode` (`coursecode`);

--
-- Indexes for table `faculty_info`
--
ALTER TABLE `faculty_info`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `FID` (`FID`,`Initial`);

--
-- Indexes for table `group_consult_faculty`
--
ALTER TABLE `group_consult_faculty`
  ADD PRIMARY KEY (`groupID`,`faculty_uID`,`schedule`);

--
-- Indexes for table `group_info`
--
ALTER TABLE `group_info`
  ADD PRIMARY KEY (`groupID`),
  ADD KEY `group_creator_uid` (`group_creator_uid`),
  ADD KEY `adminID` (`adminID`);

--
-- Indexes for table `group_rated_by_student`
--
ALTER TABLE `group_rated_by_student`
  ADD PRIMARY KEY (`groupID`,`student_UID`),
  ADD KEY `student_UID` (`student_UID`);

--
-- Indexes for table `mentor`
--
ALTER TABLE `mentor`
  ADD PRIMARY KEY (`userID`,`MentorID`),
  ADD UNIQUE KEY `MentorID` (`MentorID`);

--
-- Indexes for table `repository`
--
ALTER TABLE `repository`
  ADD PRIMARY KEY (`repoID`),
  ADD KEY `coursecode` (`coursecode`),
  ADD KEY `adminID` (`adminID`);

--
-- Indexes for table `resource_uploaded_in_repo`
--
ALTER TABLE `resource_uploaded_in_repo`
  ADD PRIMARY KEY (`repoID`,`resourceID`),
  ADD KEY `resourceID` (`resourceID`);

--
-- Indexes for table `student_consult_mentor`
--
ALTER TABLE `student_consult_mentor`
  ADD PRIMARY KEY (`student_uID`,`mentor_studentID`,`mentorID`,`timeschedule`),
  ADD KEY `mentor_studentID` (`mentor_studentID`),
  ADD KEY `mentorID` (`mentorID`);

--
-- Indexes for table `student_faculty_consultation`
--
ALTER TABLE `student_faculty_consultation`
  ADD PRIMARY KEY (`student_uID`,`faculty_uID`,`time_schedule`),
  ADD KEY `faculty_uID` (`faculty_uID`);

--
-- Indexes for table `student_info`
--
ALTER TABLE `student_info`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `SID` (`SID`);

--
-- Indexes for table `student_joins_group`
--
ALTER TABLE `student_joins_group`
  ADD PRIMARY KEY (`groupID`,`student_UID`),
  ADD KEY `student_UID` (`student_UID`);

--
-- Indexes for table `student_rates_faculty`
--
ALTER TABLE `student_rates_faculty`
  ADD PRIMARY KEY (`student_uID`,`faculty_uID`),
  ADD KEY `faculty_uID` (`faculty_uID`);

--
-- Indexes for table `student_rates_mentor`
--
ALTER TABLE `student_rates_mentor`
  ADD PRIMARY KEY (`student_uID`,`mentor_studentID`,`mentorID`),
  ADD KEY `mentor_studentID` (`mentor_studentID`);

--
-- Indexes for table `student_reviews_resources`
--
ALTER TABLE `student_reviews_resources`
  ADD PRIMARY KEY (`resourceID`,`student_UID`),
  ADD KEY `student_UID` (`student_UID`);

--
-- Indexes for table `student_review_course`
--
ALTER TABLE `student_review_course`
  ADD KEY `student_uID` (`student_uID`),
  ADD KEY `coursecode` (`coursecode`);

--
-- Indexes for table `userphone`
--
ALTER TABLE `userphone`
  ADD PRIMARY KEY (`userID`,`phone`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Indexes for table `user_info`
--
ALTER TABLE `user_info`
  ADD PRIMARY KEY (`userID`),
  ADD KEY `AdminID` (`AdminID`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `course`
--
ALTER TABLE `course`
  ADD CONSTRAINT `course_ibfk_1` FOREIGN KEY (`admin`) REFERENCES `admin_info` (`AdminID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `course_resouce`
--
ALTER TABLE `course_resouce`
  ADD CONSTRAINT `course_resouce_ibfk_1` FOREIGN KEY (`student_UID`) REFERENCES `student_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `course_resouce_ibfk_2` FOREIGN KEY (`coursecode`) REFERENCES `course` (`coursecode`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `course_student_took`
--
ALTER TABLE `course_student_took`
  ADD CONSTRAINT `course_student_took_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `student_info` (`userID`),
  ADD CONSTRAINT `course_student_took_ibfk_2` FOREIGN KEY (`coursecode`) REFERENCES `course` (`coursecode`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `faculty_courselist`
--
ALTER TABLE `faculty_courselist`
  ADD CONSTRAINT `faculty_courselist_ibfk_1` FOREIGN KEY (`faculty_UID`) REFERENCES `faculty_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `faculty_courselist_ibfk_2` FOREIGN KEY (`coursecode`) REFERENCES `course` (`coursecode`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `faculty_info`
--
ALTER TABLE `faculty_info`
  ADD CONSTRAINT `faculty_info_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `group_consult_faculty`
--
ALTER TABLE `group_consult_faculty`
  ADD CONSTRAINT `group_consult_faculty_ibfk_1` FOREIGN KEY (`groupID`) REFERENCES `group_info` (`groupID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `group_info`
--
ALTER TABLE `group_info`
  ADD CONSTRAINT `group_info_ibfk_2` FOREIGN KEY (`group_creator_uid`) REFERENCES `student_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `group_info_ibfk_3` FOREIGN KEY (`adminID`) REFERENCES `admin_info` (`AdminID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `group_rated_by_student`
--
ALTER TABLE `group_rated_by_student`
  ADD CONSTRAINT `group_rated_by_student_ibfk_1` FOREIGN KEY (`groupID`) REFERENCES `student_joins_group` (`groupID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `group_rated_by_student_ibfk_2` FOREIGN KEY (`student_UID`) REFERENCES `student_joins_group` (`student_UID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mentor`
--
ALTER TABLE `mentor`
  ADD CONSTRAINT `mentor_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `student_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `repository`
--
ALTER TABLE `repository`
  ADD CONSTRAINT `repository_ibfk_1` FOREIGN KEY (`coursecode`) REFERENCES `course` (`coursecode`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `repository_ibfk_2` FOREIGN KEY (`adminID`) REFERENCES `admin_info` (`AdminID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `resource_uploaded_in_repo`
--
ALTER TABLE `resource_uploaded_in_repo`
  ADD CONSTRAINT `resource_uploaded_in_repo_ibfk_1` FOREIGN KEY (`repoID`) REFERENCES `repository` (`repoID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `resource_uploaded_in_repo_ibfk_2` FOREIGN KEY (`resourceID`) REFERENCES `course_resouce` (`course_resourceID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_consult_mentor`
--
ALTER TABLE `student_consult_mentor`
  ADD CONSTRAINT `student_consult_mentor_ibfk_1` FOREIGN KEY (`student_uID`) REFERENCES `student_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_consult_mentor_ibfk_2` FOREIGN KEY (`mentor_studentID`) REFERENCES `mentor` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_consult_mentor_ibfk_3` FOREIGN KEY (`mentorID`) REFERENCES `mentor` (`MentorID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_faculty_consultation`
--
ALTER TABLE `student_faculty_consultation`
  ADD CONSTRAINT `student_faculty_consultation_ibfk_1` FOREIGN KEY (`faculty_uID`) REFERENCES `faculty_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_faculty_consultation_ibfk_2` FOREIGN KEY (`student_uID`) REFERENCES `student_info` (`userID`);

--
-- Constraints for table `student_info`
--
ALTER TABLE `student_info`
  ADD CONSTRAINT `student_info_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_joins_group`
--
ALTER TABLE `student_joins_group`
  ADD CONSTRAINT `student_joins_group_ibfk_1` FOREIGN KEY (`groupID`) REFERENCES `group_info` (`groupID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_joins_group_ibfk_2` FOREIGN KEY (`student_UID`) REFERENCES `student_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_rates_faculty`
--
ALTER TABLE `student_rates_faculty`
  ADD CONSTRAINT `student_rates_faculty_ibfk_1` FOREIGN KEY (`faculty_uID`) REFERENCES `faculty_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_rates_faculty_ibfk_2` FOREIGN KEY (`student_uID`) REFERENCES `student_info` (`userID`);

--
-- Constraints for table `student_rates_mentor`
--
ALTER TABLE `student_rates_mentor`
  ADD CONSTRAINT `student_rates_mentor_ibfk_1` FOREIGN KEY (`student_uID`) REFERENCES `student_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_rates_mentor_ibfk_2` FOREIGN KEY (`mentor_studentID`) REFERENCES `mentor` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_reviews_resources`
--
ALTER TABLE `student_reviews_resources`
  ADD CONSTRAINT `student_reviews_resources_ibfk_1` FOREIGN KEY (`resourceID`) REFERENCES `course_resouce` (`course_resourceID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_reviews_resources_ibfk_2` FOREIGN KEY (`student_UID`) REFERENCES `student_info` (`userID`);

--
-- Constraints for table `student_review_course`
--
ALTER TABLE `student_review_course`
  ADD CONSTRAINT `student_review_course_ibfk_1` FOREIGN KEY (`student_uID`) REFERENCES `course_student_took` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_review_course_ibfk_2` FOREIGN KEY (`coursecode`) REFERENCES `course_student_took` (`coursecode`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `userphone`
--
ALTER TABLE `userphone`
  ADD CONSTRAINT `userphone_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_info`
--
ALTER TABLE `user_info`
  ADD CONSTRAINT `user_info_ibfk_1` FOREIGN KEY (`AdminID`) REFERENCES `admin_info` (`AdminID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
