CREATE DATABASE library CHARACTER SET utf8mb4;
USE library;
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('user', 'admin') DEFAULT 'user',
  penalty_until DATE DEFAULT NULL
);
CREATE TABLE books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  author VARCHAR(100) NOT NULL,
  category VARCHAR(50),
  total_copies INT NOT NULL,
  available_copies INT NOT NULL,
  times_loaned INT DEFAULT 0
);
CREATE TABLE loans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  book_id INT NOT NULL,
  loan_date DATE NOT NULL,
  return_date DATE NOT NULL,
  returned BOOLEAN DEFAULT FALSE,

  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (book_id) REFERENCES books(id)
);
INSERT INTO users (name, email, password, role)
VALUES
('Admin', 'admin@library.com', 'admin', 'admin'),
('Juan', 'juan@mail.com', '1234', 'user'),
('Ana', 'ana@mail.com', '1234', 'user');
INSERT INTO books (title, author, category, total_copies, available_copies)
VALUES
('1984', 'George Orwell', 'Novela', 5, 5),
('El Quijote', 'Miguel de Cervantes', 'Clásico', 3, 3),
('Clean Code', 'Robert C. Martin', 'Programación', 2, 2);
