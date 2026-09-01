<?php 
require_once 'header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT o.*, s.name, s.brand, s.price, s.image_url 
    FROM orders o 
    JOIN shoes s ON o.shoe_id = s.id 
    WHERE o.user_id = ? AND o.status = 'pending'
");
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll();

$total = 0;
?>

<h1 class="mb-4">Shopping Cart</h1>

<?php if (empty($cartItems)): ?>
    <div class="alert alert-info">Your cart is empty</div>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <?php foreach ($cartItems as $item): ?>
                <?php $subtotal = $item['price'] * $item['quantity']; ?>
                <?php $total += $subtotal; ?>
                <div class="row mb-3 align-items-center">
                    <div class="col-md-2">
                        <?php if($item['image_url']): ?>
                            <img src="<?=htmlspecialchars(ltrim($item['image_url'], '/'))?>" class="img-fluid">
                        <?php else: ?>
                            <div class="bg-secondary p-3 text-white text-center">No Image</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <h5><?=htmlspecialchars($item['name'])?></h5>
                        <p class="text-muted"><?=htmlspecialchars($item['brand'])?></p>
                    </div>
                    <div class="col-md-2">
                        $<?=number_format($item['price'], 2)?>
                    </div>
                    <div class="col-md-2">
                        <div class="input-group">
                            <button class="btn btn-outline-secondary" onclick="updateCart(<?=$item['id']?>, <?=$item['quantity']-1?>)">-</button>
                            <input type="text" class="form-control text-center" value="<?=$item['quantity']?>" readonly>
                            <button class="btn btn-outline-secondary" onclick="updateCart(<?=$item['id']?>, <?=$item['quantity']+1?>)">+</button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>$<?=number_format($subtotal, 2)?></span>
                            <button class="btn btn-danger btn-sm" onclick="removeFromCart(<?=$item['id']?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <hr>
            <div class="text-end">
                <h4>Total: $<?=number_format($total, 2)?></h4>
                <button class="btn btn-success" onclick="checkout()">
                    Proceed to Checkout
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
function updateCart(orderId, quantity) {
    if (quantity < 1) return;
    window.location.href = `crud.php?action=update_cart&id=${orderId}&quantity=${quantity}`;
}

function removeFromCart(orderId) {
    if (confirm('Remove this item from cart?')) {
        window.location.href = `crud.php?action=remove_from_cart&id=${orderId}`;
    }
}

function checkout() {
    if (confirm('Proceed to checkout?')) {
        window.location.href = 'crud.php?action=checkout';
    }
}
</script>

<?php require_once 'footer.php'; ?>
