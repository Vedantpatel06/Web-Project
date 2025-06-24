-- Create database if not exists
CREATE DATABASE IF NOT EXISTS school_management;
USE school_management;

-- Create teachers table
CREATE TABLE teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') DEFAULT 'Male',
    email VARCHAR(255) NOT NULL UNIQUE,  -- Email added as a unique identifier
    phone VARCHAR(20),                   -- Phone number
    subject VARCHAR(255),
    address TEXT,
    profile_picture VARCHAR(255)        -- Profile picture field added
);

-- Create classes table
CREATE TABLE classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(255) NOT NULL,
    teacher_id INT NULL,
    description TEXT,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL
);

-- Create students table with email, phone, and profile_picture
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    roll_number VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') DEFAULT 'Male',
    email VARCHAR(255) NOT NULL UNIQUE,  -- Email added as a unique identifier
    phone VARCHAR(20),                   -- Phone number added
    class_id INT NULL,
    address TEXT,
    parents_name VARCHAR(255),
    profile_picture VARCHAR(255),        -- Profile picture field added
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL
);

-- Create attendance table to track attendance status
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('Present','Absent') NOT NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (student_id, attendance_date)
);
