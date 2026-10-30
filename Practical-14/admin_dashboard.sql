CREATE DATABASE IF NOT EXISTS studenthub;
USE studenthub;

DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS registrations;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','staff','student') NOT NULL DEFAULT 'student',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    name VARCHAR(100) NOT NULL,
    roll_no VARCHAR(30) NOT NULL UNIQUE,
    course VARCHAR(80) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    event_date DATE NOT NULL,
    venue VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    event_id INT NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(150) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO users (name, email, password, role, is_active) VALUES
('Admin User', 'admin@studenthub.com', '$2y$12$x4bv5tRANxZPaZt3LDBCnOcb6vgmwa71bcKtYWsW7IlSR878FN27y', 'admin', 1),
('Staff User', 'staff@studenthub.com', '$2y$12$QVDZX9MRtomXWwyHZA5/wuY7LPuDaBDMU9AFw1gbdcuTtxhascTjy', 'staff', 1),
('Riya Shah', 'student@studenthub.com', '$2y$12$cc2jY8dbiI8HSiIJDqBJVOOLA1QDvRohGSE99obClUinAzRurO996', 'student', 1),
('Inactive Student', 'inactive@studenthub.com', '$2y$12$cc2jY8dbiI8HSiIJDqBJVOOLA1QDvRohGSE99obClUinAzRurO996', 'student', 0);

INSERT INTO students (user_id, name, roll_no, course) VALUES
(3, 'Riya Shah', 'CE001', 'B.Tech Computer Engineering'),
(NULL, 'Amit Patel', 'CE002', 'B.Tech Computer Engineering'),
(NULL, 'Neha Mehta', 'IT001', 'B.Tech Information Technology'),
(NULL, 'Karan Joshi', 'IT002', 'B.Tech Information Technology'),
(4, 'Dev Trivedi', 'CE003', 'B.Tech Computer Engineering'),
(NULL, 'Pooja Desai', 'EC001', 'B.Tech Electronics');

INSERT INTO events (title, event_date, venue) VALUES
('Hackathon 2026', '2026-11-15', 'Seminar Hall'),
('Tech Quiz', '2026-11-22', 'Room 101'),
('Web Development Workshop', '2026-12-05', 'Computer Lab 2'),
('Annual Sports Day', '2026-12-18', 'College Ground');

INSERT INTO registrations (student_id, event_id, registered_at) VALUES
(1, 1, '2026-05-10 10:15:00'),
(2, 1, '2026-05-12 11:20:00'),
(3, 2, '2026-06-03 09:45:00'),
(4, 2, '2026-06-18 14:05:00'),
(1, 3, '2026-07-07 16:30:00'),
(5, 3, '2026-07-21 12:10:00'),
(6, 1, '2026-08-02 10:00:00'),
(2, 4, '2026-08-19 15:25:00'),
(3, 4, '2026-09-04 13:40:00'),
(4, 3, '2026-09-23 11:55:00'),
(1, 4, '2026-10-08 10:35:00'),
(6, 2, '2026-10-21 17:05:00');

INSERT INTO audit_logs (user_id, action) VALUES
(1, 'Database created with sample data');
