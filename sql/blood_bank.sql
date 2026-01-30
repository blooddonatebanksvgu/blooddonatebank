-- Blood Bank Management System Database
-- Complete SQL Schema

-- Create Database
CREATE DATABASE IF NOT EXISTS blood_bank_db;
USE blood_bank_db;

-- Drop existing tables if they exist (for fresh install)
DROP TABLE IF EXISTS feedback;
DROP TABLE IF EXISTS blood_request;
DROP TABLE IF EXISTS donation;
DROP TABLE IF EXISTS blood_stock;
DROP TABLE IF EXISTS patient;
DROP TABLE IF EXISTS donor;
DROP TABLE IF EXISTS blood_bank;
DROP TABLE IF EXISTS location;
DROP TABLE IF EXISTS city;
DROP TABLE IF EXISTS state;
DROP TABLE IF EXISTS blood_group;
DROP TABLE IF EXISTS users;

-- Users Table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    role ENUM('admin', 'bloodbank', 'donor', 'patient') NOT NULL,
    status ENUM('active', 'inactive', 'pending') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- State Table
CREATE TABLE state (
    id INT PRIMARY KEY AUTO_INCREMENT,
    state_name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- City Table
CREATE TABLE city (
    id INT PRIMARY KEY AUTO_INCREMENT,
    state_id INT NOT NULL,
    city_name VARCHAR(100) NOT NULL,
    FOREIGN KEY (state_id) REFERENCES state(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Location Table
CREATE TABLE location (
    id INT PRIMARY KEY AUTO_INCREMENT,
    city_id INT NOT NULL,
    location_name VARCHAR(150) NOT NULL,
    FOREIGN KEY (city_id) REFERENCES city(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Blood Group Table
CREATE TABLE blood_group (
    id INT PRIMARY KEY AUTO_INCREMENT,
    group_name VARCHAR(10) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Blood Bank Table
CREATE TABLE blood_bank (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    name VARCHAR(150) NOT NULL,
    address VARCHAR(255),
    state_id INT,
    city_id INT,
    location_id INT,
    phone VARCHAR(15),
    email VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (state_id) REFERENCES state(id) ON DELETE SET NULL,
    FOREIGN KEY (city_id) REFERENCES city(id) ON DELETE SET NULL,
    FOREIGN KEY (location_id) REFERENCES location(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Donor Table
CREATE TABLE donor (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    blood_group_id INT NOT NULL,
    age INT,
    gender ENUM('Male', 'Female', 'Other'),
    address VARCHAR(255),
    last_donation_date DATE,
    total_donations INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (blood_group_id) REFERENCES blood_group(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Patient Table
CREATE TABLE patient (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    blood_group_id INT,
    age INT,
    gender ENUM('Male', 'Female', 'Other'),
    address VARCHAR(255),
    disease VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (blood_group_id) REFERENCES blood_group(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Donation Table
CREATE TABLE donation (
    id INT PRIMARY KEY AUTO_INCREMENT,
    donor_id INT NOT NULL,
    blood_bank_id INT,
    blood_group_id INT NOT NULL,
    quantity_ml INT NOT NULL DEFAULT 450,
    donation_date DATE NOT NULL,
    notes TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES donor(id) ON DELETE CASCADE,
    FOREIGN KEY (blood_bank_id) REFERENCES blood_bank(id) ON DELETE SET NULL,
    FOREIGN KEY (blood_group_id) REFERENCES blood_group(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Blood Request Table
CREATE TABLE blood_request (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    blood_bank_id INT,
    blood_group_id INT NOT NULL,
    quantity_ml INT NOT NULL,
    request_date DATE NOT NULL,
    required_date DATE,
    reason TEXT,
    urgency ENUM('normal', 'urgent', 'emergency') DEFAULT 'normal',
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patient(id) ON DELETE CASCADE,
    FOREIGN KEY (blood_bank_id) REFERENCES blood_bank(id) ON DELETE SET NULL,
    FOREIGN KEY (blood_group_id) REFERENCES blood_group(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Blood Stock Table
CREATE TABLE blood_stock (
    id INT PRIMARY KEY AUTO_INCREMENT,
    blood_bank_id INT,
    blood_group_id INT NOT NULL,
    quantity_ml INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (blood_bank_id) REFERENCES blood_bank(id) ON DELETE SET NULL,
    FOREIGN KEY (blood_group_id) REFERENCES blood_group(id) ON DELETE CASCADE,
    UNIQUE KEY unique_stock (blood_bank_id, blood_group_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Feedback Table
CREATE TABLE feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    status ENUM('unread', 'read') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Blood Groups
INSERT INTO blood_group (group_name) VALUES 
('A+'), ('A-'), ('B+'), ('B-'), ('O+'), ('O-'), ('AB+'), ('AB-');

-- Insert States (Sample Indian States)
INSERT INTO state (state_name) VALUES 
('Maharashtra'), ('Gujarat'), ('Karnataka'), ('Tamil Nadu'), ('Delhi');

-- Insert Cities
INSERT INTO city (state_id, city_name) VALUES 
(1, 'Mumbai'), (1, 'Pune'), (1, 'Nagpur'),
(2, 'Ahmedabad'), (2, 'Surat'), (2, 'Vadodara'),
(3, 'Bangalore'), (3, 'Mysore'),
(4, 'Chennai'), (4, 'Coimbatore'),
(5, 'New Delhi');

-- Insert Locations
INSERT INTO location (city_id, location_name) VALUES 
(1, 'Andheri'), (1, 'Bandra'), (1, 'Dadar'),
(2, 'Kothrud'), (2, 'Shivaji Nagar'),
(4, 'CG Road'), (4, 'Satellite'),
(7, 'Koramangala'), (7, 'Whitefield'),
(9, 'T Nagar'), (9, 'Anna Nagar'),
(11, 'Connaught Place'), (11, 'Saket');

-- Insert Default Admin User (password: admin123)
INSERT INTO users (name, email, password, phone, role, status) VALUES 
('System Admin', 'admin@bloodbank.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9999999999', 'admin', 'active');

-- Insert Sample Blood Bank Users (password: bloodbank123)
INSERT INTO users (name, email, password, phone, role, status) VALUES 
('City Blood Bank', 'citybloodbank@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543210', 'bloodbank', 'active'),
('Central Blood Bank', 'centralbb@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543211', 'bloodbank', 'active');

-- Insert Blood Banks
INSERT INTO blood_bank (user_id, name, address, state_id, city_id, location_id, phone, email, status) VALUES 
(2, 'City Blood Bank', 'Main Road, Andheri West', 1, 1, 1, '9876543210', 'citybloodbank@gmail.com', 'active'),
(3, 'Central Blood Bank', 'CG Road, Ahmedabad', 2, 4, 6, '9876543211', 'centralbb@gmail.com', 'active');

-- Insert Sample Donors (password: admin123)
INSERT INTO users (name, email, password, phone, role, status) VALUES 
('Rahul Sharma', 'rahul@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9123456789', 'donor', 'active'),
('Priya Patel', 'priya@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9123456790', 'donor', 'active');

INSERT INTO donor (user_id, blood_group_id, age, gender, address, last_donation_date, total_donations) VALUES 
(4, 1, 28, 'Male', 'Andheri, Mumbai', '2024-12-15', 5),
(5, 3, 25, 'Female', 'Kothrud, Pune', '2024-11-20', 3);

-- Insert Sample Patients (password: admin123)
INSERT INTO users (name, email, password, phone, role, status) VALUES 
('Amit Kumar', 'amit@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9234567890', 'patient', 'active'),
('Sneha Gupta', 'sneha@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9234567891', 'patient', 'active');

INSERT INTO patient (user_id, blood_group_id, age, gender, address, disease) VALUES 
(6, 5, 35, 'Male', 'Bandra, Mumbai', 'Anemia'),
(7, 1, 30, 'Female', 'Satellite, Ahmedabad', 'Blood Loss Surgery');

-- Insert Initial Blood Stock
INSERT INTO blood_stock (blood_bank_id, blood_group_id, quantity_ml) VALUES 
(1, 1, 5000), (1, 2, 2000), (1, 3, 4500), (1, 4, 1500),
(1, 5, 8000), (1, 6, 3000), (1, 7, 2500), (1, 8, 1000),
(2, 1, 4000), (2, 2, 1500), (2, 3, 3500), (2, 4, 1200),
(2, 5, 7000), (2, 6, 2500), (2, 7, 2000), (2, 8, 800);

-- Insert Sample Donations
INSERT INTO donation (donor_id, blood_bank_id, blood_group_id, quantity_ml, donation_date, status) VALUES 
(1, 1, 1, 450, '2024-12-15', 'approved'),
(1, 1, 1, 450, '2024-09-10', 'approved'),
(2, 1, 3, 450, '2024-11-20', 'approved'),
(2, 2, 3, 450, '2024-08-15', 'approved');

-- Insert Sample Blood Requests
INSERT INTO blood_request (patient_id, blood_bank_id, blood_group_id, quantity_ml, request_date, required_date, reason, status) VALUES 
(1, 1, 5, 900, '2025-01-25', '2025-01-28', 'Emergency Surgery', 'approved'),
(2, 2, 1, 450, '2025-01-28', '2025-01-30', 'Scheduled Surgery', 'pending');

-- Insert Sample Feedback
INSERT INTO feedback (name, email, subject, message, status) VALUES 
('John Doe', 'john@example.com', 'Great Service', 'The blood bank staff were very helpful and professional.', 'read'),
('Jane Smith', 'jane@example.com', 'Suggestion', 'It would be great to have online appointment booking.', 'unread');

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_donation_status ON donation(status);
CREATE INDEX idx_blood_request_status ON blood_request(status);
CREATE INDEX idx_donor_blood_group ON donor(blood_group_id);
CREATE INDEX idx_patient_blood_group ON patient(blood_group_id);
