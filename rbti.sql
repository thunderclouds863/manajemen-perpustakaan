CREATE DATABASE rbti;
USE rbti;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    nim VARCHAR(20) UNIQUE,
    status ENUM('pending', 'approved') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE visitor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE Book (
    bookID INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    author VARCHAR(100),
    genre VARCHAR(100),
    totalCopies INT,
    availableCopies INT,
    status ENUM('available', 'unavailable') DEFAULT 'available'
);


-- Borrow Table (dengan userID dan bookID)
CREATE TABLE Borrow (
    borrowID INT AUTO_INCREMENT PRIMARY KEY,
    userID INT,  -- Kolom userID ditambahkan
    bookID INT,  -- Kolom bookID ditambahkan
    borrowDate DATE,
    returnDate DATE,
    extend_count INT DEFAULT 0,
    status ENUM('borrow pending','borrowed','borrow rejected', 'return pending', 'returned', 'overdue','extend pending', 'extended', 'extend rejected') DEFAULT 'borrow pending',
    FOREIGN KEY (userID) REFERENCES users(id),
    FOREIGN KEY (bookID) REFERENCES Book(bookID)
);

CREATE TABLE Notification (
    notificationID INT AUTO_INCREMENT PRIMARY KEY,
    userID INT,
    message TEXT,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('read', 'unread') DEFAULT 'unread',
    FOREIGN KEY (userID) REFERENCES users(id)
);


-- Insert Admin Example
INSERT INTO admins (username, email, password)
VALUES ('Admin', 'admin@domain.com', '1234');
