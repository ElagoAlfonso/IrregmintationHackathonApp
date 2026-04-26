CREATE DATABASE IF NOT EXISTS faculty_evaluation;
USE faculty_evaluation;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    role ENUM('student', 'professor', 'program_head', 'dean', 'admin')
);

CREATE TABLE IF NOT EXISTS faculty (
    id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_id VARCHAR(13) UNIQUE,
    user_id INT,
    name VARCHAR(100),
    department VARCHAR(100),
    college VARCHAR(100),
    role ENUM('professor') DEFAULT 'professor',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_id VARCHAR(13),
    rater_role ENUM('student', 'program_head', 'dean'),
    score1 INT,
    score2 INT,
    score3 INT,
    avg_score FLOAT,
    college VARCHAR(100),
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (faculty_id) REFERENCES faculty(faculty_id)
);

CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);