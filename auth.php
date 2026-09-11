<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php';


/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/

if (isset($_GET['logout']) && $_GET['logout'] == '1') {

    $_SESSION = [];

    if (ini_get("session.use_cookies")) {

        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    session_destroy();

    header('Location: auth.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Already Logged In
|--------------------------------------------------------------------------
*/

if (
    isset($_SESSION['user_id']) &&
    isset($_SESSION['role']) &&
    $_SESSION['role'] === 'admin'
) {
    header('Location: index.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Login
|--------------------------------------------------------------------------
*/

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';


    if ($email === '' || $password === '') {

        $error = 'Please enter both email and password.';

    } else {

        $stmt = $pdo->prepare("
            SELECT *
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->execute([$email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);


        if (
            $user &&
            password_verify($password, $user['password'])
        ) {

            if ($user['role'] !== 'admin') {

                $error = 'Staff access only.';

            } else {

                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                header('Location: index.php');
                exit;
            }

        } else {

            $error = 'Invalid email or password.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Staff Login | Elite Sneaker Hub</title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css"
    >


    <style>

        * {
            box-sizing: border-box;
        }


        body {
            margin: 0;
            min-height: 100vh;

            background: #eef0f1;

            font-family:
                Arial,
                Helvetica,
                sans-serif;
        }


        /*
        |--------------------------------------------------------------------------
        | Top Bar
        |--------------------------------------------------------------------------
        */

        .topbar {
            background: #050505;

            color: white;

            min-height: 72px;

            display: flex;

            align-items: center;
        }


        .topbar-inner {
            width: 100%;

            max-width: 1140px;

            margin: 0 auto;

            padding: 0 20px;
        }


        .brand {
            font-size: 1.15rem;

            font-weight: 800;

            letter-spacing: 0.5px;

            color: white;

            text-decoration: none;
        }


        .brand i {
            color: #d8ff24;

            margin-right: 5px;
        }


        /*
        |--------------------------------------------------------------------------
        | Login Area
        |--------------------------------------------------------------------------
        */

        .login-wrapper {
            min-height: calc(100vh - 72px);

            display: flex;

            justify-content: center;
            align-items: center;

            padding: 35px 20px;
        }


        .login-card {
            width: 100%;

            max-width: 430px;

            background: white;

            border: 1px solid #dde1e4;

            border-radius: 22px;

            padding: 34px;

            box-shadow:
                0 14px 35px
                rgba(0, 0, 0, 0.08);
        }


        .login-icon {
            width: 58px;
            height: 58px;

            display: flex;

            justify-content: center;
            align-items: center;

            border-radius: 15px;

            background: #1f2326;

            color: #d8ff24;

            font-size: 1.5rem;

            margin-bottom: 20px;
        }


        .login-label {
            color: #7a8084;

            font-size: 0.76rem;

            font-weight: 800;

            text-transform: uppercase;

            letter-spacing: 1.3px;

            margin-bottom: 6px;
        }


        .login-title {
            font-size: 2rem;

            font-weight: 850;

            color: #1d2124;

            margin-bottom: 8px;
        }


        .login-subtitle {
            color: #747b80;

            font-size: 0.9rem;

            margin-bottom: 25px;
        }


        /*
        |--------------------------------------------------------------------------
        | Form
        |--------------------------------------------------------------------------
        */

        .form-label {
            font-weight: 700;

            font-size: 0.84rem;

            color: #3a4044;
        }


        .form-control {
            border-radius: 11px;

            min-height: 46px;

            border: 1px solid #d7dcdf;
        }


        .form-control:focus {
            border-color: #b5d500;

            box-shadow:
                0 0 0 0.2rem
                rgba(216, 255, 36, 0.16);
        }


        .login-btn {
            width: 100%;

            min-height: 48px;

            background: #1f2326;

            color: white;

            border: none;

            border-radius: 11px;

            font-weight: 800;

            transition: 0.2s ease;
        }


        .login-btn:hover {
            background: #d8ff24;

            color: #111;
        }


        /*
        |--------------------------------------------------------------------------
        | Demo Details
        |--------------------------------------------------------------------------
        */

        .demo-box {
            background: #f5f7ea;

            border: 1px solid #e2e9b8;

            border-radius: 12px;

            padding: 14px;

            margin-top: 20px;

            font-size: 0.82rem;

            color: #565e25;
        }


        .demo-title {
            font-weight: 800;

            margin-bottom: 6px;
        }


        /*
        |--------------------------------------------------------------------------
        | Error
        |--------------------------------------------------------------------------
        */

        .login-error {
            background: #ffe7e7;

            border: 1px solid #f2c2c2;

            color: #a83838;

            border-radius: 11px;

            padding: 11px 13px;

            margin-bottom: 18px;

            font-size: 0.84rem;

            font-weight: 700;
        }

    </style>

</head>


<body>


    <header class="topbar">

        <div class="topbar-inner">

            <a
                href="auth.php"
                class="brand"
            >

                <i class="bi bi-lightning-charge-fill"></i>

                ELITE SNEAKER HUB

            </a>

        </div>

    </header>


    <main class="login-wrapper">


        <section class="login-card">


            <div class="login-icon">

                <i class="bi bi-person-lock"></i>

            </div>


            <div class="login-label">

                Company Management System

            </div>


            <h1 class="login-title">

                Staff Login

            </h1>


            <p class="login-subtitle">

                Sign in with an authorized staff account
                to manage inventory, orders and reports.

            </p>


            <?php if ($error !== ''): ?>

                <div class="login-error">

                    <i class="bi bi-exclamation-circle"></i>

                    <?= htmlspecialchars($error) ?>

                </div>

            <?php endif; ?>


            <form
                method="POST"
                action="auth.php"
            >


                <div class="mb-3">

                    <label
                        for="email"
                        class="form-label"
                    >

                        Email Address

                    </label>


                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-control"
                        placeholder="admin@gmail.com"
                        required
                    >

                </div>


                <div class="mb-4">

                    <label
                        for="password"
                        class="form-label"
                    >

                        Password

                    </label>


                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="form-control"
                        placeholder="Enter password"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="login-btn"
                >

                    <i class="bi bi-box-arrow-in-right"></i>

                    Sign In

                </button>


            </form>


            <div class="demo-box">

                <div class="demo-title">

                    Demo Staff Account

                </div>

                <div>

                    Email:
                    <strong>admin@gmail.com</strong>

                </div>

                <div>

                    Password:
                    <strong>admin123</strong>

                </div>

            </div>


        </section>


    </main>


</body>

</html>