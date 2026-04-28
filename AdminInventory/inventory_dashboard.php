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
   DASHBOARD DATA
========================= */

// total items
$q1 = $conn->query("
SELECT COUNT(*) total
FROM equipment_inventory
");
$row = $q1->fetch_assoc();
$totalItems = $row['total'];


// total quantity
$q2 = $conn->query("
SELECT COALESCE(SUM(quantity),0) total
FROM equipment_inventory
");
$row = $q2->fetch_assoc();
$totalQuantity = $row['total'];


// borrowed items
$q3 = $conn->query("
SELECT COUNT(*) total
FROM borrow_records
WHERE status = 'Borrowed'
");
$row = $q3->fetch_assoc();
$totalBorrowed = $row['total'];


// overdue items
$q4 = $conn->query("
SELECT COUNT(*) total
FROM borrow_records
WHERE status = 'Overdue'
");
$row = $q4->fetch_assoc();
$totalOverdue = $row['total'];


// recent borrowed
$recentBorrow = $conn->query("
SELECT 
e.description,
b.quantity_borrowed,
b.borrow_date,
b.status
FROM borrow_records b
JOIN equipment_inventory e ON b.item_id = e.item_id
ORDER BY b.borrow_id DESC
LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <title>Inventory Admin Dashboard</title>

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
            <h5 class="fw-bold mb-0">PSA INVENTORY ADMIN</h5>
            <small>Equipment & Borrowing Module</small>
        </div>

        <nav class="nav flex-column mt-4">

            <a href="inventory_dashboard.php" class="nav-link active">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <a href="employees.php" class="nav-link">
                <i class="bi bi-people"></i> Employees
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

            <a href="inventory_reports.php" class="nav-link">
                <i class="bi bi-bar-chart-line"></i> Reports
            </a>

            <a href="backup_restore.php" class="nav-link">
                <i class="bi bi-database-fill-gear"></i> Backup & Restore
            </a>


            <hr>

            <a href="../logout.php" class="nav-link text-warning">
                <i class="bi bi-box-arrow-left"></i> Logout
            </a>

        </nav>
    </div>

    <!-- MAIN -->
    <div id="main">

        <nav class="navbar bg-white rounded-4 shadow-sm px-4 mb-4">
            <h4 class="mb-0">Inventory Dashboard</h4>
            <span><?= $_SESSION['fullname']; ?></span>
        </nav>

        <!-- STATS -->
        <div class="row g-4">

            <div class="col-md-3">
                <div class="card card-stat p-4">
                    <small class="text-muted">Total Items</small>
                    <h2><?= $totalItems ?></h2>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-stat p-4">
                    <small class="text-muted">Total Quantity</small>
                    <h2><?= $totalQuantity ?></h2>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-stat p-4">
                    <small class="text-muted">Borrowed</small>
                    <h2><?= $totalBorrowed ?></h2>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-stat p-4">
                    <small class="text-muted">Overdue</small>
                    <h2><?= $totalOverdue ?></h2>
                </div>
            </div>

        </div>

        <!-- QUICK ACTIONS -->
        <h5 class="mt-5 mb-3 fw-bold">Quick Actions</h5>

        <div class="row g-4 mb-5">

            <div class="col-md-4">
                <a href="inventory_items.php" class="quick-btn">
                    <h5>Add Item</h5>
                    <p class="text-muted small mb-0">Manage equipment inventory</p>
                </a>
            </div>

            <div class="col-md-4">
                <a href="borrow_records.php" class="quick-btn">
                    <h5>Borrow Item</h5>
                    <p class="text-muted small mb-0">Track borrowed equipment</p>
                </a>
            </div>

            <div class="col-md-4">
                <a href="inventory_reports.php" class="quick-btn">
                    <h5>Reports</h5>
                    <p class="text-muted small mb-0">View inventory analytics</p>
                </a>
            </div>

        </div>

        <!-- RECENT BORROW -->
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Recent Borrowed Items</h5>
            </div>

            <div class="card-body p-0">

                <table class="table mb-0 text-center">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php
                        if ($recentBorrow->num_rows == 0) {
                            echo "<tr><td colspan='4'>No records yet</td></tr>";
                        }

                        while ($r = $recentBorrow->fetch_assoc()) {
                        ?>

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

        <footer class="text-center mt-5 py-3 border-top text-muted">
            <small>
                © <?php echo date("Y"); ?> PSA Inventory Management System. All Rights Reserved. <br>
                Developed for internal use only.
            </small>
        </footer>

    </div>

</body>

</html>