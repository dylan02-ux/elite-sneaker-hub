<?php
require_once 'db.php';
session_start();

$action = $_GET['action'] ?? '';

// Helper to require admin
function require_admin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        die('Unauthorized access');
    }
}

switch($action) {
    // --- SHOE CRUD (admin only) ---
    case 'add_shoe':
        require_admin();
        $stmt = $pdo->prepare("INSERT INTO shoes (name, brand_id, price, size, stock, category_id) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['name'],
            $_POST['brand_id'],
            $_POST['price'],
            $_POST['size'],
            $_POST['stock'],
            $_POST['category_id']
        ]);
        header("Location: dashboard.php?success=added");
        break;

    case 'update_shoe':
        require_admin();
        $stmt = $pdo->prepare("UPDATE shoes 
                              SET name=?, brand_id=?, price=?, size=?, stock=?, category_id=? 
                              WHERE id=?");
        $stmt->execute([
            $_POST['name'],
            $_POST['brand_id'],
            $_POST['price'],
            $_POST['size'],
            $_POST['stock'],
            $_POST['category_id'],
            $_POST['id']
        ]);
        header("Location: dashboard.php?success=updated");
        break;

    case 'delete_shoe':
        require_admin();
        // First, delete all orders referencing this shoe
        $stmt = $pdo->prepare("DELETE FROM orders WHERE shoe_id = ?");
        $stmt->execute([$_GET['id']]);
        // Then, delete the shoe
        $stmt = $pdo->prepare("DELETE FROM shoes WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        header("Location: dashboard.php?success=deleted");
        break;

    // --- CART ACTIONS (any logged-in user) ---
    case 'add_to_cart':
        if (!isset($_SESSION['user_id'])) {
            die('Please login first');
        }
        
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, shoe_id, quantity, status) VALUES (?, ?, 1, 'pending')");
        $stmt->execute([$_SESSION['user_id'], $_GET['shoe_id']]);
        header("Location: cart.php");
        break;

    case 'update_cart':
        if (!isset($_SESSION['user_id'])) {
            die('Please login first');
        }
        $stmt = $pdo->prepare("UPDATE orders SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$_GET['quantity'], $_GET['id'], $_SESSION['user_id']]);
        header("Location: cart.php");
        break;

    case 'remove_from_cart':
        if (!isset($_SESSION['user_id'])) {
            die('Please login first');
        }
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
        header("Location: cart.php");
        break;

    case 'checkout':
        if (!isset($_SESSION['user_id'])) {
            die('Please login first');
        }
        $stmt = $pdo->prepare("UPDATE orders SET status = 'completed' WHERE user_id = ? AND status = 'pending'");
        $stmt->execute([$_SESSION['user_id']]);
        header("Location: orders.php?success=1");
        break;

    // --- BRAND CRUD (admin only) ---
    case 'add_brand':
        require_admin();
        $stmt = $pdo->prepare("INSERT INTO brands (name) VALUES (?)");
        $stmt->execute([$_POST['name']]);
        header("Location: dashboard.php?success=brand_added");
        break;

    case 'update_brand':
        require_admin();
        $stmt = $pdo->prepare("UPDATE brands SET name=? WHERE id=?");
        $stmt->execute([$_POST['name'], $_POST['id']]);
        header("Location: dashboard.php?success=brand_updated");
        break;

    case 'delete_brand':
        require_admin();
        // Optionally, set brand_id to NULL for shoes with this brand
        $stmt = $pdo->prepare("UPDATE shoes SET brand_id=NULL WHERE brand_id=?");
        $stmt->execute([$_GET['id']]);
        $stmt = $pdo->prepare("DELETE FROM brands WHERE id=?");
        $stmt->execute([$_GET['id']]);
        header("Location: dashboard.php?success=brand_deleted");
        break;

    default:
        header("Location: dashboard.php");
        break;
}