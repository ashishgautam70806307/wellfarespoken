CREATE TABLE IF NOT EXISTS online_classes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    class_title VARCHAR(190) NOT NULL,
    course_name VARCHAR(180) NULL,
    batch_name VARCHAR(180) NULL,
    teacher_name VARCHAR(180) NULL,
    class_date DATE NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    duration_minutes INT NOT NULL DEFAULT 60,
    platform VARCHAR(80) NOT NULL DEFAULT 'Google Meet',
    meeting_url VARCHAR(700) NULL,
    recording_url VARCHAR(700) NULL,
    class_status ENUM('Scheduled','Live','Completed','Cancelled') NOT NULL DEFAULT 'Scheduled',
    short_description TEXT NULL,
    student_note TEXT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    published ENUM('Yes','No') NOT NULL DEFAULT 'Yes',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_online_public (published, class_status, class_date, start_time),
    INDEX idx_online_batch (batch_name, class_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS online_class_attendance (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    online_class_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    attendance_status ENUM('Present','Absent','Late','Excused') NOT NULL DEFAULT 'Present',
    joined_at DATETIME NULL,
    left_at DATETIME NULL,
    admin_note TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_online_attendance (online_class_id, student_id),
    INDEX idx_attendance_student (student_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
