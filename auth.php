<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php';


/*
|--------------------------------------------------------------------------
| Check Staff Login
|--------------------------------------------------------------------------
*/

$isLoggedIn =
    isset($_SESSION['user_id']) &&
    isset($_SESSION['role']) &&
    $_SESSION['role'] === 'admin';

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Elite Sneaker Hub Management System</title>


    <!-- Bootstrap CSS -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Bootstrap Icons -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css"
    >


    <style>

        body {
            background-color: #f5f6f8;
        }


        /*
        |--------------------------------------------------------------------------
        | Navbar
        |--------------------------------------------------------------------------
        */

        .navbar {
            min-height: 70px;
        }


        .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.5px;
        }


        .navbar-dark .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            transition: 0.2s ease;
        }


        .navbar-dark .nav-link:hover {
            color: white !important;
        }


        /*
        |--------------------------------------------------------------------------
        | Staff Information
        |--------------------------------------------------------------------------
        */

        .staff-email {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }


        /*
        |--------------------------------------------------------------------------
        | Logout
        |--------------------------------------------------------------------------
        */

        .logout-btn {
            border-radius: 8px;
            padding-left: 15px;
            padding-right: 15px;
        }


        /*
        |--------------------------------------------------------------------------
        | Cards
        |--------------------------------------------------------------------------
        */

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
        }


        /*
        |--------------------------------------------------------------------------
        | Sneaker Cards
        |--------------------------------------------------------------------------
        */

        .sneaker-card {
            transition: all 0.3s ease;
            overflow: hidden;
        }


        .sneaker-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
        }


        /*
        |--------------------------------------------------------------------------
        | Tables
        |--------------------------------------------------------------------------
        */

        .table {
            vertical-align: middle;
        }


        /*
        |--------------------------------------------------------------------------
        | Main Content
        |--------------------------------------------------------------------------
        */

        .main-content {
            padding-top: 10px;
            padding-bottom: 40px;
        }

    </style>

</head>


<body>


<!--
|--------------------------------------------------------------------------
| Company Navigation
|--------------------------------------------------------------------------
-->

<nav class="navbar navbar-expand-lg navbar-dark bg-black mb-4">

    <div class="container">


        <!--
        |--------------------------------------------------------------------------
        | LOGO
        |--------------------------------------------------------------------------
        |
        | Logged in  -> Inventory
        | Logged out -> Staff Login
        |
        -->

        <a
            class="navbar-brand"
            href="<?= $isLoggedIn ? 'index.php' : 'auth.php' ?>"
        >

            <i class="bi bi-lightning-charge-fill"></i>

            ELITE SNEAKER HUB

        </a>


        <?php if ($isLoggedIn): ?>


            <!-- Mobile Navigation -->

            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarContent"
            >

                <span class="navbar-toggler-icon"></span>

            </button>


            <div
                class="collapse navbar-collapse"
                id="navbarContent"
            >


                <!-- Main Navigation -->

                <ul class="navbar-nav me-auto mb-2 mb-lg-0">


                    <!-- Inventory -->

                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="index.php"
                        >

                            <i class="bi bi-box-seam"></i>

                            Inventory

                        </a>

                    </li>


                    <!-- Dashboard -->

                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="dashboard.php"
                        >

                            <i class="bi bi-speedometer2"></i>

                            Dashboard

                        </a>

                    </li>


                    <!-- Orders -->

                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="orders.php"
                        >

                            <i class="bi bi-receipt"></i>

                            Orders

                        </a>

                    </li>


                    <!-- Reports -->

                    <li class="nav-item">

                        <a
                            class="nav-link"
                            href="reports.php"
                        >

                            <i class="bi bi-bar-chart"></i>

                            Reports

                        </a>

                    </li>

                </ul>


                <!-- Right Side -->

                <div class="d-flex align-items-center gap-3">


                    <!-- Staff Email -->

                    <div class="staff-email">

                        <i class="bi bi-person-circle"></i>

                        <?= htmlspecialchars($_SESSION['email']) ?>

                    </div>


                    <!-- Logout -->

                    <a
                        href="auth.php?logout=1"
                        class="btn btn-outline-light logout-btn"
                    >

                        <i class="bi bi-box-arrow-right"></i>

                        Logout

                    </a>

                </div>

            </div>


        <?php endif; ?>

    </div>

</nav>


<!--
|--------------------------------------------------------------------------
| Main Content
|--------------------------------------------------------------------------
-->

<div class="container main-content">