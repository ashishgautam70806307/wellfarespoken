CREATE DATABASE IF NOT EXISTS wellfare_english CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE wellfare_english;

CREATE TABLE admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  published ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE courses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  short_description TEXT NULL,
  duration VARCHAR(80) NULL,
  level VARCHAR(80) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  published ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE testimonials (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_name VARCHAR(120) NOT NULL,
  message TEXT NOT NULL,
  published ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE videos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  youtube_url VARCHAR(255) NOT NULL,
  published ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE enquiries (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  course_interest VARCHAR(160) NULL,
  message TEXT NULL,
  ip_address VARCHAR(80) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO admins (name, email, password_hash, published) VALUES
('Admin', 'admin@wellfare.local', '$2y$12$DHCToBguTMZptJEHcBMUGuoAErIOUDX45NhgtxRT6i9LPRaojvz5u', 'Yes');

INSERT INTO courses (title, short_description, duration, level, sort_order, published) VALUES
('Basic Spoken English', 'Start from basic words, sentence formation, tense clarity and daily-use conversation.', '3 Months', 'Beginner', 1, 'Yes'),
('Advanced Spoken English', 'Improve fluency, confidence, pronunciation, vocabulary and natural communication.', '3 Months', 'Advanced', 2, 'Yes'),
('Grammar With Speaking', 'Learn grammar practically so you can use it while speaking and writing.', '2 Months', 'All Levels', 3, 'Yes'),
('Interview Preparation', 'Practice self-introduction, HR questions, answers and professional communication.', '1 Month', 'Job Seekers', 4, 'Yes'),
('Personality Development', 'Build confidence, presentation style, public speaking and professional behaviour.', '1 Month', 'Confidence', 5, 'Yes'),
('Student English Practice', 'Special guided English practice for school and college students.', 'Flexible', 'Students', 6, 'Yes');

INSERT INTO testimonials (student_name, message, published) VALUES
('Aman Singh', 'The classes helped me speak English without fear. Daily practice is very useful.', 'Yes'),
('Priya Yadav', 'Grammar and speaking both became easier. The teaching style is simple and clear.', 'Yes'),
('Rohit Verma', 'I improved my interview introduction and confidence after joining the course.', 'Yes');

INSERT INTO videos (title, youtube_url, published) VALUES
('English Speaking Practice Demo', 'https://www.youtube.com/watch?v=cuE2Jx9g5vk', 'Yes');

-- Phase 21 Student Learning Portal upgrade
CREATE TABLE IF NOT EXISTS students (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(160) NOT NULL,
    phone VARCHAR(40) NOT NULL,
    email VARCHAR(160) NULL,
    password_hash VARCHAR(255) NOT NULL,
    current_level VARCHAR(80) NOT NULL DEFAULT 'Zero Level',
    target_goal VARCHAR(180) NULL,
    preferred_language VARCHAR(40) NOT NULL DEFAULT 'Hindi',
    daily_goal_minutes INT NOT NULL DEFAULT 20,
    admin_note TEXT NULL,
    published ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
    status_deleted TINYINT(1) NOT NULL DEFAULT 0,
    last_login_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_students_phone (phone),
    KEY idx_students_active (published, status_deleted, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS student_activity_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    activity_type VARCHAR(80) NOT NULL,
    activity_title VARCHAR(180) NULL,
    score INT NULL,
    note TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_student_activity (student_id, created_at),
    KEY idx_student_activity_type (activity_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO nav_menus (menu_area, label, url, is_cta, sort_order, published)
SELECT 'header', 'Roadmap', 'learning-roadmap.php', 'No', 40, 'Yes'
WHERE NOT EXISTS (SELECT 1 FROM nav_menus WHERE menu_area='header' AND url='learning-roadmap.php');

INSERT INTO nav_menus (menu_area, label, url, is_cta, sort_order, published)
SELECT 'header', 'Student Login', 'student-auth.php', 'No', 45, 'Yes'
WHERE NOT EXISTS (SELECT 1 FROM nav_menus WHERE menu_area='header' AND url='student-auth.php');
