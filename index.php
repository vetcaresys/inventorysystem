<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1">

    <title>PSA Inventory Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --egov-navy: #0d2c6c;
            --egov-blue: #0d6efd;
            --egov-cyan: #28d7ff;
            --bg: #f4f7fc;
        }

        body {
            background: var(--bg);
            font-family: Arial, sans-serif;
        }

        /* NAVBAR */
        .navbar-main {
            background: white;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .05);
        }

        .text-egov-navy {
            color: var(--egov-navy);
        }

        /* HERO */
        .hero-section {
            background:
                linear-gradient(135deg,
                    #0d2c6c,
                    #1c56d7);
            padding: 100px 20px;
            color: white;
        }

        .hero-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .08);
            padding: 35px;
            height: 100%;
            transition: .3s;
        }

        .hero-card:hover {
            transform: translateY(-6px);
        }

        .icon-box {
            width: 70px;
            height: 70px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            background: #eef4ff;
            font-size: 32px;
            color: #0d6efd;
        }

        .btn-egov {
            background: #0d6efd;
            border: none;
            color: white;
        }

        .btn-egov:hover {
            background: #084298;
        }

        .feature-box {
            padding: 30px;
            background: white;
            border-radius: 18px;
            height: 100%;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .05);
        }

        footer {
            background: white;
            margin-top: 70px;
        }
    </style>
</head>

<body>


    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-main sticky-top py-3">
        <div class="container">

            <a class="navbar-brand fw-bold text-egov-navy">
                <span class="text-primary">gov</span>PH
                <span class="text-muted">
                    | PSA Ozamiz
                </span>
            </a>


            <div class="d-flex">

                <?php if (isset($_SESSION['username'])): ?>

                    <span class="me-3 mt-2">
                        Welcome,
                        <?= $_SESSION['username']; ?>
                    </span>

                    <a href="logout.php"
                        class="btn btn-danger rounded-pill px-4">
                        Logout
                    </a>

                <?php else: ?>

                    <a href="login.php"
                        class="btn btn-outline-primary rounded-pill px-4">
                        Admin Login
                    </a>

                <?php endif; ?>

            </div>

        </div>
    </nav>



    <!-- HERO -->
    <section class="hero-section text-center">
        <div class="container">

            <h1 class="display-4 fw-bold mb-3">
                PSA Inventory Management System
            </h1>

            <p class="lead opacity-75 mb-4">
                Integrated Forms Selling and Property Inventory System
            </p>

        </div>
    </section>



    <!-- MODULES -->
    <section class="container my-5">

        <div class="text-center mb-5">
            <h2 class="fw-bold">
                System Modules
            </h2>

            <p class="text-muted">
                Access specialized inventory management portals
            </p>
        </div>


        <div class="row g-4">

            <!-- FORMS -->
            <div class="col-md-6">

                <div class="hero-card">

                    <div class="icon-box">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>

                    <h3 class="fw-bold">
                        Forms Inventory
                    </h3>

                    <p class="text-muted mt-3">
                        Manage civil registry forms:
                        stock monitoring,
                        restocking,
                        selling transactions,
                        and reports.
                    </p>

                    <ul class="mt-4">
                        <li>Add Forms</li>
                        <li>Restock Inventory</li>
                        <li>Sell Forms</li>
                        <li>Sales Reports</li>
                    </ul>

                    <a href="login.php"
                        class="btn btn-egov mt-3 rounded-pill px-4">
                        Access Module
                    </a>

                </div>

            </div>



            <!-- EQUIPMENT -->
            <div class="col-md-6">

                <div class="hero-card">

                    <div class="icon-box">
                        <i class="bi bi-pc-display"></i>
                    </div>

                    <h3 class="fw-bold">
                        Equipment Inventory
                    </h3>

                    <p class="text-muted mt-3">
                        Manage devices,
                        property assets,
                        borrow and return,
                        and inventory reports.
                    </p>

                    <ul class="mt-4">
                        <li>Add Equipment</li>
                        <li>Borrow Devices</li>
                        <li>Return Devices</li>
                        <li>Track Assets</li>
                    </ul>

                    <a href="login.php"
                        class="btn btn-egov mt-3 rounded-pill px-4">
                        Access Module
                    </a>

                </div>

            </div>

        </div>

    </section>



    <!-- FEATURES -->
    <section class="container my-5">

        <div class="row g-4">

            <div class="col-md-4">
                <div class="feature-box text-center">
                    <i class="bi bi-shield-check fs-1"></i>
                    <h5 class="mt-3">
                        Secure Access
                    </h5>
                    <p class="text-muted small">
                        Role-based administrator authentication
                    </p>
                </div>
            </div>


            <div class="col-md-4">
                <div class="feature-box text-center">
                    <i class="bi bi-clipboard-data fs-1"></i>
                    <h5 class="mt-3">
                        Audit Ready
                    </h5>
                    <p class="text-muted small">
                        Complete transaction monitoring
                    </p>
                </div>
            </div>


            <div class="col-md-4">
                <div class="feature-box text-center">
                    <i class="bi bi-database-check fs-1"></i>
                    <h5 class="mt-3">
                        Asset Tracking
                    </h5>
                    <p class="text-muted small">
                        Inventory and property monitoring
                    </p>
                </div>
            </div>

        </div>

    </section>



    <footer class="py-5 border-top text-center">
        <p class="text-muted small mb-1">
            © 2026 Philippine Statistics Authority - Ozamiz
        </p>

        <small class="text-muted">
            Inventory Management System
        </small>
    </footer>

</body>

</html>