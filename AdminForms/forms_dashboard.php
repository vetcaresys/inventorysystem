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
WHERE current_stock < min_stock
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

$q4 = $conn->query("
SELECT COALESCE(SUM(total_amount),0) total
FROM form_sales
");
$row = $q4->fetch_assoc();
$totalRevenue = $row['total'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Forms Admin Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

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

        /* NEW STAT CARD DESIGN */
        .card-stat {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .08);
            background: white;
            transition: .3s;
        }

        .card-stat:hover {
            transform: translateY(-5px);
            box-shadow: 0 18px 35px rgba(0, 0, 0, .12);
        }

        .card-strip {
            height: 12px;
            width: 100%;
        }

        .strip-blue {
            background: #0d6efd;
        }

        .strip-green {
            background: #198754;
        }

        .strip-yellow {
            background: #ffc107;
        }

        .strip-red {
            background: #dc3545;
        }

        .card-stat-body {
            padding: 25px;
        }

        .card-stat small {
            color: #6c757d;
            font-size: 14px;
        }

        .card-stat h2 {
            margin-top: 8px;
            margin-bottom: 0;
            font-weight: bold;
            color: #0d2c6c;
            font-size: 34px;
        }

        /* QUICK BUTTONS */
        .quick-btn {
            background: white;
            border-radius: 20px;
            padding: 25px;
            text-decoration: none;
            display: block;
            transition: .3s;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .06);
            border: 1px solid rgba(0, 0, 0, .05);
        }

        .quick-btn h5 {
            color: #0d2c6c;
            font-weight: bold;
            margin-top: 12px;
        }

        .quick-btn p {
            color: #777;
            margin-bottom: 0;
        }

        .quick-btn:hover {
            transform: translateY(-8px);
            box-shadow: 0 18px 35px rgba(13, 44, 108, .15);
            background: #f7fbff;
        }

        .quick-icon {
            font-size: 28px;
            color: #0d6efd;
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

            <a href="forms_userprofile.php" class="nav-link">
                <i class="bi bi-person-circle"></i> My Profile
            </a>

            <a href="forms_dashboard.php" class="nav-link active">
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </a>

            <a href="forms_inventory.php" class="nav-link">
                <i class="bi bi-file-earmark-text"></i>
                Forms Inventory
            </a>

            <a href="restock_forms.php" class="nav-link">
                <i class="bi bi-box-seam"></i>
                Restock Forms
            </a>

            <a href="sales.php" class="nav-link">
                <i class="bi bi-cart-check"></i>
                Sell Forms
            </a>

            <a href="forms_reports.php" class="nav-link">
                <i class="bi bi-bar-chart-line"></i>
                Reports
            </a>

            <hr>

            <a href="../logout.php" class="nav-link text-warning">
                <i class="bi bi-box-arrow-left"></i>
                Logout
            </a>

        </nav>

    </div>


    <!-- MAIN -->
    <div id="main">

        <nav class="navbar bg-white rounded-4 shadow-sm px-4 mb-4">
            <h4 class="mb-0">Dashboard Overview</h4>
            <span><?= $_SESSION['fullname']; ?></span>
        </nav>


        <!-- STATS -->
        <div class="row g-4">

            <div class="col-md-3">
                <div class="card card-stat">
                    <div class="card-strip strip-blue"></div>
                    <div class="card-stat-body">
                        <small>Form Types</small>
                        <h2><?= $totalForms ?></h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-stat">
                    <div class="card-strip strip-green"></div>
                    <div class="card-stat-body">
                        <small>Current Stock</small>
                        <h2><?= $totalStock ?></h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-stat">
                    <div class="card-strip strip-yellow"></div>
                    <div class="card-stat-body">
                        <small>Total Sold</small>
                        <h2><?= $totalSold ?></h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-stat">
                    <div class="card-strip strip-red"></div>
                    <div class="card-stat-body">
                        <small>Low Stock Items</small>
                        <h2><?= $lowStock->num_rows ?></h2>
                    </div>
                </div>
            </div>

        </div>


        <!-- QUICK ACTIONS -->
        <h5 class="mt-5 mb-3 fw-bold">Quick Actions</h5>

        <div class="row g-4 mb-5">

            <div class="col-md-4">
                <a href="forms_inventory.php" class="quick-btn">
                    <div class="quick-icon"><i class="bi bi-file-earmark-plus"></i></div>
                    <h5>Add Forms</h5>
                    <p>Manage form records</p>
                </a>
            </div>

            <div class="col-md-4">
                <a href="restock_forms.php" class="quick-btn">
                    <div class="quick-icon"><i class="bi bi-box-seam"></i></div>
                    <h5>Restock</h5>
                    <p>Receive form deliveries</p>
                </a>
            </div>

            <div class="col-md-4">
                <a href="sales.php" class="quick-btn">
                    <div class="quick-icon"><i class="bi bi-cart-check"></i></div>
                    <h5>Sell Forms</h5>
                    <p>Process sales transactions</p>
                </a>
            </div>

        </div>


        <!-- LOW STOCK -->
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Low Stock Alerts</h5>
            </div>

            <div class="card-body p-3">

                <div class="table-responsive">
                    <table id="lowStockTable" class="table align-middle text-center custom-table">

                        <thead>
                            <tr>
                                <th class="text-start ps-4">Form</th>
                                <th>Stock</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if ($lowStock->num_rows == 0) { ?>
                                <tr>
                                    <td colspan="3" class="text-muted py-3">No low stock alerts</td>
                                </tr>
                            <?php } ?>

                            <?php while ($r = $lowStock->fetch_assoc()) { ?>
                                <tr>

                                    <td class="text-start ps-4 fw-semibold">
                                        <?= htmlspecialchars($r['form_name']); ?>
                                    </td>

                                    <td class="fw-bold">
                                        <?= $r['current_stock']; ?>
                                    </td>

                                    <td>
                                        <?php
                                        if ($r['current_stock'] <= 10) {
                                            echo '<span class="badge bg-danger">Critical</span>';
                                        } else {
                                            echo '<span class="badge bg-warning text-dark">Warning</span>';
                                        }
                                        ?>
                                    </td>

                                </tr>
                            <?php } ?>

                        </tbody>

                    </table>
                </div>

            </div>
        </div>

        <script>
            $(document).ready(function() {

                // MAIN TABLE
                $('#formsTable').DataTable({
                    pageLength: 10,
                    responsive: true
                });

                // LOW STOCK TABLE
                $('#lowStockTable').DataTable({
                    pageLength: 5,
                    lengthMenu: [5, 10, 20],
                    searching: false, // optional: remove search kung gusto simple
                    info: false, // hide "showing x entries"
                    responsive: true,
                    language: {
                        paginate: {
                            previous: "Prev",
                            next: "Next"
                        }
                    }
                });

            });
        </script>


        <!-- RECENT SALES -->
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Recent Sales</h5>
            </div>

            <div class="card-body p-3">

                <div class="table-responsive">
                    <table class="table align-middle text-center custom-table">

                        <thead>
                            <tr>
                                <th class="text-start ps-4">Form</th>
                                <th>Qty</th>
                                <th>Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if ($recentSales->num_rows == 0) { ?>
                                <tr>
                                    <td colspan="4" class="text-muted py-3">No sales yet</td>
                                </tr>
                            <?php } ?>

                            <?php while ($sale = $recentSales->fetch_assoc()) { ?>
                                <tr>

                                    <td class="text-start ps-4 fw-semibold">
                                        <?= htmlspecialchars($sale['form_name']); ?>
                                    </td>

                                    <td><?= $sale['quantity_sold']; ?></td>

                                    <td class="fw-bold text-success">
                                        ₱<?= number_format($sale['total_amount'], 2); ?>
                                    </td>

                                    <td>
                                        <?= date("M d, Y", strtotime($sale['date_sold'])); ?>
                                    </td>

                                </tr>
                            <?php } ?>

                        </tbody>

                    </table>
                </div>

            </div>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <script>
        $(document).ready(function() {

            $('#lowStockTable').DataTable({
                pageLength: 5,
                lengthChange: false,
                searching: false,
                info: false,
                responsive: true
            });

        });
    </script>

</body>

</html>