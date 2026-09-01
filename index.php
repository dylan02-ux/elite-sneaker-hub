<?php 
require_once 'header.php';

// Pagination settings
$items_per_page = 9;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Build query based on filters
$where = [];
$params = [];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where[] = "(s.name LIKE ? OR b.name LIKE ?)";
    $params[] = "%{$_GET['search']}%";
    $params[] = "%{$_GET['search']}%";
}

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $where[] = "s.category_id = ?";
    $params[] = $_GET['category'];
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Sort options
$sort = $_GET['sort'] ?? 'newest';
$order_by = match($sort) {
    'price_low' => 'ORDER BY s.price ASC',
    'price_high' => 'ORDER BY s.price DESC',
    'newest' => 'ORDER BY s.id DESC',
    default => 'ORDER BY s.id DESC'
};

// Get total count for pagination
$count_query = "SELECT COUNT(*) FROM shoes s LEFT JOIN brands b ON s.brand_id = b.id $where_clause";
$total_items = $pdo->prepare($count_query);
$total_items->execute($params);
$total_items = $total_items->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// Get shoes with pagination
$query = "SELECT s.*, c.name as category_name, b.name as brand_name 
          FROM shoes s 
          LEFT JOIN categories c ON s.category_id = c.id
          LEFT JOIN brands b ON s.brand_id = b.id
          $where_clause
          $order_by 
          LIMIT $items_per_page OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$shoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="mb-4">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1>🔥 Latest Sneakers</h1>
        </div>
        <div class="col-md-6">
            <div class="d-flex justify-content-end">
                <select class="form-select w-auto" onchange="window.location.href='?sort='+this.value">
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                    <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <?php foreach ($shoes as $shoe): ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card sneaker-card h-100">
                <div class="bg-dark text-white p-3 text-center">
                    <h5><?=htmlspecialchars($shoe['name'])?></h5>
                </div>
                <img src="shoe-default.avif" class="card-img-top p-3" alt="<?=htmlspecialchars($shoe['name'])?>">
                <div class="card-body">
                    <h6 class="text-muted"><?=htmlspecialchars($shoe['brand_name'])?></h6>
                    <p class="text-muted">Category: <?=htmlspecialchars($shoe['category_name'])?></p>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <span class="h4 mb-0">$<?=number_format($shoe['price'],2)?></span>
                        <span class="badge bg-secondary">Size: US <?=$shoe['size']?></span>
                    </div>
                    <p class="mt-2 mb-0">
                        <span class="badge <?= $shoe['stock'] > 0 ? 'bg-success' : 'bg-danger' ?>">
                            <?= $shoe['stock'] > 0 ? 'In Stock ('.$shoe['stock'].')' : 'Out of Stock' ?>
                        </span>
                    </p>
                </div>
                <div class="card-footer bg-white">
                    <?php if($shoe['stock'] > 0 && isset($_SESSION['user_id'])): ?>
                        <a href="crud.php?action=add_to_cart&shoe_id=<?=$shoe['id']?>" class="btn btn-dark w-100">
                            <i class="bi bi-cart-plus"></i> Add to Cart
                        </a>
                    <?php elseif(!isset($_SESSION['user_id'])): ?>
                        <a href="auth.php" class="btn btn-outline-dark w-100">
                            Login to Purchase
                        </a>
                    <?php else: ?>
                        <button class="btn btn-secondary w-100" disabled>
                            Out of Stock
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if($total_pages > 1): ?>
<nav aria-label="Page navigation" class="mt-4">
    <ul class="pagination justify-content-center">
        <?php for($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?=$i?><?= isset($_GET['sort']) ? '&sort='.$_GET['sort'] : '' ?>">
                    <?=$i?>
                </a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<?php require_once 'footer.php'; ?>