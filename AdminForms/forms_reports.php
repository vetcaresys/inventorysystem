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
SUMMARY DATA
========================= */

$totalForms = $conn->query("
SELECT COUNT(*) as total FROM forms
")->fetch_assoc()['total'];

$totalStock = $conn->query("
SELECT COALESCE(SUM(current_stock),0) as total FROM forms
")->fetch_assoc()['total'];

$totalSold = $conn->query("
SELECT COALESCE(SUM(quantity_sold),0) as total FROM form_sales
")->fetch_assoc()['total'];


/* =========================
REPORT DATA (OPTIMIZED)
========================= */

$report = $conn->query("
SELECT 
    f.form_name,
    f.current_stock,
    COALESCE(SUM(r.quantity_received),0) as received,
    COALESCE(SUM(s.quantity_sold),0) as sold
FROM forms f
LEFT JOIN form_restock r ON f.form_id = r.form_id
LEFT JOIN form_sales s ON f.form_id = s.form_id
GROUP BY f.form_id
ORDER BY f.form_name ASC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width,initial-scale=1">

    <title>Forms Reports</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"
        rel="stylesheet">

    <style>
        body {
            background: #eef3fb;
            font-family: Arial;
        }

        #sidebar {
            width: 260px;
            height: 100vh;
            background: #0d2c6c;
            position: fixed;
            left: 0;
            top: 0;
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

        .card-box {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .05);
        }

        .table th {
            background: #f5f7fb;
        }
    </style>
</head>

<body>



    <!-- SIDEBAR -->
    <div id="sidebar">

        <div class="p-4 text-center border-bottom text-white">
            <h5 class="fw-bold mb-0">PSA FORMS ADMIN</h5>
            <small>Reports Module</small>
        </div>

        <nav class="nav flex-column mt-4">

            <a href="forms_dashboard.php" class="nav-link">
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

            <a href="forms_reports.php" class="nav-link active">
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

        <!-- HEADER -->
        <nav class="navbar bg-white rounded-4 shadow-sm px-4 mb-4">
            <div>
                <h4 class="mb-0 fw-bold">Forms Inventory Report</h4>
                <small class="text-muted">Overview and detailed tracking of forms</small>
            </div>
        </nav>


        <!-- SUMMARY -->
        <div class="row g-4 mb-4">

            <div class="col-md-4">
                <div class="card card-box p-4">
                    <small class="text-muted">Total Form Types</small>
                    <h2 class="fw-bold"><?= $totalForms ?></h2>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-box p-4">
                    <small class="text-muted">Total Stock</small>
                    <h2 class="fw-bold"><?= $totalStock ?></h2>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-box p-4">
                    <small class="text-muted">Total Sold</small>
                    <h2 class="fw-bold"><?= $totalSold ?></h2>
                </div>
            </div>

        </div>


        <!-- ACTION BAR -->
        <div class="row g-4 mb-4">

            <!-- MONTHLY REPORT -->
            <div class="col-md-6">
                <div class="card card-box p-4 h-100">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="fw-bold mb-0">Monthly Report</h6>
                            <small class="text-muted">Generate report by month</small>
                        </div>
                        <i class="bi bi-calendar2-month fs-3 text-primary"></i>
                    </div>

                    <form method="GET" action="generate_monthly_excel.php" class="d-flex gap-2">
                        <input type="month" name="month" class="form-control" required>
                        <button type="submit" class="btn btn-primary px-4">
                            Generate
                        </button>
                    </form>

                </div>
            </div>

            <!-- YEARLY REPORT -->
            <div class="col-md-6">
                <div class="card card-box p-4 h-100">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="fw-bold mb-0">Yearly Report</h6>
                            <small class="text-muted">Generate report by year</small>
                        </div>
                        <i class="bi bi-calendar-range fs-3 text-success"></i>
                    </div>

                    <form method="GET" action="generate_yearly_excel.php" class="d-flex gap-2">
                        <input type="number" name="year" class="form-control"
                            placeholder="e.g. 2026" required>
                        <button type="submit" class="btn btn-success px-4">
                            Generate
                        </button>
                    </form>

                </div>
            </div>

        </div>


        <!-- REPORT TABLE -->
        <div class="card card-box">

            <div class="card-header bg-white">
                <h5 class="fw-bold mb-0">Detailed Form Report</h5>
            </div>

            <div class="card-body p-0">

                <table class="table table-hover text-center mb-0 align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>Form Name</th>
                            <th class="text-success">Received</th>
                            <th class="text-warning">Sold</th>
                            <th class="text-primary">Current Stock</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php
                        if ($report->num_rows == 0) {
                            echo "<tr><td colspan='4'>No records found</td></tr>";
                        }

                        while ($row = $report->fetch_assoc()) {
                        ?>

                            <tr>

                                <td class="fw-semibold">
                                    <?= $row['form_name']; ?>
                                </td>

                                <td class="text-success fw-bold">
                                    <?= $row['received']; ?>
                                </td>

                                <td class="text-warning fw-bold">
                                    <?= $row['sold']; ?>
                                </td>

                                <td class="text-primary fw-bold">
                                    <?= $row['current_stock']; ?>
                                </td>

                            </tr>

                        <?php } ?>

                    </tbody>

                </table>

            </div>
        </div>

    </div>

    </div>

</body>

</html>