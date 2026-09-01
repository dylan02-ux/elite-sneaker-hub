<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: auth.php');
    exit;
}

require_once 'header.php';

$brands = $pdo->query("SELECT * FROM brands")->fetchAll();
$shoes = $pdo->query("SELECT s.*, c.name as category_name, b.name as brand_name 
                      FROM shoes s 
                      LEFT JOIN categories c ON s.category_id = c.id
                      LEFT JOIN brands b ON s.brand_id = b.id")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-grid"></i> Inventory Dashboard</h1>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="bi bi-plus-circle"></i> Add Shoe
    </button>
</div>

<!-- Brand Management Section -->
<div class="card mb-4">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0">Manage Brands</h5>
    </div>
    <div class="card-body">
        <form class="row g-2 mb-3" method="post" action="crud.php?action=add_brand">
            <div class="col-auto">
                <input type="text" name="name" class="form-control" placeholder="New Brand Name" required>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-success">Add Brand</button>
            </div>
        </form>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Brand</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($brands as $brand): ?>
                <tr>
                    <td><?=htmlspecialchars($brand['name'])?></td>
                    <td>
                        <form method="post" action="crud.php?action=update_brand" class="d-inline">
                            <input type="hidden" name="id" value="<?=$brand['id']?>">
                            <input type="text" name="name" value="<?=htmlspecialchars($brand['name'])?>" required>
                            <button class="btn btn-sm btn-primary" type="submit">Update</button>
                        </form>
                        <a href="crud.php?action=delete_brand&id=<?=$brand['id']?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this brand?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <table class="table table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Name</th>
                    <th>Brand</th>
                    <th>Price</th>
                    <th>Size</th>
                    <th>Stock</th>
                    <th>Category</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($shoes as $shoe): ?>
                <tr>
                    <td><?=htmlspecialchars($shoe['name'])?></td>
                    <td><?=htmlspecialchars($shoe['brand_name'])?></td>
                    <td>$<?=number_format($shoe['price'], 2)?></td>
                    <td><?=$shoe['size']?></td>
                    <td><?=$shoe['stock']?></td>
                    <td><?=htmlspecialchars($shoe['category_name'])?></td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="editShoe(<?=$shoe['id']?>)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <a href="crud.php?action=delete_shoe&id=<?=$shoe['id']?>" 
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Are you sure?')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Shoe Modal -->
<div class="modal fade" id="addModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="crud.php?action=add_shoe" method="POST">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Add New Shoe</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Brand</label>
                        <select name="brand_id" class="form-control" required>
                            <option value="">Select Brand</option>
                            <?php foreach($brands as $brand): ?>
                                <option value="<?=$brand['id']?>"><?=htmlspecialchars($brand['name'])?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Price</label>
                        <input type="number" step="0.01" name="price" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Size</label>
                        <input type="number" step="0.5" name="size" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Stock</label>
                        <input type="number" name="stock" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Category</label>
                        <select name="category_id" class="form-control" required>
                            <?php foreach($categories as $category): ?>
                                <option value="<?=$category['id']?>"><?=htmlspecialchars($category['name'])?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save Shoe</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>