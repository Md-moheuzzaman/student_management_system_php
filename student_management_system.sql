-- Student Management System Database
-- MySQL / MariaDB (XAMPP)
-- Run this script in phpMyAdmin or the MySQL command line.

CREATE DATABASE IF NOT EXISTS student_management_system
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE student_management_system;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS fees;
DROP TABLE IF EXISTS marks;
DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS teacher_subjects;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS teachers;
DROP TABLE IF EXISTS subjects;
DROP TABLE IF EXISTS classes;
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. Users
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'student', 'parent') NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_users_email (email),
    INDEX idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Courses
CREATE TABLE courses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(100) NOT NULL,
    description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Classes
CREATE TABLE classes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(50) NOT NULL,
    section VARCHAR(10) NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_classes_course_id (course_id),
    CONSTRAINT fk_classes_course
        FOREIGN KEY (course_id) REFERENCES courses(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Teachers
CREATE TABLE teachers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    qualification VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_teachers_user_id (user_id),
    CONSTRAINT fk_teachers_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Students
CREATE TABLE students (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    dob DATE,
    class_id INT UNSIGNED NOT NULL,
    address VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_students_user_id (user_id),
    INDEX idx_students_class_id (class_id),
    CONSTRAINT fk_students_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_students_class
        FOREIGN KEY (class_id) REFERENCES classes(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Subjects
CREATE TABLE subjects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(100) NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_subjects_course_id (course_id),
    CONSTRAINT fk_subjects_course
        FOREIGN KEY (course_id) REFERENCES courses(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Teacher-to-subject/class assignments
CREATE TABLE teacher_subjects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT UNSIGNED NOT NULL,
    subject_id INT UNSIGNED NOT NULL,
    class_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_teacher_subjects_teacher_id (teacher_id),
    INDEX idx_teacher_subjects_subject_id (subject_id),
    INDEX idx_teacher_subjects_class_id (class_id),
    CONSTRAINT fk_teacher_subjects_teacher
        FOREIGN KEY (teacher_id) REFERENCES teachers(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_teacher_subjects_subject
        FOREIGN KEY (subject_id) REFERENCES subjects(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_teacher_subjects_class
        FOREIGN KEY (class_id) REFERENCES classes(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Attendance
CREATE TABLE attendance (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    subject_id INT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    status ENUM('P', 'A', 'L') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_attendance_student_id (student_id),
    INDEX idx_attendance_subject_id (subject_id),
    INDEX idx_attendance_date (date),
    CONSTRAINT fk_attendance_student
        FOREIGN KEY (student_id) REFERENCES students(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_attendance_subject
        FOREIGN KEY (subject_id) REFERENCES subjects(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Marks
CREATE TABLE marks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    subject_id INT UNSIGNED NOT NULL,
    exam_type VARCHAR(50) NOT NULL,
    marks DECIMAL(5,2) NOT NULL,
    exam_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_marks_student_id (student_id),
    INDEX idx_marks_subject_id (subject_id),
    INDEX idx_marks_exam_date (exam_date),
    CONSTRAINT fk_marks_student
        FOREIGN KEY (student_id) REFERENCES students(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_marks_subject
        FOREIGN KEY (subject_id) REFERENCES subjects(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Fees
CREATE TABLE fees (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE,
    status ENUM('Paid', 'Unpaid', 'Partial') NOT NULL DEFAULT 'Unpaid',
    receipt_no VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_fees_student_id (student_id),
    INDEX idx_fees_status (status),
    CONSTRAINT fk_fees_student
        FOREIGN KEY (student_id) REFERENCES students(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
