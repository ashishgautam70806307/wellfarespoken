-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 22, 2026 at 06:24 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `wellfare_english`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `published` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password_hash`, `published`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@wellfare.local', '$2y$12$DHCToBguTMZptJEHcBMUGuoAErIOUDX45NhgtxRT6i9LPRaojvz5u', 'Yes', '2026-06-20 06:23:11', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `batch_timings`
--

CREATE TABLE `batch_timings` (
  `id` int(10) UNSIGNED NOT NULL,
  `batch_name` varchar(160) NOT NULL,
  `course_name` varchar(160) DEFAULT NULL,
  `timing` varchar(120) DEFAULT NULL,
  `days` varchar(120) DEFAULT NULL,
  `seats_note` varchar(160) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `published` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `batch_timings`
--

INSERT INTO `batch_timings` (`id`, `batch_name`, `course_name`, `timing`, `days`, `seats_note`, `sort_order`, `published`, `created_at`) VALUES
(1, 'Morning Speaking Batch', 'Basic Spoken English', '7:00 AM - 8:00 AM', 'Mon to Sat', 'Limited seats available', 1, 'Yes', '2026-06-20 06:23:11'),
(2, 'Evening Confidence Batch', 'Advanced Spoken English', '6:00 PM - 7:00 PM', 'Mon to Sat', 'Best for students and working professionals', 2, 'Yes', '2026-06-20 06:23:11'),
(3, 'Weekend Interview Batch', 'Interview Preparation', '10:00 AM - 12:00 PM', 'Saturday and Sunday', 'Admission open this week', 3, 'Yes', '2026-06-20 06:23:11'),
(4, 'Morning Speaking Batch', 'Basic Spoken English', '7:00 AM - 8:00 AM', 'Mon to Sat', 'Limited seats available', 1, 'Yes', '2026-06-21 22:09:35'),
(5, 'Evening Confidence Batch', 'Advanced Spoken English', '6:00 PM - 7:00 PM', 'Mon to Sat', 'Best for students and working professionals', 2, 'Yes', '2026-06-21 22:09:35'),
(6, 'Weekend Interview Batch', 'Interview Preparation', '10:00 AM - 12:00 PM', 'Saturday and Sunday', 'Admission open this week', 3, 'Yes', '2026-06-21 22:09:35');

-- --------------------------------------------------------

--
-- Table structure for table `content_blocks`
--

CREATE TABLE `content_blocks` (
  `id` int(10) UNSIGNED NOT NULL,
  `block_type` varchar(80) NOT NULL,
  `block_key` varchar(120) DEFAULT NULL,
  `icon` varchar(40) DEFAULT NULL,
  `eyebrow` varchar(160) DEFAULT NULL,
  `title` varchar(220) NOT NULL,
  `subtitle` text DEFAULT NULL,
  `body` text DEFAULT NULL,
  `link_text` varchar(120) DEFAULT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `published` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `content_blocks`
--

INSERT INTO `content_blocks` (`id`, `block_type`, `block_key`, `icon`, `eyebrow`, `title`, `subtitle`, `body`, `link_text`, `link_url`, `sort_order`, `published`, `created_at`) VALUES
(1, 'home_feature', 'conversation', '💬', '', 'Conversation Practice', 'Daily sentence speaking, question-answer and real-life conversation drills.', '', '', '', 1, 'Yes', '2026-06-20 06:23:11'),
(2, 'home_feature', 'grammar', '🧠', '', 'Grammar Made Easy', 'Learn grammar in a practical way so students can use it while speaking.', '', '', '', 2, 'Yes', '2026-06-20 06:23:11'),
(3, 'home_feature', 'confidence', '🎯', '', 'Confidence Training', 'Remove hesitation with classroom activities, presentation and correction.', '', '', '', 3, 'Yes', '2026-06-20 06:23:11'),
(4, 'home_feature', 'interview', '💼', '', 'Interview Support', 'Prepare introduction, common questions, answers and professional communication.', '', '', '', 4, 'Yes', '2026-06-20 06:23:11'),
(5, 'hero_stat', 'practice', '', '', 'Daily', 'Speaking Practice', '', '', '', 1, 'Yes', '2026-06-20 06:23:11'),
(6, 'hero_stat', 'grammar', '', '', 'Basic+', 'Grammar to Fluency', '', '', '', 2, 'Yes', '2026-06-20 06:23:11'),
(7, 'hero_stat', 'trust', '', '', 'Local', 'Trusted Institute', '', '', '', 3, 'Yes', '2026-06-20 06:23:11'),
(8, 'about_highlight', 'trust', '🏫', '', 'Local Trust', 'Designed for students of Mariahu and nearby areas who want better English communication.', '', '', '', 1, 'Yes', '2026-06-20 06:23:11'),
(9, 'about_highlight', 'teacher', '👨‍🏫', '', 'Teacher-Led Practice', 'Classroom guidance, correction and repeated speaking practice help students improve faster.', '', '', '', 2, 'Yes', '2026-06-20 06:23:11'),
(10, 'about_highlight', 'goal', '🚀', '', 'Goal-Based Learning', 'Suitable for school, college, job interview, business and daily English speaking needs.', '', '', '', 3, 'Yes', '2026-06-20 06:23:11'),
(11, 'admission_benefit', 'beginner', '✅', '', 'Beginner friendly classes', 'Start from basic sentences and daily-use speaking.', '', '', '', 1, 'Yes', '2026-06-20 06:23:11'),
(12, 'admission_benefit', 'practice', '🎤', '', 'Practical speaking practice', 'Improve confidence with role-play, correction and conversation.', '', '', '', 2, 'Yes', '2026-06-20 06:23:11'),
(13, 'admission_benefit', 'contact', '💬', '', 'Fast contact options', 'Call or WhatsApp directly for fee, timing and demo class details.', '', '', '', 3, 'Yes', '2026-06-20 06:23:11'),
(14, 'home_feature', 'conversation', '💬', '', 'Conversation Practice', 'Daily sentence speaking, question-answer and real-life conversation drills.', '', '', '', 1, 'Yes', '2026-06-21 22:09:35'),
(15, 'home_feature', 'grammar', '🧠', '', 'Grammar Made Easy', 'Learn grammar in a practical way so students can use it while speaking.', '', '', '', 2, 'Yes', '2026-06-21 22:09:35'),
(16, 'home_feature', 'confidence', '🎯', '', 'Confidence Training', 'Remove hesitation with classroom activities, presentation and correction.', '', '', '', 3, 'Yes', '2026-06-21 22:09:35'),
(17, 'home_feature', 'interview', '💼', '', 'Interview Support', 'Prepare introduction, common questions, answers and professional communication.', '', '', '', 4, 'Yes', '2026-06-21 22:09:35'),
(18, 'hero_stat', 'practice', '', '', 'Daily', 'Speaking Practice', '', '', '', 1, 'Yes', '2026-06-21 22:09:35'),
(19, 'hero_stat', 'grammar', '', '', 'Basic+', 'Grammar to Fluency', '', '', '', 2, 'Yes', '2026-06-21 22:09:35'),
(20, 'hero_stat', 'trust', '', '', 'Local', 'Trusted Institute', '', '', '', 3, 'Yes', '2026-06-21 22:09:35'),
(21, 'about_highlight', 'trust', '🏫', '', 'Local Trust', 'Designed for students of Mariahu and nearby areas who want better English communication.', '', '', '', 1, 'Yes', '2026-06-21 22:09:35'),
(22, 'about_highlight', 'teacher', '👨‍🏫', '', 'Teacher-Led Practice', 'Classroom guidance, correction and repeated speaking practice help students improve faster.', '', '', '', 2, 'Yes', '2026-06-21 22:09:35'),
(23, 'about_highlight', 'goal', '🚀', '', 'Goal-Based Learning', 'Suitable for school, college, job interview, business and daily English speaking needs.', '', '', '', 3, 'Yes', '2026-06-21 22:09:35'),
(24, 'admission_benefit', 'beginner', '✅', '', 'Beginner friendly classes', 'Start from basic sentences and daily-use speaking.', '', '', '', 1, 'Yes', '2026-06-21 22:09:35'),
(25, 'admission_benefit', 'practice', '🎤', '', 'Practical speaking practice', 'Improve confidence with role-play, correction and conversation.', '', '', '', 2, 'Yes', '2026-06-21 22:09:35'),
(26, 'admission_benefit', 'contact', '💬', '', 'Fast contact options', 'Call or WhatsApp directly for fee, timing and demo class details.', '', '', '', 3, 'Yes', '2026-06-21 22:09:35');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(160) NOT NULL,
  `short_description` text DEFAULT NULL,
  `duration` varchar(80) DEFAULT NULL,
  `level` varchar(80) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `published` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `short_description`, `duration`, `level`, `sort_order`, `published`, `created_at`) VALUES
(1, 'Basic Spoken English', 'Start from basic words, sentence formation, tense clarity and daily-use conversation.', '3 Months', 'Beginner', 1, 'Yes', '2026-06-20 06:23:11'),
(2, 'Advanced Spoken English', 'Improve fluency, confidence, pronunciation, vocabulary and natural communication.', '3 Months', 'Advanced', 2, 'Yes', '2026-06-20 06:23:11'),
(3, 'Grammar With Speaking', 'Learn grammar practically so you can use it while speaking and writing.', '2 Months', 'All Levels', 3, 'Yes', '2026-06-20 06:23:11'),
(4, 'Interview Preparation', 'Practice self-introduction, HR questions, answers and professional communication.', '1 Month', 'Job Seekers', 4, 'Yes', '2026-06-20 06:23:11'),
(5, 'Personality Development', 'Build confidence, presentation style, public speaking and professional behaviour.', '1 Month', 'Confidence', 5, 'Yes', '2026-06-20 06:23:11'),
(6, 'Student English Practice', 'Special guided English practice for school and college students.', 'Flexible', 'Students', 6, 'Yes', '2026-06-20 06:23:11'),
(7, 'Basic Spoken English', 'Start from basic words, sentence formation, tense clarity and daily-use conversation.', '3 Months', 'Beginner', 1, 'Yes', '2026-06-21 22:09:35'),
(8, 'Advanced Spoken English', 'Improve fluency, confidence, pronunciation, vocabulary and natural communication.', '3 Months', 'Advanced', 2, 'Yes', '2026-06-21 22:09:35'),
(9, 'Grammar With Speaking', 'Learn grammar practically so you can use it while speaking and writing.', '2 Months', 'All Levels', 3, 'Yes', '2026-06-21 22:09:35'),
(10, 'Interview Preparation', 'Practice self-introduction, HR questions, answers and professional communication.', '1 Month', 'Job Seekers', 4, 'Yes', '2026-06-21 22:09:35'),
(11, 'Personality Development', 'Build confidence, presentation style, public speaking and professional behaviour.', '1 Month', 'Confidence', 5, 'Yes', '2026-06-21 22:09:35'),
(12, 'Student English Practice', 'Special guided English practice for school and college students.', 'Flexible', 'Students', 6, 'Yes', '2026-06-21 22:09:35');

-- --------------------------------------------------------

--
-- Table structure for table `enquiries`
--

CREATE TABLE `enquiries` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `course_interest` varchar(160) DEFAULT NULL,
  `current_level` varchar(120) DEFAULT NULL,
  `preferred_batch` varchar(120) DEFAULT NULL,
  `lead_source` varchar(80) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `enquiry_status` varchar(40) NOT NULL DEFAULT 'New',
  `lead_priority` varchar(30) NOT NULL DEFAULT 'Normal',
  `follow_up_date` date DEFAULT NULL,
  `last_contacted_at` datetime DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `ip_address` varchar(80) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enquiries`
--

INSERT INTO `enquiries` (`id`, `name`, `phone`, `course_interest`, `current_level`, `preferred_batch`, `lead_source`, `message`, `enquiry_status`, `lead_priority`, `follow_up_date`, `last_contacted_at`, `admin_note`, `ip_address`, `created_at`, `updated_at`) VALUES
(1, 'Tranding Topic..', '89908908', 'Basic Spoken English', 'Beginner', 'Morning Speaking Batch - 7:00 AM - 8:00 AM', 'Website Admission Form', 'i have', 'New', 'Normal', NULL, NULL, '', '::1', '2026-06-21 20:04:11', '2026-06-21 20:05:02');

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` int(10) UNSIGNED NOT NULL,
  `question` varchar(220) NOT NULL,
  `answer` text NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `published` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faqs`
--

INSERT INTO `faqs` (`id`, `question`, `answer`, `sort_order`, `published`, `created_at`) VALUES
(1, 'Can beginners join the spoken English course?', 'Yes. Beginners can join. The course starts from basic sentence formation, daily-use words and confidence practice.', 1, 'Yes', '2026-06-20 06:23:11'),
(2, 'Do you provide interview preparation?', 'Yes. Interview preparation includes self-introduction, common HR questions, answer practice and confidence correction.', 2, 'Yes', '2026-06-20 06:23:11'),
(3, 'How can I know the right batch for me?', 'Submit the admission form or WhatsApp us. We will guide you based on your current level and available timing.', 3, 'Yes', '2026-06-20 06:23:11'),
(4, 'Can beginners join the spoken English course?', 'Yes. Beginners can join. The course starts from basic sentence formation, daily-use words and confidence practice.', 1, 'Yes', '2026-06-21 22:09:35'),
(5, 'Do you provide interview preparation?', 'Yes. Interview preparation includes self-introduction, common HR questions, answer practice and confidence correction.', 2, 'Yes', '2026-06-21 22:09:35'),
(6, 'How can I know the right batch for me?', 'Submit the admission form or WhatsApp us. We will guide you based on your current level and available timing.', 3, 'Yes', '2026-06-21 22:09:35');

-- --------------------------------------------------------

--
-- Table structure for table `form_options`
--

CREATE TABLE `form_options` (
  `id` int(10) UNSIGNED NOT NULL,
  `option_group` varchar(80) NOT NULL,
  `option_label` varchar(160) NOT NULL,
  `option_value` varchar(160) DEFAULT NULL,
  `helper_text` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `published` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `form_options`
--

INSERT INTO `form_options` (`id`, `option_group`, `option_label`, `option_value`, `helper_text`, `sort_order`, `published`, `created_at`) VALUES
(1, 'current_level', 'Beginner', 'Beginner', 'New learner starting from basics', 1, 'Yes', '2026-06-20 06:23:11'),
(2, 'current_level', 'Can understand but cannot speak', 'Can understand but cannot speak', 'Understands English but hesitates while speaking', 2, 'Yes', '2026-06-20 06:23:11'),
(3, 'current_level', 'Basic speaking', 'Basic speaking', 'Can speak simple English and wants fluency', 3, 'Yes', '2026-06-20 06:23:11'),
(4, 'current_level', 'Interview preparation', 'Interview preparation', 'Needs interview and professional communication practice', 4, 'Yes', '2026-06-20 06:23:11'),
(5, 'enquiry_status', 'New', 'New', '', 1, 'Yes', '2026-06-20 06:23:11'),
(6, 'enquiry_status', 'Contacted', 'Contacted', '', 2, 'Yes', '2026-06-20 06:23:11'),
(7, 'enquiry_status', 'Converted', 'Converted', '', 3, 'Yes', '2026-06-20 06:23:11'),
(8, 'enquiry_status', 'Not Interested', 'Not Interested', '', 4, 'Yes', '2026-06-20 06:23:11'),
(9, 'current_level', 'Beginner', 'Beginner', 'New learner starting from basics', 1, 'Yes', '2026-06-21 22:09:35'),
(10, 'current_level', 'Can understand but cannot speak', 'Can understand but cannot speak', 'Understands English but hesitates while speaking', 2, 'Yes', '2026-06-21 22:09:35'),
(11, 'current_level', 'Basic speaking', 'Basic speaking', 'Can speak simple English and wants fluency', 3, 'Yes', '2026-06-21 22:09:35'),
(12, 'current_level', 'Interview preparation', 'Interview preparation', 'Needs interview and professional communication practice', 4, 'Yes', '2026-06-21 22:09:35'),
(13, 'enquiry_status', 'New', 'New', '', 1, 'Yes', '2026-06-21 22:09:35'),
(14, 'enquiry_status', 'Contacted', 'Contacted', '', 2, 'Yes', '2026-06-21 22:09:35'),
(15, 'enquiry_status', 'Converted', 'Converted', '', 3, 'Yes', '2026-06-21 22:09:35'),
(16, 'enquiry_status', 'Not Interested', 'Not Interested', '', 4, 'Yes', '2026-06-21 22:09:35');

-- --------------------------------------------------------

--
-- Table structure for table `gallery_images`
--

CREATE TABLE `gallery_images` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(160) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `image_alt` varchar(180) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `published` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery_images`
--

INSERT INTO `gallery_images` (`id`, `title`, `category`, `image_url`, `image_alt`, `description`, `sort_order`, `published`, `created_at`) VALUES
(1, 'Classroom Speaking Practice', 'Classroom', '', 'Students practicing spoken English in classroom', 'Students practicing daily spoken English in a guided class environment.', 1, 'Yes', '2026-06-20 06:23:11'),
(2, 'Interview Confidence Session', 'Activity', '', 'Interview practice session for spoken English students', 'Role-play and self-introduction practice for job seekers and students.', 2, 'Yes', '2026-06-20 06:23:11'),
(3, 'Grammar With Speaking Batch', 'Learning', '', 'Grammar with speaking batch activity', 'Practical grammar lessons connected with real spoken English usage.', 3, 'Yes', '2026-06-20 06:23:11'),
(4, 'Classroom Speaking Practice', 'Classroom', '', 'Students practicing spoken English in classroom', 'Students practicing daily spoken English in a guided class environment.', 1, 'Yes', '2026-06-21 22:09:35'),
(5, 'Interview Confidence Session', 'Activity', '', 'Interview practice session for spoken English students', 'Role-play and self-introduction practice for job seekers and students.', 2, 'Yes', '2026-06-21 22:09:35'),
(6, 'Grammar With Speaking Batch', 'Learning', '', 'Grammar with speaking batch activity', 'Practical grammar lessons connected with real spoken English usage.', 3, 'Yes', '2026-06-21 22:09:35'),
(8, 'testing pic', 'Spoken', 'assets/uploads/gallery/gallery-20260621-200631-a4ba4e65.jpg', 'Student practising spoken English .....Student practising spoken English', 'Student practising spoken EnglishStudent practising spoken EnglishStudent practising spoken English', 0, 'Yes', '2026-06-21 23:36:31');

-- --------------------------------------------------------

--
-- Table structure for table `hero_banners`
--

CREATE TABLE `hero_banners` (
  `id` int(10) UNSIGNED NOT NULL,
  `page_key` varchar(80) NOT NULL DEFAULT 'home',
  `eyebrow` varchar(160) DEFAULT NULL,
  `title` varchar(220) NOT NULL,
  `subtitle` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `desktop_image_url` varchar(500) DEFAULT NULL,
  `mobile_image_url` varchar(500) DEFAULT NULL,
  `image_alt` varchar(180) DEFAULT NULL,
  `show_content` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `content_position` enum('left','center','right') NOT NULL DEFAULT 'left',
  `overlay_strength` tinyint unsigned NOT NULL DEFAULT '58',
  `badge_one` varchar(120) DEFAULT NULL,
  `badge_two` varchar(120) DEFAULT NULL,
  `stat_one_label` varchar(120) DEFAULT NULL,
  `stat_one_value` varchar(120) DEFAULT NULL,
  `stat_two_label` varchar(120) DEFAULT NULL,
  `stat_two_value` varchar(120) DEFAULT NULL,
  `primary_text` varchar(120) DEFAULT NULL,
  `primary_url` varchar(255) DEFAULT NULL,
  `secondary_text` varchar(120) DEFAULT NULL,
  `secondary_url` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `published` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hero_banners`
--

INSERT INTO `hero_banners` (`id`, `page_key`, `eyebrow`, `title`, `subtitle`, `image_url`, `image_alt`, `badge_one`, `badge_two`, `stat_one_label`, `stat_one_value`, `stat_two_label`, `stat_two_value`, `primary_text`, `primary_url`, `secondary_text`, `secondary_url`, `sort_order`, `published`, `created_at`) VALUES
(1, 'home', 'Free Counselling Open', 'Speak English confidently in daily life', 'A premium, admin-managed hero banner area. Upload institute photos or keep the elegant fallback visual.', 'assets/uploads/banners/banner-20260622-080529-a9a3ce6f.png', 'Student practising spoken English', '🎤 Speak Daily', '📚 Easy to Advanced', 'Daily Practice', 'Yes', 'Interview Support', 'Included', 'Book Free Counselling', 'admission.php', 'Practice Room', 'spoken-materials.php', 1, 'Yes', '2026-06-20 06:23:11'),
(2, 'practice', 'Practice Room', 'Spoken English Practice Room', 'Practise tenses, daily situations, sentence correction and speaking confidence without login.', '', 'English practice lab', 'Free + Safe', 'Works Without API', 'Tense Practice', 'Free', 'Voice Input', 'Browser', 'Start Practice', '#practice-lessons', 'Book Free Demo', 'admission.php', 1, 'Yes', '2026-06-20 06:23:11'),
(3, 'home', '', 'Hero', '', 'assets/uploads/banners/banner-20260620-025947-3f0dbc56.jpg', '', '', '', '', '', '', '', '', '', '', '', 0, 'No', '2026-06-20 06:29:47');

-- --------------------------------------------------------

--
-- Table structure for table `material_assets`
--

CREATE TABLE `material_assets` (
  `id` int(10) UNSIGNED NOT NULL,
  `collection_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `title` varchar(180) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `file_type` varchar(40) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `published` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `status_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `material_assets`
--

INSERT INTO `material_assets` (`id`, `collection_id`, `title`, `file_path`, `original_name`, `file_type`, `notes`, `sort_order`, `published`, `status_deleted`, `created_at`) VALUES
(1, 1, 'Spoken Note Image 1', 'assets/uploads/materials/notes/note_01.jpeg', 'note_01.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 1, 'Yes', 0, '2026-06-21 22:28:52'),
(2, 1, 'Spoken Note Image 2', 'assets/uploads/materials/notes/note_02.jpeg', 'note_02.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 2, 'Yes', 0, '2026-06-21 22:28:52'),
(3, 1, 'Spoken Note Image 3', 'assets/uploads/materials/notes/note_03.jpeg', 'note_03.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 3, 'Yes', 0, '2026-06-21 22:28:52'),
(4, 1, 'Spoken Note Image 4', 'assets/uploads/materials/notes/note_04.jpeg', 'note_04.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 4, 'Yes', 0, '2026-06-21 22:28:52'),
(5, 1, 'Spoken Note Image 5', 'assets/uploads/materials/notes/note_05.jpeg', 'note_05.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 5, 'Yes', 0, '2026-06-21 22:28:52'),
(6, 1, 'Spoken Note Image 6', 'assets/uploads/materials/notes/note_06.jpeg', 'note_06.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 6, 'Yes', 0, '2026-06-21 22:28:52'),
(7, 1, 'Spoken Note Image 7', 'assets/uploads/materials/notes/note_07.jpeg', 'note_07.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 7, 'Yes', 0, '2026-06-21 22:28:52'),
(8, 1, 'Spoken Note Image 8', 'assets/uploads/materials/notes/note_08.jpeg', 'note_08.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 8, 'Yes', 0, '2026-06-21 22:28:52'),
(9, 1, 'Spoken Note Image 9', 'assets/uploads/materials/notes/note_09.jpeg', 'note_09.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 9, 'Yes', 0, '2026-06-21 22:28:52'),
(10, 1, 'Spoken Note Image 10', 'assets/uploads/materials/notes/note_10.jpeg', 'note_10.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 10, 'Yes', 0, '2026-06-21 22:28:52'),
(11, 1, 'Spoken Note Image 11', 'assets/uploads/materials/notes/note_11.jpeg', 'note_11.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 11, 'Yes', 0, '2026-06-21 22:28:52'),
(12, 1, 'Spoken Note Image 12', 'assets/uploads/materials/notes/note_12.jpeg', 'note_12.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 12, 'Yes', 0, '2026-06-21 22:28:52'),
(13, 1, 'Spoken Note Image 13', 'assets/uploads/materials/notes/note_13.jpeg', 'note_13.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 13, 'Yes', 0, '2026-06-21 22:28:52'),
(14, 1, 'Spoken Note Image 14', 'assets/uploads/materials/notes/note_14.jpeg', 'note_14.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 14, 'Yes', 0, '2026-06-21 22:28:52'),
(15, 1, 'Spoken Note Image 15', 'assets/uploads/materials/notes/note_15.jpeg', 'note_15.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 15, 'Yes', 0, '2026-06-21 22:28:52'),
(16, 1, 'Spoken Note Image 16', 'assets/uploads/materials/notes/note_16.jpeg', 'note_16.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 16, 'Yes', 0, '2026-06-21 22:28:52'),
(17, 1, 'Spoken Note Image 17', 'assets/uploads/materials/notes/note_17.jpeg', 'note_17.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 17, 'Yes', 0, '2026-06-21 22:28:52'),
(18, 1, 'Spoken Note Image 18', 'assets/uploads/materials/notes/note_18.jpeg', 'note_18.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 18, 'Yes', 0, '2026-06-21 22:28:52'),
(19, 1, 'Spoken Note Image 19', 'assets/uploads/materials/notes/note_19.jpeg', 'note_19.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 19, 'Yes', 0, '2026-06-21 22:28:52'),
(20, 1, 'Spoken Note Image 20', 'assets/uploads/materials/notes/note_20.jpeg', 'note_20.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 20, 'Yes', 0, '2026-06-21 22:28:52'),
(21, 1, 'Spoken Note Image 21', 'assets/uploads/materials/notes/note_21.jpeg', 'note_21.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 21, 'Yes', 0, '2026-06-21 22:28:52'),
(22, 1, 'Spoken Note Image 22', 'assets/uploads/materials/notes/note_22.jpeg', 'note_22.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 22, 'Yes', 0, '2026-06-21 22:28:52'),
(23, 1, 'Spoken Note Image 23', 'assets/uploads/materials/notes/note_23.jpeg', 'note_23.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 23, 'Yes', 0, '2026-06-21 22:28:52'),
(24, 1, 'Spoken Note Image 24', 'assets/uploads/materials/notes/note_24.jpeg', 'note_24.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 24, 'Yes', 0, '2026-06-21 22:28:52'),
(25, 1, 'Spoken Note Image 25', 'assets/uploads/materials/notes/note_25.jpeg', 'note_25.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 25, 'Yes', 0, '2026-06-21 22:28:52'),
(26, 1, 'Spoken Note Image 26', 'assets/uploads/materials/notes/note_26.jpeg', 'note_26.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 26, 'Yes', 0, '2026-06-21 22:28:52'),
(27, 1, 'Spoken Note Image 27', 'assets/uploads/materials/notes/note_27.jpeg', 'note_27.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 27, 'Yes', 0, '2026-06-21 22:28:52'),
(28, 1, 'Spoken Note Image 28', 'assets/uploads/materials/notes/note_28.jpeg', 'note_28.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 28, 'Yes', 0, '2026-06-21 22:28:52'),
(29, 1, 'Spoken Note Image 29', 'assets/uploads/materials/notes/note_29.jpeg', 'note_29.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 29, 'Yes', 0, '2026-06-21 22:28:52'),
(30, 1, 'Spoken Note Image 30', 'assets/uploads/materials/notes/note_30.jpeg', 'note_30.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 30, 'Yes', 0, '2026-06-21 22:28:52'),
(31, 1, 'Spoken Note Image 31', 'assets/uploads/materials/notes/note_31.jpeg', 'note_31.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 31, 'Yes', 0, '2026-06-21 22:28:52'),
(32, 1, 'Spoken Note Image 32', 'assets/uploads/materials/notes/note_32.jpeg', 'note_32.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 32, 'Yes', 0, '2026-06-21 22:28:52'),
(33, 1, 'Spoken Note Image 33', 'assets/uploads/materials/notes/note_33.jpeg', 'note_33.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 33, 'Yes', 0, '2026-06-21 22:28:52'),
(34, 1, 'Spoken Note Image 34', 'assets/uploads/materials/notes/note_34.jpeg', 'note_34.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 34, 'Yes', 0, '2026-06-21 22:28:52'),
(35, 1, 'Spoken Note Image 35', 'assets/uploads/materials/notes/note_35.jpeg', 'note_35.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 35, 'Yes', 0, '2026-06-21 22:28:52'),
(36, 1, 'Spoken Note Image 36', 'assets/uploads/materials/notes/note_36.jpeg', 'note_36.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 36, 'Yes', 0, '2026-06-21 22:28:52'),
(37, 1, 'Spoken Note Image 37', 'assets/uploads/materials/notes/note_37.jpeg', 'note_37.jpeg', 'image', 'Uploaded spoken-English note image. Convert important lines into sentence pairs from admin.', 37, 'Yes', 0, '2026-06-21 22:28:52');

-- --------------------------------------------------------

--
-- Table structure for table `material_collections`
--

CREATE TABLE `material_collections` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(180) NOT NULL,
  `slug` varchar(180) DEFAULT NULL,
  `category` varchar(120) DEFAULT NULL,
  `level` varchar(80) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `cover_image` varchar(500) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `published` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `status_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `material_collections`
--

INSERT INTO `material_collections` (`id`, `title`, `slug`, `category`, `level`, `description`, `cover_image`, `sort_order`, `published`, `status_deleted`, `created_at`) VALUES
(1, 'Uploaded Spoken Notes', 'uploaded-spoken-notes', 'Notes', 'Beginner to Advanced', 'Your uploaded spoken English WhatsApp note images are stored here. Admin can add more images and convert important lines into Hindi-English practice pairs.', '', 1, 'Yes', 0, '2026-06-20 06:23:12'),
(2, 'Daily Hindi to English Sentences', 'daily-hindi-to-english', 'Translation Practice', 'Beginner', 'Daily-use Hindi sentences with simple spoken English answers for regular practice.', '', 2, 'Yes', 0, '2026-06-20 06:23:12'),
(3, 'Testing Hindi-English Practice Data', 'testing-hindi-english-practice-data', 'Translation Practice', 'Beginner to Intermediate', 'Ready-made testing sentences for Hindi to English and English to Hindi practice.', '', 3, 'Yes', 0, '2026-06-20 06:38:23');

-- --------------------------------------------------------

--
-- Table structure for table `material_practice_attempts`
--

CREATE TABLE `material_practice_attempts` (
  `id` int(10) UNSIGNED NOT NULL,
  `session_id` varchar(120) NOT NULL,
  `pair_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `practice_direction` varchar(40) NOT NULL DEFAULT 'hindi_to_english',
  `user_answer` text DEFAULT NULL,
  `correct_answer` text DEFAULT NULL,
  `score` int(11) NOT NULL DEFAULT 0,
  `feedback` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `match_type` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `material_practice_attempts`
--

INSERT INTO `material_practice_attempts` (`id`, `session_id`, `pair_id`, `practice_direction`, `user_answer`, `correct_answer`, `score`, `feedback`, `created_at`, `is_correct`, `match_type`) VALUES
(1, '7aqh6dddrd138837r28mraegch', 1, 'hindi_to_english', 'he cj', 'I try to speak English every day.', 0, 'Keep practising. Read the correct answer and say it loudly three times.', '2026-06-20 06:36:33', 0, NULL),
(2, '7aqh6dddrd138837r28mraegch', 1, 'hindi_to_english', 'he cj', 'I try to speak English every day.', 0, 'Keep practising. Read the correct answer and say it loudly three times.', '2026-06-20 06:38:34', 0, NULL),
(3, '7aqh6dddrd138837r28mraegch', 1, 'hindi_to_english', 'i try to speAK ENGLISH EVERY DAY', 'I try to speak English every day.', 10, 'Excellent. Your answer matches the expected sentence.', '2026-06-20 06:39:21', 0, NULL),
(4, '7aqh6dddrd138837r28mraegch', 9, 'hindi_to_english', 'i speak english every day', 'I speak English every day.', 10, 'Excellent. Your answer matches the expected sentence.', '2026-06-20 22:55:43', 0, NULL),
(5, '7aqh6dddrd138837r28mraegch', 1, 'hindi_to_english', 'nmb', 'I try to speak English every day.', 0, 'Keep practising. Read the correct answer and say it loudly three times.', '2026-06-20 22:55:57', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `material_settings`
--

CREATE TABLE `material_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `material_settings`
--

INSERT INTO `material_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'material_library_enabled', 'Yes', '2026-06-20 06:23:12'),
(2, 'material_public_title', 'Spoken English Material & Hindi-English Practice', '2026-06-20 06:23:12'),
(3, 'material_public_subtitle', 'Learn from notes, practise Hindi to English and English to Hindi, and improve sentence making daily.', '2026-06-20 06:23:12'),
(4, 'material_upload_max_note', 'Recommended: upload notes in small batches. Images/PDF/TXT are supported; CSV/text import is best for very big sentence data.', '2026-06-20 06:23:12'),
(5, 'material_daily_practice_limit', '50', '2026-06-20 06:23:12'),
(1196, 'auto_translate_enabled', 'No', '2026-06-21 22:28:52'),
(1197, 'auto_translate_provider', 'none', '2026-06-21 22:28:52'),
(1198, 'auto_translate_note', 'Use teacher-approved material first. External translation requires a legal provider/API key and is optional.', '2026-06-21 22:28:52');

-- --------------------------------------------------------

--
-- Table structure for table `material_units`
--

CREATE TABLE `material_units` (
  `id` int(10) UNSIGNED NOT NULL,
  `collection_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `title` varchar(180) NOT NULL,
  `unit_type` varchar(80) NOT NULL DEFAULT 'lesson',
  `tense_name` varchar(120) DEFAULT NULL,
  `level` varchar(80) DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `published` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `status_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `material_units`
--

INSERT INTO `material_units` (`id`, `collection_id`, `title`, `unit_type`, `tense_name`, `level`, `instructions`, `sort_order`, `published`, `status_deleted`, `created_at`) VALUES
(1, 2, 'Daily Life Sentences', 'translation', 'Mixed Tenses', 'Beginner', 'Read Hindi, speak/write English, then compare with the natural answer.', 1, 'Yes', 0, '2026-06-20 06:32:10'),
(2, 3, 'Daily Use Sentences - Testing Set', 'translation', 'Mixed', 'Beginner', 'Practise these lines both Hindi to English and English to Hindi.', 1, 'Yes', 0, '2026-06-20 06:38:23');

-- --------------------------------------------------------

--
-- Table structure for table `nav_menus`
--

CREATE TABLE `nav_menus` (
  `id` int(10) UNSIGNED NOT NULL,
  `menu_area` varchar(40) NOT NULL DEFAULT 'header',
  `label` varchar(120) NOT NULL,
  `url` varchar(255) NOT NULL,
  `is_cta` enum('Yes','No') NOT NULL DEFAULT 'No',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `published` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nav_menus`
--

INSERT INTO `nav_menus` (`id`, `menu_area`, `label`, `url`, `is_cta`, `sort_order`, `published`, `created_at`) VALUES
(1, 'header', 'Home', 'index.php', 'No', 1, 'Yes', '2026-06-20 06:23:11'),
(2, 'header', 'About', 'about.php', 'No', 2, 'Yes', '2026-06-20 06:23:11'),
(3, 'header', 'Courses', 'courses.php', 'No', 3, 'Yes', '2026-06-20 06:23:11'),
(4, 'header', 'Gallery', 'gallery.php', 'No', 4, 'Yes', '2026-06-20 06:23:11'),
(5, 'header', 'Reviews', 'reviews.php', 'No', 5, 'Yes', '2026-06-20 06:23:11'),
(6, 'header', 'Contact', 'contact.php', 'No', 6, 'Yes', '2026-06-20 06:23:11'),
(7, 'header', 'Admission', 'admission.php', 'Yes', 7, 'Yes', '2026-06-20 06:23:11'),
(8, 'footer', 'Courses', 'courses.php', 'No', 1, 'Yes', '2026-06-20 06:23:11'),
(9, 'footer', 'Gallery', 'gallery.php', 'No', 2, 'Yes', '2026-06-20 06:23:11'),
(10, 'footer', 'Reviews', 'reviews.php', 'No', 3, 'Yes', '2026-06-20 06:23:11'),
(11, 'footer', 'Admission', 'admission.php', 'No', 4, 'Yes', '2026-06-20 06:23:11'),
(12, 'header', 'Practice Room', 'spoken-materials.php', 'No', 7, 'Yes', '2026-06-20 06:38:23'),
(13, 'header', 'Study Practice', 'spoken-materials.php', 'No', 8, 'Yes', '2026-06-20 06:38:23'),
(14, 'footer', 'Practice Room', 'spoken-materials.php', 'No', 5, 'Yes', '2026-06-20 06:38:23'),
(15, 'footer', 'Study Materials', 'spoken-materials.php', 'No', 6, 'Yes', '2026-06-20 06:38:23');

-- --------------------------------------------------------

--
-- Table structure for table `practice_ai_logs`
--

CREATE TABLE `practice_ai_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `session_id` varchar(120) NOT NULL,
  `question_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `provider` varchar(60) DEFAULT NULL,
  `model` varchar(120) DEFAULT NULL,
  `request_type` varchar(80) DEFAULT NULL,
  `prompt_chars` int(11) NOT NULL DEFAULT 0,
  `response_chars` int(11) NOT NULL DEFAULT 0,
  `status` varchar(40) NOT NULL DEFAULT 'skipped',
  `error_message` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `practice_ai_logs`
--

INSERT INTO `practice_ai_logs` (`id`, `session_id`, `question_id`, `provider`, `model`, `request_type`, `prompt_chars`, `response_chars`, `status`, `error_message`, `created_at`) VALUES
(1, '7aqh6dddrd138837r28mraegch', 16, 'openai', 'gpt-4o-mini', 'practice_feedback', 0, 0, 'skipped', 'AI disabled, API key missing, or daily limit reached.', '2026-06-20 06:40:26'),
(2, '7aqh6dddrd138837r28mraegch', 16, 'openai', 'gpt-4o-mini', 'practice_feedback', 0, 0, 'skipped', 'AI disabled, API key missing, or daily limit reached.', '2026-06-20 06:47:22'),
(3, '7aqh6dddrd138837r28mraegch', 16, 'openai', 'gpt-4o-mini', 'practice_feedback', 0, 0, 'skipped', 'AI disabled, API key missing, or daily limit reached.', '2026-06-20 06:50:00'),
(4, '7aqh6dddrd138837r28mraegch', 9, 'openai', 'gpt-4o-mini', 'practice_feedback', 0, 0, 'skipped', 'AI disabled, API key missing, or daily limit reached.', '2026-06-20 22:32:35'),
(5, '7aqh6dddrd138837r28mraegch', 1, 'openai', 'gpt-4o-mini', 'practice_feedback', 0, 0, 'skipped', 'AI disabled, API key missing, or daily limit reached.', '2026-06-20 23:00:54'),
(6, '7aqh6dddrd138837r28mraegch', 2, 'openai', 'gpt-4o-mini', 'practice_feedback', 0, 0, 'skipped', 'AI disabled, API key missing, or daily limit reached.', '2026-06-20 23:02:28'),
(7, '7aqh6dddrd138837r28mraegch', 3, 'openai', 'gpt-4o-mini', 'practice_feedback', 0, 0, 'skipped', 'AI disabled, API key missing, or daily limit reached.', '2026-06-20 23:05:28'),
(8, '7aqh6dddrd138837r28mraegch', 3, 'openai', 'gpt-4o-mini', 'practice_feedback', 0, 0, 'skipped', 'AI disabled, API key missing, or daily limit reached.', '2026-06-20 23:05:54'),
(9, '7aqh6dddrd138837r28mraegch', 1, 'openai', 'gpt-4o-mini', 'practice_feedback', 0, 0, 'skipped', 'AI disabled, API key missing, or daily limit reached.', '2026-06-21 22:32:39'),
(10, '7aqh6dddrd138837r28mraegch', 26, 'openai', 'gpt-4o-mini', 'practice_feedback', 0, 0, 'skipped', 'AI disabled, API key missing, or daily limit reached.', '2026-06-21 22:33:25'),
(11, '7aqh6dddrd138837r28mraegch', 1, 'openai', 'gpt-4o-mini', 'practice_feedback', 0, 0, 'skipped', 'AI disabled, API key missing, or daily limit reached.', '2026-06-22 11:38:48');

-- --------------------------------------------------------

--
-- Table structure for table `practice_attempts`
--

CREATE TABLE `practice_attempts` (
  `id` int(10) UNSIGNED NOT NULL,
  `session_id` varchar(120) NOT NULL,
  `student_name` varchar(160) DEFAULT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `question_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `user_answer` text DEFAULT NULL,
  `correct_answer` text DEFAULT NULL,
  `score` int(11) NOT NULL DEFAULT 0,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `match_type` varchar(80) DEFAULT NULL,
  `local_feedback` text DEFAULT NULL,
  `suggested_next_step` varchar(220) DEFAULT NULL,
  `ai_feedback` text DEFAULT NULL,
  `ai_status` varchar(40) DEFAULT NULL,
  `ai_model` varchar(120) DEFAULT NULL,
  `corrected_answer` text DEFAULT NULL,
  `natural_answer` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `practice_attempts`
--

INSERT INTO `practice_attempts` (`id`, `session_id`, `student_name`, `phone`, `question_id`, `user_answer`, `correct_answer`, `score`, `is_correct`, `match_type`, `local_feedback`, `suggested_next_step`, `ai_feedback`, `ai_status`, `ai_model`, `corrected_answer`, `natural_answer`, `created_at`) VALUES
(1, '7aqh6dddrd138837r28mraegch', NULL, NULL, 16, 'hello', 'I want to improve my English.', 0, 0, NULL, 'Good try. Compare your answer with the correct version and practise again.', 'Rewrite the correct sentence and say it loudly three times.', '', 'off', '', 'I want to improve my English.', 'I want to improve my English.', '2026-06-20 06:40:26'),
(2, '7aqh6dddrd138837r28mraegch', NULL, NULL, 16, 'hello', 'I want to improve my English.', 0, 0, NULL, 'Good try. Compare your answer with the correct version and practise again.', 'Rewrite the correct sentence and say it loudly three times.', '', 'off', '', 'I want to improve my English.', 'I want to improve my English.', '2026-06-20 06:47:22'),
(3, '7aqh6dddrd138837r28mraegch', NULL, NULL, 16, 'I want to improve my English', 'I want to improve my English.', 10, 0, NULL, 'Correct. Good job. Repeat it loudly three times for speaking confidence.', 'Try the next question or practise this sentence by speaking.', '', 'off', '', 'I want to improve my English.', 'I want to improve my English.', '2026-06-20 06:50:00'),
(4, '7aqh6dddrd138837r28mraegch', NULL, NULL, 9, 'sockets right there have', 'Sorry, teacher. I am late because of traffic. It will not happen again.', 5, 0, NULL, 'Your answer has effort. Now make it more natural using the sample answer.', 'Rewrite the correct sentence and say it loudly three times.', '', 'off', '', 'Sorry, teacher. I am late because of traffic. It will not happen again.', 'Sorry, teacher. I am late because of traffic. It will not happen again.', '2026-06-20 22:32:35'),
(5, '7aqh6dddrd138837r28mraegch', NULL, NULL, 1, 'speak', 'I speak English every day.', 10, 0, NULL, 'Correct. Good job. Repeat it loudly three times for speaking confidence.', 'Try the next question or practise this sentence by speaking.', '', 'off', '', 'I speak English every day.', 'I speak English every day.', '2026-06-20 23:00:54'),
(6, '7aqh6dddrd138837r28mraegch', NULL, NULL, 2, 'drink', 'She drinks tea in the morning.', 0, 0, NULL, 'Good try. Compare your answer with the correct version and practise again.', 'Rewrite the correct sentence and say it loudly three times.', '', 'off', '', 'She drinks tea in the morning.', 'She drinks tea in the morning.', '2026-06-20 23:02:28'),
(7, '7aqh6dddrd138837r28mraegch', NULL, NULL, 3, 'I goes to school', 'I go to school every day.', 0, 0, NULL, 'Good try. Compare your answer with the correct version and practise again.', 'Rewrite the correct sentence and say it loudly three times.', '', 'off', '', 'I go to school every day.', 'I go to school every day.', '2026-06-20 23:05:28'),
(8, '7aqh6dddrd138837r28mraegch', NULL, NULL, 3, 'I go to school every day', 'I go to school every day.', 10, 0, NULL, 'Correct. Good job. Repeat it loudly three times for speaking confidence.', 'Try the next question or practise this sentence by speaking.', '', 'off', '', 'I go to school every day.', 'I go to school every day.', '2026-06-20 23:05:54'),
(9, '7aqh6dddrd138837r28mraegch', NULL, NULL, 1, 'speaks', 'I speak English every day.', 9, 1, 'smart_close_match', 'Correct. Good job. This answer matches the teacher-approved answer set.', 'Try the next question or speak this sentence three times.', '', 'off', '', 'I speak English every day.', 'I speak English every day.', '2026-06-21 22:32:40'),
(10, '7aqh6dddrd138837r28mraegch', NULL, NULL, 26, 'I want to improve my English speaking confidence', 'I want to improve my English speaking confidence.', 10, 1, 'exact_or_accepted', 'Correct. Good job. This answer matches the teacher-approved answer set.', 'Try the next question or speak this sentence three times.', '', 'off', '', 'I want to improve my English speaking confidence.', 'I want to improve my English speaking confidence.', '2026-06-21 22:33:26'),
(11, '7aqh6dddrd138837r28mraegch', NULL, NULL, 1, 'speak', 'I speak English every day.', 10, 1, 'exact_or_accepted', 'Correct. Good job. This answer matches the teacher-approved answer set.', 'Try the next question or speak this sentence three times.', '', 'off', '', 'I speak English every day.', 'I speak English every day.', '2026-06-22 11:38:48');

-- --------------------------------------------------------

--
-- Table structure for table `practice_categories`
--

CREATE TABLE `practice_categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_name` varchar(160) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(40) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `published` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `status_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `practice_categories`
--

INSERT INTO `practice_categories` (`id`, `category_name`, `slug`, `description`, `icon`, `sort_order`, `published`, `status_deleted`, `created_at`) VALUES
(1, 'Tense Practice', 'tense-practice', 'Practise English tenses with fill blanks, sentence making and Hindi-English examples.', '⏱️', 1, 'Yes', 0, '2026-06-20 06:38:23'),
(2, 'Situation Practice', 'situation-practice', 'Learn what to say in real-life situations like class, market, phone call and office.', '💬', 2, 'Yes', 0, '2026-06-20 06:38:23'),
(3, 'Sentence Correction', 'sentence-correction', 'Fix common grammar mistakes and speak more naturally.', '✅', 3, 'Yes', 0, '2026-06-20 06:38:23'),
(4, 'Interview English', 'interview-english', 'Practise self introduction, strengths, goals and interview answers.', '🎯', 4, 'Yes', 0, '2026-06-20 06:38:23'),
(5, 'Voice Speaking', 'voice-speaking', 'Speak answers using browser voice input and practise pronunciation confidence.', '🎤', 5, 'Yes', 0, '2026-06-20 06:38:23'),
(6, 'Voice Practice', 'voice-practice', 'Speak using browser voice input and compare your spoken sentence with the correct answer.', '🎤', 4, 'Yes', 0, '2026-06-21 22:28:51');

-- --------------------------------------------------------

--
-- Table structure for table `practice_common_mistakes`
--

CREATE TABLE `practice_common_mistakes` (
  `id` int(10) UNSIGNED NOT NULL,
  `wrong_pattern` varchar(220) NOT NULL,
  `correct_pattern` varchar(220) NOT NULL,
  `explanation` text DEFAULT NULL,
  `example_sentence` text DEFAULT NULL,
  `published` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `status_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `practice_common_mistakes`
--

INSERT INTO `practice_common_mistakes` (`id`, `wrong_pattern`, `correct_pattern`, `explanation`, `example_sentence`, `published`, `status_deleted`, `created_at`) VALUES
(1, 'I am go', 'I am going', 'Use am + verb-ing for present continuous.', 'I am going to the market now.', 'Yes', 0, '2026-06-20 06:38:23'),
(2, 'I am go to market yesterday', 'I went to the market yesterday', 'For yesterday, use Past Simple: went.', 'I went to the market yesterday.', 'Yes', 0, '2026-06-20 06:38:23'),
(3, 'He go', 'He goes', 'With He/She/It in Present Simple, add s/es.', 'He goes to school every day.', 'Yes', 0, '2026-06-20 06:38:23'),
(4, 'I has', 'I have', 'Use have with I/You/We/They.', 'I have a book.', 'Yes', 0, '2026-06-20 06:38:23'),
(5, 'did not went', 'did not go', 'After did/did not, use base verb.', 'I did not go there.', 'Yes', 0, '2026-06-20 06:38:23'),
(6, 'more better', 'better', 'Do not use more with better.', 'This answer is better.', 'Yes', 0, '2026-06-20 06:38:23');

-- --------------------------------------------------------

--
-- Table structure for table `practice_lessons`
--

CREATE TABLE `practice_lessons` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `lesson_title` varchar(180) NOT NULL,
  `lesson_type` varchar(80) NOT NULL DEFAULT 'tense',
  `level` varchar(80) DEFAULT NULL,
  `tense_name` varchar(120) DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `published` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `status_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `practice_lessons`
--

INSERT INTO `practice_lessons` (`id`, `category_id`, `lesson_title`, `lesson_type`, `level`, `tense_name`, `short_description`, `instructions`, `sort_order`, `published`, `status_deleted`, `created_at`) VALUES
(1, 1, 'Present Simple - Daily Habits', 'tense', 'Beginner', 'Present Simple', 'Practise habits and daily routine sentences.', 'Use base verb with I/You/We/They. Use verb+s/es with He/She/It.', 1, 'Yes', 0, '2026-06-20 06:38:23'),
(2, 1, 'Present Continuous - Right Now', 'tense', 'Beginner', 'Present Continuous', 'Practise actions happening now.', 'Use am/is/are + verb ing for actions happening now.', 2, 'Yes', 0, '2026-06-20 06:38:23'),
(3, 1, 'Past Simple - Yesterday Actions', 'tense', 'Beginner', 'Past Simple', 'Practise completed past actions.', 'Use verb 2 for positive past sentences. Use did not + base verb for negative.', 3, 'Yes', 0, '2026-06-20 06:38:23'),
(4, 1, 'Future Simple - Plans', 'tense', 'Beginner', 'Future Simple', 'Practise future plans and promises.', 'Use will + base verb for simple future actions.', 4, 'Yes', 0, '2026-06-20 06:38:23'),
(5, 2, 'Daily Speaking Situations', 'situation', 'Beginner', NULL, 'Practise common real-life spoken English lines.', 'Write a polite and natural answer. Compare with the sample answer and repeat loudly.', 5, 'Yes', 0, '2026-06-20 06:38:23'),
(6, 4, 'Interview Self Introduction', 'situation', 'Intermediate', NULL, 'Practise simple interview answers.', 'Keep answers clear, short and confident. Do not memorize too much.', 6, 'Yes', 0, '2026-06-20 06:38:23'),
(7, 3, 'Common Grammar Mistake Correction', 'correction', 'Beginner', NULL, 'Correct common Indian learner mistakes.', 'Type the corrected sentence. Then say it loudly three times.', 7, 'Yes', 0, '2026-06-20 06:38:23'),
(8, 5, 'Speak and Check Basic Answers', 'voice', 'Beginner', NULL, 'Use voice input to practise speaking simple English answers.', 'Click Start Speaking, say the answer clearly, then check it.', 8, 'Yes', 0, '2026-06-20 06:38:23'),
(9, 1, 'Present Simple Practice', 'tense', 'Beginner', 'Present Simple', 'Practise daily routine sentences using base verb and s/es.', 'Use base verb with I/You/We/They. Use s/es with He/She/It.', 1, 'Yes', 0, '2026-06-21 22:28:51'),
(10, 1, 'Past Simple Practice', 'tense', 'Beginner', 'Past Simple', 'Practise completed actions using verb second form.', 'Use V2 for positive past sentences and did + base verb for negative/questions.', 2, 'Yes', 0, '2026-06-21 22:28:51'),
(11, 1, 'Present Continuous Practice', 'tense', 'Beginner', 'Present Continuous', 'Practise actions happening now using am/is/are + verb-ing.', 'Use am/is/are + verb-ing for current actions.', 3, 'Yes', 0, '2026-06-21 22:28:51'),
(12, 2, 'Daily Life Situations', 'situation', 'Beginner', '', 'Practise simple answers for real daily speaking situations.', 'Write a natural answer for the situation. Focus on clear and polite English.', 1, 'Yes', 0, '2026-06-21 22:28:51'),
(13, 2, 'Interview Speaking', 'situation', 'Intermediate', '', 'Practise interview answers such as self introduction and strengths.', 'Write a short confident answer. Keep it natural and professional.', 2, 'Yes', 0, '2026-06-21 22:28:51'),
(14, 3, 'Correct My Sentence', 'correction', 'All Levels', '', 'Type or practise incorrect sentences and learn the correct version.', 'Compare your answer with the corrected sample and explanation.', 1, 'Yes', 0, '2026-06-21 22:28:51'),
(15, 6, 'Speak and Compare', 'voice', 'All Levels', '', 'Use browser voice typing to practise English pronunciation and sentence flow.', 'Click Start Speaking, say your answer, then compare it with the correct answer.', 1, 'Yes', 0, '2026-06-21 22:28:51');

-- --------------------------------------------------------

--
-- Table structure for table `practice_questions`
--

CREATE TABLE `practice_questions` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `lesson_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `question_type` varchar(60) NOT NULL DEFAULT 'fill_blank',
  `question_text` text NOT NULL,
  `option_a` varchar(255) DEFAULT NULL,
  `option_b` varchar(255) DEFAULT NULL,
  `option_c` varchar(255) DEFAULT NULL,
  `option_d` varchar(255) DEFAULT NULL,
  `correct_answer` text DEFAULT NULL,
  `sample_answer` text DEFAULT NULL,
  `accepted_answers` text DEFAULT NULL,
  `answer_match_mode` varchar(40) NOT NULL DEFAULT 'smart',
  `answer_help` text DEFAULT NULL,
  `explanation` text DEFAULT NULL,
  `ai_prompt_hint` text DEFAULT NULL,
  `tense_name` varchar(120) DEFAULT NULL,
  `level` varchar(80) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `published` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `status_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `practice_questions`
--

INSERT INTO `practice_questions` (`id`, `category_id`, `lesson_id`, `question_type`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_answer`, `sample_answer`, `accepted_answers`, `answer_match_mode`, `answer_help`, `explanation`, `ai_prompt_hint`, `tense_name`, `level`, `sort_order`, `published`, `status_deleted`, `created_at`) VALUES
(1, 1, 1, 'fill_blank', 'I ___ English every day.', 'speak', 'speaks', 'speaking', 'spoke', 'speak', 'I speak English every day.', NULL, 'smart', NULL, 'With I, use base verb: speak.', NULL, 'Present Simple', 'Beginner', 1, 'Yes', 0, '2026-06-20 06:38:23'),
(2, 1, 1, 'fill_blank', 'She ___ tea in the morning.', 'drink', 'drinks', 'drinking', 'drank', 'drinks', 'She drinks tea in the morning.', NULL, 'smart', NULL, 'With She/He/It, add s/es to the verb.', NULL, 'Present Simple', 'Beginner', 2, 'Yes', 0, '2026-06-20 06:38:23'),
(3, 1, 1, 'hindi_to_english', 'मैं रोज स्कूल जाता हूँ।', NULL, NULL, NULL, NULL, 'I go to school every day', 'I go to school every day.', NULL, 'smart', NULL, 'Habit sentence, so use Present Simple.', NULL, 'Present Simple', 'Beginner', 3, 'Yes', 0, '2026-06-20 06:38:23'),
(4, 1, 2, 'fill_blank', 'I ___ English now.', 'am learning', 'is learning', 'learn', 'learned', 'am learning', 'I am learning English now.', NULL, 'smart', NULL, 'Use am + verb ing with I.', NULL, 'Present Continuous', 'Beginner', 1, 'Yes', 0, '2026-06-20 06:38:23'),
(5, 1, 2, 'fill_blank', 'They ___ cricket at the moment.', 'are playing', 'is playing', 'play', 'played', 'are playing', 'They are playing cricket at the moment.', NULL, 'smart', NULL, 'Use are + verb ing with They/We/You.', NULL, 'Present Continuous', 'Beginner', 2, 'Yes', 0, '2026-06-20 06:38:23'),
(6, 1, 3, 'fill_blank', 'I ___ to the market yesterday.', 'go', 'went', 'gone', 'going', 'went', 'I went to the market yesterday.', NULL, 'smart', NULL, 'Yesterday shows past time, so use verb 2: went.', NULL, 'Past Simple', 'Beginner', 1, 'Yes', 0, '2026-06-20 06:38:23'),
(7, 1, 3, 'hindi_to_english', 'मैंने कल अपना होमवर्क किया।', NULL, NULL, NULL, NULL, 'I did my homework yesterday', 'I did my homework yesterday.', NULL, 'smart', NULL, 'Completed action in the past: did.', NULL, 'Past Simple', 'Beginner', 2, 'Yes', 0, '2026-06-20 06:38:23'),
(8, 1, 4, 'fill_blank', 'I ___ call you tomorrow.', 'will', 'am', 'was', 'did', 'will', 'I will call you tomorrow.', NULL, 'smart', NULL, 'Tomorrow shows future plan. Use will + base verb.', NULL, 'Future Simple', 'Beginner', 1, 'Yes', 0, '2026-06-20 06:38:23'),
(9, 2, 5, 'situation', 'You are late for class. What will you say to your teacher?', NULL, NULL, NULL, NULL, 'Sorry teacher I am late because of traffic', 'Sorry, teacher. I am late because of traffic. It will not happen again.', NULL, 'smart', NULL, 'Be polite and give a short reason.', NULL, NULL, 'Beginner', 1, 'Yes', 0, '2026-06-20 06:38:23'),
(10, 2, 5, 'situation', 'You want to ask the price in a shop. What will you say?', NULL, NULL, NULL, NULL, 'What is the price of this', 'Excuse me, what is the price of this?', NULL, 'smart', NULL, 'Use Excuse me to sound polite.', NULL, NULL, 'Beginner', 2, 'Yes', 0, '2026-06-20 06:38:23'),
(11, 2, 5, 'situation', 'You did not understand someone. What will you say?', NULL, NULL, NULL, NULL, 'Could you please repeat that', 'Sorry, could you please repeat that?', NULL, 'smart', NULL, 'This is more polite than saying What?', NULL, NULL, 'Beginner', 3, 'Yes', 0, '2026-06-20 06:38:23'),
(12, 4, 6, 'situation', 'Interview question: Tell me about yourself.', NULL, NULL, NULL, NULL, 'My name is Rahul. I am from Jaunpur. I am learning spoken English to improve my confidence.', 'My name is Rahul. I am from Jaunpur. I have completed my studies and I am learning spoken English to improve my confidence and communication skills.', NULL, 'smart', NULL, 'Keep self introduction simple: name, place, education/work, goal.', NULL, NULL, 'Intermediate', 1, 'Yes', 0, '2026-06-20 06:38:23'),
(13, 4, 6, 'situation', 'Interview question: Why do you want this job?', NULL, NULL, NULL, NULL, 'I want this job because I want to learn and grow', 'I want this job because it matches my skills and gives me a chance to learn, grow and contribute to the company.', NULL, 'smart', NULL, 'Connect your answer with skills, growth and contribution.', NULL, NULL, 'Intermediate', 2, 'Yes', 0, '2026-06-20 06:38:23'),
(14, 3, 7, 'correction', 'Correct this sentence: I am go to market yesterday.', NULL, NULL, NULL, NULL, 'I went to the market yesterday', 'I went to the market yesterday.', NULL, 'smart', NULL, 'For yesterday, use Past Simple: went.', NULL, NULL, 'Beginner', 1, 'Yes', 0, '2026-06-20 06:38:23'),
(15, 3, 7, 'correction', 'Correct this sentence: He go to school every day.', NULL, NULL, NULL, NULL, 'He goes to school every day', 'He goes to school every day.', NULL, 'smart', NULL, 'With He/She/It in Present Simple, use verb+s/es.', NULL, NULL, 'Beginner', 2, 'Yes', 0, '2026-06-20 06:38:23'),
(16, 5, 8, 'voice', 'Speak this answer: I want to improve my English.', NULL, NULL, NULL, NULL, 'I want to improve my English', 'I want to improve my English.', NULL, 'smart', NULL, 'Speak slowly and clearly. Voice input works best in Chrome.', NULL, NULL, 'Beginner', 1, 'Yes', 0, '2026-06-20 06:38:23'),
(17, 1, 9, 'fill_blank', 'I ___ tea every morning.', '', '', '', '', 'drink', 'I drink tea every morning.', NULL, 'smart', NULL, 'With I, use base verb in Present Simple.', NULL, 'Present Simple', 'Beginner', 1, 'Yes', 0, '2026-06-21 22:28:51'),
(18, 1, 9, 'fill_blank', 'She ___ English every day.', '', '', '', '', 'speaks', 'She speaks English every day.', NULL, 'smart', NULL, 'With She/He/It, add s/es to the verb.', NULL, 'Present Simple', 'Beginner', 2, 'Yes', 0, '2026-06-21 22:28:51'),
(19, 1, 10, 'fill_blank', 'I ___ to the market yesterday.', '', '', '', '', 'went', 'I went to the market yesterday.', NULL, 'smart', NULL, 'Yesterday shows past time, so use went.', NULL, 'Past Simple', 'Beginner', 1, 'Yes', 0, '2026-06-21 22:28:51'),
(20, 1, 10, 'conversion', 'Convert to negative: I watched the class video.', '', '', '', '', 'I did not watch the class video.', 'I did not watch the class video.', NULL, 'smart', NULL, 'In Past Simple negative, use did not + base verb.', NULL, 'Past Simple', 'Beginner', 2, 'Yes', 0, '2026-06-21 22:28:51'),
(21, 1, 11, 'fill_blank', 'They ___ speaking English now.', '', '', '', '', 'are', 'They are speaking English now.', NULL, 'smart', NULL, 'They uses are in Present Continuous.', NULL, 'Present Continuous', 'Beginner', 1, 'Yes', 0, '2026-06-21 22:28:51'),
(22, 2, 12, 'situation', 'You are late for class. What will you say to your teacher?', '', '', '', '', 'Sorry, teacher. I am late because there was traffic.', 'Sorry, teacher. I am late because there was traffic. It will not happen again.', NULL, 'smart', NULL, 'Use polite apology + clear reason + promise.', NULL, '', 'Beginner', 1, 'Yes', 0, '2026-06-21 22:28:51'),
(23, 2, 12, 'situation', 'You want to ask someone for help in English. What will you say?', '', '', '', '', 'Could you please help me?', 'Excuse me, could you please help me with this?', NULL, 'smart', NULL, 'Use could you please for polite requests.', NULL, '', 'Beginner', 2, 'Yes', 0, '2026-06-21 22:28:51'),
(24, 2, 13, 'situation', 'Answer this interview question: Tell me about yourself.', '', '', '', '', 'My name is Rahul. I am a hardworking student and I want to improve my communication skills.', 'My name is Rahul. I have completed my studies and I am improving my English communication to build a better career.', NULL, 'smart', NULL, 'Keep your answer short, confident and relevant.', NULL, '', 'Intermediate', 1, 'Yes', 0, '2026-06-21 22:28:51'),
(25, 3, 14, 'correction', 'Correct this sentence: I am go market yesterday.', '', '', '', '', 'I went to the market yesterday.', 'I went to the market yesterday.', NULL, 'smart', NULL, 'Yesterday needs Past Simple. Use went, not am go.', NULL, '', 'Beginner', 1, 'Yes', 0, '2026-06-21 22:28:51'),
(26, 6, 15, 'voice', 'Speak this sentence clearly: I want to improve my English speaking confidence.', '', '', '', '', 'I want to improve my English speaking confidence.', 'I want to improve my English speaking confidence.', NULL, 'smart', NULL, 'Speak slowly, clearly and repeat until the sentence feels natural.', NULL, '', 'All Levels', 1, 'Yes', 0, '2026-06-21 22:28:51');

-- --------------------------------------------------------

--
-- Table structure for table `practice_settings`
--

CREATE TABLE `practice_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `setting_key` varchar(80) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `practice_settings`
--

INSERT INTO `practice_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'ai_correction_enabled', 'Yes', '2026-06-20 06:23:11'),
(2, 'ai_fallback_enabled', 'Yes', '2026-06-20 06:23:11'),
(3, 'ai_provider', 'openai', '2026-06-20 06:23:11'),
(4, 'openai_api_key', '', '2026-06-20 06:23:11'),
(5, 'openai_model', 'gpt-4o-mini', '2026-06-20 06:23:11'),
(6, 'openai_endpoint', '', '2026-06-20 06:23:11'),
(7, 'ai_daily_limit', '10', '2026-06-20 06:23:11'),
(8, 'ai_timeout_seconds', '18', '2026-06-20 06:23:11'),
(9, 'ai_temperature', '0.2', '2026-06-20 06:23:11'),
(10, 'ai_system_prompt', 'You are a friendly spoken English practice coach for Indian learners. Correct grammar, make answers natural, explain simply, and keep feedback short.', '2026-06-20 06:23:11'),
(11, 'practice_enabled', 'Yes', '2026-06-20 06:38:23'),
(12, 'local_mode_enabled', 'Yes', '2026-06-20 06:38:23'),
(13, 'browser_voice_enabled', 'Yes', '2026-06-20 06:38:23'),
(14, 'ai_enabled', 'No', '2026-06-20 06:38:23'),
(49, 'free_daily_limit', '20', '2026-06-21 22:28:51'),
(50, 'practice_intro_note', 'Start with free local practice. AI enhancement can be enabled later from settings without breaking the core practice engine.', '2026-06-21 22:28:51');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `setting_key` varchar(80) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'site_name', 'Well Fare English Spoken', '2026-06-20 06:23:11'),
(2, 'site_logo', 'assets/uploads/brand/logo_20260620_191105_6c0c3e25.png', '2026-06-20 22:41:05'),
(3, 'site_favicon', 'assets/uploads/brand/favicon_20260620_191105_32a3449f.png', '2026-06-20 22:41:05'),
(4, 'brand_logo_alt', 'Institute logo', '2026-06-20 06:23:11'),
(5, 'brand_mark_mode', 'text', '2026-06-20 06:23:11'),
(6, 'phone', '9506617831', '2026-06-22 12:36:51'),
(7, 'whatsapp', '910000000000', '2026-06-20 06:23:11'),
(8, 'email', 'info@wellfare.local', '2026-06-20 06:23:11'),
(9, 'address', 'Station Road, Mariahu, Jaunpur', '2026-06-20 06:23:11'),
(10, 'map_url', 'https://www.google.com/maps', '2026-06-20 06:23:11'),
(11, 'hero_headline', 'Speak English confidently in daily life, interviews and career conversations.', '2026-06-20 06:23:11'),
(12, 'hero_subtitle', 'Join practical spoken English classes designed for students, job seekers, working professionals and homemakers who want real speaking confidence.', '2026-06-20 06:23:11'),
(13, 'admission_note', 'Admission open for spoken English, grammar, confidence and interview preparation batches.', '2026-06-20 06:23:11'),
(14, 'seo_home_title', 'Well Fare English Spoken | Spoken English Institute in Mariahu Jaunpur', '2026-06-20 06:23:11'),
(15, 'seo_home_description', 'Join practical spoken English, grammar, interview preparation and personality development classes at Well Fare English Spoken in Mariahu Jaunpur.', '2026-06-20 06:23:11'),
(16, 'seo_courses_title', 'Spoken English Courses | Well Fare English Spoken', '2026-06-20 06:23:11'),
(17, 'seo_courses_description', 'Explore beginner, advanced, grammar, interview and personality development English speaking courses.', '2026-06-20 06:23:11'),
(18, 'seo_admission_title', 'Admission Enquiry | Well Fare English Spoken', '2026-06-20 06:23:11'),
(19, 'seo_admission_description', 'Book free counselling for spoken English classes and get batch timing and course details.', '2026-06-20 06:23:11'),
(20, 'seo_contact_title', 'Contact Well Fare English Spoken', '2026-06-20 06:23:11'),
(21, 'seo_contact_description', 'Call, WhatsApp or visit Well Fare English Spoken for course details and admission counselling.', '2026-06-20 06:23:11'),
(22, 'seo_gallery_title', 'Gallery | Well Fare English Spoken', '2026-06-20 06:23:11'),
(23, 'seo_gallery_description', 'View classroom, activity and student practice photos from Well Fare English Spoken.', '2026-06-20 06:23:11'),
(24, 'seo_reviews_title', 'Student Reviews | Well Fare English Spoken', '2026-06-20 06:23:11'),
(25, 'seo_reviews_description', 'Read student reviews and feedback for spoken English classes.', '2026-06-20 06:23:11'),
(26, 'seo_about_title', 'About Well Fare English Spoken', '2026-06-20 06:23:11'),
(27, 'seo_about_description', 'Learn about Well Fare English Spoken, practical spoken English and confidence training institute.', '2026-06-20 06:23:11'),
(28, 'practice_tool_label', 'Free Smart English Practice Tool', '2026-06-20 06:23:11'),
(29, 'practice_tool_note', 'Free local practice works for everyone. Optional OpenAI can be enabled from admin for advanced feedback.', '2026-06-20 06:23:11'),
(30, 'brand_short', 'WF', '2026-06-20 06:23:11'),
(31, 'brand_title', 'Well Fare', '2026-06-20 06:23:11'),
(32, 'brand_subtitle', 'English Spoken', '2026-06-20 06:23:11'),
(33, 'facebook_url', '', '2026-06-20 06:23:11'),
(34, 'instagram_url', '', '2026-06-20 06:23:11'),
(35, 'youtube_url', '', '2026-06-20 06:23:11'),
(36, 'footer_about', 'Practical spoken English classes for students, job seekers and working professionals.', '2026-06-20 06:23:11'),
(37, 'footer_copyright', 'All rights reserved.', '2026-06-20 06:23:11'),
(38, 'hero_eyebrow', 'Trusted Spoken English Institute in Mariahu', '2026-06-20 06:23:11'),
(39, 'hero_primary_text', 'Book Free Counselling', '2026-06-20 06:23:11'),
(40, 'hero_primary_url', 'admission.php', '2026-06-20 06:23:11'),
(41, 'hero_secondary_text', 'Call Now', '2026-06-20 06:23:11'),
(42, 'home_features_title', 'Built for students who want real speaking confidence.', '2026-06-20 06:23:11'),
(43, 'home_features_subtitle', 'Simple lessons, daily practice and guided correction make English easier for school students, college students, job seekers and working professionals.', '2026-06-20 06:23:11'),
(44, 'home_courses_title', 'Popular Courses', '2026-06-20 06:23:11'),
(45, 'home_courses_subtitle', 'Choose a course based on your current level, confidence and career goal.', '2026-06-20 06:23:11'),
(46, 'home_batches_eyebrow', 'Batch Timings', '2026-06-20 06:23:11'),
(47, 'home_batches_title', 'Choose a comfortable speaking practice batch.', '2026-06-20 06:23:11'),
(48, 'home_batches_subtitle', 'Admin-managed batch timings help students quickly decide when to join.', '2026-06-20 06:23:11'),
(49, 'home_gallery_title', 'Inside the institute', '2026-06-20 06:23:11'),
(50, 'home_gallery_subtitle', 'Show real classroom trust with admin-managed gallery photos.', '2026-06-20 06:23:11'),
(51, 'home_reviews_title', 'Student Reviews', '2026-06-20 06:23:11'),
(52, 'home_reviews_subtitle', 'Real testimonials can be managed from the admin panel.', '2026-06-20 06:23:11'),
(53, 'home_videos_title', 'Class Videos', '2026-06-20 06:23:11'),
(54, 'home_videos_subtitle', 'Add YouTube links from admin and they will appear here.', '2026-06-20 06:23:11'),
(55, 'home_faq_eyebrow', 'Common Questions', '2026-06-20 06:23:11'),
(56, 'home_faq_title', 'Before you join', '2026-06-20 06:23:11'),
(57, 'home_faq_subtitle', 'Answers to common admission and course questions.', '2026-06-20 06:23:11'),
(58, 'home_cta_title', 'Admission open for spoken English batches.', '2026-06-20 06:23:11'),
(59, 'admission_eyebrow', 'Admission Open', '2026-06-20 06:23:11'),
(60, 'admission_title', 'Book your free spoken English counselling call.', '2026-06-20 06:23:11'),
(61, 'admission_privacy_note', 'Your details are safe with us.', '2026-06-20 06:23:11'),
(62, 'admission_faq_title', 'Admission FAQs', '2026-06-20 06:23:11'),
(63, 'admission_faq_subtitle', 'Helpful answers managed from admin.', '2026-06-20 06:23:11'),
(64, 'about_eyebrow', 'About Institute', '2026-06-20 06:23:11'),
(65, 'about_title', 'About Well Fare English Spoken', '2026-06-20 06:23:11'),
(66, 'about_subtitle', 'A student-friendly English speaking institute focused on practical learning and confidence building.', '2026-06-20 06:23:11'),
(67, 'about_promise_title', 'Our teaching promise', '2026-06-20 06:23:11'),
(68, 'about_promise_body', 'Students do not need only theory. They need habit, correction and practice. We make English simple, practical and confidence-focused.', '2026-06-20 06:23:11'),
(69, 'courses_page_title', 'Choose the right spoken English course', '2026-06-20 06:23:11'),
(70, 'courses_page_subtitle', 'Every course is designed to improve confidence, grammar clarity and practical communication.', '2026-06-20 06:23:11'),
(71, 'gallery_page_title', 'Gallery', '2026-06-20 06:23:11'),
(72, 'gallery_page_subtitle', 'Classroom moments, student practice and institute activities managed from admin.', '2026-06-20 06:23:11'),
(73, 'reviews_page_title', 'Student Reviews', '2026-06-20 06:23:11'),
(74, 'reviews_page_subtitle', 'Student feedback and success stories managed from admin.', '2026-06-20 06:23:11'),
(75, 'contact_page_title', 'Contact the institute', '2026-06-20 06:23:11'),
(76, 'contact_page_subtitle', 'Call, WhatsApp or visit for admission counselling and batch details.', '2026-06-20 06:23:11'),
(306, 'seo_practice_title', 'Spoken English Practice Room | Tense and Speaking Practice', '2026-06-21 22:28:51'),
(307, 'seo_practice_description', 'Practise English tenses, sentences, situations and speaking for free with a local AI-style practice lab.', '2026-06-21 22:28:51'),
(308, 'practice_page_title', 'Spoken English Practice Room', '2026-06-21 22:28:51'),
(309, 'practice_page_subtitle', 'Practise tenses, daily situations, sentence correction and speaking confidence without login. The free practice engine works even without paid AI API.', '2026-06-21 22:28:51'),
(310, 'practice_cta_title', 'Want teacher guidance after practice?', '2026-06-21 22:28:51'),
(311, 'practice_cta_body', 'Share your practice score with the counsellor and book a free demo class for personal spoken English correction.', '2026-06-21 22:28:51');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_name` varchar(120) NOT NULL,
  `message` text NOT NULL,
  `published` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `student_name`, `message`, `published`, `created_at`) VALUES
(1, 'Aman Singh', 'The classes helped me speak English without fear. Daily practice is very useful.', 'Yes', '2026-06-20 06:23:11'),
(2, 'Priya Yadav', 'Grammar and speaking both became easier. The teaching style is simple and clear.', 'Yes', '2026-06-20 06:23:11'),
(3, 'Rohit Verma', 'I improved my interview introduction and confidence after joining the course.', 'Yes', '2026-06-20 06:23:11'),
(4, 'Aman Singh', 'The classes helped me speak English without fear. Daily practice is very useful.', 'Yes', '2026-06-21 22:09:35'),
(5, 'Priya Yadav', 'Grammar and speaking both became easier. The teaching style is simple and clear.', 'Yes', '2026-06-21 22:09:35'),
(6, 'Rohit Verma', 'I improved my interview introduction and confidence after joining the course.', 'Yes', '2026-06-21 22:09:35');

-- --------------------------------------------------------

--
-- Table structure for table `translation_pairs`
--

CREATE TABLE `translation_pairs` (
  `id` int(10) UNSIGNED NOT NULL,
  `collection_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `unit_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `hindi_text` text NOT NULL,
  `english_text` text NOT NULL,
  `roman_text` text DEFAULT NULL,
  `tense_name` varchar(120) DEFAULT NULL,
  `situation_tag` varchar(120) DEFAULT NULL,
  `level` varchar(80) DEFAULT NULL,
  `explanation` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `published` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `status_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `accepted_english_answers` text DEFAULT NULL,
  `accepted_hindi_answers` text DEFAULT NULL,
  `answer_match_mode` varchar(40) NOT NULL DEFAULT 'smart'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `translation_pairs`
--

INSERT INTO `translation_pairs` (`id`, `collection_id`, `unit_id`, `hindi_text`, `english_text`, `roman_text`, `tense_name`, `situation_tag`, `level`, `explanation`, `sort_order`, `published`, `status_deleted`, `created_at`, `accepted_english_answers`, `accepted_hindi_answers`, `answer_match_mode`) VALUES
(1, 2, 1, 'मैं रोज अंग्रेजी बोलने की कोशिश करता हूँ।', 'I try to speak English every day.', NULL, 'Present Simple', 'Daily Practice', 'Beginner', 'Use try to + base verb for habit/practice.', 1, 'Yes', 0, '2026-06-20 06:32:10', NULL, NULL, 'smart'),
(2, 2, 1, 'मैंने कल अपना होमवर्क पूरा किया।', 'I completed my homework yesterday.', NULL, 'Past Simple', 'Daily Practice', 'Beginner', 'Yesterday shows past time, so use completed.', 2, 'Yes', 0, '2026-06-20 06:32:10', NULL, NULL, 'smart'),
(3, 2, 1, 'मैं अभी अंग्रेजी सीख रहा हूँ।', 'I am learning English right now.', NULL, 'Present Continuous', 'Daily Practice', 'Beginner', 'Right now shows action happening now: am/is/are + verb-ing.', 3, 'Yes', 0, '2026-06-20 06:32:10', NULL, NULL, 'smart'),
(4, 2, 1, 'क्या आप मेरी मदद कर सकते हैं?', 'Can you help me?', NULL, 'Modal Verb', 'Polite Speaking', 'Beginner', 'Can you is a simple polite request.', 4, 'Yes', 0, '2026-06-20 06:32:10', NULL, NULL, 'smart'),
(5, 2, 1, 'मुझे इंटरव्यू के लिए अंग्रेजी सुधारनी है।', 'I want to improve my English for an interview.', NULL, 'Present Simple', 'Interview', 'Beginner', 'Want to + verb is used for goals.', 5, 'Yes', 0, '2026-06-20 06:32:10', NULL, NULL, 'smart'),
(6, 2, 1, 'मैं पहले अंग्रेजी बोलने से डरता था।', 'I used to be afraid of speaking English.', NULL, 'Past Habit', 'Confidence', 'Intermediate', 'Used to describes a past habit or past condition.', 6, 'Yes', 0, '2026-06-20 06:32:10', NULL, NULL, 'smart'),
(7, 2, 1, 'कृपया इस वाक्य को दोहराइए।', 'Please repeat this sentence.', NULL, 'Imperative', 'Classroom', 'Beginner', 'Please makes the command polite.', 7, 'Yes', 0, '2026-06-20 06:32:10', NULL, NULL, 'smart'),
(8, 2, 1, 'मैं फोन पर स्पष्ट बोलना चाहता हूँ।', 'I want to speak clearly on the phone.', NULL, 'Present Simple', 'Phone English', 'Beginner', 'Clearly describes how you want to speak.', 8, 'Yes', 0, '2026-06-20 06:32:10', NULL, NULL, 'smart'),
(9, 3, 2, 'मैं रोज अंग्रेजी बोलता हूँ।', 'I speak English every day.', 'Main roz angrezi bolta hoon.', 'Present Simple', 'Daily Practice', 'Beginner', 'Habit sentence: use Present Simple.', 1, 'Yes', 0, '2026-06-20 06:38:23', NULL, NULL, 'smart'),
(10, 3, 2, 'मैं अंग्रेजी सीख रहा हूँ।', 'I am learning English.', 'Main angrezi seekh raha hoon.', 'Present Continuous', 'Learning', 'Beginner', 'Action happening now: am learning.', 2, 'Yes', 0, '2026-06-20 06:38:23', NULL, NULL, 'smart'),
(11, 3, 2, 'मैं कल बाजार गया था।', 'I went to the market yesterday.', 'Main kal bazaar gaya tha.', 'Past Simple', 'Daily Practice', 'Beginner', 'Yesterday means past: went.', 3, 'Yes', 0, '2026-06-20 06:38:23', NULL, NULL, 'smart'),
(12, 3, 2, 'मैं कल आपको फोन करूंगा।', 'I will call you tomorrow.', 'Main kal aapko phone karunga.', 'Future Simple', 'Phone Call', 'Beginner', 'Tomorrow means future: will call.', 4, 'Yes', 0, '2026-06-20 06:38:23', NULL, NULL, 'smart'),
(13, 3, 2, 'कृपया इसे फिर से दोहराइए।', 'Please repeat it again.', 'Kripya ise phir se dohraiye.', 'Imperative', 'Classroom', 'Beginner', 'Polite request sentence.', 5, 'Yes', 0, '2026-06-20 06:38:23', NULL, NULL, 'smart'),
(14, 3, 2, 'मुझे अंग्रेजी बोलने में झिझक होती है।', 'I hesitate while speaking English.', 'Mujhe angrezi bolne mein jhijhak hoti hai.', 'Present Simple', 'Confidence', 'Beginner', 'Use hesitate while + verb-ing.', 6, 'Yes', 0, '2026-06-20 06:38:23', NULL, NULL, 'smart'),
(15, 3, 2, 'आपका नाम क्या है?', 'What is your name?', 'Aapka naam kya hai?', 'Question', 'Introduction', 'Beginner', 'Basic question sentence.', 8, 'Yes', 0, '2026-06-20 06:38:23', NULL, NULL, 'smart'),
(16, 3, 2, 'मैं जौनपुर से हूँ।', 'I am from Jaunpur.', 'Main Jaunpur se hoon.', 'Present Simple', 'Introduction', 'Beginner', 'Use I am from + place.', 9, 'Yes', 0, '2026-06-20 06:38:23', NULL, NULL, 'smart'),
(17, 3, 2, 'मुझे थोड़ा समय चाहिए।', 'I need some time.', 'Mujhe thoda samay chahiye.', 'Present Simple', 'Daily Practice', 'Beginner', 'Useful polite sentence.', 10, 'Yes', 0, '2026-06-20 06:38:23', NULL, NULL, 'smart'),
(18, 3, 2, 'मैं अपनी अंग्रेजी सुधारना चाहता हूँ।', 'I want to improve my English.', 'Main apni angrezi sudharna chahta hoon.', 'Present Simple', 'Learning', 'Beginner', 'Use want to + base verb.', 11, 'Yes', 0, '2026-06-20 06:38:23', NULL, NULL, 'smart'),
(19, 3, 2, 'मैंने आज नया शब्द सीखा।', 'I learned a new word today.', 'Maine aaj naya shabd seekha.', 'Past Simple', 'Learning', 'Beginner', 'Completed action today: learned.', 12, 'Yes', 0, '2026-06-20 06:38:23', NULL, NULL, 'smart'),
(20, 3, 2, 'मैं अभी व्यस्त हूँ।', 'I am busy right now.', 'Main abhi vyast hoon.', 'Present Simple', 'Daily Practice', 'Beginner', 'Short daily-use sentence.', 13, 'Yes', 0, '2026-06-20 06:38:23', NULL, NULL, 'smart'),
(21, 3, 2, 'कृपया धीरे बोलिए।', 'Please speak slowly.', 'Kripya dheere boliye.', 'Imperative', 'Conversation', 'Beginner', 'Polite request.', 14, 'Yes', 0, '2026-06-20 06:38:23', NULL, NULL, 'smart'),
(22, 3, 2, 'मुझे समझ में नहीं आया।', 'I did not understand.', 'Mujhe samajh mein nahi aaya.', 'Past Simple', 'Conversation', 'Beginner', 'Use did not + base verb.', 15, 'Yes', 0, '2026-06-20 06:38:23', NULL, NULL, 'smart');

-- --------------------------------------------------------

--
-- Table structure for table `videos`
--

CREATE TABLE `videos` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(160) NOT NULL,
  `youtube_url` varchar(255) NOT NULL,
  `published` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `videos`
--

INSERT INTO `videos` (`id`, `title`, `youtube_url`, `published`, `created_at`) VALUES
(1, 'English Speaking Practice Demo', 'https://www.youtube.com/watch?v=cuE2Jx9g5vk', 'Yes', '2026-06-20 06:23:11'),
(2, 'English Speaking Practice Demo', 'https://www.youtube.com/watch?v=cuE2Jx9g5vk', 'Yes', '2026-06-21 22:09:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `batch_timings`
--
ALTER TABLE `batch_timings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `content_blocks`
--
ALTER TABLE `content_blocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_blocks_type` (`block_type`,`published`,`sort_order`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `enquiries`
--
ALTER TABLE `enquiries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `form_options`
--
ALTER TABLE `form_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_options_group` (`option_group`,`published`,`sort_order`);

--
-- Indexes for table `gallery_images`
--
ALTER TABLE `gallery_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hero_banners`
--
ALTER TABLE `hero_banners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hero_page` (`page_key`,`published`,`sort_order`);

--
-- Indexes for table `material_assets`
--
ALTER TABLE `material_assets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_material_assets` (`collection_id`,`published`,`status_deleted`,`sort_order`);

--
-- Indexes for table `material_collections`
--
ALTER TABLE `material_collections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_material_collection` (`published`,`status_deleted`,`sort_order`);

--
-- Indexes for table `material_practice_attempts`
--
ALTER TABLE `material_practice_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_material_attempts` (`session_id`,`created_at`),
  ADD KEY `idx_material_pair` (`pair_id`);

--
-- Indexes for table `material_settings`
--
ALTER TABLE `material_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `material_units`
--
ALTER TABLE `material_units`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_material_units` (`collection_id`,`published`,`status_deleted`,`sort_order`);

--
-- Indexes for table `nav_menus`
--
ALTER TABLE `nav_menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_nav_area` (`menu_area`,`published`,`sort_order`);

--
-- Indexes for table `practice_ai_logs`
--
ALTER TABLE `practice_ai_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ai_logs_session` (`session_id`,`created_at`),
  ADD KEY `idx_ai_logs_date` (`created_at`);

--
-- Indexes for table `practice_attempts`
--
ALTER TABLE `practice_attempts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `practice_categories`
--
ALTER TABLE `practice_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `practice_common_mistakes`
--
ALTER TABLE `practice_common_mistakes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `practice_lessons`
--
ALTER TABLE `practice_lessons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `practice_questions`
--
ALTER TABLE `practice_questions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `practice_settings`
--
ALTER TABLE `practice_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `translation_pairs`
--
ALTER TABLE `translation_pairs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_translation_pairs` (`collection_id`,`unit_id`,`published`,`status_deleted`,`sort_order`);

--
-- Indexes for table `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `batch_timings`
--
ALTER TABLE `batch_timings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `content_blocks`
--
ALTER TABLE `content_blocks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `enquiries`
--
ALTER TABLE `enquiries`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `form_options`
--
ALTER TABLE `form_options`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `gallery_images`
--
ALTER TABLE `gallery_images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `hero_banners`
--
ALTER TABLE `hero_banners`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `material_assets`
--
ALTER TABLE `material_assets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `material_collections`
--
ALTER TABLE `material_collections`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `material_practice_attempts`
--
ALTER TABLE `material_practice_attempts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `material_settings`
--
ALTER TABLE `material_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1436;

--
-- AUTO_INCREMENT for table `material_units`
--
ALTER TABLE `material_units`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `nav_menus`
--
ALTER TABLE `nav_menus`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `practice_ai_logs`
--
ALTER TABLE `practice_ai_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `practice_attempts`
--
ALTER TABLE `practice_attempts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `practice_categories`
--
ALTER TABLE `practice_categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `practice_common_mistakes`
--
ALTER TABLE `practice_common_mistakes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `practice_lessons`
--
ALTER TABLE `practice_lessons`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `practice_questions`
--
ALTER TABLE `practice_questions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `practice_settings`
--
ALTER TABLE `practice_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7715;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41685;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `translation_pairs`
--
ALTER TABLE `translation_pairs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `videos`
--
ALTER TABLE `videos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
