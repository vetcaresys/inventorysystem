<?php
session_start();
require '../connectiondb.php';

if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] != 'forms_admin'
) {
    header("Location: ../login.php");
    exit;
}


/* =========================
DASHBOARD DATA
========================= */


// total form types
$q1 = $conn->query("
SELECT COUNT(*) total
FROM forms
");
$row = $q1->fetch_assoc();
$totalForms = $row['total'];


// total stock
$q2 = $conn->query("
SELECT COALESCE(SUM(current_stock),0) total
FROM forms
");
$row = $q2->fetch_assoc();
$totalStock = $row['total'];


// total sold
$q3 = $conn->query("
SELECT COALESCE(SUM(quantity_sold),0) total
FROM form_sales
");
$row = $q3->fetch_assoc();
$totalSold = $row['total'];


// low stock
$lowStock = $conn->query("
SELECT *
FROM forms
WHERE current_stock < 50
");


// recent sales
$recentSales = $conn->query("
SELECT
f.form_name,
s.quantity_sold,
s.total_amount,
s.date_sold
FROM form_sales s
JOIN forms f
ON s.form_id=f.form_id
ORDER BY sale_id DESC
LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width,initial-scale=1">

    <title>Forms Admin Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: #eef3fb;
            font-family: Arial;
        }

        #sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: #0d2c6c;
            color: white;
        }

        #sidebar .nav-link {
            color: #dbe7ff;
            padding: 14px 25px;
            display: block;
        }

        #sidebar .nav-link:hover,
        #sidebar .active {
            background: #1f4fb8;
            color: white;
        }

        #main {
            margin-left: 260px;
            padding: 30px;
        }

        .card-stat {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, .05);
        }

        .quick-btn {
            border-radius: 15px;
            padding: 20px;
            text-decoration: none;
            display: block;
            background: white;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .05);
        }
    </style>
</head>

<body>


    <!-- SIDEBAR -->
    <div id="sidebar">

        <div class="p-4 text-center border-bottom">
            <h5 class="fw-bold mb-0">
                PSA FORMS ADMIN
            </h5>
            <small>
                Selling Inventory Module
            </small>
        </div>


        <nav class="nav flex-column mt-4">

            <a href="forms_dashboard.php"
                class="nav-link active">
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </a>

            <a href="forms_inventory.php"
                class="nav-link">
                <i class="bi bi-file-earmark-text"></i>
                Forms Inventory
            </a>

            <a href="restock_forms.php"
                class="nav-link">
                <i class="bi bi-box-seam"></i>
                Restock Forms
            </a>

            <a href="sales.php"
                class="nav-link">
                <i class="bi bi-cart-check"></i>
                Sell Forms
            </a>

            <a href="forms_reports.php"
                class="nav-link">
                <i class="bi bi-bar-chart-line"></i>
                Reports
            </a>

            <hr>

            <a href="../logout.php"
                class="nav-link text-warning">
                <i class="bi bi-box-arrow-left"></i>
                Logout
            </a>

        </nav>

    </div>



    <!-- MAIN -->
    <div id="main">

        <nav class="navbar bg-white rounded-4 shadow-sm px-4 mb-4">
            <h4 class="mb-0">
                Dashboard Overview
            </h4>
            <span>
                <?= $_SESSION['fullname']; ?>
            </span>
        </nav>



        <!-- STATS -->
        <div class="row g-4">

            <div class="col-md-3">
                <div class="card card-stat p-4">
                    <small class="text-muted">
                        Form Types
                    </small>
                    <h2>
                        <?= $totalForms ?>
                    </h2>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-stat p-4">
                    <small class="text-muted">
                        Current Stock
                    </small>
                    <h2>
                        <?= $totalStock ?>
                    </h2>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-stat p-4">
                    <small class="text-muted">
                        Total Sold
                    </small>
                    <h2>
                        <?= $totalSold ?>
                    </h2>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-stat p-4">
                    <small class="text-muted">
                        Low Stock Items
                    </small>
                    <h2>
                        <?= $lowStock->num_rows ?>
                    </h2>
                </div>
            </div>

        </div>



        <!-- QUICK ACTIONS -->
        <h5 class="mt-5 mb-3 fw-bold">
            Quick Actions
        </h5>

        <div class="row g-4 mb-5">

            <div class="col-md-4">
                <a href="forms_inventory.php"
                    class="quick-btn">
                    <h5>Add Forms</h5>
                    <p class="text-muted small mb-0">
                        Manage form records
                    </p>
                </a>
            </div>

            <div class="col-md-4">
                <a href="restock_forms.php"
                    class="quick-btn">
                    <h5>Restock</h5>
                    <p class="text-muted small mb-0">
                        Receive form deliveries
                    </p>
                </a>
            </div>

            <div class="col-md-4">
                <a href="sales.php"
                    class="quick-btn">
                    <h5>Sell Forms</h5>
                    <p class="text-muted small mb-0">
                        Process sales transactions
                    </p>
                </a>
            </div>

        </div>



        <!-- LOW STOCK -->
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">
                    Low Stock Alerts
                </h5>
            </div>

            <div class="card-body p-0">

                <table class="table mb-0 text-center">
                    <thead class="table-light">
                        <tr>
                            <th>Form</th>
                            <th>Stock</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php
                        if ($lowStock->num_rows == 0) {
                            echo "
<tr>
<td colspan='3'>
No low stock alerts
</td>
</tr>";
                        }

                        while ($r = $lowStock->fetch_assoc()) {
                        ?>

                            <tr>
                                <td>
                                    <?= $r['form_name']; ?>
                                </td>

                                <td>
                                    <?= $r['current_stock']; ?>
                                </td>

                                <td>
                                    <span class="badge bg-danger">
                                        Low Stock
                                    </span>
                                </td>

                            </tr>

                        <?php } ?>

                    </tbody>
                </table>

            </div>
        </div>



        <!-- RECENT SALES -->
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">
                    Recent Sales
                </h5>
            </div>

            <div class="card-body p-0">

                <table class="table mb-0 text-center">
                    <thead class="table-light">
                        <tr>
                            <th>Form</th>
                            <th>Qty</th>
                            <th>Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php
                        if ($recentSales->num_rows == 0) {
                            echo "
<tr>
<td colspan='4'>
No sales yet
</td>
</tr>";
                        }

                        while ($sale = $recentSales->fetch_assoc()) {
                        ?>

                            <tr>
                                <td>
                                    <?= $sale['form_name']; ?>
                                </td>

                                <td>
                                    <?= $sale['quantity_sold']; ?>
                                </td>

                                <td>
                                    ₱<?= number_format(
                                            $sale['total_amount'],
                                            2
                                        ); ?>
                                </td>

                                <td>
                                    <?= $sale['date_sold']; ?>
                                </td>

                            </tr>

                        <?php } ?>

                    </tbody>
                </table>

            </div>
        </div>

    </div>

</body>

</html>