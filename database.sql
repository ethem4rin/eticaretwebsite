-- Élégance E-Commerce Database Schema
-- Run: mysql -u root -p < database.sql

CREATE DATABASE IF NOT EXISTS eticaretdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE eticaretdb;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    address TEXT,
    role ENUM('admin','user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(500) DEFAULT '',
    stock_quantity INT DEFAULT 0,
    category_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cart table
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_cart_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('Pending','Shipped','Delivered') DEFAULT 'Pending',
    shipping_name VARCHAR(150),
    shipping_email VARCHAR(150),
    shipping_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price_at_purchase DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_orderitem_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_orderitem_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================
-- SAMPLE DATA
-- =============================================

-- Admin user (password: Admin123!)
-- Hash generated with: password_hash('Admin123!', PASSWORD_DEFAULT)
INSERT INTO users (full_name, email, password, address, role) VALUES
('Admin User', 'admin@example.com', '$2y$10$f9fZGCVNC1pRCDbbZFbFMut9zeaJyj1ZvItXPokP72DGlKt3WmixK', '123 Admin Street, City', 'admin');

-- Categories
INSERT INTO categories (category_name) VALUES
('Men'),
('Women'),
('Accessories');

-- Products
INSERT INTO products (name, description, price, image_url, stock_quantity, category_id) VALUES
('Merino Wool Blazer', 'A finely tailored blazer crafted from 100% merino wool. Perfect for both formal and smart-casual occasions. Features a slim cut with structured shoulders and a two-button closure.', 289.00, '', 15, 1),
('Cashmere Turtleneck', 'Luxuriously soft cashmere turtleneck in a relaxed silhouette. Made from Grade A Mongolian cashmere for unparalleled warmth and comfort.', 195.00, '', 20, 1),
('Tailored Slim Trousers', 'Precision-cut slim trousers in a premium wool-blend fabric. Featuring a flat-front design with side-seam pockets and a clean hem.', 165.00, '', 18, 1),
('Silk Evening Gown', 'An ethereal floor-length gown in pure silk charmeuse. Features a draped neckline, bias-cut skirt for fluid movement, and a subtle slit. Ideal for black tie events.', 450.00, '', 8, 2),
('Linen Wrap Dress', 'Effortlessly chic wrap dress in 100% European linen. The relaxed silhouette is cinched at the waist with a self-tie belt. Transitions seamlessly from day to evening.', 225.00, '', 14, 2),
('Wool Crepe Blazer', 'A structured blazer in luxurious wool crepe. Features notched lapels, a single-button closure, and welt pockets. A wardrobe cornerstone.', 310.00, '', 10, 2),
('Leather Crossbody Bag', 'Artisan-crafted in full-grain Italian leather with a supple, aged patina. Features a top zip closure, interior card slots, and an adjustable shoulder strap.', 175.00, '', 25, 3),
('Silk Pocket Square', 'Hand-rolled silk pocket square in a refined geometric print. Made from pure mulberry silk for a lustrous finish. Elevates any formal look.', 65.00, '', 40, 3),
('Suede Chelsea Boots', 'Hand-stitched suede Chelsea boots with an elastic gore panel for easy slip-on wear. Features a leather-lined interior and a stacked heel for subtle elevation.', 340.00, '', 12, 3);
