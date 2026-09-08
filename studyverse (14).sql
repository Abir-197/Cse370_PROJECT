-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 08, 2026 at 10:50 PM
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
-- Table structure for table `admin_course_delete_consensus`
--

CREATE TABLE `admin_course_delete_consensus` (
  `requestID` int(11) NOT NULL,
  `coursecode` varchar(10) NOT NULL,
  `requested_by_adminID` varchar(8) NOT NULL,
  `course_owner_adminID` varchar(8) NOT NULL,
  `status` enum('PENDING','APPROVED','REJECTED') NOT NULL DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_course_delete_consensus`
--

INSERT INTO `admin_course_delete_consensus` (`requestID`, `coursecode`, `requested_by_adminID`, `course_owner_adminID`, `status`, `created_at`) VALUES
(1, 'PHY112', 'A2', 'A1', 'PENDING', '2026-09-03 09:54:28');

-- --------------------------------------------------------

--
-- Table structure for table `admin_course_delete_requests`
--

CREATE TABLE `admin_course_delete_requests` (
  `requestID` int(11) NOT NULL,
  `coursecode` varchar(10) NOT NULL,
  `requestedBy` varchar(8) NOT NULL,
  `status` varchar(20) DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_course_delete_votes`
--

CREATE TABLE `admin_course_delete_votes` (
  `requestID` int(11) NOT NULL,
  `adminID` varchar(8) NOT NULL,
  `vote` varchar(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_delete_votes`
--

CREATE TABLE `admin_delete_votes` (
  `voteID` int(11) NOT NULL,
  `requestID` int(11) NOT NULL,
  `adminID` varchar(8) NOT NULL,
  `vote` enum('YES','NO') NOT NULL,
  `voted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_delete_votes`
--

INSERT INTO `admin_delete_votes` (`voteID`, `requestID`, `adminID`, `vote`, `voted_at`) VALUES
(1, 1, 'A1', 'NO', '2026-09-03 09:54:28');

-- --------------------------------------------------------

--
-- Table structure for table `admin_incentive_campaigns`
--

CREATE TABLE `admin_incentive_campaigns` (
  `campaignID` int(11) NOT NULL,
  `adminID` varchar(8) NOT NULL,
  `student_UID` varchar(8) NOT NULL,
  `reward_offer` varchar(150) NOT NULL,
  `status` enum('DISPATCHED','CLAIMED') NOT NULL DEFAULT 'DISPATCHED',
  `dispatched_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_incentive_campaigns`
--

INSERT INTO `admin_incentive_campaigns` (`campaignID`, `adminID`, `student_UID`, `reward_offer`, `status`, `dispatched_at`) VALUES
(1, 'A1', 'ZS22320', 'Top Note Contributor - 50 Academic Tokens', 'DISPATCHED', '2026-09-03 09:54:28'),
(2, 'A1001', 'S20018', 'Peer Tutor Excellence Recognition', 'CLAIMED', '2026-09-03 09:54:28'),
(3, 'A1', 'S20333', 'Senior Mentor Honor Roll & 30 Lab Tokens', 'DISPATCHED', '2026-09-03 09:57:11');

-- --------------------------------------------------------

--
-- Table structure for table `admin_info`
--

CREATE TABLE `admin_info` (
  `AdminID` varchar(8) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL DEFAULT 'admin123'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_info`
--

INSERT INTO `admin_info` (`AdminID`, `name`, `email`, `phone`, `password`) VALUES
('A1', 'System Administrator', 'admin1@studyverse.edu', '01000000', 'admin123'),
('A10', 'AMI', 'A10@gmail.com', '01600000', 'admin123'),
('A1001', 'Dr. Aminul Islam', 'aminul.islam@studyverse.edu', '', 'admin123'),
('A2', 'Rashidul Hasan', 'r.hasan@studyverse.edu', '', 'admin123'),
('A3', 'Farhana Chowdhury', 'f.chowdhury@studyverse.edu', '', 'admin123'),
('A4', 'Nafis Imtiaz', 'n.imtiaz@studyverse.edu', '', 'admin123'),
('A5', 'Tahmina Akter', 't.akter@studyverse.edu', '', 'admin123');

-- --------------------------------------------------------

--
-- Table structure for table `admin_messages`
--

CREATE TABLE `admin_messages` (
  `messageID` int(11) NOT NULL,
  `fromAdmin` varchar(8) NOT NULL,
  `toAdmin` varchar(8) NOT NULL,
  `message` varchar(500) NOT NULL,
  `status` varchar(20) DEFAULT 'UNREAD',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_messages`
--

INSERT INTO `admin_messages` (`messageID`, `fromAdmin`, `toAdmin`, `message`, `status`, `created_at`) VALUES
(1, 'A1', 'A1001', 'Please verify syllabus updates for CSE370 before term registration.', 'UNREAD', '2026-09-03 09:54:28'),
(2, 'A1001', 'A1', 'Reviewed and approved all database lab syllabi.', 'READ', '2026-09-03 09:54:28');

-- --------------------------------------------------------

--
-- Table structure for table `admin_work_due`
--

CREATE TABLE `admin_work_due` (
  `workID` int(11) NOT NULL,
  `adminID` varchar(8) NOT NULL,
  `title` varchar(150) NOT NULL,
  `details` varchar(500) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_work_due`
--

INSERT INTO `admin_work_due` (`workID`, `adminID`, `title`, `details`, `status`, `created_at`) VALUES
(3, 'A1', 'Audit Course Catalogs', 'Verify prerequisites for new 400-level electives', 'DONE', '2026-09-03 09:54:27'),
(4, 'A1001', 'Faculty Consultation Review', 'Inspect department office hour availability logs', 'PENDING', '2026-09-03 09:54:27'),
(5, 'A1', 'abc', 'sbd', 'DONE', '2026-09-03 10:41:10');

-- --------------------------------------------------------

--
-- Table structure for table `consultation_availability`
--

CREATE TABLE `consultation_availability` (
  `availability_id` int(11) NOT NULL,
  `faculty_uID` varchar(8) NOT NULL,
  `day_name` varchar(20) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `consultation_availability`
--

INSERT INTO `consultation_availability` (`availability_id`, `faculty_uID`, `day_name`, `start_time`, `end_time`, `notes`, `is_active`, `created_at`) VALUES
(1, '1122', 'Monday', '08:00:00', '09:00:00', '', 1, '2026-09-03 09:47:13'),
(2, '1122', 'Tuesday', '11:00:00', '11:45:00', '', 1, '2026-09-03 09:47:52');

-- --------------------------------------------------------

--
-- Table structure for table `consultation_requests`
--

CREATE TABLE `consultation_requests` (
  `request_id` int(11) NOT NULL,
  `student_uID` varchar(8) NOT NULL,
  `faculty_uID` varchar(8) NOT NULL,
  `availability_id` int(11) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `consultation_requests`
--

INSERT INTO `consultation_requests` (`request_id`, `student_uID`, `faculty_uID`, `availability_id`, `reason`, `status`, `created_at`, `updated_at`) VALUES
(1, '22300000', '1122', 1, 'aa', 'accepted', '2026-09-03 10:46:24', '2026-09-03 10:48:58'),
(2, '22300000', '1122', 2, 'aa', 'pending', '2026-09-03 10:46:45', '2026-09-03 10:46:45'),
(3, 'ZS22320', '1122', 1, 'aaaa', 'accepted', '2026-09-03 10:47:35', '2026-09-03 10:49:01');

-- --------------------------------------------------------

--
-- Table structure for table `content_reports`
--

CREATE TABLE `content_reports` (
  `reportID` int(11) NOT NULL,
  `reporterID` varchar(8) NOT NULL,
  `report_type` varchar(30) NOT NULL,
  `targetID` varchar(20) NOT NULL,
  `reason` varchar(500) NOT NULL,
  `status` varchar(20) DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `content_reports`
--

INSERT INTO `content_reports` (`reportID`, `reporterID`, `report_type`, `targetID`, `reason`, `status`, `created_at`) VALUES
(1, 'S21102', 'RESOURCE', 'RES101', 'Old syllabus slides included in note pack', 'PENDING', '2026-09-03 09:54:28'),
(2, 'ZS22320', 'SWAP_REQUEST', '102', 'Requester did not respond after initial handshake', 'PENDING', '2026-09-03 09:54:28');

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `coursecode` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(500) NOT NULL,
  `credit` float NOT NULL DEFAULT 3,
  `total_mark` decimal(3,0) NOT NULL DEFAULT 100,
  `admin` varchar(8) NOT NULL,
  `dept` varchar(20) NOT NULL DEFAULT 'CSE',
  `course_level` int(1) NOT NULL DEFAULT 1,
  `dept_type` enum('CORE','ELECTIVE','COD','GENERAL') NOT NULL DEFAULT 'CORE',
  `track` enum('Software','Hardware','Theory','DBMS','Cybersecurity','General') NOT NULL DEFAULT 'General',
  `has_lab` tinyint(1) NOT NULL DEFAULT 0,
  `has_project` tinyint(1) NOT NULL DEFAULT 0,
  `assessment_type` enum('EXAM_BASED','ASSIGNMENT_BASED') NOT NULL DEFAULT 'EXAM_BASED',
  `lab_assessment_type` enum('NONE','CONTINUOUS_ONLY','EXAM_BASED') NOT NULL DEFAULT 'NONE',
  `avg_teaching_rate` decimal(3,2) NOT NULL DEFAULT 4.00,
  `official_resource_rate` decimal(3,2) NOT NULL DEFAULT 4.00,
  `is_thesis_or_internship` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`coursecode`, `name`, `description`, `credit`, `total_mark`, `admin`, `dept`, `course_level`, `dept_type`, `track`, `has_lab`, `has_project`, `assessment_type`, `lab_assessment_type`, `avg_teaching_rate`, `official_resource_rate`, `is_thesis_or_internship`) VALUES
('CSE110', 'Programming Language I', 'Basic programming constructs, loops, and functions', 3, 100, 'A1', 'CSE', 1, 'CORE', 'Software', 1, 0, 'EXAM_BASED', 'CONTINUOUS_ONLY', 4.60, 4.80, 0),
('CSE111', 'Programming Language 2', 'Object-oriented programming concepts and design', 3, 100, 'A1', 'CSE', 1, 'CORE', 'Software', 1, 0, 'EXAM_BASED', 'CONTINUOUS_ONLY', 4.50, 4.70, 0),
('CSE220', 'Data Structure', 'Arrays, linked lists, stacks, queues, trees, and graphs', 3, 100, 'A1', 'CSE', 2, 'CORE', 'Software', 1, 0, 'EXAM_BASED', 'EXAM_BASED', 4.40, 4.50, 0),
('CSE221', 'Algorithm Analysis & Design', 'Sorting, divide & conquer, dynamic programming, greedy methods', 3, 100, 'A1', 'CSE', 2, 'CORE', 'Software', 1, 0, 'EXAM_BASED', 'EXAM_BASED', 4.70, 4.80, 0),
('CSE230', 'Discrete Mathematics', 'Logic, set theory, combinatorics, and graph theory', 3, 100, 'A1', 'CSE', 2, 'CORE', 'Theory', 0, 0, 'EXAM_BASED', 'NONE', 4.20, 4.10, 0),
('CSE250', 'Circuits and Electronics', 'DC/AC circuits, diodes, transistors, and operational amplifiers', 3, 100, 'A1', 'CSE', 2, 'CORE', 'Hardware', 1, 0, 'EXAM_BASED', 'CONTINUOUS_ONLY', 4.00, 3.90, 0),
('CSE251', 'Electronic Devices and Circuits', 'Semiconductor devices, amplifier circuits, and frequency response', 3, 100, 'A1', 'CSE', 2, 'CORE', 'Hardware', 1, 0, 'EXAM_BASED', 'CONTINUOUS_ONLY', 4.10, 4.00, 0),
('CSE260', 'Digital Logic Design', 'Boolean algebra, combinational and sequential circuit design', 3, 100, 'A1', 'CSE', 2, 'CORE', 'Hardware', 1, 0, 'EXAM_BASED', 'CONTINUOUS_ONLY', 4.50, 4.60, 0),
('CSE320', 'Data Communications', 'Transmission media, modulation, multiplexing, and protocols', 3, 100, 'A1', 'CSE', 3, 'CORE', 'General', 0, 0, 'EXAM_BASED', 'NONE', 4.30, 4.20, 0),
('CSE321', 'Operating Systems', 'Process management, concurrency, memory management, and file systems', 3, 100, 'A1', 'CSE', 3, 'CORE', 'Software', 1, 0, 'EXAM_BASED', 'CONTINUOUS_ONLY', 4.60, 4.70, 0),
('CSE330', 'Numerical Methods', 'Roots of equations, numerical differentiation, integration, linear systems', 3, 100, 'A1', 'CSE', 3, 'CORE', 'Theory', 1, 0, 'EXAM_BASED', 'CONTINUOUS_ONLY', 4.20, 4.30, 0),
('CSE331', 'Automata and Computability', 'Finite automata, regular expressions, context-free grammars, Turing machines', 3, 100, 'A1', 'CSE', 3, 'CORE', 'Theory', 0, 0, 'EXAM_BASED', 'NONE', 4.40, 4.10, 0),
('CSE340', 'Computer Architecture', 'Instruction set architecture, pipelining, memory hierarchy, I/O', 3, 100, 'A1', 'CSE', 3, 'CORE', 'Hardware', 0, 0, 'EXAM_BASED', 'NONE', 4.00, 3.90, 0),
('CSE341', 'MICROPROCESSORS', '8086 architecture, assembly language programming, interfacing', 3, 100, 'A1', 'CSE', 3, 'CORE', 'Hardware', 1, 0, 'EXAM_BASED', 'EXAM_BASED', 4.30, 4.40, 0),
('CSE350', 'Digital Electronics and Pulse Techniques', 'Multivibrators, wave shaping, digital IC families', 3, 100, 'A1', 'CSE', 3, 'ELECTIVE', 'Hardware', 1, 0, 'EXAM_BASED', 'CONTINUOUS_ONLY', 4.10, 4.00, 0),
('CSE360', 'Computer Interfacing', 'Parallel and serial interfacing, bus standards, peripheral interfacing', 3, 100, 'A1', 'CSE', 3, 'ELECTIVE', 'Hardware', 1, 0, 'EXAM_BASED', 'CONTINUOUS_ONLY', 4.20, 4.00, 0),
('CSE370', 'Database', 'Relational data model, SQL, normalization, transactions, indexing', 3, 100, 'A1', 'CSE', 3, 'CORE', 'DBMS', 1, 1, 'EXAM_BASED', 'CONTINUOUS_ONLY', 4.80, 4.90, 0),
('CSE400', 'Final Year Design Project', 'Capstone project thesis implementation and defense', 6, 100, 'A1', 'CSE', 4, 'CORE', 'General', 0, 1, 'ASSIGNMENT_BASED', 'NONE', 4.90, 4.80, 1),
('CSE420', 'Compiler Design', 'Lexical analysis, syntax analysis, code generation, and optimization', 3, 100, 'A1', 'CSE', 4, 'CORE', 'Software', 1, 0, 'EXAM_BASED', 'EXAM_BASED', 4.30, 4.40, 0),
('CSE421', 'Computer Networks', 'OSI layers, TCP/IP protocol suite, routing algorithms, socket programming', 3, 100, 'A1', 'CSE', 4, 'CORE', 'Software', 1, 0, 'EXAM_BASED', 'CONTINUOUS_ONLY', 4.70, 4.60, 0),
('CSE422', 'Artificial Intelligence', 'Search algorithms, knowledge representation, game playing, machine learning', 3, 100, 'A1', 'CSE', 4, 'CORE', 'Software', 1, 0, 'EXAM_BASED', 'CONTINUOUS_ONLY', 4.80, 4.70, 0),
('CSE423', 'Computer Graphics', 'Raster graphics, geometric transformations, 2D/3D viewing, shading', 3, 100, 'A1', 'CSE', 4, 'ELECTIVE', 'Software', 1, 0, 'EXAM_BASED', 'CONTINUOUS_ONLY', 4.40, 4.30, 0),
('CSE460', 'VLSI Design', 'MOS transistor theory, CMOS logic, layout design rules, FPGA', 3, 100, 'A1', 'CSE', 4, 'ELECTIVE', 'Hardware', 1, 0, 'EXAM_BASED', 'CONTINUOUS_ONLY', 4.20, 4.10, 0),
('CSE461', 'Introduction to Robotics', 'Kinematics, dynamics, sensors, control, and robotic vision', 3, 100, 'A1', 'CSE', 4, 'ELECTIVE', 'Hardware', 1, 1, 'EXAM_BASED', 'CONTINUOUS_ONLY', 4.50, 4.50, 0),
('CSE470', 'Software Engineering', 'SDLC, Agile methodologies, requirements engineering, design patterns, testing', 3, 100, 'A1', 'CSE', 4, 'CORE', 'Software', 0, 1, 'ASSIGNMENT_BASED', 'NONE', 4.70, 4.80, 0),
('CSE471', 'System Analysis and Design', 'Object-oriented analysis, UML modeling, system architecture, deployment', 3, 100, 'A1', 'CSE', 4, 'CORE', 'Software', 0, 1, 'ASSIGNMENT_BASED', 'NONE', 4.60, 4.50, 0),
('MAT216', 'Linear Algebra and Complex Variables', 'Vector spaces, matrices, complex functions', 3, 100, 'A1', 'MNS', 2, 'GENERAL', 'Theory', 0, 0, 'EXAM_BASED', 'NONE', 4.30, 4.20, 0),
('PHY112', 'Principles of Physics II', 'Electromagnetism, optics, and modern physics', 3, 100, 'A1', 'MNS', 1, 'GENERAL', 'Theory', 1, 0, 'EXAM_BASED', 'CONTINUOUS_ONLY', 4.10, 4.00, 0);

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `coursecode` varchar(15) NOT NULL,
  `name` varchar(100) NOT NULL,
  `credit` decimal(3,1) NOT NULL DEFAULT 3.0,
  `dept` varchar(10) NOT NULL DEFAULT 'CSE',
  `type` varchar(50) DEFAULT 'Core Theory',
  `faculty_rating` decimal(3,2) DEFAULT 4.50,
  `resource_rating` decimal(3,2) DEFAULT 4.50
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_prerequisite`
--

CREATE TABLE `course_prerequisite` (
  `coursecode` varchar(10) NOT NULL,
  `prereq_code` varchar(10) NOT NULL,
  `prereq_type` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_prerequisites`
--

CREATE TABLE `course_prerequisites` (
  `coursecode` varchar(10) NOT NULL,
  `prereq_coursecode` varchar(10) NOT NULL,
  `prereq_type` enum('HARD','SOFT') NOT NULL DEFAULT 'HARD'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_prerequisites`
--

INSERT INTO `course_prerequisites` (`coursecode`, `prereq_coursecode`, `prereq_type`) VALUES
('CSE111', 'CSE110', 'HARD'),
('CSE220', 'CSE111', 'HARD'),
('CSE221', 'CSE220', 'HARD'),
('CSE250', 'PHY112', 'SOFT'),
('CSE251', 'CSE250', 'HARD'),
('CSE260', 'CSE251', 'HARD'),
('CSE321', 'CSE221', 'HARD'),
('CSE321', 'CSE340', 'SOFT'),
('CSE330', 'MAT216', 'HARD'),
('CSE331', 'CSE221', 'HARD'),
('CSE340', 'CSE260', 'HARD'),
('CSE341', 'CSE340', 'SOFT'),
('CSE350', 'CSE251', 'HARD'),
('CSE360', 'CSE341', 'HARD'),
('CSE370', 'CSE221', 'HARD'),
('CSE420', 'CSE321', 'HARD'),
('CSE420', 'CSE331', 'HARD'),
('CSE420', 'CSE340', 'HARD'),
('CSE421', 'CSE320', 'SOFT'),
('CSE422', 'CSE221', 'HARD'),
('CSE423', 'MAT216', 'HARD'),
('CSE460', 'CSE260', 'HARD'),
('CSE461', 'CSE260', 'HARD'),
('CSE461', 'CSE341', 'HARD'),
('CSE461', 'CSE360', 'HARD'),
('CSE470', 'CSE370', 'HARD'),
('CSE471', 'CSE370', 'HARD');

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
  `name` varchar(100) NOT NULL,
  `content_category` enum('NOTE','PREV_SLIDE','PREV_QUESTION','OTHER') NOT NULL DEFAULT 'NOTE',
  `file_path` varchar(255) DEFAULT NULL,
  `verified_by_faculty_UID` varchar(8) DEFAULT NULL,
  `view_count` int(11) NOT NULL DEFAULT 0,
  `download_count` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_resouce`
--

INSERT INTO `course_resouce` (`course_resourceID`, `student_UID`, `coursecode`, `resource_type`, `content`, `name`, `content_category`, `file_path`, `verified_by_faculty_UID`, `view_count`, `download_count`) VALUES
('RES101', 'ZS22320', 'CSE370', 'Lecture Notes', 'Complete Normalization guide covering 1NF through BCNF with worked exercises', 'Database Normalization Handbook', 'NOTE', 'uploads/res101.pdf', 'FAC01', 64, 28),
('RES102', 'S20018', 'CSE220', 'Exam Preparation', 'Previous midterm solved traces on trees and binary search algorithms', 'DSA Midterm Revision Bank', 'PREV_QUESTION', 'uploads/res102.pdf', 'FAC03', 104, 52),
('RES103', 'S21088', 'CSE321', 'Lab Handout', 'Process synchronization with mutex locks and semaphores implemented in C', 'OS Concurrency Code Notes', 'NOTE', 'uploads/res103.pdf', 'FAC04', 42, 19),
('RES104', 'S20333', 'CSE420', 'Study Notes', 'Grammar Parsing, First and Follow Sets calculation reference sheet', 'Compiler LL(1) & LR Parsing Cheat Sheet', 'NOTE', 'uploads/res104_compiler.pdf', 'FAC02', 38, 14);

-- --------------------------------------------------------

--
-- Table structure for table `course_resources`
--

CREATE TABLE `course_resources` (
  `resourceID` int(11) NOT NULL,
  `coursecode` varchar(15) NOT NULL,
  `title` varchar(150) NOT NULL,
  `file_url` varchar(255) DEFAULT 'uploads/sample_notes.pdf',
  `uploaded_by` varchar(8) DEFAULT 'ZS22320',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_resources`
--

INSERT INTO `course_resources` (`resourceID`, `coursecode`, `title`, `file_url`, `uploaded_by`, `created_at`) VALUES
(1, 'CSE110', 'Lecture 1-5 Complete Slides & Notes', 'uploads/cse110_slides.pdf', 'ZS22320', '2026-09-01 06:16:18'),
(2, 'CSE110', 'Midterm Practice Question Bank with Solutions', 'uploads/cse110_mid_solved.pdf', 'S21102', '2026-09-01 06:16:18'),
(3, 'CSE111', 'OOP Java Lab Code Examples', 'uploads/cse111_lab_codes.zip', 'S22104', '2026-09-01 06:16:18'),
(4, 'CSE111', 'Inheritance and Polymorphism Cheat Sheet', 'uploads/cse111_cheatsheet.pdf', 'ZS22320', '2026-09-01 06:16:18'),
(5, 'CSE220', 'Linked List & BST Implementation Repo', 'uploads/cse220_dsa_code.zip', 'S20018', '2026-09-01 06:16:18'),
(6, 'CSE220', 'Previous 5 Semesters Final Exam Questions', 'uploads/cse220_finals.pdf', 'ZS22320', '2026-09-01 06:16:18'),
(7, 'CSE221', 'Graph Algorithms & Dynamic Programming Notes', 'uploads/cse221_dp_notes.pdf', 'S21305', '2026-09-01 06:16:18'),
(8, 'CSE230', 'Discrete Math Proofs & Truth Table Summaries', 'uploads/cse230_summary.pdf', 'S22210', '2026-09-01 06:16:18'),
(9, 'CSE250', 'Circuit Simulation Labs & Proteus Guides', 'uploads/cse250_labs.zip', 'S21088', '2026-09-01 06:16:18'),
(10, 'CSE260', 'K-Map Minimization & Sequential Logic Handout', 'uploads/cse260_kmap.pdf', 'ZS22320', '2026-09-01 06:16:18'),
(11, 'CSE320', 'Packet Switching & OSI Model Study Guide', 'uploads/cse320_osi.pdf', 'S21102', '2026-09-01 06:16:18'),
(12, 'CSE321', 'OS Process Synchronization & Semaphore Guide', 'uploads/cse321_semaphores.pdf', 'ZS22320', '2026-09-01 06:16:18'),
(13, 'CSE330', 'Numerical Methods MATLAB Codes', 'uploads/cse330_matlab.zip', 'S22104', '2026-09-01 06:16:18'),
(14, 'CSE331', 'Turing Machines & Context-Free Grammars', 'uploads/cse331_cfg.pdf', 'S20018', '2026-09-01 06:16:18'),
(15, 'CSE340', 'MIPS Assembly Architecture Code Samples', 'uploads/cse340_mips.zip', 'S21305', '2026-09-01 06:16:18'),
(16, 'CSE370', 'SQL Schema, Joins & Normalization Complete Cheatsheet', 'uploads/cse370_sql_handbook.pdf', 'ZS22320', '2026-09-01 06:16:18'),
(17, 'CSE370', 'ERD Diagrams & Relational Algebra Practice Set', 'uploads/cse370_erd.pdf', 'S21102', '2026-09-01 06:16:18');

-- --------------------------------------------------------

--
-- Table structure for table `course_student_took`
--

CREATE TABLE `course_student_took` (
  `recordID` int(11) NOT NULL,
  `userID` varchar(8) NOT NULL,
  `coursecode` varchar(10) NOT NULL,
  `marks` decimal(5,2) NOT NULL DEFAULT 0.00,
  `grade_letter` varchar(3) NOT NULL DEFAULT 'I',
  `grade_point` decimal(3,2) NOT NULL DEFAULT 0.00,
  `faculty_initial` varchar(10) DEFAULT NULL,
  `semester_taken` varchar(20) NOT NULL DEFAULT 'Spring 2026',
  `semester_order` int(6) NOT NULL DEFAULT 20261,
  `is_completed` tinyint(1) NOT NULL DEFAULT 1,
  `enrollment_semester` varchar(30) DEFAULT NULL,
  `grade` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_student_took`
--

INSERT INTO `course_student_took` (`recordID`, `userID`, `coursecode`, `marks`, `grade_letter`, `grade_point`, `faculty_initial`, `semester_taken`, `semester_order`, `is_completed`, `enrollment_semester`, `grade`) VALUES
(1, 'ZS22320', 'CSE110', 92.50, 'A', 4.00, 'THS', 'Fall 2023', 20233, 1, 'Fall 2023', 92.50),
(2, 'ZS22320', 'CSE111', 88.00, 'A-', 3.70, 'MMA', 'Spring 2024', 20241, 1, 'Spring 2024', 88.00),
(3, 'ZS22320', 'CSE220', 90.00, 'A', 4.00, 'KDR', 'Summer 2024', 20242, 1, 'Summer 2024', 90.00),
(4, 'ZS22320', 'CSE221', 86.50, 'A-', 3.70, 'DRH', 'Fall 2024', 20243, 1, 'Fall 2024', 86.50),
(5, 'ZS22320', 'CSE230', 85.00, 'A-', 3.70, 'THS', 'Fall 2024', 20243, 1, 'Fall 2024', 85.00),
(6, 'ZS22320', 'CSE250', 82.00, 'B+', 3.30, 'KDR', 'Spring 2025', 20251, 1, 'Spring 2025', 82.00),
(7, 'ZS22320', 'CSE251', 84.00, 'B+', 3.30, 'DRH', 'Summer 2025', 20252, 1, 'Summer 2025', 84.00),
(8, 'ZS22320', 'CSE260', 91.00, 'A', 4.00, 'MMA', 'Summer 2025', 20252, 1, 'Summer 2025', 91.00),
(9, 'ZS22320', 'CSE320', 87.00, 'A-', 3.70, 'THS', 'Fall 2025', 20253, 1, 'Fall 2025', 87.00),
(10, 'ZS22320', 'PHY112', 85.00, 'A-', 3.70, 'MNS', 'Fall 2023', 20233, 1, 'Fall 2023', 85.00),
(11, 'ZS22320', 'MAT216', 88.50, 'A-', 3.70, 'MNS', 'Spring 2024', 20241, 1, 'Spring 2024', 88.50),
(12, 'S21102', 'CSE110', 85.00, 'A-', 3.70, 'THS', 'Spring 2024', 20241, 1, 'Spring 2024', 85.00),
(13, 'S21102', 'CSE370', 94.00, 'A', 4.00, 'THS', 'Fall 2025', 20253, 1, 'Fall 2025', 94.00),
(14, 'S21102', 'CSE321', 89.00, 'A', 4.00, 'DRH', 'Fall 2025', 20253, 1, 'Fall 2025', 89.00),
(15, 'S22104', 'CSE110', 78.00, 'B', 3.00, 'MMA', 'Spring 2024', 20241, 1, 'Spring 2024', 78.00),
(16, 'S22104', 'CSE220', 82.00, 'B+', 3.30, 'KDR', 'Summer 2024', 20242, 1, 'Summer 2024', 82.00),
(17, 'S22104', 'CSE370', 88.00, 'A-', 3.70, 'MMA', 'Fall 2025', 20253, 1, 'Fall 2025', 88.00),
(18, 'S20018', 'CSE110', 95.00, 'A', 4.00, 'THS', 'Fall 2023', 20233, 1, 'Fall 2023', 95.00),
(19, 'S20018', 'CSE220', 91.00, 'A', 4.00, 'THS', 'Spring 2024', 20241, 1, 'Spring 2024', 91.00),
(20, 'S20018', 'CSE370', 96.00, 'A', 4.00, 'DRH', 'Spring 2025', 20251, 1, 'Spring 2025', 96.00),
(21, 'S21305', 'CSE110', 76.00, 'B', 3.00, 'MMA', 'Spring 2024', 20241, 1, 'Spring 2024', 76.00),
(22, 'S21305', 'CSE221', 80.00, 'B+', 3.30, 'DRH', 'Fall 2024', 20243, 1, 'Fall 2024', 80.00),
(23, 'S21305', 'CSE340', 85.00, 'A-', 3.70, 'KDR', 'Fall 2025', 20253, 1, 'Fall 2025', 85.00),
(24, 'S22210', 'CSE110', 88.00, 'A-', 3.70, 'THS', 'Fall 2023', 20233, 1, 'Fall 2023', 88.00),
(25, 'S22210', 'CSE220', 84.00, 'B+', 3.30, 'KDR', 'Spring 2024', 20241, 1, 'Spring 2024', 84.00),
(26, 'S22210', 'CSE230', 90.00, 'A', 4.00, 'THS', 'Summer 2024', 20242, 1, 'Summer 2024', 90.00),
(27, 'S21088', 'CSE110', 90.00, 'A', 4.00, 'THS', 'Spring 2024', 20241, 1, 'Spring 2024', 90.00),
(28, 'S21088', 'CSE320', 88.00, 'A-', 3.70, 'THS', 'Fall 2024', 20243, 1, 'Fall 2024', 88.00),
(29, 'S21088', 'CSE330', 86.00, 'A-', 3.70, 'MMA', 'Spring 2025', 20251, 1, 'Spring 2025', 86.00),
(30, 'S21088', 'CSE331', 89.00, 'A', 4.00, 'THS', 'Fall 2025', 20253, 1, 'Fall 2025', 89.00),
(31, 'ZS22320', 'CSE370', 0.00, 'A-', 3.70, '', 'Summer 2026', 20262, 1, 'Summer 2026', 0.00),
(32, 'S23101', 'CSE110', 88.00, 'A-', 3.70, 'THS', 'Spring 2024', 20241, 1, 'Spring 2024', 88.00),
(33, 'S23101', 'CSE111', 82.50, 'B+', 3.30, 'MMA', 'Summer 2024', 20242, 1, 'Summer 2024', 82.50),
(34, 'S23101', 'CSE220', 86.00, 'A-', 3.70, 'KDR', 'Fall 2024', 20243, 1, 'Fall 2024', 86.00),
(35, 'S23101', 'CSE221', 90.00, 'A', 4.00, 'DRH', 'Spring 2025', 20251, 1, 'Spring 2025', 90.00),
(36, 'S23101', 'PHY112', 79.00, 'B', 3.00, 'MNS', 'Spring 2024', 20241, 1, 'Spring 2024', 79.00),
(37, 'S23101', 'MAT216', 85.00, 'A-', 3.70, 'MNS', 'Summer 2024', 20242, 1, 'Summer 2024', 85.00),
(38, 'S23202', 'CSE110', 94.00, 'A', 4.00, 'THS', 'Summer 2024', 20242, 1, 'Summer 2024', 94.00),
(39, 'S23202', 'CSE111', 91.50, 'A', 4.00, 'MMA', 'Fall 2024', 20243, 1, 'Fall 2024', 91.50),
(40, 'S23202', 'CSE220', 89.00, 'A', 4.00, 'KDR', 'Spring 2025', 20251, 1, 'Spring 2025', 89.00),
(41, 'S23202', 'PHY112', 88.00, 'A-', 3.70, 'MNS', 'Summer 2024', 20242, 1, 'Summer 2024', 88.00),
(42, 'S20333', 'CSE110', 96.00, 'A', 4.00, 'THS', 'Fall 2022', 20223, 1, 'Fall 2022', 96.00),
(43, 'S20333', 'CSE111', 93.00, 'A', 4.00, 'DRH', 'Spring 2023', 20231, 1, 'Spring 2023', 93.00),
(44, 'S20333', 'CSE220', 95.00, 'A', 4.00, 'KDR', 'Summer 2023', 20232, 1, 'Summer 2023', 95.00),
(45, 'S20333', 'CSE221', 92.00, 'A', 4.00, 'DRH', 'Fall 2023', 20233, 1, 'Fall 2023', 92.00),
(46, 'S20333', 'CSE260', 90.00, 'A', 4.00, 'MMA', 'Spring 2024', 20241, 1, 'Spring 2024', 90.00),
(47, 'S20333', 'CSE321', 89.00, 'A', 4.00, 'DRH', 'Summer 2024', 20242, 1, 'Summer 2024', 89.00),
(48, 'S20333', 'CSE340', 88.00, 'A-', 3.70, 'KDR', 'Fall 2024', 20243, 1, 'Fall 2024', 88.00),
(49, 'S20333', 'CSE370', 98.00, 'A', 4.00, 'THS', 'Spring 2025', 20251, 1, 'Spring 2025', 98.00),
(50, 'S20333', 'CSE420', 87.00, 'A-', 3.70, 'MMA', 'Fall 2025', 20253, 1, 'Fall 2025', 87.00),
(51, 'ZS22320', 'CSE331', 0.00, 'F', 0.00, 'DRH', 'Spring 2026', 20261, 0, NULL, 0.00),
(52, 'ZS22320', 'CSE340', 0.00, 'F', 0.00, 'DRH', 'Spring 2025', 20251, 0, NULL, 0.00),
(53, 'ZS22320', 'CSE330', 0.00, 'B', 3.00, 'KDR', 'Spring 2025', 20251, 1, NULL, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `enrolled_courses`
--

CREATE TABLE `enrolled_courses` (
  `enrollmentID` int(11) NOT NULL,
  `userID` varchar(8) NOT NULL,
  `coursecode` varchar(15) NOT NULL,
  `section_no` int(11) NOT NULL DEFAULT 1,
  `semester` varchar(20) DEFAULT 'Spring 2026',
  `status` enum('ENROLLED','COMPLETED','DROPPED') NOT NULL DEFAULT 'ENROLLED'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrolled_courses`
--

INSERT INTO `enrolled_courses` (`enrollmentID`, `userID`, `coursecode`, `section_no`, `semester`, `status`) VALUES
(1, 'ZS22320', 'CSE370', 1, 'Spring 2026', 'ENROLLED'),
(2, 'ZS22320', 'CSE321', 1, 'Spring 2026', 'ENROLLED'),
(3, 'S21102', 'CSE370', 2, 'Spring 2026', 'ENROLLED'),
(4, 'S21102', 'CSE340', 1, 'Spring 2026', 'ENROLLED'),
(5, 'S22104', 'CSE370', 3, 'Spring 2026', 'ENROLLED'),
(6, 'S22104', 'CSE221', 1, 'Spring 2026', 'ENROLLED'),
(7, 'S20018', 'CSE220', 1, 'Spring 2026', 'ENROLLED'),
(8, 'S20018', 'CSE111', 1, 'Spring 2026', 'ENROLLED'),
(9, 'S21305', 'CSE110', 1, 'Spring 2026', 'ENROLLED'),
(10, 'S21305', 'CSE111', 2, 'Spring 2026', 'ENROLLED'),
(11, 'S22210', 'CSE220', 2, 'Spring 2026', 'ENROLLED'),
(12, 'S22210', 'CSE230', 1, 'Spring 2026', 'ENROLLED'),
(13, 'S21088', 'CSE320', 1, 'Spring 2026', 'ENROLLED'),
(14, 'S21088', 'CSE330', 1, 'Spring 2026', 'ENROLLED'),
(15, 'S21088', 'CSE331', 1, 'Spring 2026', 'ENROLLED'),
(16, 'S23101', 'CSE370', 1, 'Spring 2026', 'ENROLLED'),
(17, 'S23101', 'CSE260', 1, 'Spring 2026', 'ENROLLED'),
(18, 'S23202', 'CSE221', 2, 'Spring 2026', 'ENROLLED'),
(19, 'S23202', 'CSE230', 1, 'Spring 2026', 'ENROLLED'),
(20, 'S20333', 'CSE400', 1, 'Spring 2026', 'ENROLLED'),
(21, 'S20333', 'CSE422', 1, 'Spring 2026', 'ENROLLED');

-- --------------------------------------------------------

--
-- Table structure for table `faculty_consultation_slots`
--

CREATE TABLE `faculty_consultation_slots` (
  `slotID` int(11) NOT NULL,
  `faculty_uID` varchar(8) NOT NULL,
  `day_of_week` enum('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty_consultation_slots`
--

INSERT INTO `faculty_consultation_slots` (`slotID`, `faculty_uID`, `day_of_week`, `start_time`, `end_time`, `is_active`) VALUES
(1, 'FAC01', 'Sunday', '10:00:00', '11:30:00', 1),
(2, 'FAC01', 'Tuesday', '14:00:00', '15:30:00', 1),
(3, 'FAC02', 'Monday', '09:30:00', '11:00:00', 1),
(4, 'FAC03', 'Wednesday', '11:00:00', '12:30:00', 1),
(5, 'FAC04', 'Thursday', '13:00:00', '14:30:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `faculty_courselist`
--

CREATE TABLE `faculty_courselist` (
  `faculty_UID` varchar(8) NOT NULL,
  `coursecode` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty_courselist`
--

INSERT INTO `faculty_courselist` (`faculty_UID`, `coursecode`) VALUES
('FAC01', 'CSE110'),
('FAC01', 'CSE220'),
('FAC01', 'CSE230'),
('FAC01', 'CSE320'),
('FAC01', 'CSE331'),
('FAC01', 'CSE370'),
('FAC01', 'CSE470'),
('FAC02', 'CSE110'),
('FAC02', 'CSE221'),
('FAC02', 'CSE260'),
('FAC02', 'CSE330'),
('FAC02', 'CSE370'),
('FAC02', 'CSE422'),
('FAC03', 'CSE111'),
('FAC03', 'CSE220'),
('FAC03', 'CSE250'),
('FAC03', 'CSE321'),
('FAC03', 'CSE340'),
('FAC03', 'CSE420'),
('FAC04', 'CSE111'),
('FAC04', 'CSE221'),
('FAC04', 'CSE251'),
('FAC04', 'CSE321'),
('FAC04', 'CSE370'),
('FAC04', 'CSE421');

-- --------------------------------------------------------

--
-- Table structure for table `faculty_info`
--

CREATE TABLE `faculty_info` (
  `userID` varchar(8) NOT NULL,
  `FID` int(10) DEFAULT NULL,
  `faculty_initial` varchar(10) DEFAULT NULL,
  `Initial` varchar(10) DEFAULT NULL,
  `Dept` varchar(100) DEFAULT NULL,
  `designation` varchar(500) NOT NULL DEFAULT 'Lecturer',
  `room_no` varchar(500) NOT NULL DEFAULT 'UB000'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty_info`
--

INSERT INTO `faculty_info` (`userID`, `FID`, `faculty_initial`, `Initial`, `Dept`, `designation`, `room_no`) VALUES
('1122', NULL, NULL, NULL, NULL, 'Lecturer', 'UB000'),
('FAC01', NULL, 'THS', 'THS', 'CSE', 'Associate Professor', 'UB801'),
('FAC02', NULL, 'MMA', 'MMA', 'CSE', 'Assistant Professor', 'UB802'),
('FAC03', NULL, 'KDR', 'KDR', 'CSE', 'Senior Lecturer', 'UB805'),
('FAC04', NULL, 'DRH', 'DRH', 'CSE', 'Professor', 'UB901');

-- --------------------------------------------------------

--
-- Table structure for table `faculty_work_reviews`
--

CREATE TABLE `faculty_work_reviews` (
  `review_id` int(11) NOT NULL,
  `submission_id` int(11) NOT NULL,
  `faculty_uID` varchar(8) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `review` text NOT NULL,
  `feedback` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty_work_reviews`
--

INSERT INTO `faculty_work_reviews` (`review_id`, `submission_id`, `faculty_uID`, `rating`, `review`, `feedback`, `created_at`) VALUES
(1, 1, 'FAC01', 5, 'Clean server-side architecture and robust schema constraints.', 'Consider indexing search columns for high load scenarios.', '2026-09-03 09:54:28'),
(2, 2, 'FAC04', 4, 'Solid theoretical foundation and reproducible experimental data.', 'Add edge test-cases under asynchronous partitions.', '2026-09-03 09:54:28');

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

--
-- Dumping data for table `group_info`
--

INSERT INTO `group_info` (`groupID`, `group_creator_uid`, `type`, `targeted_subjects`, `adminID`) VALUES
('GRP01', 'ZS22320', 'Study Circle', 'CSE370, CSE321', 'A1'),
('GRP02', 'S20018', 'Competitive Programming', 'CSE221, CSE420', 'A1001'),
('GRP03', 'S21088', 'Hardware & Embedded Projects', 'CSE260, CSE340', 'A1'),
('GRP04', 'S23202', 'Midterm Revision Group', 'CSE221, CSE230', 'A1001');

-- --------------------------------------------------------

--
-- Table structure for table `group_join_requests`
--

CREATE TABLE `group_join_requests` (
  `requestID` int(11) NOT NULL,
  `groupID` varchar(10) NOT NULL,
  `student_UID` varchar(8) NOT NULL,
  `status` varchar(20) DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `group_join_requests`
--

INSERT INTO `group_join_requests` (`requestID`, `groupID`, `student_UID`, `status`, `created_at`) VALUES
(1, 'GRP01', 'S21305', 'PENDING', '2026-09-03 09:54:28'),
(2, 'GRP02', 'S22104', 'PENDING', '2026-09-03 09:54:28');

-- --------------------------------------------------------

--
-- Table structure for table `group_messages`
--

CREATE TABLE `group_messages` (
  `messageID` int(11) NOT NULL,
  `groupID` varchar(10) NOT NULL,
  `senderID` varchar(8) NOT NULL,
  `message` varchar(500) NOT NULL,
  `message_type` varchar(30) DEFAULT 'JOIN',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `group_messages`
--

INSERT INTO `group_messages` (`messageID`, `groupID`, `senderID`, `message`, `message_type`, `created_at`) VALUES
(1, 'GRP01', 'ZS22320', 'Welcome to the DBMS study group! Next review this Sunday.', 'JOIN', '2026-09-03 09:54:28'),
(2, 'GRP01', 'S21102', 'Can someone upload the notes for multi-valued dependency?', 'JOIN', '2026-09-03 09:54:28'),
(3, 'GRP02', 'S20018', 'Weekend contest starts Saturday 8 PM.', 'JOIN', '2026-09-03 09:54:28'),
(4, 'GRP04', 'S23202', 'Group created for Midterm prep. Check the weekly tasks!', 'JOIN', '2026-09-03 09:57:11'),
(5, 'GRP04', 'S23101', 'Thanks for adding me! Ready to start graph theory questions.', 'JOIN', '2026-09-03 09:57:11');

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

--
-- Dumping data for table `group_rated_by_student`
--

INSERT INTO `group_rated_by_student` (`groupID`, `student_UID`, `rate`, `review`) VALUES
('GRP01', 'S21102', 4.9, 'Active collaboration, helped significantly with DBMS project work.'),
('GRP02', 'S22210', 4.7, 'Great practice problems and shared solutions.');

-- --------------------------------------------------------

--
-- Table structure for table `group_weekly_goals`
--

CREATE TABLE `group_weekly_goals` (
  `goalID` int(11) NOT NULL,
  `groupID` varchar(10) NOT NULL,
  `studentID` varchar(8) NOT NULL,
  `goal` varchar(300) NOT NULL,
  `week_start` date NOT NULL,
  `completed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `group_weekly_goals`
--

INSERT INTO `group_weekly_goals` (`goalID`, `groupID`, `studentID`, `goal`, `week_start`, `completed`) VALUES
(1, 'GRP01', 'ZS22320', 'Complete Schema Normalization Practice Set', '2026-09-01', 1),
(2, 'GRP01', 'S21102', 'Design ERD for Semester Project', '2026-09-01', 0),
(3, 'GRP02', 'S20018', 'Solve 10 Dynamic Programming problems on Codeforces', '2026-09-01', 1),
(4, 'GRP04', 'S23202', 'Implement Dijkstra and Bellman-Ford in C++', '2026-09-01', 1),
(5, 'GRP04', 'S23101', 'Trace 5 Induction Proof problems', '2026-09-01', 0);

-- --------------------------------------------------------

--
-- Table structure for table `mentor`
--

CREATE TABLE `mentor` (
  `userID` varchar(8) NOT NULL,
  `MentorID` varchar(10) NOT NULL,
  `Subject` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mentor`
--

INSERT INTO `mentor` (`userID`, `MentorID`, `Subject`, `is_active`) VALUES
('S20018', 'MTR01', 'Data Structures & Algorithms', 1),
('S20333', 'MTR04', 'Algorithms & Compiler Design', 1),
('S21088', 'MTR02', 'Operating Systems & Networking', 1),
('ZS22320', 'MTR03', 'CSE370', 1);

-- --------------------------------------------------------

--
-- Table structure for table `next_sem_planned_combinations`
--

CREATE TABLE `next_sem_planned_combinations` (
  `planID` int(11) NOT NULL,
  `userID` varchar(8) NOT NULL,
  `session_token` varchar(64) NOT NULL,
  `combo_id` int(11) NOT NULL,
  `coursecode` varchar(10) NOT NULL,
  `combo_stress_score` decimal(5,2) NOT NULL,
  `is_finalized` tinyint(1) NOT NULL DEFAULT 0,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `next_sem_planned_combinations`
--

INSERT INTO `next_sem_planned_combinations` (`planID`, `userID`, `session_token`, `combo_id`, `coursecode`, `combo_stress_score`, `is_finalized`, `generated_at`) VALUES
(1, 'S23101', 'token_s23101_plan', 1, 'CSE321', 6.50, 0, '2026-09-03 09:57:11'),
(2, 'S23101', 'token_s23101_plan', 1, 'CSE330', 6.50, 0, '2026-09-03 09:57:11');

-- --------------------------------------------------------

--
-- Table structure for table `repositories`
--

CREATE TABLE `repositories` (
  `repoID` int(11) NOT NULL,
  `coursecode` varchar(15) NOT NULL,
  `title` varchar(150) NOT NULL,
  `link` varchar(255) DEFAULT 'https://github.com/studyverse'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `repositories`
--

INSERT INTO `repositories` (`repoID`, `coursecode`, `title`, `link`) VALUES
(1, 'CSE110', 'CSE110 Python Starter Lab Repo', 'https://github.com/studyverse/cse110-lab'),
(2, 'CSE111', 'CSE111 Java OOP Projects', 'https://github.com/studyverse/cse111-projects'),
(3, 'CSE220', 'CSE220 Data Structures in C++/Java', 'https://github.com/studyverse/cse220-dsa'),
(4, 'CSE221', 'CSE221 Algorithm Analysis Benchmarks', 'https://github.com/studyverse/cse221-algos'),
(5, 'CSE321', 'CSE321 OS Shell and Multi-threading Labs', 'https://github.com/studyverse/cse321-os'),
(6, 'CSE370', 'CSE370 Database Project Full-Stack Repo', 'https://github.com/studyverse/cse370-dbms');

-- --------------------------------------------------------

--
-- Table structure for table `repository`
--

CREATE TABLE `repository` (
  `repoID` varchar(10) NOT NULL,
  `coursecode` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `adminID` varchar(8) NOT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `max_content_capacity` int(4) NOT NULL DEFAULT 100
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `repository`
--

INSERT INTO `repository` (`repoID`, `coursecode`, `name`, `adminID`, `is_published`, `max_content_capacity`) VALUES
('RE320-2', 'CSE320', 'Datacommunication', 'A1', 1, 100),
('REP110', 'CSE110', 'CSE110 Python', 'A1', 1, 100),
('REP111', 'CSE111', 'CSE111 Java OOP & Design Lab Repo', 'A1', 1, 100),
('REP220', 'CSE220', 'CSE220 Data Structures Implementation Hub', 'A1', 1, 100),
('REP221', 'CSE221', 'CSE221 Algorithms & Problem Sets Repo', 'A1', 1, 100),
('REP230', 'CSE230', 'CSE230 Discrete Mathematics & Proofs', 'A1', 1, 100),
('REP250', 'CSE250', 'CSE250 Circuits & Electronics Simulation Files', 'A1', 1, 100),
('REP260', 'CSE260', 'CSE260 Digital Logic Circuit Handouts', 'A1', 1, 100),
('REP320', 'CSE320', 'CSE320 Data Communications Study Materials', 'A1', 1, 100),
('REP321', 'CSE321', 'CSE321 Operating Systems Labs & Semaphores', 'A1', 1, 100),
('REP330', 'CSE330', 'CSE330 Numerical Methods MATLAB Repository', 'A1', 1, 100),
('REP331', 'CSE331', 'CSE331 Automata & Formal Grammars Archive', 'A1', 1, 100),
('REP340', 'CSE340', 'CSE340 Computer Architecture & MIPS Hub', 'A1', 1, 100),
('REP370', 'CSE370', 'CSE370 Database Schema & Normalization Hub', 'A1', 1, 100),
('REP420', 'CSE420', 'CSE420 Compiler Lex & Yacc Projects', 'A1', 1, 100),
('REP421', 'CSE421', 'CSE421 Computer Networks Socket Code', 'A1', 1, 100),
('REP422', 'CSE422', 'CSE422 AI & Machine Learning Notebooks', 'A1', 1, 100),
('REP470', 'CSE470', 'CSE470 Software Engineering Project Docs', 'A1', 1, 100);

-- --------------------------------------------------------

--
-- Table structure for table `repo_creation_requests`
--

CREATE TABLE `repo_creation_requests` (
  `requestID` int(11) NOT NULL,
  `repoID` varchar(10) DEFAULT NULL,
  `student_UID` varchar(8) DEFAULT NULL,
  `requestedBy` varchar(8) DEFAULT NULL,
  `coursecode` varchar(10) NOT NULL,
  `suggested_repo_name` varchar(100) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `repo_creation_requests`
--

INSERT INTO `repo_creation_requests` (`requestID`, `repoID`, `student_UID`, `requestedBy`, `coursecode`, `suggested_repo_name`, `status`, `created_at`) VALUES
(1, NULL, 'ZS22320', 'ZS22320', 'CSE423', 'Computer Graphics Shader & WebGL Archive', 'PENDING', '2026-09-03 09:54:28'),
(2, NULL, 'S21305', 'S21305', 'CSE460', 'VLSI Design Verilog Testing Suite', 'PENDING', '2026-09-03 09:54:28');

-- --------------------------------------------------------

--
-- Table structure for table `resource_uploaded_in_repo`
--

CREATE TABLE `resource_uploaded_in_repo` (
  `repoID` varchar(10) NOT NULL,
  `resourceID` varchar(10) NOT NULL,
  `upload_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resource_uploaded_in_repo`
--

INSERT INTO `resource_uploaded_in_repo` (`repoID`, `resourceID`, `upload_date`) VALUES
('REP220', 'RES102', '2026-08-28'),
('REP321', 'RES103', '2026-08-30'),
('REP370', 'RES101', '2026-08-25'),
('REP420', 'RES104', '2026-09-02');

-- --------------------------------------------------------

--
-- Table structure for table `resource_views_reads`
--

CREATE TABLE `resource_views_reads` (
  `viewID` int(11) NOT NULL,
  `resourceID` varchar(10) NOT NULL,
  `student_UID` varchar(8) NOT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `has_read` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resource_views_reads`
--

INSERT INTO `resource_views_reads` (`viewID`, `resourceID`, `student_UID`, `viewed_at`, `has_read`) VALUES
(1, 'RES101', 'S21102', '2026-09-03 09:54:28', 1),
(2, 'RES101', 'S22104', '2026-09-03 09:54:28', 1),
(3, 'RES102', 'ZS22320', '2026-09-03 09:54:28', 1),
(4, 'RES104', 'S23101', '2026-09-03 09:57:11', 1);

-- --------------------------------------------------------

--
-- Table structure for table `section_offerings`
--

CREATE TABLE `section_offerings` (
  `offeringID` int(11) NOT NULL,
  `coursecode` varchar(10) NOT NULL,
  `section_no` int(3) NOT NULL,
  `faculty_initial` varchar(10) NOT NULL,
  `time_slot` varchar(50) NOT NULL,
  `semester` varchar(20) NOT NULL DEFAULT 'Spring 2026'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `section_offerings`
--

INSERT INTO `section_offerings` (`offeringID`, `coursecode`, `section_no`, `faculty_initial`, `time_slot`, `semester`) VALUES
(1, 'CSE370', 1, 'THS', 'Sun/Tue 08:00 AM - 09:20 AM', 'Spring 2026'),
(2, 'CSE370', 2, 'MMA', 'Sun/Tue 09:30 AM - 10:50 AM', 'Spring 2026'),
(3, 'CSE370', 3, 'DRH', 'Mon/Wed 11:00 AM - 12:20 PM', 'Spring 2026'),
(4, 'CSE220', 1, 'KDR', 'Sun/Tue 09:30 AM - 10:50 AM', 'Spring 2026'),
(5, 'CSE220', 2, 'THS', 'Mon/Wed 11:00 AM - 12:20 PM', 'Spring 2026'),
(6, 'CSE340', 1, 'KDR', 'Sun/Tue 09:30 AM - 10:50 AM', 'Spring 2026'),
(7, 'CSE340', 2, 'SZK', 'Mon/Wed 09:30 AM - 10:50 AM', 'Spring 2026'),
(8, 'CSE420', 1, 'AAK', 'Sun/Tue 03:30 PM - 04:50 PM', 'Spring 2026'),
(9, 'CSE420', 2, 'NTR', 'Mon/Wed 12:30 PM - 01:50 PM', 'Spring 2026'),
(10, 'CSE461', 1, 'DRH', 'Sun/Tue 09:30 AM - 10:50 AM', 'Spring 2026'),
(11, 'CSE461', 2, 'MNA', 'Mon/Wed 03:30 PM - 04:50 PM', 'Spring 2026'),
(12, 'CSE110', 1, 'THS', 'Sun/Tue 08:00 AM - 09:20 AM', 'Spring 2026'),
(13, 'CSE110', 2, 'MMA', 'Mon/Wed 09:30 AM - 10:50 AM', 'Spring 2026'),
(14, 'CSE111', 1, 'KDR', 'Sun/Tue 11:00 AM - 12:20 PM', 'Spring 2026'),
(15, 'CSE111', 2, 'DRH', 'Mon/Wed 02:00 PM - 03:20 PM', 'Spring 2026'),
(16, 'CSE221', 1, 'DRH', 'Sun/Tue 02:00 PM - 03:20 PM', 'Spring 2026'),
(17, 'CSE221', 2, 'MMA', 'Mon/Wed 03:30 PM - 04:50 PM', 'Spring 2026'),
(18, 'CSE230', 1, 'THS', 'Sun/Tue 09:30 AM - 10:50 AM', 'Spring 2026'),
(19, 'CSE250', 1, 'KDR', 'Mon/Wed 08:00 AM - 09:20 AM', 'Spring 2026'),
(20, 'CSE251', 1, 'DRH', 'Sun/Tue 11:00 AM - 12:20 PM', 'Spring 2026'),
(21, 'CSE260', 1, 'MMA', 'Mon/Wed 02:00 PM - 03:20 PM', 'Spring 2026'),
(22, 'CSE320', 1, 'THS', 'Sun/Tue 08:00 AM - 09:20 AM', 'Spring 2026'),
(23, 'CSE321', 1, 'DRH', 'Mon/Wed 09:30 AM - 10:50 AM', 'Spring 2026'),
(24, 'CSE321', 2, 'KDR', 'Sun/Tue 02:00 PM - 03:20 PM', 'Spring 2026'),
(25, 'CSE330', 1, 'MMA', 'Sun/Tue 11:00 AM - 12:20 PM', 'Spring 2026'),
(26, 'CSE331', 1, 'THS', 'Mon/Wed 03:30 PM - 04:50 PM', 'Spring 2026');

-- --------------------------------------------------------

--
-- Table structure for table `section_swap_requests`
--

CREATE TABLE `section_swap_requests` (
  `swapID` int(11) NOT NULL,
  `userID` varchar(8) NOT NULL,
  `coursecode` varchar(10) NOT NULL,
  `upcoming_sem_no` int(2) NOT NULL,
  `current_sec` int(3) NOT NULL,
  `desired_sec` int(3) NOT NULL,
  `preferred_faculty` varchar(10) DEFAULT NULL,
  `preferred_time_slot` varchar(50) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('OPEN','PENDING_PEER','ACCEPTED','REJECTED','CANCELLED') NOT NULL DEFAULT 'OPEN',
  `matched_userID` varchar(8) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `section_swap_requests`
--

INSERT INTO `section_swap_requests` (`swapID`, `userID`, `coursecode`, `upcoming_sem_no`, `current_sec`, `desired_sec`, `preferred_faculty`, `preferred_time_slot`, `reason`, `status`, `matched_userID`, `created_at`) VALUES
(1, 'ZS22320', 'CSE370', 7, 1, 2, 'FEK', 'Sun/Tue 9:30 AM', '', 'OPEN', NULL, '2026-09-01 05:22:58'),
(101, 'S21102', 'CSE370', 7, 2, 1, 'THS', 'Sun/Tue 08:00 AM - 09:20 AM', 'Clash with university bus schedule on morning route', 'PENDING_PEER', 'ZS22320', '2026-09-01 05:27:47'),
(102, 'S22104', 'CSE370', 7, 3, 2, 'MMA', 'Sun/Tue 09:30 AM - 10:50 AM', 'Want to take MMA section with project group mates', 'OPEN', NULL, '2026-09-01 05:27:47'),
(103, 'S20018', 'CSE220', 7, 2, 1, 'KDR', 'Sun/Tue 11:00 AM - 12:20 PM', 'Need morning slot to attend lab duties later in the day', 'OPEN', NULL, '2026-09-01 05:27:47'),
(105, 'S22210', 'CSE420', 7, 2, 1, 'AAK', 'Sun/Tue 03:30 PM - 04:50 PM', 'Requested trade from Ayesha Siddiqua', 'PENDING_PEER', 'ZS22320', '2026-09-01 05:27:47'),
(106, 'ZS22320', 'CSE461', 7, 1, 2, 'MNA', 'Mon/Wed 03:30 PM - 04:50 PM', 'Swap approved by both students', 'ACCEPTED', 'S21088', '2026-09-01 05:27:47'),
(107, 'ZS22320', 'CSE321', 7, 4, 1, 'MAH', 'Sun/Tue 08:00 AM', 'Looking for Section 1 early morning', 'OPEN', NULL, '2026-09-01 05:27:47'),
(108, 'S23101', 'CSE370', 5, 1, 2, 'MMA', 'Sun/Tue 09:30 AM - 10:50 AM', 'Clashing with lab schedule on Monday', 'OPEN', NULL, '2026-09-03 09:57:10');

-- --------------------------------------------------------

--
-- Table structure for table `student_consult_mentor`
--

CREATE TABLE `student_consult_mentor` (
  `student_uID` varchar(8) NOT NULL,
  `mentor_studentID` varchar(8) NOT NULL,
  `mentorID` varchar(10) NOT NULL,
  `timeschedule` datetime NOT NULL,
  `status` enum('PENDING','ACCEPTED','REJECTED','CANCELLED') NOT NULL DEFAULT 'PENDING'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_consult_mentor`
--

INSERT INTO `student_consult_mentor` (`student_uID`, `mentor_studentID`, `mentorID`, `timeschedule`, `status`) VALUES
('S21305', 'S21088', 'MTR02', '2026-09-10 11:00:00', 'ACCEPTED'),
('S22104', 'S20018', 'MTR01', '2026-09-08 14:00:00', 'ACCEPTED'),
('S22210', 'ZS22320', 'MTR03', '2026-09-09 16:30:00', 'PENDING'),
('S23202', 'S20333', 'MTR04', '2026-09-12 15:00:00', 'ACCEPTED');

-- --------------------------------------------------------

--
-- Table structure for table `student_faculty_consultation`
--

CREATE TABLE `student_faculty_consultation` (
  `consultID` int(11) NOT NULL,
  `student_uID` varchar(8) NOT NULL,
  `faculty_uID` varchar(8) NOT NULL,
  `slotID` int(11) DEFAULT NULL,
  `consult_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `time_schedule` datetime DEFAULT NULL,
  `topic_reason` varchar(255) NOT NULL DEFAULT 'Academic Consultation',
  `is_emergency` tinyint(1) NOT NULL DEFAULT 0,
  `emergency_details` text DEFAULT NULL,
  `status` enum('PENDING','ACCEPTED','REJECTED','CANCELLED') NOT NULL DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_faculty_consultation`
--

INSERT INTO `student_faculty_consultation` (`consultID`, `student_uID`, `faculty_uID`, `slotID`, `consult_date`, `start_time`, `end_time`, `time_schedule`, `topic_reason`, `is_emergency`, `emergency_details`, `status`, `created_at`) VALUES
(1, 'ZS22320', 'FAC01', 1, '2026-09-06', '10:00:00', '10:30:00', '2026-09-06 10:00:00', 'Clarification on B+ Tree Indexing in CSE370', 0, NULL, 'ACCEPTED', '2026-09-03 09:54:28'),
(2, 'S21102', 'FAC02', 3, '2026-09-07', '09:30:00', '10:00:00', '2026-09-07 09:30:00', 'Discussion on Algorithm mid-term script checking', 0, NULL, 'PENDING', '2026-09-03 09:54:28'),
(3, 'S23101', 'FAC01', 1, '2026-09-13', '10:00:00', '10:30:00', '2026-09-13 10:00:00', 'Project Schema Review for CSE370', 0, NULL, 'ACCEPTED', '2026-09-03 09:57:10');

-- --------------------------------------------------------

--
-- Table structure for table `student_info`
--

CREATE TABLE `student_info` (
  `userID` varchar(8) NOT NULL,
  `SID` int(8) DEFAULT NULL,
  `cgpa` decimal(3,2) DEFAULT 0.00,
  `semester` varchar(100) DEFAULT NULL,
  `current_semester_no` int(2) NOT NULL DEFAULT 1,
  `completed_cod_credits` int(2) NOT NULL DEFAULT 0,
  `thesis_semesters_completed` int(1) NOT NULL DEFAULT 0,
  `is_doing_thesis` tinyint(1) NOT NULL DEFAULT 0,
  `is_doing_internship` tinyint(1) NOT NULL DEFAULT 0,
  `dept` varchar(500) NOT NULL DEFAULT 'CSE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_info`
--

INSERT INTO `student_info` (`userID`, `SID`, `cgpa`, `semester`, `current_semester_no`, `completed_cod_credits`, `thesis_semesters_completed`, `is_doing_thesis`, `is_doing_internship`, `dept`) VALUES
('22300000', NULL, 0.00, NULL, 1, 0, 0, 0, 0, 'CSE'),
('S20018', 20018002, 3.92, 'Spring 2026', 9, 124, 0, 1, 0, 'CSE'),
('S20333', 20333012, 3.91, 'Spring 2026', 10, 128, 1, 1, 0, 'CSE'),
('S21088', 21088015, 3.80, 'Spring 2026', 8, 105, 0, 0, 1, 'CSE'),
('S21102', 21102045, 3.78, 'Spring 2026', 8, 110, 0, 1, 0, 'CSE'),
('S21305', 21305088, 3.42, 'Spring 2026', 7, 92, 0, 0, 0, 'CSE'),
('S22104', 22104019, 3.65, 'Spring 2026', 7, 95, 0, 0, 0, 'CSE'),
('S22210', 22210034, 3.71, 'Spring 2026', 6, 84, 0, 0, 0, 'CSE'),
('S23101', 23101005, 3.65, 'Spring 2026', 5, 45, 0, 0, 0, 'CSE'),
('S23202', 23202041, 3.82, 'Spring 2026', 4, 33, 0, 0, 0, 'CSE'),
('ZS22320', 22320001, 3.85, 'Spring 2026', 6, 6, 1, 1, 0, 'CSE');

-- --------------------------------------------------------

--
-- Table structure for table `student_joins_group`
--

CREATE TABLE `student_joins_group` (
  `groupID` varchar(10) NOT NULL,
  `student_UID` varchar(8) NOT NULL,
  `joindate` date NOT NULL,
  `leaveDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_joins_group`
--

INSERT INTO `student_joins_group` (`groupID`, `student_UID`, `joindate`, `leaveDate`) VALUES
('GRP01', 'S21102', '2026-08-05', NULL),
('GRP01', 'S22104', '2026-08-10', NULL),
('GRP01', 'ZS22320', '2026-08-01', NULL),
('GRP02', 'S20018', '2026-08-01', NULL),
('GRP02', 'S22210', '2026-08-12', NULL),
('GRP04', 'S23101', '2026-09-02', NULL),
('GRP04', 'S23202', '2026-09-01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_rates_faculty`
--

CREATE TABLE `student_rates_faculty` (
  `student_uID` varchar(8) NOT NULL,
  `faculty_uID` varchar(8) NOT NULL,
  `rating` decimal(10,0) NOT NULL,
  `review` varchar(500) NOT NULL,
  `coursecode` varchar(10) NOT NULL DEFAULT 'CSE110'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_rates_faculty`
--

INSERT INTO `student_rates_faculty` (`student_uID`, `faculty_uID`, `rating`, `review`, `coursecode`) VALUES
('S20333', 'FAC04', 5, 'Brilliant guidance on advanced algorithmic paradigms.', 'CSE221'),
('S23101', 'FAC01', 5, 'Exceptional slides and very accessible during office hours.', 'CSE110'),
('ZS22320', 'FAC04', 5, '', 'CSE251'),
('ZS22320', 'FAC04', 2, '', 'CSE321'),
('ZS22320', 'FAC04', 5, 'Impressive Teaching', 'CSE421');

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

--
-- Dumping data for table `student_rates_mentor`
--

INSERT INTO `student_rates_mentor` (`student_uID`, `mentor_studentID`, `mentorID`, `rate`, `review`) VALUES
('S22104', 'S20018', 'MTR01', 5, 'Clear guidance on graph traversal and tree problems.'),
('S22210', 'ZS22320', 'MTR03', 4.8, 'Very practical explanation of normalization rules.'),
('S23202', 'S20333', 'MTR04', 5, 'Incredible help with dynamic programming memoization approaches.');

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

--
-- Dumping data for table `student_reviews_resources`
--

INSERT INTO `student_reviews_resources` (`resourceID`, `student_UID`, `rate`, `review`) VALUES
('RES101', 'S21102', 5, 'Clear breakdown of functional dependencies and candidate keys.'),
('RES102', 'ZS22320', 4.7, 'Well-structured diagrams for binary search trees.'),
('RES104', 'S23101', 5, 'Extremely clear breakdown of shift-reduce conflicts.');

-- --------------------------------------------------------

--
-- Table structure for table `student_review_course`
--

CREATE TABLE `student_review_course` (
  `reviewID` int(11) NOT NULL,
  `student_uID` varchar(8) NOT NULL,
  `coursecode` varchar(10) NOT NULL,
  `rate` float NOT NULL,
  `review` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_review_course`
--

INSERT INTO `student_review_course` (`reviewID`, `student_uID`, `coursecode`, `rate`, `review`) VALUES
(1, 'S21102', 'CSE110', 4.5, 'Great introductory programming course!'),
(2, 'S22104', 'CSE110', 4, 'Clear fundamentals and lab practice.'),
(3, 'ZS22320', 'CSE220', 4.8, 'Challenging data structures, very rewarding.'),
(4, 'S20018', 'CSE220', 4.2, 'Essential for coding interviews.'),
(5, 'S21088', 'CSE370', 5, 'Best DBMS course, highly practical project.'),
(6, 'S22210', 'CSE370', 4.6, 'Solid SQL foundations and schema design.'),
(7, 'S23101', 'CSE221', 4.7, 'Demanding assignments, but teaches core problem-solving intuition.'),
(8, 'S20333', 'CSE370', 5, 'The most practical course in the CS degree. Master SQL normalization early!'),
(9, 'S23202', 'CSE111', 4.3, 'Good hands-on OOP projects in Java.');

-- --------------------------------------------------------

--
-- Table structure for table `student_saved_resources`
--

CREATE TABLE `student_saved_resources` (
  `saveID` int(11) NOT NULL,
  `student_UID` varchar(8) NOT NULL,
  `resourceID` varchar(10) NOT NULL,
  `folder_type` enum('READ_LATER','SAVE_FOLDER') NOT NULL DEFAULT 'READ_LATER',
  `saved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_saved_resources`
--

INSERT INTO `student_saved_resources` (`saveID`, `student_UID`, `resourceID`, `folder_type`, `saved_at`) VALUES
(1, 'ZS22320', 'RES102', 'SAVE_FOLDER', '2026-09-03 09:54:28'),
(2, 'S21102', 'RES101', 'READ_LATER', '2026-09-03 09:54:28'),
(3, 'S22104', 'RES101', 'SAVE_FOLDER', '2026-09-03 09:54:28'),
(4, 'S23101', 'RES104', 'SAVE_FOLDER', '2026-09-03 09:57:11');

-- --------------------------------------------------------

--
-- Table structure for table `student_work_submission`
--

CREATE TABLE `student_work_submission` (
  `submission_id` int(11) NOT NULL,
  `student_uID` varchar(8) NOT NULL,
  `title` varchar(150) NOT NULL,
  `work_type` enum('Project','Paper','Journal') NOT NULL,
  `description` text DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_work_submission`
--

INSERT INTO `student_work_submission` (`submission_id`, `student_uID`, `title`, `work_type`, `description`, `file_name`, `file_path`, `uploaded_at`) VALUES
(1, '22300000', 'Data mining', 'Paper', 'gsuayhui hdsg', 'normalized.drawio.png', 'uploads/works/normalized_drawio_1788429085_6a99431d715bd.png', '2026-09-03 09:51:25'),
(2, 'ZS22320', 'StudyVerse - Pure PHP Academic Ecosystem', 'Project', 'Full-stack course management and peer exchange without JavaScript.', 'studyverse_report.pdf', 'uploads/submissions/sub_01.pdf', '2026-09-03 09:54:28'),
(3, 'S20018', 'Distributed Consensus Protocol Benchmarks', 'Paper', 'Evaluation of Raft algorithms under network partition failures.', 'raft_benchmarks.pdf', 'uploads/submissions/sub_02.pdf', '2026-09-03 09:54:28'),
(4, 'S20333', 'Automated SQL Query Plan Optimization via Cost Modeling', 'Paper', 'Research paper evaluating B-Tree node access overheads in high-write workloads.', 'sql_optimization_report.pdf', 'uploads/submissions/s20333_paper.pdf', '2026-09-03 09:57:11'),
(5, 'ZS22320', 'sds', 'Project', 'sss', 'normalized.drawio.png', 'uploads/works/normalized_drawio_1788432473_6a995059cf974.png', '2026-09-03 10:47:53');

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
('1122', '985327'),
('22300000', '01600000'),
('S20018', '01644556677'),
('S20333', '01912340003'),
('S21088', '01977889900'),
('S21102', '01822334455'),
('S21305', '01755667788'),
('S22104', '01933445566'),
('S22210', '01866778899'),
('S23101', '01712340001'),
('S23202', '01812340002'),
('ZS22320', '01711002233'),
('ZS22320', '017111111banai');

-- --------------------------------------------------------

--
-- Table structure for table `user_contributions`
--

CREATE TABLE `user_contributions` (
  `contribID` int(11) NOT NULL,
  `userID` varchar(8) NOT NULL,
  `activity_type` enum('UPLOAD_NOTE','VERIFIED_ANSWER','MENTOR_SESSION','SWAP_ASSIST','COURSE_REVIEW') NOT NULL,
  `tokens_earned` int(11) NOT NULL DEFAULT 5,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_contributions`
--

INSERT INTO `user_contributions` (`contribID`, `userID`, `activity_type`, `tokens_earned`, `created_at`) VALUES
(1, 'ZS22320', 'COURSE_REVIEW', 5, '2026-09-03 09:54:28'),
(2, 'ZS22320', 'UPLOAD_NOTE', 10, '2026-09-03 09:54:28'),
(3, 'S20018', 'MENTOR_SESSION', 15, '2026-09-03 09:54:28'),
(4, 'S21088', 'UPLOAD_NOTE', 10, '2026-09-03 09:54:28'),
(5, 'S21102', 'SWAP_ASSIST', 5, '2026-09-03 09:54:28'),
(6, 'S20333', 'UPLOAD_NOTE', 10, '2026-09-03 09:57:11'),
(7, 'S20333', 'MENTOR_SESSION', 15, '2026-09-03 09:57:11'),
(8, 'S23101', 'COURSE_REVIEW', 5, '2026-09-03 09:57:11'),
(9, 'S23202', 'COURSE_REVIEW', 5, '2026-09-03 09:57:11');

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
  `AdminID` varchar(8) NOT NULL,
  `pending_password` varchar(255) DEFAULT NULL,
  `password_status` enum('NONE','PENDING','APPROVED','REJECTED') NOT NULL DEFAULT 'NONE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_info`
--

INSERT INTO `user_info` (`userID`, `name`, `email`, `password`, `road`, `area`, `AdminID`, `pending_password`, `password_status`) VALUES
('1122', 'Md. Fahim', 'fahim@gmail.com', '1122', 'road11', 'banani', 'A1', NULL, 'NONE'),
('22300000', 'Sadman', 'sadman@gmail.com', '2230', '11', 'merul badda', 'A1', NULL, 'NONE'),
('FAC01', 'Dr. Tariq Hasan', 't.hasan@studyverse.edu', '1234', 'Road 1', 'Banani', 'A1001', NULL, 'NONE'),
('FAC02', 'Mahmudul Alam', 'm.alam@studyverse.edu', '1234', 'Road 5', 'Dhanmondi', 'A1001', NULL, 'NONE'),
('FAC03', 'Kazi Dawlatur Rahman', 'k.rahman@studyverse.edu', '1234', 'Road 12', 'Gulshan', 'A1001', NULL, 'NONE'),
('FAC04', 'Dr. Rezwana Huq', 'r.huq@studyverse.edu', '1234', 'Road 8', 'Uttara', 'A1001', NULL, 'NONE'),
('S20018', 'Maliha Rahman', 'maliha.r@studyverse.edu', '1234', 'Road 7', 'Gulshan', 'A1001', NULL, 'NONE'),
('S20333', 'Kazi Tamim', 'kazi.t@studyverse.edu', 'tamim1234', 'Road 27', 'Banani', 'A1', NULL, 'NONE'),
('S21088', 'Nabil Iqbal', 'nabil.i@studyverse.edu', '1234', 'Road 15', 'Bashundhara', 'A1001', NULL, 'NONE'),
('S21102', 'Shafin Hasan', 'shafin.h@studyverse.edu', '1234', 'Road 4', 'Dhanmondi', 'A1001', NULL, 'NONE'),
('S21305', 'Fahim Ahmed', 'fahim.a@studyverse.edu', '1234', 'Road 2', 'Mirpur', 'A1001', NULL, 'NONE'),
('S22104', 'Tanvir Khan', 'tanvir.k@studyverse.edu', '1234', 'Road 18', 'Uttara', 'A1001', NULL, 'NONE'),
('S22210', 'Ayesha Siddiqua', 'ayesha.s@studyverse.edu', '1234', 'Road 9', 'Mohakhali', 'A1001', NULL, 'NONE'),
('S23101', 'Arif Hossain', 'arif.h@studyverse.edu', 'arif1234', 'Road 14', 'Uttara', 'A1', 'passReset2026', 'PENDING'),
('S23202', 'Nusrat Jahan', 'nusrat.j@studyverse.edu', 'nusrat1234', 'Road 3', 'Dhanmondi', 'A1001', NULL, 'NONE'),
('ZS22320', 'Zareen Subah', 'zareen.subah1@g.bracu.ac.bd', 'zareensubah', 'road11', 'banani', 'A1', 'newpassword123', 'PENDING');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_course_delete_consensus`
--
ALTER TABLE `admin_course_delete_consensus`
  ADD PRIMARY KEY (`requestID`),
  ADD KEY `coursecode` (`coursecode`),
  ADD KEY `requested_by_adminID` (`requested_by_adminID`),
  ADD KEY `course_owner_adminID` (`course_owner_adminID`);

--
-- Indexes for table `admin_course_delete_requests`
--
ALTER TABLE `admin_course_delete_requests`
  ADD PRIMARY KEY (`requestID`),
  ADD KEY `coursecode` (`coursecode`),
  ADD KEY `requestedBy` (`requestedBy`);

--
-- Indexes for table `admin_course_delete_votes`
--
ALTER TABLE `admin_course_delete_votes`
  ADD PRIMARY KEY (`requestID`,`adminID`),
  ADD KEY `adminID` (`adminID`);

--
-- Indexes for table `admin_delete_votes`
--
ALTER TABLE `admin_delete_votes`
  ADD PRIMARY KEY (`voteID`),
  ADD UNIQUE KEY `unique_admin_vote` (`requestID`,`adminID`),
  ADD KEY `adminID` (`adminID`);

--
-- Indexes for table `admin_incentive_campaigns`
--
ALTER TABLE `admin_incentive_campaigns`
  ADD PRIMARY KEY (`campaignID`),
  ADD KEY `adminID` (`adminID`),
  ADD KEY `student_UID` (`student_UID`);

--
-- Indexes for table `admin_info`
--
ALTER TABLE `admin_info`
  ADD PRIMARY KEY (`AdminID`);

--
-- Indexes for table `admin_messages`
--
ALTER TABLE `admin_messages`
  ADD PRIMARY KEY (`messageID`),
  ADD KEY `fromAdmin` (`fromAdmin`),
  ADD KEY `toAdmin` (`toAdmin`);

--
-- Indexes for table `admin_work_due`
--
ALTER TABLE `admin_work_due`
  ADD PRIMARY KEY (`workID`),
  ADD KEY `adminID` (`adminID`);

--
-- Indexes for table `consultation_availability`
--
ALTER TABLE `consultation_availability`
  ADD PRIMARY KEY (`availability_id`);

--
-- Indexes for table `consultation_requests`
--
ALTER TABLE `consultation_requests`
  ADD PRIMARY KEY (`request_id`);

--
-- Indexes for table `content_reports`
--
ALTER TABLE `content_reports`
  ADD PRIMARY KEY (`reportID`),
  ADD KEY `reporterID` (`reporterID`);

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`coursecode`),
  ADD KEY `admin` (`admin`),
  ADD KEY `idx_course_track` (`track`),
  ADD KEY `idx_course_dept` (`dept`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`coursecode`);

--
-- Indexes for table `course_prerequisite`
--
ALTER TABLE `course_prerequisite`
  ADD PRIMARY KEY (`coursecode`,`prereq_code`),
  ADD KEY `fk_cp_target` (`prereq_code`);

--
-- Indexes for table `course_prerequisites`
--
ALTER TABLE `course_prerequisites`
  ADD PRIMARY KEY (`coursecode`,`prereq_coursecode`),
  ADD KEY `prereq_coursecode` (`prereq_coursecode`);

--
-- Indexes for table `course_resouce`
--
ALTER TABLE `course_resouce`
  ADD PRIMARY KEY (`course_resourceID`),
  ADD KEY `student_UID` (`student_UID`),
  ADD KEY `coursecode` (`coursecode`),
  ADD KEY `verified_by_faculty_UID` (`verified_by_faculty_UID`);

--
-- Indexes for table `course_resources`
--
ALTER TABLE `course_resources`
  ADD PRIMARY KEY (`resourceID`);

--
-- Indexes for table `course_student_took`
--
ALTER TABLE `course_student_took`
  ADD PRIMARY KEY (`recordID`),
  ADD UNIQUE KEY `unique_student_course` (`userID`,`coursecode`),
  ADD KEY `coursecode` (`coursecode`);

--
-- Indexes for table `enrolled_courses`
--
ALTER TABLE `enrolled_courses`
  ADD PRIMARY KEY (`enrollmentID`),
  ADD UNIQUE KEY `user_course_sem` (`userID`,`coursecode`,`semester`);

--
-- Indexes for table `faculty_consultation_slots`
--
ALTER TABLE `faculty_consultation_slots`
  ADD PRIMARY KEY (`slotID`),
  ADD KEY `faculty_uID` (`faculty_uID`);

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
  ADD UNIQUE KEY `FID` (`FID`,`faculty_initial`);

--
-- Indexes for table `faculty_work_reviews`
--
ALTER TABLE `faculty_work_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `unique_review` (`submission_id`,`faculty_uID`);

--
-- Indexes for table `group_consult_faculty`
--
ALTER TABLE `group_consult_faculty`
  ADD PRIMARY KEY (`groupID`,`faculty_uID`,`schedule`),
  ADD KEY `faculty_uID` (`faculty_uID`);

--
-- Indexes for table `group_info`
--
ALTER TABLE `group_info`
  ADD PRIMARY KEY (`groupID`),
  ADD KEY `group_creator_uid` (`group_creator_uid`),
  ADD KEY `adminID` (`adminID`);

--
-- Indexes for table `group_join_requests`
--
ALTER TABLE `group_join_requests`
  ADD PRIMARY KEY (`requestID`),
  ADD KEY `groupID` (`groupID`),
  ADD KEY `student_UID` (`student_UID`);

--
-- Indexes for table `group_messages`
--
ALTER TABLE `group_messages`
  ADD PRIMARY KEY (`messageID`),
  ADD KEY `groupID` (`groupID`),
  ADD KEY `senderID` (`senderID`);

--
-- Indexes for table `group_rated_by_student`
--
ALTER TABLE `group_rated_by_student`
  ADD PRIMARY KEY (`groupID`,`student_UID`),
  ADD KEY `student_UID` (`student_UID`);

--
-- Indexes for table `group_weekly_goals`
--
ALTER TABLE `group_weekly_goals`
  ADD PRIMARY KEY (`goalID`),
  ADD UNIQUE KEY `uq_goal` (`groupID`,`studentID`,`week_start`),
  ADD KEY `studentID` (`studentID`);

--
-- Indexes for table `mentor`
--
ALTER TABLE `mentor`
  ADD PRIMARY KEY (`userID`,`MentorID`),
  ADD UNIQUE KEY `MentorID` (`MentorID`);

--
-- Indexes for table `next_sem_planned_combinations`
--
ALTER TABLE `next_sem_planned_combinations`
  ADD PRIMARY KEY (`planID`),
  ADD KEY `userID` (`userID`),
  ADD KEY `coursecode` (`coursecode`),
  ADD KEY `idx_session_combo` (`session_token`,`combo_id`);

--
-- Indexes for table `repositories`
--
ALTER TABLE `repositories`
  ADD PRIMARY KEY (`repoID`);

--
-- Indexes for table `repository`
--
ALTER TABLE `repository`
  ADD PRIMARY KEY (`repoID`),
  ADD UNIQUE KEY `unique_repo_name` (`name`),
  ADD KEY `coursecode` (`coursecode`),
  ADD KEY `adminID` (`adminID`);

--
-- Indexes for table `repo_creation_requests`
--
ALTER TABLE `repo_creation_requests`
  ADD PRIMARY KEY (`requestID`),
  ADD KEY `student_UID` (`student_UID`),
  ADD KEY `requestedBy` (`requestedBy`),
  ADD KEY `coursecode` (`coursecode`);

--
-- Indexes for table `resource_uploaded_in_repo`
--
ALTER TABLE `resource_uploaded_in_repo`
  ADD PRIMARY KEY (`repoID`,`resourceID`),
  ADD KEY `resourceID` (`resourceID`);

--
-- Indexes for table `resource_views_reads`
--
ALTER TABLE `resource_views_reads`
  ADD PRIMARY KEY (`viewID`),
  ADD UNIQUE KEY `unique_student_read` (`resourceID`,`student_UID`),
  ADD KEY `student_UID` (`student_UID`);

--
-- Indexes for table `section_offerings`
--
ALTER TABLE `section_offerings`
  ADD PRIMARY KEY (`offeringID`),
  ADD UNIQUE KEY `unique_course_sec` (`coursecode`,`section_no`,`semester`);

--
-- Indexes for table `section_swap_requests`
--
ALTER TABLE `section_swap_requests`
  ADD PRIMARY KEY (`swapID`),
  ADD KEY `userID` (`userID`),
  ADD KEY `coursecode` (`coursecode`),
  ADD KEY `matched_userID` (`matched_userID`);

--
-- Indexes for table `student_consult_mentor`
--
ALTER TABLE `student_consult_mentor`
  ADD PRIMARY KEY (`student_uID`,`mentor_studentID`,`mentorID`,`timeschedule`),
  ADD KEY `mentor_studentID` (`mentor_studentID`),
  ADD KEY `mentorID` (`mentorID`),
  ADD KEY `student_consult_mentor_ibfk_2` (`mentor_studentID`,`mentorID`);

--
-- Indexes for table `student_faculty_consultation`
--
ALTER TABLE `student_faculty_consultation`
  ADD PRIMARY KEY (`consultID`),
  ADD KEY `student_uID` (`student_uID`),
  ADD KEY `faculty_uID` (`faculty_uID`),
  ADD KEY `slotID` (`slotID`);

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
  ADD PRIMARY KEY (`student_uID`,`faculty_uID`,`coursecode`),
  ADD KEY `faculty_uID` (`faculty_uID`);

--
-- Indexes for table `student_rates_mentor`
--
ALTER TABLE `student_rates_mentor`
  ADD PRIMARY KEY (`student_uID`,`mentor_studentID`,`mentorID`),
  ADD KEY `mentor_studentID` (`mentor_studentID`),
  ADD KEY `student_rates_mentor_ibfk_2` (`mentor_studentID`,`mentorID`);

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
  ADD PRIMARY KEY (`reviewID`),
  ADD KEY `student_uID` (`student_uID`),
  ADD KEY `coursecode` (`coursecode`);

--
-- Indexes for table `student_saved_resources`
--
ALTER TABLE `student_saved_resources`
  ADD PRIMARY KEY (`saveID`),
  ADD UNIQUE KEY `unique_saved_item` (`student_UID`,`resourceID`,`folder_type`),
  ADD KEY `resourceID` (`resourceID`);

--
-- Indexes for table `student_work_submission`
--
ALTER TABLE `student_work_submission`
  ADD PRIMARY KEY (`submission_id`);

--
-- Indexes for table `userphone`
--
ALTER TABLE `userphone`
  ADD PRIMARY KEY (`userID`,`phone`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Indexes for table `user_contributions`
--
ALTER TABLE `user_contributions`
  ADD PRIMARY KEY (`contribID`),
  ADD KEY `userID` (`userID`),
  ADD KEY `idx_contrib_date` (`created_at`);

--
-- Indexes for table `user_info`
--
ALTER TABLE `user_info`
  ADD PRIMARY KEY (`userID`),
  ADD KEY `AdminID` (`AdminID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_course_delete_consensus`
--
ALTER TABLE `admin_course_delete_consensus`
  MODIFY `requestID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_course_delete_requests`
--
ALTER TABLE `admin_course_delete_requests`
  MODIFY `requestID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_delete_votes`
--
ALTER TABLE `admin_delete_votes`
  MODIFY `voteID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_incentive_campaigns`
--
ALTER TABLE `admin_incentive_campaigns`
  MODIFY `campaignID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `admin_messages`
--
ALTER TABLE `admin_messages`
  MODIFY `messageID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `admin_work_due`
--
ALTER TABLE `admin_work_due`
  MODIFY `workID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `consultation_availability`
--
ALTER TABLE `consultation_availability`
  MODIFY `availability_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `consultation_requests`
--
ALTER TABLE `consultation_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `content_reports`
--
ALTER TABLE `content_reports`
  MODIFY `reportID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `course_resources`
--
ALTER TABLE `course_resources`
  MODIFY `resourceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `course_student_took`
--
ALTER TABLE `course_student_took`
  MODIFY `recordID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `enrolled_courses`
--
ALTER TABLE `enrolled_courses`
  MODIFY `enrollmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `faculty_consultation_slots`
--
ALTER TABLE `faculty_consultation_slots`
  MODIFY `slotID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `faculty_work_reviews`
--
ALTER TABLE `faculty_work_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `group_join_requests`
--
ALTER TABLE `group_join_requests`
  MODIFY `requestID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `group_messages`
--
ALTER TABLE `group_messages`
  MODIFY `messageID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `group_weekly_goals`
--
ALTER TABLE `group_weekly_goals`
  MODIFY `goalID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `next_sem_planned_combinations`
--
ALTER TABLE `next_sem_planned_combinations`
  MODIFY `planID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `repositories`
--
ALTER TABLE `repositories`
  MODIFY `repoID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `repo_creation_requests`
--
ALTER TABLE `repo_creation_requests`
  MODIFY `requestID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `resource_views_reads`
--
ALTER TABLE `resource_views_reads`
  MODIFY `viewID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `section_offerings`
--
ALTER TABLE `section_offerings`
  MODIFY `offeringID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `section_swap_requests`
--
ALTER TABLE `section_swap_requests`
  MODIFY `swapID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT for table `student_faculty_consultation`
--
ALTER TABLE `student_faculty_consultation`
  MODIFY `consultID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `student_review_course`
--
ALTER TABLE `student_review_course`
  MODIFY `reviewID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `student_saved_resources`
--
ALTER TABLE `student_saved_resources`
  MODIFY `saveID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `student_work_submission`
--
ALTER TABLE `student_work_submission`
  MODIFY `submission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_contributions`
--
ALTER TABLE `user_contributions`
  MODIFY `contribID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_course_delete_consensus`
--
ALTER TABLE `admin_course_delete_consensus`
  ADD CONSTRAINT `fk_del_owner_admin` FOREIGN KEY (`course_owner_adminID`) REFERENCES `admin_info` (`AdminID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_del_req_admin` FOREIGN KEY (`requested_by_adminID`) REFERENCES `admin_info` (`AdminID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_del_req_course` FOREIGN KEY (`coursecode`) REFERENCES `course` (`coursecode`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `admin_course_delete_requests`
--
ALTER TABLE `admin_course_delete_requests`
  ADD CONSTRAINT `fk_cdr_admin` FOREIGN KEY (`requestedBy`) REFERENCES `admin_info` (`AdminID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cdr_course` FOREIGN KEY (`coursecode`) REFERENCES `course` (`coursecode`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `admin_course_delete_votes`
--
ALTER TABLE `admin_course_delete_votes`
  ADD CONSTRAINT `fk_cdv_admin` FOREIGN KEY (`adminID`) REFERENCES `admin_info` (`AdminID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `admin_delete_votes`
--
ALTER TABLE `admin_delete_votes`
  ADD CONSTRAINT `fk_vote_admin` FOREIGN KEY (`adminID`) REFERENCES `admin_info` (`AdminID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_vote_request` FOREIGN KEY (`requestID`) REFERENCES `admin_course_delete_consensus` (`requestID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `admin_incentive_campaigns`
--
ALTER TABLE `admin_incentive_campaigns`
  ADD CONSTRAINT `fk_camp_admin` FOREIGN KEY (`adminID`) REFERENCES `admin_info` (`AdminID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_camp_student` FOREIGN KEY (`student_UID`) REFERENCES `student_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `admin_messages`
--
ALTER TABLE `admin_messages`
  ADD CONSTRAINT `fk_msg_from_admin` FOREIGN KEY (`fromAdmin`) REFERENCES `admin_info` (`AdminID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_msg_to_admin` FOREIGN KEY (`toAdmin`) REFERENCES `admin_info` (`AdminID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `admin_work_due`
--
ALTER TABLE `admin_work_due`
  ADD CONSTRAINT `fk_work_admin` FOREIGN KEY (`adminID`) REFERENCES `admin_info` (`AdminID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `content_reports`
--
ALTER TABLE `content_reports`
  ADD CONSTRAINT `fk_report_reporter` FOREIGN KEY (`reporterID`) REFERENCES `user_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `course`
--
ALTER TABLE `course`
  ADD CONSTRAINT `course_ibfk_1` FOREIGN KEY (`admin`) REFERENCES `admin_info` (`AdminID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `course_prerequisite`
--
ALTER TABLE `course_prerequisite`
  ADD CONSTRAINT `fk_cp_course` FOREIGN KEY (`coursecode`) REFERENCES `course` (`coursecode`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cp_target` FOREIGN KEY (`prereq_code`) REFERENCES `course` (`coursecode`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `course_prerequisites`
--
ALTER TABLE `course_prerequisites`
  ADD CONSTRAINT `fk_prereq_course` FOREIGN KEY (`coursecode`) REFERENCES `course` (`coursecode`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_prereq_target` FOREIGN KEY (`prereq_coursecode`) REFERENCES `course` (`coursecode`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `course_resouce`
--
ALTER TABLE `course_resouce`
  ADD CONSTRAINT `course_resouce_ibfk_1` FOREIGN KEY (`student_UID`) REFERENCES `student_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `course_resouce_ibfk_2` FOREIGN KEY (`coursecode`) REFERENCES `course` (`coursecode`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_resource_faculty_ver` FOREIGN KEY (`verified_by_faculty_UID`) REFERENCES `faculty_info` (`userID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `course_student_took`
--
ALTER TABLE `course_student_took`
  ADD CONSTRAINT `course_student_took_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `student_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `course_student_took_ibfk_2` FOREIGN KEY (`coursecode`) REFERENCES `course` (`coursecode`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `faculty_consultation_slots`
--
ALTER TABLE `faculty_consultation_slots`
  ADD CONSTRAINT `fk_slot_faculty` FOREIGN KEY (`faculty_uID`) REFERENCES `faculty_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `group_consult_faculty_ibfk_1` FOREIGN KEY (`groupID`) REFERENCES `group_info` (`groupID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `group_consult_faculty_ibfk_2` FOREIGN KEY (`faculty_uID`) REFERENCES `faculty_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `group_info`
--
ALTER TABLE `group_info`
  ADD CONSTRAINT `group_info_ibfk_2` FOREIGN KEY (`group_creator_uid`) REFERENCES `student_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `group_info_ibfk_3` FOREIGN KEY (`adminID`) REFERENCES `admin_info` (`AdminID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `group_join_requests`
--
ALTER TABLE `group_join_requests`
  ADD CONSTRAINT `fk_gjr_group` FOREIGN KEY (`groupID`) REFERENCES `group_info` (`groupID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_gjr_student` FOREIGN KEY (`student_UID`) REFERENCES `student_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `group_messages`
--
ALTER TABLE `group_messages`
  ADD CONSTRAINT `fk_gmsg_group` FOREIGN KEY (`groupID`) REFERENCES `group_info` (`groupID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_gmsg_sender` FOREIGN KEY (`senderID`) REFERENCES `user_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `group_rated_by_student`
--
ALTER TABLE `group_rated_by_student`
  ADD CONSTRAINT `group_rated_by_student_ibfk_1` FOREIGN KEY (`groupID`,`student_UID`) REFERENCES `student_joins_group` (`groupID`, `student_UID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `group_weekly_goals`
--
ALTER TABLE `group_weekly_goals`
  ADD CONSTRAINT `fk_gwg_group` FOREIGN KEY (`groupID`) REFERENCES `group_info` (`groupID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_gwg_student` FOREIGN KEY (`studentID`) REFERENCES `student_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mentor`
--
ALTER TABLE `mentor`
  ADD CONSTRAINT `mentor_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `student_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `next_sem_planned_combinations`
--
ALTER TABLE `next_sem_planned_combinations`
  ADD CONSTRAINT `fk_plan_course` FOREIGN KEY (`coursecode`) REFERENCES `course` (`coursecode`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_plan_user` FOREIGN KEY (`userID`) REFERENCES `student_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `repository`
--
ALTER TABLE `repository`
  ADD CONSTRAINT `repository_ibfk_1` FOREIGN KEY (`coursecode`) REFERENCES `course` (`coursecode`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `repository_ibfk_2` FOREIGN KEY (`adminID`) REFERENCES `admin_info` (`AdminID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `repo_creation_requests`
--
ALTER TABLE `repo_creation_requests`
  ADD CONSTRAINT `fk_repo_req_course` FOREIGN KEY (`coursecode`) REFERENCES `course` (`coursecode`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `resource_uploaded_in_repo`
--
ALTER TABLE `resource_uploaded_in_repo`
  ADD CONSTRAINT `resource_uploaded_in_repo_ibfk_1` FOREIGN KEY (`repoID`) REFERENCES `repository` (`repoID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `resource_uploaded_in_repo_ibfk_2` FOREIGN KEY (`resourceID`) REFERENCES `course_resouce` (`course_resourceID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `resource_views_reads`
--
ALTER TABLE `resource_views_reads`
  ADD CONSTRAINT `fk_read_res` FOREIGN KEY (`resourceID`) REFERENCES `course_resouce` (`course_resourceID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_read_student` FOREIGN KEY (`student_UID`) REFERENCES `student_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `section_offerings`
--
ALTER TABLE `section_offerings`
  ADD CONSTRAINT `fk_sec_course` FOREIGN KEY (`coursecode`) REFERENCES `course` (`coursecode`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `section_swap_requests`
--
ALTER TABLE `section_swap_requests`
  ADD CONSTRAINT `fk_swap_course` FOREIGN KEY (`coursecode`) REFERENCES `course` (`coursecode`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_swap_match_user` FOREIGN KEY (`matched_userID`) REFERENCES `student_info` (`userID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_swap_user` FOREIGN KEY (`userID`) REFERENCES `student_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_consult_mentor`
--
ALTER TABLE `student_consult_mentor`
  ADD CONSTRAINT `student_consult_mentor_ibfk_1` FOREIGN KEY (`student_uID`) REFERENCES `student_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_consult_mentor_ibfk_2` FOREIGN KEY (`mentor_studentID`,`mentorID`) REFERENCES `mentor` (`userID`, `MentorID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_faculty_consultation`
--
ALTER TABLE `student_faculty_consultation`
  ADD CONSTRAINT `fk_sfc_faculty` FOREIGN KEY (`faculty_uID`) REFERENCES `faculty_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sfc_slot` FOREIGN KEY (`slotID`) REFERENCES `faculty_consultation_slots` (`slotID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sfc_student` FOREIGN KEY (`student_uID`) REFERENCES `student_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `student_rates_mentor_ibfk_2` FOREIGN KEY (`mentor_studentID`,`mentorID`) REFERENCES `mentor` (`userID`, `MentorID`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `fk_review_course_student` FOREIGN KEY (`student_uID`) REFERENCES `student_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_review_course_target` FOREIGN KEY (`coursecode`) REFERENCES `course` (`coursecode`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_saved_resources`
--
ALTER TABLE `student_saved_resources`
  ADD CONSTRAINT `fk_save_res` FOREIGN KEY (`resourceID`) REFERENCES `course_resouce` (`course_resourceID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_save_student` FOREIGN KEY (`student_UID`) REFERENCES `student_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `userphone`
--
ALTER TABLE `userphone`
  ADD CONSTRAINT `userphone_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_contributions`
--
ALTER TABLE `user_contributions`
  ADD CONSTRAINT `fk_contrib_user` FOREIGN KEY (`userID`) REFERENCES `user_info` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_info`
--
ALTER TABLE `user_info`
  ADD CONSTRAINT `user_info_ibfk_1` FOREIGN KEY (`AdminID`) REFERENCES `admin_info` (`AdminID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
