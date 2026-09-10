<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| Staff Access Only
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header('Location: auth.php');
    exit;
}


require_once 'header.php';


/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

$items_per_page = 9;

$page = isset($_GET['page'])
    ? max(1, (int) $_GET['page'])
    : 1;

$offset = ($page - 1) * $items_per_page;


/*
|--------------------------------------------------------------------------
| Search / Filters
|--------------------------------------------------------------------------
*/

$where = [];
$params = [];


/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

if (
    isset($_GET['search']) &&
    trim($_GET['search']) !== ''
) {

    $search = trim($_GET['search']);

    $where[] = "
        (
            s.name LIKE ?
            OR b.name LIKE ?
            OR c.name LIKE ?
        )
    ";

    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}


/*
|--------------------------------------------------------------------------
| Category Filter
|--------------------------------------------------------------------------
*/

if (
    isset($_GET['category']) &&
    $_GET['category'] !== ''
) {

    $where[] = "s.category_id = ?";
    $params[] = $_GET['category'];
}


$where_clause = !empty($where)
    ? "WHERE " . implode(" AND ", $where)
    : "";


/*
|--------------------------------------------------------------------------
| Sorting
|--------------------------------------------------------------------------
*/

$sort = $_GET['sort'] ?? 'newest';

$order_by = match ($sort) {

    'price_low' =>
        'ORDER BY s.price ASC',

    'price_high' =>
        'ORDER BY s.price DESC',

    'stock_high' =>
        'ORDER BY s.stock DESC',

    'stock_low' =>
        'ORDER BY s.stock ASC',

    default =>
        'ORDER BY s.id DESC'
};


/*
|--------------------------------------------------------------------------
| Categories
|--------------------------------------------------------------------------
*/

$categories = $pdo
    ->query("
        SELECT *
        FROM categories
        ORDER BY name ASC
    ")
    ->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Inventory Statistics
|--------------------------------------------------------------------------
*/

$total_products = $pdo
    ->query("
        SELECT COUNT(*)
        FROM shoes
    ")
    ->fetchColumn();


$total_stock = $pdo
    ->query("
        SELECT COALESCE(SUM(stock), 0)
        FROM shoes
    ")
    ->fetchColumn();


$low_stock = $pdo
    ->query("
        SELECT COUNT(*)
        FROM shoes
        WHERE stock > 0
        AND stock <= 10
    ")
    ->fetchColumn();


$out_of_stock = $pdo
    ->query("
        SELECT COUNT(*)
        FROM shoes
        WHERE stock <= 0
    ")
    ->fetchColumn();


/*
|--------------------------------------------------------------------------
| Count Filtered Products
|--------------------------------------------------------------------------
*/

$count_query = "
    SELECT COUNT(*)

    FROM shoes s

    LEFT JOIN brands b
        ON s.brand_id = b.id

    LEFT JOIN categories c
        ON s.category_id = c.id

    $where_clause
";


$count_stmt = $pdo->prepare($count_query);

$count_stmt->execute($params);

$total_items = $count_stmt->fetchColumn();

$total_pages = ceil(
    $total_items / $items_per_page
);


/*
|--------------------------------------------------------------------------
| Get Sneakers
|--------------------------------------------------------------------------
*/

$query = "
    SELECT
        s.*,
        c.name AS category_name,
        b.name AS brand_name

    FROM shoes s

    LEFT JOIN categories c
        ON s.category_id = c.id

    LEFT JOIN brands b
        ON s.brand_id = b.id

    $where_clause

    $order_by

    LIMIT $items_per_page

    OFFSET $offset
";


$stmt = $pdo->prepare($query);

$stmt->execute($params);

$shoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<style>

/*
|--------------------------------------------------------------------------
| Two-Color Inventory Theme
|--------------------------------------------------------------------------
*/

body {
    background: #eef0f1 !important;
}


.inventory-page {
    margin-top: -10px;
    padding-bottom: 55px;
    color: #1c1f21;
}


/*
|--------------------------------------------------------------------------
| Hero
|--------------------------------------------------------------------------
*/

.inventory-hero {
    background:
        linear-gradient(
            120deg,
            #1f2326 0%,
            #2a2f33 100%
        );

    border-radius: 24px;

    padding: 38px;

    margin-bottom: 26px;

    position: relative;

    overflow: hidden;

    box-shadow:
        0 10px 30px
        rgba(0, 0, 0, 0.12);
}


.inventory-hero::after {
    content: "";

    position: absolute;

    width: 280px;
    height: 280px;

    background:
        rgba(215, 255, 28, 0.14);

    border-radius: 50%;

    right: -95px;
    top: -110px;
}


.inventory-label {
    color: #d8ff24;

    font-size: 0.8rem;

    font-weight: 800;

    letter-spacing: 2px;

    text-transform: uppercase;

    margin-bottom: 10px;
}


.inventory-title {
    font-size: 2.8rem;

    font-weight: 850;

    letter-spacing: -1px;

    margin-bottom: 8px;

    color: #ffffff;
}


.inventory-title span {
    color: #d8ff24;
}


.inventory-subtitle {
    color: #c9ced1;

    max-width: 650px;

    margin-bottom: 0;

    font-size: 0.95rem;
}


/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

.inventory-stats {
    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 15px;

    margin-bottom: 26px;
}


.inventory-stat {
    background: #ffffff;

    border: 1px solid #dde1e4;

    border-radius: 18px;

    padding: 21px;

    box-shadow:
        0 5px 15px
        rgba(0, 0, 0, 0.05);

    transition: 0.2s ease;
}


.inventory-stat:hover {
    transform: translateY(-3px);

    box-shadow:
        0 9px 22px
        rgba(0, 0, 0, 0.08);
}


.stat-icon {
    width: 42px;
    height: 42px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 12px;

    background: #d8ff24;

    color: #111;

    margin-bottom: 14px;

    font-size: 1.1rem;

    font-weight: 700;
}


.stat-number {
    font-size: 1.7rem;

    font-weight: 850;

    color: #1e2225;

    margin-bottom: 2px;
}


.stat-label {
    color: #737a7f;

    font-size: 0.84rem;
}


/*
|--------------------------------------------------------------------------
| Search Panel
|--------------------------------------------------------------------------
*/

.inventory-controls {
    background: #ffffff;

    border: 1px solid #dde1e4;

    border-radius: 20px;

    padding: 20px;

    margin-bottom: 18px;

    box-shadow:
        0 5px 15px
        rgba(0, 0, 0, 0.04);
}


.inventory-controls label {
    color: #42484c;

    font-size: 0.79rem;

    margin-bottom: 7px;

    font-weight: 700;
}


.inventory-controls .form-control,
.inventory-controls .form-select {
    background: #f7f8f8;

    border: 1px solid #d5dade;

    color: #1c1f21;

    min-height: 47px;

    border-radius: 12px;
}


.inventory-controls .form-control::placeholder {
    color: #969da2;
}


.inventory-controls .form-control:focus,
.inventory-controls .form-select:focus {
    background: #ffffff;

    border-color: #b6d800;

    box-shadow:
        0 0 0 0.2rem
        rgba(216, 255, 36, 0.16);
}


.inventory-search-btn {
    background: #1f2326;

    color: #ffffff;

    border: none;

    min-height: 47px;

    border-radius: 12px;

    font-weight: 700;

    transition: 0.2s ease;
}


.inventory-search-btn:hover {
    background: #d8ff24;

    color: #111;
}


/*
|--------------------------------------------------------------------------
| Category Pills
|--------------------------------------------------------------------------
*/

.category-strip {
    display: flex;

    align-items: center;

    flex-wrap: wrap;

    gap: 8px;

    margin-bottom: 28px;
}


.category-chip {
    text-decoration: none;

    background: #ffffff;

    color: #40464a;

    border: 1px solid #d8dcdf;

    border-radius: 50px;

    padding: 9px 16px;

    font-size: 0.82rem;

    font-weight: 600;

    transition: 0.2s ease;
}


.category-chip:hover {
    color: #111;

    background: #d8ff24;

    border-color: #d8ff24;
}


.category-chip.active {
    color: #111;

    background: #d8ff24;

    border-color: #d8ff24;

    font-weight: 800;
}


/*
|--------------------------------------------------------------------------
| Products Heading
|--------------------------------------------------------------------------
*/

.products-heading {
    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 16px;
}


.products-heading h4 {
    font-weight: 800;

    margin: 0;

    color: #1d2124;
}


.products-count {
    background: #1f2326;

    color: #d8ff24;

    font-weight: 800;

    border-radius: 30px;

    padding: 8px 14px;

    font-size: 0.75rem;
}


/*
|--------------------------------------------------------------------------
| Sneaker Cards
|--------------------------------------------------------------------------
*/

.inventory-card {
    background: #ffffff;

    border: 1px solid #dde1e4;

    border-radius: 19px;

    overflow: hidden;

    height: 100%;

    box-shadow:
        0 6px 18px
        rgba(0, 0, 0, 0.055);

    transition:
        transform 0.25s ease,
        box-shadow 0.25s ease;
}


.inventory-card:hover {
    transform: translateY(-5px);

    box-shadow:
        0 15px 32px
        rgba(0, 0, 0, 0.11);
}


/*
|--------------------------------------------------------------------------
| Product Image
|--------------------------------------------------------------------------
*/

.product-image-area {
    background:
        linear-gradient(
            145deg,
            #f3f4f4,
            #e4e7e8
        );

    height: 235px;

    position: relative;

    display: flex;

    justify-content: center;

    align-items: center;

    overflow: hidden;
}


.product-image-area img {
    width: 78%;
    height: 78%;

    object-fit: contain;

    transition: 0.3s ease;
}


.inventory-card:hover .product-image-area img {
    transform:
        scale(1.06)
        rotate(-2deg);
}


.product-id {
    position: absolute;

    right: 13px;
    top: 13px;

    background: #1f2326;

    color: white;

    border-radius: 30px;

    padding: 6px 10px;

    font-size: 0.7rem;
}


/*
|--------------------------------------------------------------------------
| Product Body
|--------------------------------------------------------------------------
*/

.product-body {
    padding: 19px;
}


.product-brand {
    color: #788500;

    text-transform: uppercase;

    letter-spacing: 1px;

    font-size: 0.72rem;

    font-weight: 800;

    margin-bottom: 5px;
}


.product-name {
    font-size: 1.16rem;

    font-weight: 800;

    margin-bottom: 13px;

    color: #1b1f22;
}


/*
|--------------------------------------------------------------------------
| Meta Boxes
|--------------------------------------------------------------------------
*/

.product-meta {
    display: grid;

    grid-template-columns:
        1fr 1fr;

    gap: 10px;

    margin-bottom: 17px;
}


.meta-box {
    background: #f3f5f5;

    border: 1px solid #e1e5e7;

    border-radius: 11px;

    padding: 10px;
}


.meta-label {
    display: block;

    color: #90979b;

    font-size: 0.65rem;

    text-transform: uppercase;

    letter-spacing: 0.6px;
}


.meta-value {
    color: #252a2d;

    font-size: 0.86rem;

    font-weight: 700;
}


/*
|--------------------------------------------------------------------------
| Price / Stock
|--------------------------------------------------------------------------
*/

.product-bottom {
    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 10px;

    margin-bottom: 15px;
}


.product-price {
    font-size: 1.25rem;

    font-weight: 850;

    color: #1c2023;
}


.stock-good,
.stock-low,
.stock-out {
    display: inline-flex;

    align-items: center;

    gap: 5px;

    border-radius: 30px;

    padding: 6px 9px;

    font-size: 0.69rem;

    font-weight: 800;
}


.stock-good {
    background: #eff8cf;

    color: #627000;
}


.stock-low {
    background: #fff3ce;

    color: #9b6b00;
}


.stock-out {
    background: #ffe0e0;

    color: #b83232;
}


/*
|--------------------------------------------------------------------------
| Manage Product Button
|--------------------------------------------------------------------------
*/

.manage-product-btn {
    width: 100%;

    background: #1f2326;

    color: white;

    border: none;

    border-radius: 11px;

    padding: 11px;

    font-weight: 700;

    transition: 0.2s ease;
}


.manage-product-btn:hover {
    background: #d8ff24;

    color: #111;
}


/*
|--------------------------------------------------------------------------
| Empty State
|--------------------------------------------------------------------------
*/

.inventory-empty {
    background: #ffffff;

    border: 1px solid #dde1e4;

    border-radius: 18px;

    padding: 60px 20px;

    text-align: center;

    color: #777;
}


/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

.pagination .page-link {
    background: #ffffff;

    color: #25292c;

    border-color: #d7dcdf;

    margin: 0 3px;

    border-radius: 9px !important;
}


.pagination .page-link:hover {
    background: #d8ff24;

    color: #111;

    border-color: #d8ff24;
}


.pagination .active .page-link {
    background: #1f2326;

    color: #d8ff24;

    border-color: #1f2326;

    font-weight: 800;
}


/*
|--------------------------------------------------------------------------
| Responsive
|--------------------------------------------------------------------------
*/

@media (max-width: 992px) {

    .inventory-stats {
        grid-template-columns:
            repeat(2, 1fr);
    }

}


@media (max-width: 576px) {

    .inventory-stats {
        grid-template-columns:
            1fr;
    }


    .inventory-title {
        font-size: 2rem;
    }


    .inventory-hero {
        padding: 26px;
    }

}

</style>


<div class="inventory-page">


    <!--
    |--------------------------------------------------------------------------
    | Hero
    |--------------------------------------------------------------------------
    -->

    <section class="inventory-hero">

        <div class="inventory-label">
            Elite Sneaker Hub • Internal System
        </div>


        <h1 class="inventory-title">

            SNEAKER
            <span>INVENTORY</span>

        </h1>


        <p class="inventory-subtitle">

            Browse company sneakers, monitor stock levels
            and access product management tools.

        </p>

    </section>


    <!--
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    -->

    <section class="inventory-stats">


        <div class="inventory-stat">

            <div class="stat-icon">
                <i class="bi bi-box-seam"></i>
            </div>

            <div class="stat-number">
                <?= $total_products ?>
            </div>

            <div class="stat-label">
                Total Products
            </div>

        </div>


        <div class="inventory-stat">

            <div class="stat-icon">
                <i class="bi bi-boxes"></i>
            </div>

            <div class="stat-number">
                <?= $total_stock ?>
            </div>

            <div class="stat-label">
                Total Stock Units
            </div>

        </div>


        <div class="inventory-stat">

            <div class="stat-icon">
                <i class="bi bi-exclamation-triangle"></i>
            </div>

            <div class="stat-number">
                <?= $low_stock ?>
            </div>

            <div class="stat-label">
                Low Stock Products
            </div>

        </div>


        <div class="inventory-stat">

            <div class="stat-icon">
                <i class="bi bi-x-circle"></i>
            </div>

            <div class="stat-number">
                <?= $out_of_stock ?>
            </div>

            <div class="stat-label">
                Out of Stock
            </div>

        </div>


    </section>


    <!--
    |--------------------------------------------------------------------------
    | Search / Sort
    |--------------------------------------------------------------------------
    -->

    <section class="inventory-controls">

        <form
            method="GET"
            action="index.php"
            class="row g-3 align-items-end"
        >


            <!-- Search -->

            <div class="col-md-7">

                <label>
                    Search Inventory
                </label>

                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search by model, brand or category..."
                    value="<?= isset($_GET['search'])
                        ? htmlspecialchars($_GET['search'])
                        : '' ?>"
                >

                <?php if (
                    isset($_GET['category']) &&
                    $_GET['category'] !== ''
                ): ?>

                    <input
                        type="hidden"
                        name="category"
                        value="<?= htmlspecialchars($_GET['category']) ?>"
                    >

                <?php endif; ?>

            </div>


            <!-- Sort -->

            <div class="col-md-3">

                <label>
                    Sort By
                </label>

                <select
                    name="sort"
                    class="form-select"
                >

                    <option
                        value="newest"
                        <?= $sort === 'newest'
                            ? 'selected'
                            : '' ?>
                    >
                        Newest First
                    </option>


                    <option
                        value="price_low"
                        <?= $sort === 'price_low'
                            ? 'selected'
                            : '' ?>
                    >
                        Price: Low to High
                    </option>


                    <option
                        value="price_high"
                        <?= $sort === 'price_high'
                            ? 'selected'
                            : '' ?>
                    >
                        Price: High to Low
                    </option>


                    <option
                        value="stock_high"
                        <?= $sort === 'stock_high'
                            ? 'selected'
                            : '' ?>
                    >
                        Stock: High to Low
                    </option>


                    <option
                        value="stock_low"
                        <?= $sort === 'stock_low'
                            ? 'selected'
                            : '' ?>
                    >
                        Stock: Low to High
                    </option>

                </select>

            </div>


            <!-- Search Button -->

            <div class="col-md-2">

                <button
                    type="submit"
                    class="inventory-search-btn w-100"
                >

                    <i class="bi bi-search"></i>

                    Search

                </button>

            </div>

        </form>

    </section>


    <!--
    |--------------------------------------------------------------------------
    | Category Pills
    |--------------------------------------------------------------------------
    -->

    <div class="category-strip">


        <a
            href="index.php<?= isset($_GET['sort'])
                ? '?sort=' . urlencode($_GET['sort'])
                : '' ?>"
            class="category-chip
            <?= !isset($_GET['category']) ||
                $_GET['category'] === ''
                ? 'active'
                : '' ?>"
        >
            All
        </a>


        <?php foreach ($categories as $category): ?>

            <a
                href="index.php?category=<?= $category['id'] ?><?= isset($_GET['sort'])
                    ? '&sort=' . urlencode($_GET['sort'])
                    : '' ?>"
                class="category-chip
                <?= (
                    isset($_GET['category']) &&
                    $_GET['category'] == $category['id']
                )
                    ? 'active'
                    : '' ?>"
            >

                <?= htmlspecialchars(
                    $category['name']
                ) ?>

            </a>

        <?php endforeach; ?>


    </div>


    <!--
    |--------------------------------------------------------------------------
    | Product Heading
    |--------------------------------------------------------------------------
    -->

    <div class="products-heading">

        <h4>
            Company Sneakers
        </h4>


        <div class="products-count">

            <?= $total_items ?>

            PRODUCTS

        </div>

    </div>


    <!--
    |--------------------------------------------------------------------------
    | Sneaker Cards
    |--------------------------------------------------------------------------
    -->

    <div class="row g-4">


        <?php if (count($shoes) > 0): ?>


            <?php foreach ($shoes as $shoe): ?>


                <div class="col-xl-4 col-md-6">


                    <article class="inventory-card">


                        <!-- Image -->

                        <div class="product-image-area">


                            <div class="product-id">

                                #<?= $shoe['id'] ?>

                            </div>


                            <img
                                src="shoe-default.avif"
                                alt="<?= htmlspecialchars(
                                    $shoe['name']
                                ) ?>"
                            >


                        </div>


                        <!-- Body -->

                        <div class="product-body">


                            <!-- Brand -->

                            <div class="product-brand">

                                <?= htmlspecialchars(
                                    $shoe['brand_name']
                                    ?? 'Unknown Brand'
                                ) ?>

                            </div>


                            <!-- Name -->

                            <div class="product-name">

                                <?= htmlspecialchars(
                                    $shoe['name']
                                ) ?>

                            </div>


                            <!-- Meta -->

                            <div class="product-meta">


                                <div class="meta-box">

                                    <span class="meta-label">
                                        Category
                                    </span>

                                    <span class="meta-value">

                                        <?= htmlspecialchars(
                                            $shoe['category_name']
                                            ?? 'Uncategorized'
                                        ) ?>

                                    </span>

                                </div>


                                <div class="meta-box">

                                    <span class="meta-label">
                                        Size
                                    </span>

                                    <span class="meta-value">

                                        US
                                        <?= htmlspecialchars(
                                            $shoe['size']
                                        ) ?>

                                    </span>

                                </div>


                            </div>


                            <!-- Price / Stock -->

                            <div class="product-bottom">


                                <div class="product-price">

                                    $<?= number_format(
                                        $shoe['price'],
                                        2
                                    ) ?>

                                </div>


                                <?php if ($shoe['stock'] > 10): ?>

                                    <span class="stock-good">

                                        <i class="bi bi-check-circle-fill"></i>

                                        <?= $shoe['stock'] ?>
                                        IN STOCK

                                    </span>


                                <?php elseif ($shoe['stock'] > 0): ?>

                                    <span class="stock-low">

                                        <i class="bi bi-exclamation-circle-fill"></i>

                                        <?= $shoe['stock'] ?>
                                        LOW STOCK

                                    </span>


                                <?php else: ?>

                                    <span class="stock-out">

                                        <i class="bi bi-x-circle-fill"></i>

                                        OUT OF STOCK

                                    </span>

                                <?php endif; ?>


                            </div>


                            <!-- Manage -->

                            <a
                                href="dashboard.php"
                                class="btn manage-product-btn"
                            >

                                <i class="bi bi-pencil-square"></i>

                                Manage Product

                            </a>


                        </div>


                    </article>


                </div>


            <?php endforeach; ?>


        <?php else: ?>


            <div class="col-12">

                <div class="inventory-empty">

                    <i
                        class="bi bi-box-seam"
                        style="font-size: 2rem;"
                    ></i>

                    <h5 class="mt-3">
                        No sneakers found
                    </h5>

                    <p class="mb-0">

                        Try changing your search
                        or category filter.

                    </p>

                </div>

            </div>


        <?php endif; ?>


    </div>


    <!--
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    -->

    <?php if ($total_pages > 1): ?>

        <nav class="mt-5">

            <ul class="pagination justify-content-center">


                <?php for (
                    $i = 1;
                    $i <= $total_pages;
                    $i++
                ): ?>


                    <li
                        class="page-item
                        <?= $i === $page
                            ? 'active'
                            : '' ?>"
                    >

                        <a
                            class="page-link"
                            href="?page=<?= $i ?><?= isset($_GET['search'])
                                ? '&search=' .
                                urlencode($_GET['search'])
                                : '' ?><?= isset($_GET['category'])
                                ? '&category=' .
                                urlencode($_GET['category'])
                                : '' ?><?= isset($_GET['sort'])
                                ? '&sort=' .
                                urlencode($_GET['sort'])
                                : '' ?>"
                        >

                            <?= $i ?>

                        </a>

                    </li>


                <?php endfor; ?>


            </ul>

        </nav>

    <?php endif; ?>


</div>


<?php require_once 'footer.php'; ?>