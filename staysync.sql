-- Active: 1768916402106@@127.0.0.1@3307@autofix
CREATE DATABASE IF NOT EXISTS staysync;

USE staysync;

CREATE TABLE IF NOT EXISTS user(
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(20),
    email VARCHAR(50),
    phone VARCHAR(20),
    password VARCHAR(20)
);

CREATE TABLE IF NOT EXISTS booking(
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100),
    email VARCHAR(150),
    phone VARCHAR(15),
    age INT,
    room INT,
    checkin DATE,
    checkout DATE,
    adult VARCHAR(30),
    children VARCHAR(30),
    special TEXT,
    total_amount INT,
    booking_status VARCHAR(20) DEFAULT 'Pending',
    payment_method VARCHAR(20) DEFAULT NULL,
    payment_status VARCHAR(20) DEFAULT "Pending",
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(100) NOT NULL,
    room_type VARCHAR(100),
    rating INT NOT NULL,
    comment TEXT NOT NULL,
    status ENUM('pending', 'approved') DEFAULT 'approved', -- Set to 'approved' for immediate display
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_name VARCHAR(100) NOT NULL,
    room_type VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    total_rooms INT NOT NULL,
    description TEXT,
    image VARCHAR(255),
    status ENUM('Available','Unavailable') DEFAULT 'Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO rooms(room_name, room_type, price, total_rooms)
VALUES
("Standard Room", "Non A.C", 400, 5),
("Deluxe Room", "A.C", 700, 3),
("Premium Suite", "Luxury", 2000, 2);

CREATE TABLE IF NOT EXISTS transactions(
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(50) DEFAULT NULL,
    email VARCHAR(50) DEFAULT NULL,
    room_name VARCHAR(20) DEFAULT NULL,
    room_price FLOAT(10,2) DEFAULT NULL,
    room_price_currency VARCHAR(50) DEFAULT NULL,
    paid_amount FLOAT(10,2) NOT NULL,
    paid_amount_currency VARCHAR(50) NOT NULL,
    txn_id VARCHAR(50) NOT NULL,
    payment_status VARCHAR(50) NOT NULL,
    created DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS admin(
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(50) NOT NULL,
    password VARCHAR(20) NOT NULL
);

INSERT INTO admin
(email, password)
VALUES
("staysync@gmail.com", "staysync");

SELECT * FROM admin
WHERE email = "staysync@gmail.com" AND password = "staysync";