
CREATE DATABASE IF NOT EXISTS studenthub;
USE studenthub;

CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    event_date DATE NOT NULL,
    venue VARCHAR(120) NOT NULL,
    poster VARCHAR(255) DEFAULT NULL,
    status ENUM('Open', 'Closed') NOT NULL DEFAULT 'Open'
);

INSERT INTO events (title, event_date, venue, poster, status)
VALUES
('HTML Workshop', '2027-01-15', 'Computer Lab 1', NULL, 'Open'),
('Web Development Seminar', '2027-02-10', 'Seminar Hall', NULL, 'Open'),
('Coding Competition', '2027-03-05', 'Main Auditorium', NULL, 'Closed'),
('Project Exhibition', '2027-04-12', 'Exhibition Hall', NULL, 'Open'),
('Technical Quiz', '2027-05-20', 'Computer Lab 2', NULL, 'Open');
