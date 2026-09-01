<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=shoe_store', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if tables exist, if not create them
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('users', $tables)) {
        // Create users table
        $pdo->exec("CREATE TABLE users (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'customer') DEFAULT 'customer'
        )");
        
        // Insert admin and customer users with hashed passwords
        $stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
        $stmt->execute(['admin@gmail.com', password_hash('admin123', PASSWORD_DEFAULT), 'admin']);
        $stmt->execute(['customer@gmail.com', password_hash('user123', PASSWORD_DEFAULT), 'customer']);
    } else {
        // Ensure admin and customer users exist and have hashed passwords
        // Check if admin exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute(['admin@gmail.com']);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$admin) {
            $stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
            $stmt->execute(['admin@gmail.com', password_hash('admin123', PASSWORD_DEFAULT), 'admin']);
        } elseif (!password_verify('admin123', $admin['password'])) {
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([password_hash('admin123', PASSWORD_DEFAULT), 'admin@gmail.com']);
        }

        // Check if customer exists
        $stmt_cust = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt_cust->execute(['customer@gmail.com']);
        $customer = $stmt_cust->fetch(PDO::FETCH_ASSOC);
        if (!$customer) {
            $stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
            $stmt->execute(['customer@gmail.com', password_hash('user123', PASSWORD_DEFAULT), 'customer']);
        } elseif (!password_verify('user123', $customer['password'])) {
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([password_hash('user123', PASSWORD_DEFAULT), 'customer@gmail.com']);
        }
    }
    
    if (!in_array('brands', $tables)) {
        $pdo->exec("CREATE TABLE brands (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL UNIQUE
        )");
        $pdo->exec("INSERT INTO brands (name) VALUES ('Nike'), ('Adidas'), ('Puma')");
    }

    if (!in_array('shoes', $tables)) {
        $pdo->exec("CREATE TABLE shoes (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            brand_id INT(11),
            price DECIMAL(10, 2) NOT NULL,
            size DECIMAL(10, 2) NOT NULL,
            stock INT(11) DEFAULT 0,
            category_id INT(11),
            image_url VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (brand_id) REFERENCES brands(id)
        )");
    } else {
        // Add brand_id column if not exists
        $columns = $pdo->query("SHOW COLUMNS FROM shoes")->fetchAll(PDO::FETCH_COLUMN, 0);
        if (!in_array('brand_id', $columns)) {
            $pdo->exec("ALTER TABLE shoes ADD COLUMN brand_id INT(11) AFTER name");
            $pdo->exec("ALTER TABLE shoes ADD FOREIGN KEY (brand_id) REFERENCES brands(id)");
        }
    }

    if (!in_array('categories', $tables)) {
        $pdo->exec("CREATE TABLE categories (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL UNIQUE
        )");
        
        $pdo->exec("INSERT INTO categories (name) VALUES ('Running'), ('Basketball'), ('Casual')");
    }

    if (!in_array('orders', $tables)) {
        $pdo->exec("CREATE TABLE orders (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            user_id INT(11),
            shoe_id INT(11),
            quantity INT(11),
            order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY(user_id) REFERENCES users(id),
            FOREIGN KEY(shoe_id) REFERENCES shoes(id)
        )");
    }
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

//Admin: admin@gmail.com / admin123
//Customer: customer@gmail.com / user123