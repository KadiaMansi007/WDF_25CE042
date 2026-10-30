CREATE DATABASE IF NOT EXISTS studenthub;
USE studenthub;
 
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    course VARCHAR(80) NOT NULL
);
 
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    event_date DATE NOT NULL,
    venue VARCHAR(120) NOT NULL
);
 
CREATE TABLE registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    event_id INT NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (event_id) REFERENCES events(id),
    UNIQUE (student_id, event_id)
);
 
INSERT INTO students (name, email, course)
VALUES ('Aarav Shah', 'aarav@example.com', 'Computer Engineering');
 
INSERT INTO events (title, event_date, venue)
VALUES ('Web Development Workshop', '2026-11-03', 'Computer Lab 1');
