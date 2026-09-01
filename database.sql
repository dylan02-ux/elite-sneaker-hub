CREATE DATABASE IF NOT EXISTS shoe_store;
USE shoe_store;

-- Drop existing tables in reverse order of dependencies
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS shoes;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- Create Tables
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'customer'
) ENGINE=InnoDB;

CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE shoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    brand VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    size DECIMAL(4,1) NOT NULL,
    stock INT DEFAULT 0,
    category_id INT,
    image_url VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(category_id) REFERENCES categories(id)
) ENGINE=InnoDB;

CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    shoe_id INT,
    quantity INT,
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50) DEFAULT 'pending',
    FOREIGN KEY(user_id) REFERENCES users(id),
    FOREIGN KEY(shoe_id) REFERENCES shoes(id)
) ENGINE=InnoDB;

-- Insert Sample Data
INSERT INTO users (email, password, role) VALUES 
('admin@gmail.com', 'admin123', 'admin'),
('customer@gmail.com', 'user123', 'customer'),
('john.doe@gmail.com', 'pass123', 'customer'),
('jane.smith@yahoo.com', 'pass123', 'customer'),
('mike.wilson@hotmail.com', 'pass123', 'customer'),
('sara.brown@gmail.com', 'pass123', 'customer'),
('david.lee@outlook.com', 'pass123', 'customer'),
('emma.taylor@gmail.com', 'pass123', 'customer'),
('alex.wang@yahoo.com', 'pass123', 'customer'),
('lisa.garcia@gmail.com', 'pass123', 'customer'),
('kevin.chen@hotmail.com', 'pass123', 'customer'),
('anna.lopez@gmail.com', 'pass123', 'customer'),
('chris.miller@yahoo.com', 'pass123', 'customer'),
('sophia.kim@gmail.com', 'pass123', 'customer'),
('ryan.davis@outlook.com', 'pass123', 'customer'),
('olivia.white@gmail.com', 'pass123', 'customer'),
('james.brown@yahoo.com', 'pass123', 'customer'),
('emily.jones@hotmail.com', 'pass123', 'customer'),
('daniel.wilson@gmail.com', 'pass123', 'customer'),
('maria.santos@yahoo.com', 'pass123', 'customer'),
('thomas.anderson@gmail.com', 'pass123', 'customer'),
('jessica.martinez@outlook.com', 'pass123', 'customer'),
('william.taylor@gmail.com', 'pass123', 'customer'),
('amanda.wright@yahoo.com', 'pass123', 'customer'),
('robert.king@hotmail.com', 'pass123', 'customer'),
('michelle.lee@gmail.com', 'pass123', 'customer'),
('steven.clark@yahoo.com', 'pass123', 'customer'),
('laura.rodriguez@gmail.com', 'pass123', 'customer'),
('brian.evans@outlook.com', 'pass123', 'customer'),
('rachel.nguyen@gmail.com', 'pass123', 'customer');

INSERT INTO categories (name) VALUES 
('Running'),
('Basketball'),
('Casual'),
('Sport'),
('Limited Edition'),
('Tennis'),
('Training'),
('Skateboarding'),
('Walking'),
('Cross Training'),
('Indoor Court'),
('Outdoor'),
('Lifestyle'),
('Performance'),
('Golf'),
('Soccer'),
('Baseball'),
('Volleyball'),
('Track & Field'),
('Trail Running'),
('Cycling'),
('Hiking'),
('Wrestling'),
('Boxing'),
('Dance'),
('Cheerleading'),
('Water Sports'),
('Gym'),
('Street Style'),
('Classic');

INSERT INTO shoes (name, brand, price, size, stock, category_id, image_url) VALUES 
('Air Jordan 1', 'Nike', 179.99, 10.0, 15, 2, '/qondra.png'),
('Ultra Boost 21', 'Adidas', 159.99, 9.5, 20, 1, '/qondra.png'),
('Chuck Taylor', 'Converse', 69.99, 8.5, 25, 3, '/qondra.png'),
('Lebron 18', 'Nike', 199.99, 11.0, 10, 2, '/qondra.png'),
('RS-X', 'Puma', 89.99, 9.0, 30, 4, '/qondra.png'),
('Air Max 270', 'Nike', 149.99, 8.0, 18, 1, '/qondra.png'),
('Superstar', 'Adidas', 84.99, 7.5, 22, 3, '/qondra.png'),
('Old Skool', 'Vans', 59.99, 9.0, 28, 8, '/qondra.png'),
('Gel-Kayano 27', 'ASICS', 159.99, 10.5, 12, 1, '/qondra.png'),
('Blazer Mid', 'Nike', 99.99, 8.5, 15, 13, '/qondra.png'),
('NMD R1', 'Adidas', 129.99, 9.5, 20, 13, '/qondra.png'),
('Rider FV', 'Puma', 79.99, 8.0, 25, 3, '/qondra.png'),
('Air Force 1', 'Nike', 109.99, 10.0, 30, 13, '/qondra.png'),
('Stan Smith', 'Adidas', 89.99, 9.0, 35, 3, '/qondra.png'),
('Classic Leather', 'Reebok', 74.99, 8.5, 18, 30, '/qondra.png'),
('Zoom Freak', 'Nike', 129.99, 11.0, 15, 2, '/qondra.png'),
('Continental 80', 'Adidas', 94.99, 9.5, 22, 13, '/qondra.png'),
('Suede Classic', 'Puma', 69.99, 8.0, 28, 3, '/qondra.png'),
('Air Zoom Pegasus', 'Nike', 119.99, 10.5, 20, 1, '/qondra.png'),
('Dame 7', 'Adidas', 139.99, 11.0, 12, 2, '/qondra.png'),
('Club C 85', 'Reebok', 79.99, 9.0, 25, 30, '/qondra.png'),
('Kyrie 7', 'Nike', 129.99, 10.0, 18, 2, '/qondra.png'),
('Ozweego', 'Adidas', 109.99, 8.5, 20, 13, '/qondra.png'),
('Future Rider', 'Puma', 84.99, 9.5, 30, 13, '/qondra.png'),
('Metcon 6', 'Nike', 129.99, 10.0, 15, 7, '/qondra.png'),
('UltraBoost DNA', 'Adidas', 179.99, 9.0, 18, 1, '/qondra.png'),
('Sky Dreamer', 'Puma', 109.99, 11.0, 20, 2, '/qondra.png'),
('Crater Impact', 'Nike', 89.99, 8.5, 25, 13, '/qondra.png'),
('ZX 750', 'Adidas', 99.99, 9.5, 22, 13, '/qondra.png'),
('Cell Alien', 'Puma', 94.99, 10.0, 28, 13, '/qondra.png');

INSERT INTO orders (user_id, shoe_id, quantity, status) VALUES 
(2, 1, 1, 'completed'),
(3, 2, 2, 'completed'),
(4, 3, 1, 'completed'),
(5, 4, 1, 'pending'),
(6, 5, 2, 'completed'),
(7, 6, 1, 'completed'),
(8, 7, 1, 'pending'),
(9, 8, 3, 'completed'),
(10, 9, 1, 'completed'),
(11, 10, 2, 'pending'),
(12, 11, 1, 'completed'),
(13, 12, 1, 'completed'),
(14, 13, 2, 'pending'),
(15, 14, 1, 'completed'),
(16, 15, 1, 'completed'),
(17, 16, 2, 'pending'),
(18, 17, 1, 'completed'),
(19, 18, 1, 'completed'),
(20, 19, 3, 'pending'),
(21, 20, 1, 'completed'),
(22, 21, 2, 'completed'),
(23, 22, 1, 'pending'),
(24, 23, 1, 'completed'),
(25, 24, 2, 'completed'),
(26, 25, 1, 'pending'),
(27, 26, 1, 'completed'),
(28, 27, 2, 'completed'),
(29, 28, 1, 'pending'),
(30, 29, 1, 'completed'),
(2, 30, 2, 'completed');

-- Update all shoes to have the same image
UPDATE shoes SET image_url = '/qondra.png';

-- Create Indexes
CREATE INDEX idx_shoes_category ON shoes(category_id);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_shoe ON orders(shoe_id);
