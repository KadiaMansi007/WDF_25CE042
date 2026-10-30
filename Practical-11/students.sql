
CREATE DATABASE IF NOT EXISTS ce1_mansi;
USE ce1_mansi;

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    course VARCHAR(80) NOT NULL
);

INSERT INTO students (name, email, course) VALUES
('Aarav Shah', 'aarav@example.com', 'Computer Engineering'),
('Diya Mehta', 'diya@example.com', 'Computer Science'),
('Rohan Patel', 'rohan@example.com', 'Information Technology'),
('Priya Desai', 'priya@example.com', 'Computer Engineering'),
('Karan Shah', 'karan@example.com', 'Computer Science'),
('Neha Patel', 'neha@example.com', 'Information Technology'),
('Arjun Mehta', 'arjun@example.com', 'Computer Engineering'),
('Isha Shah', 'isha@example.com', 'Computer Science'),
('Dev Patel', 'dev@example.com', 'Information Technology'),
('Riya Desai', 'riya@example.com', 'Computer Engineering'),
('Krish Mehta', 'krish@example.com', 'Computer Science'),
('Anaya Patel', 'anaya@example.com', 'Information Technology'),
('Vivaan Shah', 'vivaan@example.com', 'Computer Engineering'),
('Sara Mehta', 'sara@example.com', 'Computer Science'),
('Yash Desai', 'yash@example.com', 'Information Technology'),
('Mira Shah', 'mira@example.com', 'Computer Engineering'),
('Aditya Patel', 'aditya@example.com', 'Computer Science'),
('Pooja Mehta', 'pooja@example.com', 'Information Technology'),
('Dhruv Shah', 'dhruv@example.com', 'Computer Engineering'),
('Nisha Desai', 'nisha@example.com', 'Computer Science')
ON DUPLICATE KEY UPDATE
name = VALUES(name),
course = VALUES(course);
