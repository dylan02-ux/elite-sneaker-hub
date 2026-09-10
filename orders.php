<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php';


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


/*
|--------------------------------------------------------------------------
| Update Order Status
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['order_id'], $_POST['status'])
) {

    $orderId = (int) $_POST['order_id'];

    $allowedStatuses = [
        'pending',
        'completed',
        'cancelled'
    ];

    $status = $_POST['status'];


    if (in_array($status, $allowedStatuses, true)) {

        $stmt = $pdo->prepare("
            UPDATE orders
            SET status = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $status,
            $orderId
        ]);

        header('Location: orders.php?updated=1');
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| Load Company Orders
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        o.*,
        u.email,
        s.name AS shoe_name,
        s.price,
        s.image_url,
        b.name AS brand_name

    FROM orders o

    LEFT JOIN users u
        ON o.user_id = u.id

    LEFT JOIN shoes s
        ON o.shoe_id = s.id

    LEFT JOIN brands b
        ON s.brand_id = b.id

    ORDER BY o.order_date DESC
");

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Order Statistics
|--------------------------------------------------------------------------
*/

$totalOrders = count($orders);

$pendingOrders = 0;
$completedOrders = 0;
$cancelledOrders = 0;

foreach ($orders as $order) {

    if ($order['status'] === 'pending') {
        $pendingOrders++;
    }

    elseif ($order['status'] === 'completed') {
        $completedOrders++;
    }

    elseif ($order['status'] === 'cancelled') {
        $cancelledOrders++;
    }
}


require_once 'header.php';

?>


<style>

/*
|--------------------------------------------------------------------------
| Orders Page
|--------------------------------------------------------------------------
*/

body {
    background: #eef0f1 !important;
}


.orders-page {
    margin-top: -10px;
    padding-bottom: 55px;
    color: #1c1f21;
}


/*
|--------------------------------------------------------------------------
| Hero
|--------------------------------------------------------------------------
*/

.orders-hero {
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


.orders-hero::after {
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


.orders-label {
    color: #d8ff24;

    font-size: 0.78rem;

    font-weight: 800;

    letter-spacing: 2px;

    text-transform: uppercase;

    margin-bottom: 9px;
}


.orders-title {
    color: white;

    font-size: 2.5rem;

    font-weight: 850;

    margin-bottom: 8px;
}


.orders-title span {
    color: #d8ff24;
}


.orders-subtitle {
    color: #c9ced1;

    max-width: 650px;

    margin-bottom: 0;
}


/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

.order-stats {
    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 15px;

    margin-bottom: 26px;
}


.order-stat {
    background: white;

    border: 1px solid #dde1e4;

    border-radius: 18px;

    padding: 21px;

    box-shadow:
        0 5px 15px
        rgba(0, 0, 0, 0.05);

    transition: 0.2s ease;
}


.order-stat:hover {
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
| Success Message
|--------------------------------------------------------------------------
*/

.update-message {
    background: #eff8cf;

    border: 1px solid #dceaa7;

    color: #566300;

    border-radius: 13px;

    padding: 13px 16px;

    margin-bottom: 20px;

    font-weight: 700;
}


/*
|--------------------------------------------------------------------------
| Orders Card
|--------------------------------------------------------------------------
*/

.orders-card {
    background: #ffffff;

    border: 1px solid #dde1e4;

    border-radius: 20px;

    overflow: hidden;

    box-shadow:
        0 6px 18px
        rgba(0, 0, 0, 0.05);
}


.orders-card-header {
    background: #1f2326;

    color: white;

    padding: 18px 21px;

    display: flex;

    justify-content: space-between;
    align-items: center;

    gap: 15px;
}


.orders-card-title {
    margin: 0;

    font-size: 1.05rem;

    font-weight: 800;
}


.orders-card-title i {
    color: #d8ff24;

    margin-right: 7px;
}


.orders-card-subtitle {
    color: #aeb4b7;

    font-size: 0.76rem;

    margin-top: 3px;
}


.orders-count {
    background: #d8ff24;

    color: #111;

    border-radius: 30px;

    padding: 7px 12px;

    font-size: 0.72rem;

    font-weight: 800;
}


/*
|--------------------------------------------------------------------------
| Table
|--------------------------------------------------------------------------
*/

.orders-table-wrap {
    overflow-x: auto;

    max-height: 720px;
}


.orders-table {
    margin-bottom: 0;

    min-width: 1100px;
}


.orders-table thead {
    position: sticky;

    top: 0;

    z-index: 5;
}


.orders-table thead th {
    background: #f1f3f4;

    color: #50575b;

    border-bottom: 1px solid #dfe3e5;

    font-size: 0.72rem;

    text-transform: uppercase;

    letter-spacing: 0.4px;

    padding: 13px 12px;

    white-space: nowrap;
}


.orders-table tbody td {
    padding: 13px 12px;

    vertical-align: middle;

    color: #292e31;

    border-color: #edf0f1;

    font-size: 0.84rem;
}


.orders-table tbody tr:hover {
    background: #f8f9f9;
}


/*
|--------------------------------------------------------------------------
| Order / User
|--------------------------------------------------------------------------
*/

.order-id {
    width: 36px;
    height: 36px;

    display: inline-flex;

    justify-content: center;
    align-items: center;

    background: #1f2326;

    color: #d8ff24;

    border-radius: 10px;

    font-size: 0.72rem;

    font-weight: 800;
}


.customer-email {
    font-weight: 700;

    color: #303538;
}


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
| Product Image
|--------------------------------------------------------------------------
*/

.order-shoe-image {
    width: 58px;
    height: 58px;

    object-fit: contain;

    background: #f0f2f3;

    border-radius: 10px;

    padding: 5px;
}


/*
|--------------------------------------------------------------------------
| Price
|--------------------------------------------------------------------------
*/

.order-price {
    font-weight: 700;
}


.order-total {
    font-weight: 850;

    color: #1d2124;
}


/*
|--------------------------------------------------------------------------
| Status Form
|--------------------------------------------------------------------------
*/

.status-form {
    display: flex;

    align-items: center;

    gap: 7px;
}


.status-select {
    min-width: 115px;

    border-radius: 9px;

    border: 1px solid #d4d9dc;

    background: #f7f8f8;

    font-size: 0.78rem;
}


.status-select:focus {
    border-color: #b3d200;

    box-shadow:
        0 0 0 0.2rem
        rgba(216, 255, 36, 0.14);
}


.update-status-btn {
    background: #1f2326;

    color: white;

    border: none;

    border-radius: 9px;

    padding: 7px 10px;

    font-size: 0.73rem;

    font-weight: 700;

    transition: 0.2s ease;
}


.update-status-btn:hover {
    background: #d8ff24;

    color: #111;
}


/*
|--------------------------------------------------------------------------
| Status Indicator
|--------------------------------------------------------------------------
*/

.status-pill {
    display: inline-flex;

    align-items: center;

    gap: 5px;

    border-radius: 30px;

    padding: 5px 9px;

    font-size: 0.68rem;

    font-weight: 800;

    margin-bottom: 7px;
}


.status-pending {
    background: #fff2cb;

    color: #926500;
}


.status-completed {
    background: #edf8d1;

    color: #5e6c00;
}


.status-cancelled {
    background: #ffe1e1;

    color: #b23434;
}


/*
|--------------------------------------------------------------------------
| Order Date
|--------------------------------------------------------------------------
*/

.order-date {
    color: #646b70;

    white-space: nowrap;
}


/*
|--------------------------------------------------------------------------
| Empty State
|--------------------------------------------------------------------------
*/

.orders-empty {
    background: white;

    border: 1px solid #dde1e4;

    border-radius: 18px;

    padding: 60px 20px;

    text-align: center;

    color: #777;
}


/*
|--------------------------------------------------------------------------
| Responsive
|--------------------------------------------------------------------------
*/

@media (max-width: 992px) {

    .order-stats {
        grid-template-columns:
            repeat(2, 1fr);
    }

}


@media (max-width: 576px) {

    .order-stats {
        grid-template-columns:
            1fr;
    }


    .orders-title {
        font-size: 2rem;
    }


    .orders-hero {
        padding: 27px;
    }


    .orders-card-header {
        align-items: flex-start;

        flex-direction: column;
    }

}

</style>


<div class="orders-page">


    <!--
    |--------------------------------------------------------------------------
    | Hero
    |--------------------------------------------------------------------------
    -->

    <section class="orders-hero">

        <div class="orders-label">

            Elite Sneaker Hub • Order Management

        </div>


        <h1 class="orders-title">

            COMPANY
            <span>ORDERS</span>

        </h1>


        <p class="orders-subtitle">

            Review customer orders, track quantities and
            update each order's current status.

        </p>

    </section>


    <!--
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    -->

    <section class="order-stats">


        <!-- Total Orders -->

        <div class="order-stat">

            <div class="stat-icon">

                <i class="bi bi-bag-check"></i>

            </div>


            <div class="stat-number">

                <?= $totalOrders ?>

            </div>


            <div class="stat-label">

                Total Orders

            </div>

        </div>


        <!-- Pending -->

        <div class="order-stat">

            <div class="stat-icon">

                <i class="bi bi-clock-history"></i>

            </div>


            <div class="stat-number">

                <?= $pendingOrders ?>

            </div>


            <div class="stat-label">

                Pending Orders

            </div>

        </div>


        <!-- Completed -->

        <div class="order-stat">

            <div class="stat-icon">

                <i class="bi bi-check-circle"></i>

            </div>


            <div class="stat-number">

                <?= $completedOrders ?>

            </div>


            <div class="stat-label">

                Completed Orders

            </div>

        </div>


        <!-- Cancelled -->

        <div class="order-stat">

            <div class="stat-icon">

                <i class="bi bi-x-circle"></i>

            </div>


            <div class="stat-number">

                <?= $cancelledOrders ?>

            </div>


            <div class="stat-label">

                Cancelled Orders

            </div>

        </div>


    </section>


    <!--
    |--------------------------------------------------------------------------
    | Update Confirmation
    |--------------------------------------------------------------------------
    -->

    <?php if (isset($_GET['updated'])): ?>

        <div class="update-message">

            <i class="bi bi-check-circle-fill"></i>

            Order status updated successfully.

        </div>

    <?php endif; ?>


    <!--
    |--------------------------------------------------------------------------
    | Orders
    |--------------------------------------------------------------------------
    -->

    <?php if (empty($orders)): ?>


        <div class="orders-empty">

            <i
                class="bi bi-bag"
                style="font-size: 2rem;"
            ></i>


            <h5 class="mt-3">

                No orders found

            </h5>


            <p class="mb-0">

                Company orders will appear here.

            </p>

        </div>


    <?php else: ?>


        <section class="orders-card">


            <!-- Header -->

            <div class="orders-card-header">


                <div>

                    <h5 class="orders-card-title">

                        <i class="bi bi-receipt-cutoff"></i>

                        Order Management

                    </h5>


                    <div class="orders-card-subtitle">

                        View company orders and update their status.

                    </div>

                </div>


                <div class="orders-count">

                    <?= $totalOrders ?>

                    ORDERS

                </div>


            </div>


            <!-- Table -->

            <div class="orders-table-wrap">


                <table class="table orders-table">


                    <thead>

                        <tr>

                            <th>
                                #
                            </th>

                            <th>
                                Customer
                            </th>

                            <th>
                                Shoe
                            </th>

                            <th>
                                Brand
                            </th>

                            <th>
                                Image
                            </th>

                            <th>
                                Price
                            </th>

                            <th>
                                Qty
                            </th>

                            <th>
                                Total
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Order Date
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php foreach ($orders as $order): ?>


                            <tr>


                                <!-- ID -->

                                <td>

                                    <span class="order-id">

                                        #<?= $order['id'] ?>

                                    </span>

                                </td>


                                <!-- Customer -->

                                <td>

                                    <span class="customer-email">

                                        <?= htmlspecialchars(
                                            $order['email']
                                            ?? 'Unknown User'
                                        ) ?>

                                    </span>

                                </td>


                                <!-- Shoe -->

                                <td>

                                    <span class="shoe-name">

                                        <?= htmlspecialchars(
                                            $order['shoe_name']
                                            ?? 'Unknown Shoe'
                                        ) ?>

                                    </span>

                                </td>


                                <!-- Brand -->

                                <td>

                                    <span class="brand-badge">

                                        <?= htmlspecialchars(
                                            $order['brand_name']
                                            ?? 'Unknown'
                                        ) ?>

                                    </span>

                                </td>


                                <!-- Image -->

                                <td>


                                    <?php if (
                                        !empty($order['image_url'])
                                    ): ?>


                                        <img
                                            src="<?= htmlspecialchars(
                                                $order['image_url']
                                            ) ?>"
                                            alt="<?= htmlspecialchars(
                                                $order['shoe_name']
                                                ?? 'Sneaker'
                                            ) ?>"
                                            class="order-shoe-image"
                                        >


                                    <?php else: ?>


                                        <img
                                            src="shoe-default.avif"
                                            alt="Sneaker"
                                            class="order-shoe-image"
                                        >


                                    <?php endif; ?>


                                </td>


                                <!-- Price -->

                                <td class="order-price">

                                    $<?= number_format(
                                        (float) $order['price'],
                                        2
                                    ) ?>

                                </td>


                                <!-- Quantity -->

                                <td>

                                    <?= (int) $order['quantity'] ?>

                                </td>


                                <!-- Total -->

                                <td class="order-total">

                                    $<?= number_format(
                                        (float) $order['price']
                                        *
                                        (int) $order['quantity'],
                                        2
                                    ) ?>

                                </td>


                                <!-- Status -->

                                <td>


                                    <?php

                                    $statusClass =
                                        'status-pending';

                                    if (
                                        $order['status']
                                        === 'completed'
                                    ) {
                                        $statusClass =
                                            'status-completed';
                                    }

                                    elseif (
                                        $order['status']
                                        === 'cancelled'
                                    ) {
                                        $statusClass =
                                            'status-cancelled';
                                    }

                                    ?>


                                    <div>

                                        <span
                                            class="status-pill
                                            <?= $statusClass ?>"
                                        >

                                            <?php if (
                                                $order['status']
                                                === 'completed'
                                            ): ?>

                                                <i class="bi bi-check-circle-fill"></i>

                                            <?php elseif (
                                                $order['status']
                                                === 'cancelled'
                                            ): ?>

                                                <i class="bi bi-x-circle-fill"></i>

                                            <?php else: ?>

                                                <i class="bi bi-clock-fill"></i>

                                            <?php endif; ?>


                                            <?= ucfirst(
                                                $order['status']
                                            ) ?>

                                        </span>

                                    </div>


                                    <form
                                        method="POST"
                                        action="orders.php"
                                        class="status-form"
                                    >


                                        <input
                                            type="hidden"
                                            name="order_id"
                                            value="<?= $order['id'] ?>"
                                        >


                                        <select
                                            name="status"
                                            class="form-select form-select-sm status-select"
                                        >


                                            <option
                                                value="pending"
                                                <?= $order['status']
                                                    === 'pending'
                                                    ? 'selected'
                                                    : '' ?>
                                            >

                                                Pending

                                            </option>


                                            <option
                                                value="completed"
                                                <?= $order['status']
                                                    === 'completed'
                                                    ? 'selected'
                                                    : '' ?>
                                            >

                                                Completed

                                            </option>


                                            <option
                                                value="cancelled"
                                                <?= $order['status']
                                                    === 'cancelled'
                                                    ? 'selected'
                                                    : '' ?>
                                            >

                                                Cancelled

                                            </option>


                                        </select>


                                        <button
                                            type="submit"
                                            class="update-status-btn"
                                        >

                                            Update

                                        </button>


                                    </form>


                                </td>


                                <!-- Date -->

                                <td>

                                    <span class="order-date">

                                        <?= date(
                                            'Y-m-d H:i',
                                            strtotime(
                                                $order['order_date']
                                            )
                                        ) ?>

                                    </span>

                                </td>


                            </tr>


                        <?php endforeach; ?>


                    </tbody>


                </table>


            </div>


        </section>


    <?php endif; ?>


</div>


<?php require_once 'footer.php'; ?>