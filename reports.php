<?php
require_once 'header.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: auth.php');
    exit;
}

// Get inventory stats
$inventoryStats = $pdo->query("SELECT 
    COUNT(*) as total_shoes,
    SUM(stock) as total_stock,
    AVG(price) as avg_price,
    MIN(price) as min_price,
    MAX(price) as max_price
FROM shoes")->fetch();

// Get top selling shoes
$topSellers = $pdo->query("SELECT 
    s.name, s.brand, 
    COUNT(o.id) as total_orders,
    SUM(o.quantity) as total_sold
FROM shoes s
LEFT JOIN orders o ON s.id = o.shoe_id
GROUP BY s.id
ORDER BY total_sold DESC
LIMIT 5")->fetchAll();

// Get low stock alerts
$lowStock = $pdo->query("SELECT * FROM shoes WHERE stock < 5")->fetchAll();
?>

<h1 class="mb-4">📊 Reports Dashboard</h1>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5>Total Shoes</h5>
                <h2><?=$inventoryStats['total_shoes']?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5>Total Stock</h5>
                <h2><?=$inventoryStats['total_stock']?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5>Average Price</h5>
                <h2>$<?=number_format($inventoryStats['avg_price'], 2)?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Top Selling Shoes</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Shoe</th>
                            <th>Orders</th>
                            <th>Units Sold</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($topSellers as $shoe): ?>
                        <tr>
                            <td><?=htmlspecialchars($shoe['name'])?></td>
                            <td><?=$shoe['total_orders'] ?? 0?></td>
                            <td><?=$shoe['total_sold'] ?? 0?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">Low Stock Alert</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Shoe</th>
                            <th>Brand</th>
                            <th>Current Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($lowStock as $shoe): ?>
                        <tr>
                            <td><?=htmlspecialchars($shoe['name'])?></td>
                            <td><?=htmlspecialchars($shoe['brand'])?></td>
                            <td><?=$shoe['stock']?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>
