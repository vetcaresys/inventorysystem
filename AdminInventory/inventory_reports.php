<?php
session_start();
require '../connectiondb.php';

if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] != 'inventory_admin'
) {
    header("Location: ../login.php");
    exit;
}

/* =========================
   SUMMARY DATA
========================= */

// total items
$totalItems = $conn->query("
SELECT COUNT(*) as total FROM equipment_inventory
")->fetch_assoc()['total'];

// total borrowed
$totalBorrowed = $conn->query("
SELECT COUNT(*) as total FROM borrow_records
")->fetch_assoc()['total'];

// total returned
$totalReturned = $conn->query("
SELECT COUNT(*) as total FROM return_records
")->fetch_assoc()['total'];

// total employees
$totalEmployees = $conn->query("
SELECT COUNT(*) as total FROM employees
")->fetch_assoc()['total'];


/* =========================
   CONDITION BREAKDOWN
========================= */
$condition = $conn->query("
SELECT item_condition, COUNT(*) as total
FROM equipment_inventory
GROUP BY item_condition
");


/* =========================
   MONTHLY BORROW REPORT
========================= */
$monthlyBorrow = $conn->query("
SELECT 
DATE_FORMAT(borrow_date, '%Y-%m') as month,
COUNT(*) as total_borrowed
FROM borrow_records
GROUP BY month
ORDER BY month DESC
LIMIT 6
");


/* =========================
   RECENT ACTIVITY
========================= */
$recent = $conn->query("
SELECT 
e.description,
b.quantity_borrowed,
b.borrow_date,
b.status
FROM borrow_records b
JOIN equipment_inventory e ON b.item_id = e.item_id
ORDER BY b.borrow_id DESC
LIMIT 8
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <title>Inventory Reports</title>

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
            background: #123a5a;
            color: white;
        }

        #sidebar .nav-link {
            color: #dbe7ff;
            padding: 14px 25px;
            display: block;
        }

        #sidebar .nav-link:hover,
        #sidebar .active {
            background: #1f6fb8;
            color: white;
        }

        #main {
            margin-left: 260px;
            padding: 30px;
        }

        .card-box {
            border: none;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, .05);
        }
    </style>

</head>

<body>

    <!-- SIDEBAR -->
    <div id="sidebar">

        <div class="p-4 text-center border-bottom">
            <h5 class="fw-bold mb-0">PSA INVENTORY ADMIN</h5>
            <small>Reports Module</small>
        </div>

        <nav class="nav flex-column mt-4">

            <a href="inventory_dashboard.php" class="nav-link">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <a href="inventory_items.php" class="nav-link">
                <i class="bi bi-box-seam"></i> Inventory Items
            </a>

            <a href="borrow_records.php" class="nav-link">
                <i class="bi bi-journal-arrow-up"></i> Borrow Records
            </a>

            <a href="return_records.php" class="nav-link">
                <i class="bi bi-journal-arrow-down"></i> Return Records
            </a>

            <a href="employees.php" class="nav-link">
                <i class="bi bi-people"></i> Employees
            </a>

            <a href="inventory_reports.php" class="nav-link active">
                <i class="bi bi-bar-chart-line"></i> Reports
            </a>

            <hr>

            <a href="../logout.php" class="nav-link text-warning">
                <i class="bi bi-box-arrow-left"></i> Logout
            </a>

        </nav>
    </div>

    <!-- MAIN -->
    <div id="main">

        <h3 class="fw-bold mb-4">Inventory Reports</h3>

        <!-- SUMMARY -->
        <div class="row g-4 mb-4">

            <div class="col-md-3">
                <div class="card card-box p-4 text-center">
                    <small>Total Items</small>
                    <h2><?= $totalItems ?></h2>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-box p-4 text-center">
                    <small>Borrowed</small>
                    <h2><?= $totalBorrowed ?></h2>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-box p-4 text-center">
                    <small>Returned</small>
                    <h2><?= $totalReturned ?></h2>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-box p-4 text-center">
                    <small>Employees</small>
                    <h2><?= $totalEmployees ?></h2>
                </div>
            </div>

        </div>

        <!-- CONDITION BREAKDOWN -->
        <div class="card card-box p-4 mb-4">

            <h5 class="mb-3">Item Condition Breakdown</h5>

            <table class="table text-center">
                <thead class="table-light">
                    <tr>
                        <th>Condition</th>
                        <th>Total</th>
                    </tr>
                </thead>

                <tbody>

                    <?php while ($c = $condition->fetch_assoc()) { ?>

                        <tr>
                            <td><?= $c['item_condition']; ?></td>
                            <td><?= $c['total']; ?></td>
                        </tr>

                    <?php } ?>

                </tbody>
            </table>

        </div>

        <!-- MONTHLY BORROW -->
        <div class="card card-box p-4 mb-4">

            <h5 class="mb-3">Monthly Borrow Report</h5>

            <table class="table text-center">
                <thead class="table-light">
                    <tr>
                        <th>Month</th>
                        <th>Total Borrowed</th>
                    </tr>
                </thead>

                <tbody>

                    <?php while ($m = $monthlyBorrow->fetch_assoc()) { ?>

                        <tr>
                            <td><?= $m['month']; ?></td>
                            <td><?= $m['total_borrowed']; ?></td>
                        </tr>

                    <?php } ?>

                </tbody>
            </table>

        </div>

        <!-- RECENT ACTIVITY -->
        <div class="card card-box p-4">

            <h5 class="mb-3">Recent Borrow Activity</h5>

            <table class="table table-hover text-center">
                <thead class="table-light">
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                    <?php while ($r = $recent->fetch_assoc()) { ?>

                        <tr>
                            <td><?= $r['description']; ?></td>
                            <td><?= $r['quantity_borrowed']; ?></td>
                            <td><?= $r['borrow_date']; ?></td>
                            <td><?= $r['status']; ?></td>
                        </tr>

                    <?php } ?>

                </tbody>
            </table>

        </div>

    </div>

</body>

</html>