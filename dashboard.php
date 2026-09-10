<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


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
| Load Data
|--------------------------------------------------------------------------
*/

$brands = $pdo
    ->query("
        SELECT *
        FROM brands
        ORDER BY name ASC
    ")
    ->fetchAll(PDO::FETCH_ASSOC);


$shoes = $pdo
    ->query("
        SELECT
            s.*,
            c.name AS category_name,
            b.name AS brand_name

        FROM shoes s

        LEFT JOIN categories c
            ON s.category_id = c.id

        LEFT JOIN brands b
            ON s.brand_id = b.id

        ORDER BY s.id DESC
    ")
    ->fetchAll(PDO::FETCH_ASSOC);


$categories = $pdo
    ->query("
        SELECT *
        FROM categories
        ORDER BY name ASC
    ")
    ->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/

$totalShoes = count($shoes);

$totalBrands = count($brands);

$totalCategories = count($categories);

$totalStock = 0;

foreach ($shoes as $shoe) {
    $totalStock += (int) $shoe['stock'];
}

?>


<style>

/*
|--------------------------------------------------------------------------
| Dashboard Theme
|--------------------------------------------------------------------------
*/

body {
    background: #eef0f1 !important;
}


.dashboard-page {
    margin-top: -10px;
    padding-bottom: 55px;
    color: #1c1f21;
}


/*
|--------------------------------------------------------------------------
| Dashboard Hero
|--------------------------------------------------------------------------
*/

.dashboard-hero {
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


.dashboard-hero::after {
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


.dashboard-label {
    color: #d8ff24;

    font-size: 0.78rem;
    font-weight: 800;

    letter-spacing: 2px;
    text-transform: uppercase;

    margin-bottom: 9px;
}


.dashboard-title {
    color: #ffffff;

    font-size: 2.5rem;
    font-weight: 850;

    margin-bottom: 8px;
}


.dashboard-title span {
    color: #d8ff24;
}


.dashboard-subtitle {
    color: #c9ced1;

    max-width: 650px;

    margin-bottom: 0;
}


/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

.dashboard-stats {
    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 15px;

    margin-bottom: 28px;
}


.dashboard-stat {
    background: #ffffff;

    border: 1px solid #dde1e4;

    border-radius: 18px;

    padding: 21px;

    box-shadow:
        0 5px 15px
        rgba(0, 0, 0, 0.05);
}


.stat-icon {
    width: 42px;
    height: 42px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 12px;

    background: #d8ff24;

    color: #151515;

    font-size: 1.1rem;

    margin-bottom: 13px;
}


.stat-number {
    font-size: 1.65rem;

    font-weight: 850;

    color: #1d2124;
}


.stat-label {
    color: #777f84;

    font-size: 0.82rem;
}


/*
|--------------------------------------------------------------------------
| Management Choice Cards
|--------------------------------------------------------------------------
*/

.management-choice-grid {
    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 18px;

    margin-bottom: 26px;
}


.management-choice-card {
    background: #ffffff;

    border: 1px solid #dde1e4;

    border-radius: 20px;

    padding: 25px;

    box-shadow:
        0 6px 18px
        rgba(0, 0, 0, 0.05);

    cursor: pointer;

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease,
        border-color 0.2s ease;
}


.management-choice-card:hover {
    transform: translateY(-4px);

    border-color: #c5e300;

    box-shadow:
        0 12px 28px
        rgba(0, 0, 0, 0.09);
}


.management-choice-icon {
    width: 52px;
    height: 52px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 15px;

    background: #1f2326;

    color: #d8ff24;

    font-size: 1.35rem;

    margin-bottom: 18px;
}


.management-choice-title {
    font-size: 1.15rem;

    font-weight: 850;

    color: #1d2124;

    margin-bottom: 6px;
}


.management-choice-text {
    color: #777f84;

    font-size: 0.86rem;

    margin-bottom: 18px;
}


.management-choice-action {
    display: inline-flex;

    align-items: center;

    gap: 7px;

    color: #1d2124;

    font-size: 0.8rem;

    font-weight: 800;
}


.management-choice-action i {
    color: #91a900;
}


/*
|--------------------------------------------------------------------------
| Management Sections
|--------------------------------------------------------------------------
*/

.management-section {
    display: none;
}


.management-section.active {
    display: block;
}


.management-card {
    background: #ffffff;

    border: 1px solid #dde1e4;

    border-radius: 20px;

    overflow: hidden;

    margin-bottom: 26px;

    box-shadow:
        0 6px 18px
        rgba(0, 0, 0, 0.05);
}


.management-card-header {
    display: flex;

    justify-content: space-between;
    align-items: center;

    gap: 15px;

    background: #1f2326;

    color: white;

    padding: 18px 21px;
}


.management-card-title {
    margin: 0;

    font-size: 1.05rem;

    font-weight: 800;
}


.management-card-title i {
    color: #d8ff24;

    margin-right: 7px;
}


.management-card-subtitle {
    color: #aeb4b7;

    font-size: 0.76rem;

    margin-top: 3px;
}


.management-card-body {
    padding: 21px;
}


/*
|--------------------------------------------------------------------------
| Back Button
|--------------------------------------------------------------------------
*/

.back-management-btn {
    background: transparent;

    color: #d8ff24;

    border: 1px solid #4b5054;

    border-radius: 9px;

    padding: 7px 12px;

    font-size: 0.78rem;

    font-weight: 700;
}


.back-management-btn:hover {
    background: #d8ff24;

    color: #111;

    border-color: #d8ff24;
}


/*
|--------------------------------------------------------------------------
| Lime Button
|--------------------------------------------------------------------------
*/

.btn-lime {
    background: #d8ff24;

    color: #111;

    border: none;

    border-radius: 11px;

    font-weight: 800;

    padding: 10px 16px;

    transition: 0.2s ease;
}


.btn-lime:hover {
    background: #c9ee20;

    color: #111;

    transform: translateY(-1px);
}


/*
|--------------------------------------------------------------------------
| Brand Add Form
|--------------------------------------------------------------------------
*/

.brand-add-form {
    background: #f5f6f6;

    border: 1px solid #e1e4e6;

    border-radius: 14px;

    padding: 15px;

    margin-bottom: 19px;
}


.brand-add-form .form-control {
    min-height: 43px;

    border-radius: 10px;

    border: 1px solid #d6dade;

    background: white;
}


.brand-add-form .form-control:focus {
    border-color: #b7d700;

    box-shadow:
        0 0 0 0.2rem
        rgba(216, 255, 36, 0.15);
}


/*
|--------------------------------------------------------------------------
| Tables
|--------------------------------------------------------------------------
*/

.dashboard-table-wrap {
    overflow-x: auto;

    max-height: 650px;
}


.dashboard-table {
    margin-bottom: 0;
}


.dashboard-table thead {
    position: sticky;

    top: 0;

    z-index: 2;
}


.dashboard-table thead th {
    background: #f1f3f4;

    color: #4d5458;

    border-bottom: 1px solid #dfe3e5;

    font-size: 0.75rem;

    text-transform: uppercase;

    letter-spacing: 0.4px;

    padding: 13px 12px;
}


.dashboard-table tbody td {
    padding: 13px 12px;

    vertical-align: middle;

    color: #2a2e31;

    border-color: #edf0f1;
}


.dashboard-table tbody tr:hover {
    background: #f8f9f9;
}


/*
|--------------------------------------------------------------------------
| Brand Editing
|--------------------------------------------------------------------------
*/

.brand-edit-form {
    display: flex;

    align-items: center;

    gap: 8px;

    flex-wrap: wrap;
}


.brand-edit-input {
    width: 180px;

    min-height: 36px;

    border-radius: 8px;

    border: 1px solid #d4d8db;

    padding: 5px 9px;
}


.brand-edit-input:focus {
    outline: none;

    border-color: #b3d100;
}


/*
|--------------------------------------------------------------------------
| Small Buttons
|--------------------------------------------------------------------------
*/

.btn-update {
    background: #1f2326;

    color: white;

    border: none;

    border-radius: 8px;

    padding: 7px 11px;

    font-size: 0.75rem;

    font-weight: 700;
}


.btn-update:hover {
    background: #d8ff24;

    color: #111;
}


.btn-delete {
    background: #fff0f0;

    color: #c53a3a;

    border: 1px solid #f0cccc;

    border-radius: 8px;

    padding: 7px 11px;

    font-size: 0.75rem;

    font-weight: 700;

    text-decoration: none;
}


.btn-delete:hover {
    background: #d94646;

    color: white;

    border-color: #d94646;
}


/*
|--------------------------------------------------------------------------
| Badges
|--------------------------------------------------------------------------
*/

.product-name-cell {
    font-weight: 800;

    color: #1c2023;
}


.brand-badge {
    display: inline-block;

    background: #edf6c9;

    color: #606d00;

    border-radius: 30px;

    padding: 5px 9px;

    font-size: 0.72rem;

    font-weight: 700;
}


.category-badge {
    display: inline-block;

    background: #f0f2f3;

    color: #53595d;

    border-radius: 30px;

    padding: 5px 9px;

    font-size: 0.72rem;

    font-weight: 700;
}


/*
|--------------------------------------------------------------------------
| Stock
|--------------------------------------------------------------------------
*/

.stock-good,
.stock-low,
.stock-out {
    display: inline-block;

    border-radius: 30px;

    padding: 5px 9px;

    font-size: 0.72rem;

    font-weight: 800;
}


.stock-good {
    background: #eef8cf;

    color: #647200;
}


.stock-low {
    background: #fff2cb;

    color: #986900;
}


.stock-out {
    background: #ffe0e0;

    color: #b52f2f;
}


/*
|--------------------------------------------------------------------------
| Product Actions
|--------------------------------------------------------------------------
*/

.product-actions {
    display: flex;

    gap: 7px;
}


.edit-product-btn {
    width: 34px;
    height: 34px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    background: #1f2326;

    color: white;

    border: none;

    border-radius: 9px;
}


.edit-product-btn:hover {
    background: #d8ff24;

    color: #111;
}


.delete-product-btn {
    width: 34px;
    height: 34px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    background: #fff0f0;

    color: #c43838;

    border: 1px solid #efcccc;

    border-radius: 9px;

    text-decoration: none;
}


.delete-product-btn:hover {
    background: #d94343;

    color: white;
}


/*
|--------------------------------------------------------------------------
| Modal
|--------------------------------------------------------------------------
*/

.modal-content {
    border: none;

    border-radius: 18px;

    overflow: hidden;

    box-shadow:
        0 20px 60px
        rgba(0, 0, 0, 0.25);
}


.modal-header {
    background: #1f2326;

    color: white;

    border-bottom: none;

    padding: 19px 21px;
}


.modal-title {
    font-weight: 800;
}


.modal-title i {
    color: #d8ff24;

    margin-right: 6px;
}


.modal-body {
    padding: 22px;
}


.modal-body label {
    font-size: 0.8rem;

    font-weight: 700;

    color: #444a4e;

    margin-bottom: 6px;
}


.modal-body .form-control,
.modal-body .form-select {
    border-radius: 10px;

    min-height: 43px;

    border: 1px solid #d6dade;
}


.modal-footer {
    border-top: 1px solid #eceeef;

    padding: 15px 22px;
}


/*
|--------------------------------------------------------------------------
| Responsive
|--------------------------------------------------------------------------
*/

@media (max-width: 992px) {

    .dashboard-stats {
        grid-template-columns:
            repeat(2, 1fr);
    }


    .management-choice-grid {
        grid-template-columns:
            1fr;
    }

}


@media (max-width: 576px) {

    .dashboard-stats {
        grid-template-columns:
            1fr;
    }


    .dashboard-title {
        font-size: 2rem;
    }


    .dashboard-hero {
        padding: 28px;
    }


    .management-card-header {
        align-items: flex-start;
        flex-direction: column;
    }

}

</style>


<div class="dashboard-page">


    <!--
    |--------------------------------------------------------------------------
    | Dashboard Hero
    |--------------------------------------------------------------------------
    -->

    <section class="dashboard-hero">

        <div class="dashboard-label">
            Elite Sneaker Hub • Management
        </div>


        <h1 class="dashboard-title">

            INVENTORY
            <span>DASHBOARD</span>

        </h1>


        <p class="dashboard-subtitle">

            View company inventory statistics and access
            brand and product management tools.

        </p>

    </section>


    <!--
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    -->

    <section class="dashboard-stats">


        <div class="dashboard-stat">

            <div class="stat-icon">
                <i class="bi bi-box-seam"></i>
            </div>

            <div class="stat-number">
                <?= $totalShoes ?>
            </div>

            <div class="stat-label">
                Total Sneakers
            </div>

        </div>


        <div class="dashboard-stat">

            <div class="stat-icon">
                <i class="bi bi-tags"></i>
            </div>

            <div class="stat-number">
                <?= $totalBrands ?>
            </div>

            <div class="stat-label">
                Active Brands
            </div>

        </div>


        <div class="dashboard-stat">

            <div class="stat-icon">
                <i class="bi bi-grid"></i>
            </div>

            <div class="stat-number">
                <?= $totalCategories ?>
            </div>

            <div class="stat-label">
                Categories
            </div>

        </div>


        <div class="dashboard-stat">

            <div class="stat-icon">
                <i class="bi bi-boxes"></i>
            </div>

            <div class="stat-number">
                <?= $totalStock ?>
            </div>

            <div class="stat-label">
                Total Stock Units
            </div>

        </div>


    </section>


    <!--
    |--------------------------------------------------------------------------
    | Management Choices
    |--------------------------------------------------------------------------
    -->

    <section
        class="management-choice-grid"
        id="managementChoices"
    >


        <!-- Brand Management -->

        <div
            class="management-choice-card"
            onclick="openManagement('brandSection')"
        >

            <div class="management-choice-icon">
                <i class="bi bi-tags-fill"></i>
            </div>


            <div class="management-choice-title">

                Brand Management

            </div>


            <div class="management-choice-text">

                View all brands and add, rename or delete
                company sneaker brands.

            </div>


            <div class="management-choice-action">

                Open Brand Management

                <i class="bi bi-arrow-right"></i>

            </div>

        </div>


        <!-- Product Management -->

        <div
            class="management-choice-card"
            onclick="openManagement('productSection')"
        >

            <div class="management-choice-icon">
                <i class="bi bi-box-seam-fill"></i>
            </div>


            <div class="management-choice-title">

                Product Management

            </div>


            <div class="management-choice-text">

                View sneaker inventory and add, edit or
                delete company products.

            </div>


            <div class="management-choice-action">

                Open Product Management

                <i class="bi bi-arrow-right"></i>

            </div>

        </div>


    </section>


    <!--
    |--------------------------------------------------------------------------
    | Brand Management Section
    |--------------------------------------------------------------------------
    -->

    <section
        class="management-section"
        id="brandSection"
    >


        <div class="management-card">


            <div class="management-card-header">


                <div>

                    <h5 class="management-card-title">

                        <i class="bi bi-tags-fill"></i>

                        Brand Management

                    </h5>

                    <div class="management-card-subtitle">

                        Add, rename or remove sneaker brands.

                    </div>

                </div>


                <button
                    type="button"
                    class="back-management-btn"
                    onclick="closeManagement()"
                >

                    <i class="bi bi-arrow-left"></i>

                    Back

                </button>


            </div>


            <div class="management-card-body">


                <!-- Add Brand -->

                <form
                    class="brand-add-form"
                    method="POST"
                    action="crud.php?action=add_brand"
                >

                    <div class="row g-2 align-items-center">


                        <div class="col-md-9">

                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                placeholder="Enter new brand name..."
                                required
                            >

                        </div>


                        <div class="col-md-3">

                            <button
                                type="submit"
                                class="btn btn-lime w-100"
                            >

                                <i class="bi bi-plus-circle"></i>

                                Add Brand

                            </button>

                        </div>


                    </div>

                </form>


                <!-- Brand List -->

                <div class="dashboard-table-wrap">


                    <table class="table dashboard-table">


                        <thead>

                            <tr>

                                <th>
                                    Brand
                                </th>

                                <th style="width: 340px;">
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php foreach ($brands as $brand): ?>


                                <tr>


                                    <td>

                                        <span class="brand-badge">

                                            <?= htmlspecialchars(
                                                $brand['name']
                                            ) ?>

                                        </span>

                                    </td>


                                    <td>


                                        <form
                                            method="POST"
                                            action="crud.php?action=update_brand"
                                            class="brand-edit-form"
                                        >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= $brand['id'] ?>"
                                            >


                                            <input
                                                type="text"
                                                name="name"
                                                class="brand-edit-input"
                                                value="<?= htmlspecialchars(
                                                    $brand['name']
                                                ) ?>"
                                                required
                                            >


                                            <button
                                                type="submit"
                                                class="btn-update"
                                            >

                                                <i class="bi bi-check-lg"></i>

                                                Update

                                            </button>


                                            <a
                                                href="crud.php?action=delete_brand&id=<?= $brand['id'] ?>"
                                                class="btn-delete"
                                                onclick="return confirm('Delete this brand?')"
                                            >

                                                <i class="bi bi-trash"></i>

                                                Delete

                                            </a>


                                        </form>


                                    </td>


                                </tr>


                            <?php endforeach; ?>


                        </tbody>


                    </table>


                </div>


            </div>


        </div>


    </section>


    <!--
    |--------------------------------------------------------------------------
    | Product Management Section
    |--------------------------------------------------------------------------
    -->

    <section
        class="management-section"
        id="productSection"
    >


        <div class="management-card">


            <div class="management-card-header">


                <div>

                    <h5 class="management-card-title">

                        <i class="bi bi-box-seam-fill"></i>

                        Product Management

                    </h5>

                    <div class="management-card-subtitle">

                        Add, edit or remove sneaker products.

                    </div>

                </div>


                <div class="d-flex gap-2">


                    <button
                        class="btn btn-lime"
                        data-bs-toggle="modal"
                        data-bs-target="#addModal"
                    >

                        <i class="bi bi-plus-circle"></i>

                        Add Shoe

                    </button>


                    <button
                        type="button"
                        class="back-management-btn"
                        onclick="closeManagement()"
                    >

                        <i class="bi bi-arrow-left"></i>

                        Back

                    </button>


                </div>


            </div>


            <div class="management-card-body">


                <div class="dashboard-table-wrap">


                    <table class="table dashboard-table">


                        <thead>

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


                            <?php foreach ($shoes as $shoe): ?>


                                <tr>


                                    <td class="product-name-cell">

                                        <?= htmlspecialchars(
                                            $shoe['name']
                                        ) ?>

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

                                        <strong>

                                            $<?= number_format(
                                                $shoe['price'],
                                                2
                                            ) ?>

                                        </strong>

                                    </td>


                                    <td>

                                        US
                                        <?= htmlspecialchars(
                                            $shoe['size']
                                        ) ?>

                                    </td>


                                    <td>


                                        <?php if ($shoe['stock'] > 10): ?>

                                            <span class="stock-good">

                                                <?= $shoe['stock'] ?>
                                                In Stock

                                            </span>


                                        <?php elseif ($shoe['stock'] > 0): ?>

                                            <span class="stock-low">

                                                <?= $shoe['stock'] ?>
                                                Low

                                            </span>


                                        <?php else: ?>

                                            <span class="stock-out">

                                                Out of Stock

                                            </span>

                                        <?php endif; ?>


                                    </td>


                                    <td>

                                        <span class="category-badge">

                                            <?= htmlspecialchars(
                                                $shoe['category_name']
                                                ?? 'Uncategorized'
                                            ) ?>

                                        </span>

                                    </td>


                                    <td>


                                        <div class="product-actions">


                                            <button
                                                class="edit-product-btn"
                                                onclick="editShoe(<?= $shoe['id'] ?>)"
                                                title="Edit Shoe"
                                            >

                                                <i class="bi bi-pencil"></i>

                                            </button>


                                            <a
                                                href="crud.php?action=delete_shoe&id=<?= $shoe['id'] ?>"
                                                class="delete-product-btn"
                                                onclick="return confirm('Are you sure you want to delete this shoe?')"
                                                title="Delete Shoe"
                                            >

                                                <i class="bi bi-trash"></i>

                                            </a>


                                        </div>


                                    </td>


                                </tr>


                            <?php endforeach; ?>


                        </tbody>


                    </table>


                </div>


            </div>


        </div>


    </section>


</div>


<!--
|--------------------------------------------------------------------------
| Add Shoe Modal
|--------------------------------------------------------------------------
-->

<div
    class="modal fade"
    id="addModal"
    tabindex="-1"
>


    <div class="modal-dialog modal-dialog-centered">


        <div class="modal-content">


            <form
                action="crud.php?action=add_shoe"
                method="POST"
            >


                <div class="modal-header">

                    <h5 class="modal-title">

                        <i class="bi bi-plus-circle"></i>

                        Add New Sneaker

                    </h5>


                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                    ></button>

                </div>


                <div class="modal-body">


                    <div class="mb-3">

                        <label class="form-label">
                            Shoe Name
                        </label>

                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            placeholder="Enter shoe name"
                            required
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Brand
                        </label>

                        <select
                            name="brand_id"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Select Brand
                            </option>


                            <?php foreach ($brands as $brand): ?>

                                <option
                                    value="<?= $brand['id'] ?>"
                                >

                                    <?= htmlspecialchars(
                                        $brand['name']
                                    ) ?>

                                </option>

                            <?php endforeach; ?>


                        </select>

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Price
                        </label>

                        <input
                            type="number"
                            step="0.01"
                            name="price"
                            class="form-control"
                            placeholder="0.00"
                            required
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Size
                        </label>

                        <input
                            type="number"
                            step="0.5"
                            name="size"
                            class="form-control"
                            placeholder="US Size"
                            required
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Stock Quantity
                        </label>

                        <input
                            type="number"
                            name="stock"
                            class="form-control"
                            placeholder="Enter stock quantity"
                            required
                        >

                    </div>


                    <div class="mb-3">

                        <label class="form-label">
                            Category
                        </label>

                        <select
                            name="category_id"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Select Category
                            </option>


                            <?php foreach ($categories as $category): ?>

                                <option
                                    value="<?= $category['id'] ?>"
                                >

                                    <?= htmlspecialchars(
                                        $category['name']
                                    ) ?>

                                </option>

                            <?php endforeach; ?>


                        </select>

                    </div>


                </div>


                <div class="modal-footer">


                    <button
                        type="button"
                        class="btn btn-light border"
                        data-bs-dismiss="modal"
                    >

                        Cancel

                    </button>


                    <button
                        type="submit"
                        class="btn btn-lime"
                    >

                        <i class="bi bi-check-circle"></i>

                        Save Sneaker

                    </button>


                </div>


            </form>


        </div>


    </div>


</div>


<script>

/*
|--------------------------------------------------------------------------
| Dashboard Management Navigation
|--------------------------------------------------------------------------
*/

function openManagement(sectionId) {

    const choices =
        document.getElementById('managementChoices');

    const brandSection =
        document.getElementById('brandSection');

    const productSection =
        document.getElementById('productSection');


    choices.style.display = 'none';


    brandSection.classList.remove('active');

    productSection.classList.remove('active');


    const selectedSection =
        document.getElementById(sectionId);


    selectedSection.classList.add('active');


    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });

}


function closeManagement() {

    const choices =
        document.getElementById('managementChoices');

    const brandSection =
        document.getElementById('brandSection');

    const productSection =
        document.getElementById('productSection');


    brandSection.classList.remove('active');

    productSection.classList.remove('active');


    choices.style.display = 'grid';


    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });

}

</script>


<?php require_once 'footer.php'; ?>