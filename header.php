<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'db.php';

// Fetch categories for dropdown with shoe count
$categories = $pdo->query("
    SELECT c.*, COUNT(s.id) as shoe_count
    FROM categories c
    LEFT JOIN shoes s ON s.category_id = c.id
    GROUP BY c.id
")->fetchAll();

// Get cart count if user is logged in
$cartCount = 0;
if(isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status = 'pending'");
    $stmt->execute([$_SESSION['user_id']]);
    $cartCount = $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Elite Sneaker Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sneaker-card { transition: all 0.3s; border-radius: 15px; overflow: hidden; }
        .sneaker-card:hover { box-shadow: 0 10px 20px rgba(0,0,0,0.1); transform: translateY(-5px); }
        .navbar-dark .nav-link { color: rgba(255,255,255,.8) !important; }
        .navbar-dark .nav-link:hover { color: white !important; }
        .search-form { width: 300px; }
        @media (max-width: 768px) { .search-form { width: 100%; } }
        .cart-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 0.7em;
            padding: 2px 5px;
            border-radius: 50%;
            background: red;
        }
        /* Advanced categories dropdown styles */
        .dropdown-categories {
            max-height: 350px;
            overflow-y: auto;
            min-width: 250px;
        }
        .dropdown-categories .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .dropdown-categories .cat-icon {
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #f1f1f1;
            border-radius: 50%;
            font-size: 1.1em;
        }
        .dropdown-categories .badge {
            margin-left: auto;
        }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-black mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="bi bi-lightning-charge"></i> SNEAKERHUB
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-list-ul"></i> Categories
                    </a>
                    <ul class="dropdown-menu dropdown-categories shadow">
                        <?php foreach($categories as $category): ?>
                            <li>
                                <a class="dropdown-item" href="index.php?category=<?=$category['id']?>">
                                    <span class="cat-icon bg-light text-dark">
                                        <i class="bi bi-tag"></i>
                                    </span>
                                    <?=htmlspecialchars($category['name'])?>
                                    <span class="badge bg-secondary"><?=$category['shoe_count']?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">Reports</a>
                    </li>
                <?php endif; ?>
            </ul>

            <form class="d-flex search-form me-3" action="index.php" method="GET">
                <div class="input-group">
                    <input class="form-control" type="search" name="search" placeholder="Search shoes...">
                    <button class="btn btn-outline-light" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>

            <ul class="navbar-nav">
                <?php if(isset($_SESSION['email'])): ?>
                    <li class="nav-item me-3">
                        <a class="nav-link position-relative" href="cart.php">
                            <i class="bi bi-cart3"></i>
                            <?php if($cartCount > 0): ?>
                                <span class="cart-badge"><?=$cartCount?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?=htmlspecialchars($_SESSION['email'])?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="orders.php">My Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="auth.php?logout">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="auth.php">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<div class="container">