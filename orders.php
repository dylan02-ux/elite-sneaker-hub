<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'db.php';

// Handle status update (admin only) BEFORE any output
if (
    isset($_POST['order_id'], $_POST['status']) &&
    isset($_SESSION['role']) && $_SESSION['role'] === 'admin'
) {
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], $_POST['order_id']]);
    header("Location: orders.php?updated=1");
    exit;
}

require_once 'header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

// Admin can see all orders, users see only their own
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $stmt = $pdo->query("
        SELECT o.*, u.email, s.name AS shoe_name, s.brand, s.price, s.image_url
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN shoes s ON o.shoe_id = s.id
        ORDER BY o.order_date DESC
    ");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare("
        SELECT o.*, s.name AS shoe_name, s.brand, s.price, s.image_url
        FROM orders o
        JOIN shoes s ON o.shoe_id = s.id
        WHERE o.user_id = ?
        ORDER BY o.order_date DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<h1 class="mb-4"><i class="bi bi-bag-check"></i> Orders</h1>

<?php if (isset($_GET['updated'])): ?>
    <div class="alert alert-success">Order status updated.</div>
<?php endif; ?>

<?php if (empty($orders)): ?>
    <div class="alert alert-info">No orders found.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <th>User</th>
                    <?php endif; ?>
                    <th>Shoe</th>
                    <th>Brand</th>
                    <th>Image</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Order Date</th>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <th>Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <td><?=htmlspecialchars($order['email'])?></td>
                    <?php endif; ?>
                    <td><?=htmlspecialchars($order['shoe_name'])?></td>
                    <td><?=htmlspecialchars($order['brand'])?></td>
                    <td>
                        <?php if ($order['image_url']): ?>
                            <img src="<?=htmlspecialchars($order['image_url'])?>" alt="" style="width:60px;">
                        <?php else: ?>
                            <span class="text-muted">No Image</span>
                        <?php endif; ?>
                    </td>
                    <td>$<?=number_format($order['price'], 2)?></td>
                    <td><?=$order['quantity']?></td>
                    <td>$<?=number_format($order['price'] * $order['quantity'], 2)?></td>
                    <td>
                        <?php if (
                            isset($_SESSION['role']) && $_SESSION['role'] === 'admin'
                        ): ?>
                            <form method="post" class="d-flex align-items-center gap-2">
                                <input type="hidden" name="order_id" value="<?=$order['id']?>">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="pending" <?=$order['status']=='pending'?'selected':''?>>Pending</option>
                                    <option value="completed" <?=$order['status']=='completed'?'selected':''?>>Completed</option>
                                    <option value="cancelled" <?=$order['status']=='cancelled'?'selected':''?>>Cancelled</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">Update</button>
                            </form>
                        <?php else: ?>
                            <span class="badge 
                                <?= $order['status']=='completed'?'bg-success':($order['status']=='pending'?'bg-warning text-dark':'bg-danger') ?>">
                                <?=ucfirst($order['status'])?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td><?=date('Y-m-d H:i', strtotime($order['order_date']))?></td>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <td>
                            <!-- Optionally, add delete/cancel button here -->
                        </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
