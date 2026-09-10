<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php';


/*
|--------------------------------------------------------------------------
| Admin Access Only
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header('Location: auth.php');
    exit;
}


require_once 'header.php';


/*
|--------------------------------------------------------------------------
| Inventory Statistics
|--------------------------------------------------------------------------
*/

$inventoryStats = $pdo->query("
    SELECT
        COUNT(*) AS total_shoes,
        COALESCE(SUM(stock), 0) AS total_stock,
        COALESCE(AVG(price), 0) AS avg_price,
        COALESCE(MIN(price), 0) AS min_price,
        COALESCE(MAX(price), 0) AS max_price
    FROM shoes
")->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Top Selling Shoes
|--------------------------------------------------------------------------
*/

$topSellers = $pdo->query("
    SELECT
        s.name,
        b.name AS brand_name,
        COUNT(o.id) AS total_orders,
        COALESCE(SUM(o.quantity), 0) AS total_sold

    FROM shoes s

    LEFT JOIN brands b
        ON s.brand_id = b.id

    LEFT JOIN orders o
        ON s.id = o.shoe_id

    GROUP BY
        s.id,
        s.name,
        b.name

    ORDER BY total_sold DESC

    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Low Stock Alerts
|--------------------------------------------------------------------------
*/

$lowStock = $pdo->query("
    SELECT
        s.id,
        s.name,
        s.stock,
        b.name AS brand_name

    FROM shoes s

    LEFT JOIN brands b
        ON s.brand_id = b.id

    WHERE s.stock < 5

    ORDER BY s.stock ASC, s.name ASC
")->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Extra Report Values
|--------------------------------------------------------------------------
*/

$lowStockCount = count($lowStock);

?>


<style>

/*
|--------------------------------------------------------------------------
| Reports Theme
|--------------------------------------------------------------------------
*/

body {
    background: #eef0f1 !important;
}


.reports-page {
    margin-top: -10px;
    padding-bottom: 55px;
    color: #1c1f21;
}


/*
|--------------------------------------------------------------------------
| Hero
|--------------------------------------------------------------------------
*/

.reports-hero {
    background:
        linear-gradient(
            120deg,
            #1f2326 0%,
            #2a2f33 100%
        );

    border-radius: 24px;

    padding: 35px 38px;

    margin-bottom: 26px;

    position: relative;

    overflow: hidden;

    box-shadow:
        0 10px 30px
        rgba(0, 0, 0, 0.12);
}


.reports-hero::after {
    content: "";

    position: absolute;

    width: 270px;
    height: 270px;

    border-radius: 50%;

    background:
        rgba(216, 255, 36, 0.14);

    right: -90px;
    top: -110px;
}


.reports-label {
    color: #d8ff24;

    font-size: 0.78rem;

    font-weight: 800;

    letter-spacing: 2px;

    text-transform: uppercase;

    margin-bottom: 9px;
}


.reports-title {
    color: white;

    font-size: 2.5rem;

    font-weight: 850;

    margin-bottom: 8px;
}


.reports-title span {
    color: #d8ff24;
}


.reports-subtitle {
    color: #c9ced1;

    max-width: 680px;

    margin-bottom: 0;
}


/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

.reports-stats {
    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 15px;

    margin-bottom: 26px;
}


.report-stat {
    background: white;

    border: 1px solid #dde1e4;

    border-radius: 18px;

    padding: 21px;

    box-shadow:
        0 5px 15px
        rgba(0, 0, 0, 0.05);

    transition: 0.2s ease;
}


.report-stat:hover {
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
}


.stat-number {
    font-size: 1.65rem;

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
| Report Grid
|--------------------------------------------------------------------------
*/

.report-grid {
    display: grid;

    grid-template-columns:
        1.2fr 0.8fr;

    gap: 20px;

    margin-bottom: 20px;
}


/*
|--------------------------------------------------------------------------
| Report Card
|--------------------------------------------------------------------------
*/

.report-card {
    background: #ffffff;

    border: 1px solid #dde1e4;

    border-radius: 20px;

    overflow: hidden;

    box-shadow:
        0 6px 18px
        rgba(0, 0, 0, 0.05);
}


.report-card-header {
    background: #1f2326;

    color: white;

    padding: 18px 21px;

    display: flex;

    justify-content: space-between;
    align-items: center;

    gap: 12px;
}


.report-card-title {
    margin: 0;

    font-size: 1.05rem;

    font-weight: 800;
}


.report-card-title i {
    color: #d8ff24;

    margin-right: 7px;
}


.report-card-subtitle {
    color: #aeb4b7;

    font-size: 0.76rem;

    margin-top: 3px;
}


.report-count {
    background: #d8ff24;

    color: #111;

    border-radius: 30px;

    padding: 7px 12px;

    font-size: 0.72rem;

    font-weight: 800;

    white-space: nowrap;
}


.report-card-body {
    padding: 18px 21px;
}


/*
|--------------------------------------------------------------------------
| Table
|--------------------------------------------------------------------------
*/

.report-table {
    margin-bottom: 0;
}


.report-table thead th {
    background: #f1f3f4;

    color: #50575b;

    border-bottom: 1px solid #dfe3e5;

    font-size: 0.72rem;

    text-transform: uppercase;

    letter-spacing: 0.4px;

    padding: 13px 12px;
}


.report-table tbody td {
    padding: 13px 12px;

    vertical-align: middle;

    color: #292e31;

    border-color: #edf0f1;
}


.report-table tbody tr:hover {
    background: #f8f9f9;
}


/*
|--------------------------------------------------------------------------
| Shoe / Brand Styling
|--------------------------------------------------------------------------
*/

.shoe-name {
    font-weight: 800;

    color: #1d2124;
}


.brand-badge {
    display: inline-block;

    background: #edf6c9;

    color: #606d00;

    border-radius: 30px;

    padding: 5px 9px;

    font-size: 0.7rem;

    font-weight: 800;
}


/*
|--------------------------------------------------------------------------
| Ranking
|--------------------------------------------------------------------------
*/

.rank-badge {
    width: 32px;
    height: 32px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    border-radius: 9px;

    background: #1f2326;

    color: #d8ff24;

    font-size: 0.72rem;

    font-weight: 800;
}


/*
|--------------------------------------------------------------------------
| Units Sold
|--------------------------------------------------------------------------
*/

.units-sold {
    font-weight: 850;

    color: #1e2225;
}


/*
|--------------------------------------------------------------------------
| Low Stock
|--------------------------------------------------------------------------
*/

.stock-danger {
    display: inline-flex;

    align-items: center;

    gap: 5px;

    border-radius: 30px;

    padding: 5px 9px;

    background: #ffe0e0;

    color: #b52f2f;

    font-size: 0.71rem;

    font-weight: 800;
}


.stock-warning {
    display: inline-flex;

    align-items: center;

    gap: 5px;

    border-radius: 30px;

    padding: 5px 9px;

    background: #fff2cb;

    color: #976700;

    font-size: 0.71rem;

    font-weight: 800;
}


/*
|--------------------------------------------------------------------------
| Empty State
|--------------------------------------------------------------------------
*/

.empty-report {
    text-align: center;

    padding: 45px 20px;

    color: #777;
}


.empty-report i {
    font-size: 2rem;

    color: #92999d;
}


/*
|--------------------------------------------------------------------------
| Price Range Card
|--------------------------------------------------------------------------
*/

.price-card {
    background:
        linear-gradient(
            120deg,
            #ffffff 0%,
            #f7f8f8 100%
        );

    border: 1px solid #dde1e4;

    border-radius: 20px;

    padding: 22px;

    box-shadow:
        0 6px 18px
        rgba(0, 0, 0, 0.05);

    margin-bottom: 20px;
}


.price-card-title {
    font-size: 1rem;

    font-weight: 800;

    margin-bottom: 18px;

    color: #1e2225;
}


.price-range-grid {
    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 12px;
}


.price-box {
    background: white;

    border: 1px solid #e0e4e6;

    border-radius: 13px;

    padding: 16px;
}


.price-box-label {
    color: #80878b;

    font-size: 0.72rem;

    text-transform: uppercase;

    letter-spacing: 0.5px;

    margin-bottom: 5px;
}


.price-box-value {
    color: #1e2225;

    font-weight: 850;

    font-size: 1.15rem;
}


/*
|--------------------------------------------------------------------------
| Responsive
|--------------------------------------------------------------------------
*/

@media (max-width: 992px) {

    .reports-stats {
        grid-template-columns:
            repeat(2, 1fr);
    }


    .report-grid {
        grid-template-columns:
            1fr;
    }

}


@media (max-width: 576px) {

    .reports-stats {
        grid-template-columns:
            1fr;
    }


    .reports-title {
        font-size: 2rem;
    }


    .reports-hero {
        padding: 27px;
    }


    .price-range-grid {
        grid-template-columns:
            1fr;
    }


    .report-card-header {
        align-items: flex-start;

        flex-direction: column;
    }

}

</style>


<div class="reports-page">


    <!--
    |--------------------------------------------------------------------------
    | Hero
    |--------------------------------------------------------------------------
    -->

    <section class="reports-hero">

        <div class="reports-label">

            Elite Sneaker Hub • Business Reports

        </div>


        <h1 class="reports-title">

            REPORTS
            <span>DASHBOARD</span>

        </h1>


        <p class="reports-subtitle">

            Monitor inventory performance, product sales,
            pricing and low-stock alerts.

        </p>

    </section>


    <!--
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    -->

    <section class="reports-stats">


        <div class="report-stat">

            <div class="stat-icon">

                <i class="bi bi-box-seam"></i>

            </div>


            <div class="stat-number">

                <?= $inventoryStats['total_shoes'] ?>

            </div>


            <div class="stat-label">

                Total Shoes

            </div>

        </div>


        <div class="report-stat">

            <div class="stat-icon">

                <i class="bi bi-boxes"></i>

            </div>


            <div class="stat-number">

                <?= $inventoryStats['total_stock'] ?>

            </div>


            <div class="stat-label">

                Total Stock Units

            </div>

        </div>


        <div class="report-stat">

            <div class="stat-icon">

                <i class="bi bi-currency-dollar"></i>

            </div>


            <div class="stat-number">

                $<?= number_format(
                    $inventoryStats['avg_price'],
                    2
                ) ?>

            </div>


            <div class="stat-label">

                Average Price

            </div>

        </div>


        <div class="report-stat">

            <div class="stat-icon">

                <i class="bi bi-exclamation-triangle"></i>

            </div>


            <div class="stat-number">

                <?= $lowStockCount ?>

            </div>


            <div class="stat-label">

                Low Stock Products

            </div>

        </div>


    </section>


    <!--
    |--------------------------------------------------------------------------
    | Price Range
    |--------------------------------------------------------------------------
    -->

    <section class="price-card">

        <div class="price-card-title">

            <i class="bi bi-cash-stack"></i>

            Inventory Price Overview

        </div>


        <div class="price-range-grid">


            <div class="price-box">

                <div class="price-box-label">

                    Lowest Price

                </div>

                <div class="price-box-value">

                    $<?= number_format(
                        $inventoryStats['min_price'],
                        2
                    ) ?>

                </div>

            </div>


            <div class="price-box">

                <div class="price-box-label">

                    Average Price

                </div>

                <div class="price-box-value">

                    $<?= number_format(
                        $inventoryStats['avg_price'],
                        2
                    ) ?>

                </div>

            </div>


            <div class="price-box">

                <div class="price-box-label">

                    Highest Price

                </div>

                <div class="price-box-value">

                    $<?= number_format(
                        $inventoryStats['max_price'],
                        2
                    ) ?>

                </div>

            </div>


        </div>

    </section>


    <!--
    |--------------------------------------------------------------------------
    | Report Sections
    |--------------------------------------------------------------------------
    -->

    <section class="report-grid">


        <!--
        |--------------------------------------------------------------------------
        | Top Selling Shoes
        |--------------------------------------------------------------------------
        -->

        <div class="report-card">


            <div class="report-card-header">


                <div>

                    <h5 class="report-card-title">

                        <i class="bi bi-trophy"></i>

                        Top Selling Shoes

                    </h5>


                    <div class="report-card-subtitle">

                        Products with the highest number of units sold.

                    </div>

                </div>


                <div class="report-count">

                    TOP 5

                </div>


            </div>


            <div class="report-card-body">


                <?php if (empty($topSellers)): ?>


                    <div class="empty-report">

                        <i class="bi bi-bar-chart"></i>

                        <h6 class="mt-3">

                            No sales data

                        </h6>

                        <p class="mb-0">

                            Sales information will appear here.

                        </p>

                    </div>


                <?php else: ?>


                    <div class="table-responsive">


                        <table class="table report-table">


                            <thead>

                                <tr>

                                    <th>
                                        Rank
                                    </th>

                                    <th>
                                        Shoe
                                    </th>

                                    <th>
                                        Brand
                                    </th>

                                    <th>
                                        Orders
                                    </th>

                                    <th>
                                        Units Sold
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                                <?php
                                $rank = 1;
                                foreach ($topSellers as $shoe):
                                ?>


                                    <tr>


                                        <td>

                                            <span class="rank-badge">

                                                #<?= $rank ?>

                                            </span>

                                        </td>


                                        <td>

                                            <span class="shoe-name">

                                                <?= htmlspecialchars(
                                                    $shoe['name']
                                                ) ?>

                                            </span>

                                        </td>


                                        <td>

                                            <span class="brand-badge">

                                                <?= htmlspecialchars(
                                                    $shoe['brand_name']
                                                    ?? 'Unknown'
                                                ) ?>

                                            </span>

                                        </td>


                                        <td>

                                            <?= (int) (
                                                $shoe['total_orders']
                                                ?? 0
                                            ) ?>

                                        </td>


                                        <td>

                                            <span class="units-sold">

                                                <?= (int) (
                                                    $shoe['total_sold']
                                                    ?? 0
                                                ) ?>

                                            </span>

                                        </td>


                                    </tr>


                                <?php
                                $rank++;
                                endforeach;
                                ?>


                            </tbody>


                        </table>


                    </div>


                <?php endif; ?>


            </div>


        </div>


        <!--
        |--------------------------------------------------------------------------
        | Low Stock Alerts
        |--------------------------------------------------------------------------
        -->

        <div class="report-card">


            <div class="report-card-header">


                <div>

                    <h5 class="report-card-title">

                        <i class="bi bi-exclamation-triangle-fill"></i>

                        Low Stock Alerts

                    </h5>


                    <div class="report-card-subtitle">

                        Sneakers with fewer than 5 units remaining.

                    </div>

                </div>


                <div class="report-count">

                    <?= $lowStockCount ?>

                    ALERTS

                </div>


            </div>


            <div class="report-card-body">


                <?php if (empty($lowStock)): ?>


                    <div class="empty-report">

                        <i class="bi bi-check-circle"></i>

                        <h6 class="mt-3">

                            Stock levels are healthy

                        </h6>

                        <p class="mb-0">

                            No products currently have critically low stock.

                        </p>

                    </div>


                <?php else: ?>


                    <div class="table-responsive">


                        <table class="table report-table">


                            <thead>

                                <tr>

                                    <th>
                                        Shoe
                                    </th>

                                    <th>
                                        Brand
                                    </th>

                                    <th>
                                        Current Stock
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                                <?php foreach ($lowStock as $shoe): ?>


                                    <tr>


                                        <td>

                                            <span class="shoe-name">

                                                <?= htmlspecialchars(
                                                    $shoe['name']
                                                ) ?>

                                            </span>

                                        </td>


                                        <td>

                                            <span class="brand-badge">

                                                <?= htmlspecialchars(
                                                    $shoe['brand_name']
                                                    ?? 'Unknown'
                                                ) ?>

                                            </span>

                                        </td>


                                        <td>


                                            <?php if ($shoe['stock'] <= 1): ?>


                                                <span class="stock-danger">

                                                    <i class="bi bi-exclamation-circle-fill"></i>

                                                    <?= (int) $shoe['stock'] ?>

                                                    Units

                                                </span>


                                            <?php else: ?>


                                                <span class="stock-warning">

                                                    <i class="bi bi-exclamation-triangle-fill"></i>

                                                    <?= (int) $shoe['stock'] ?>

                                                    Units

                                                </span>


                                            <?php endif; ?>


                                        </td>


                                    </tr>


                                <?php endforeach; ?>


                            </tbody>


                        </table>


                    </div>


                <?php endif; ?>


            </div>


        </div>


    </section>


</div>


<?php require_once 'footer.php'; ?>