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

// total asset records
$q1 = $conn->query("
SELECT COUNT(*) total
FROM equipment_inventory
");
$totalItems = $q1->fetch_assoc()['total'];


// total quantity
$q2 = $conn->query("
SELECT COALESCE(SUM(quantity),0) total
FROM equipment_inventory
");
$totalQty = $q2->fetch_assoc()['total'];


// currently borrowed
$q3 = $conn->query("
SELECT COUNT(*) total
FROM borrow_records
WHERE status='Borrowed'
");
$totalBorrowed = $q3->fetch_assoc()['total'];


// returned
$q4 = $conn->query("
SELECT COUNT(*) total
FROM borrow_records
WHERE status='Returned'
");
$totalReturned = $q4->fetch_assoc()['total'];


// devices only
$q5 = $conn->query("
SELECT COUNT(*) total
FROM equipment_inventory
WHERE category='Device'
");
$totalDevices = $q5->fetch_assoc()['total'];


// low quantity alert
$lowAssets = $conn->query("
SELECT *
FROM equipment_inventory
WHERE quantity <= 2
");


// recent borrow transactions
$recent = $conn->query("
SELECT
e.description,
b.quantity_borrowed,
b.borrow_date,
b.status
FROM borrow_records b
JOIN equipment_inventory e
ON b.item_id=e.item_id
ORDER BY b.borrow_id DESC
LIMIT 5
");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width,initial-scale=1">

    <title>Inventory Dashboard</title>

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
            position: fixed;
            left: 0;
            top: 0;
            background: #123a5a;
        }

        #sidebar .nav-link {
            color: #dbe7ff;
            padding: 14px 25px;
            display: block;
        }

        #sidebar .nav-link:hover,
        #sidebar .active {
            background: #1f6fb8;
            color: #fff;
        }

        #main {
            margin-left: 260px;
            padding: 30px;
        }

        .card-stat {
            border: none;
            border-radius: 22px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .05);
        }

        .quick-btn {
            display: block;
            background: white;
            padding: 25px;
            border-radius: 18px;
            text-decoration: none;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .05);
        }

        .badge-borrowed {
            background: #0d6efd;
        }

        .badge-returned {
            background: #198754;
        }
    </style>

</head>

<body>



    <!-- SIDEBAR -->
    <div id="sidebar">

        <div class="p-4 text-center border-bottom text-white">
            <h5 class="fw-bold mb-0">
                PSA INVENTORY ADMIN
            </h5>

            <small>
                Dashboard Module
            </small>

        </div>


        <nav class="nav flex-column mt-4">

            <a href="userprofile.php" class="nav-link">
                <i class="bi bi-person-circle"></i> 
                User Profile
            </a>

            <a href="inventory_dashboard.php"
                class="nav-link active">
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </a>

            <a href="employees.php"
                class="nav-link">
                <i class="bi bi-people"></i>
                Employees
            </a>

            <a href="inventory_items.php"
                class="nav-link">
                <i class="bi bi-box-seam"></i>
                Inventory Items
            </a>

            <a href="borrow_records.php"
                class="nav-link">
                <i class="bi bi-journal-arrow-up"></i>
                Borrow Records
            </a>

            <a href="return_records.php"
                class="nav-link">
                <i class="bi bi-journal-arrow-down"></i>
                Return Records
            </a>

            <a href="inventory_reports.php"
                class="nav-link">
                <i class="bi bi-bar-chart-line"></i>
                Reports
            </a>

            <a href="backup_restore.php" class="nav-link">
                <i class="bi bi-database-fill-gear"></i>
                Backup & Restore
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

        <nav class="navbar bg-white rounded-4 shadow-sm px-4 py-3 mb-4">

            <h4 class="mb-0 fw-bold">
                Inventory Dashboard
            </h4>

            <div class="d-flex align-items-center gap-4">

                <!-- Notification Bell -->
                <div class="dropdown">

                    <button class="btn position-relative border-0 bg-transparent"
                        data-bs-toggle="dropdown">

                        <i class="bi bi-bell fs-4"></i>

                        <span class="position-absolute top-0 start-100 translate-middle 
                    badge rounded-pill bg-danger">
                            3
                        </span>

                    </button>

                    <ul class="dropdown-menu dropdown-menu-end shadow" style="width:320px;">

                        <li class="dropdown-header fw-bold">
                            Notifications
                        </li>

                        <li>
                            <a class="dropdown-item" href="#">
                                Low stock: Bond Paper
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item" href="#">
                                Laptop returned today
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item" href="#">
                                New borrow request
                            </a>
                        </li>

                    </ul>

                </div>


                <!-- User Profile -->
                <div class="dropdown">

                    <button class="btn d-flex align-items-center gap-2 border-0 bg-transparent"
                        data-bs-toggle="dropdown">

                        <img src="../uploads/default.png"
                            width="40"
                            height="40"
                            class="rounded-circle">

                        <div class="text-start">
                            <div class="fw-semibold">
                                <?= $_SESSION['fullname']; ?>
                            </div>
                            <small class="text-muted">
                                Inventory Admin
                            </small>
                        </div>

                        <i class="bi bi-chevron-down"></i>

                    </button>


                    <ul class="dropdown-menu dropdown-menu-end shadow">

                        <li>
                            <a class="dropdown-item" href="profile.php">
                                <i class="bi bi-person-circle"></i>
                                My Profile
                            </a>
                        </li>

                        <li>
                            <a class="dropdown-item" href="settings.php">
                                <i class="bi bi-gear"></i>
                                Settings
                            </a>
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <li>
                            <a class="dropdown-item text-danger"
                                href="../logout.php">
                                <i class="bi bi-box-arrow-right"></i>
                                Logout
                            </a>
                        </li>

                    </ul>

                </div>

            </div>

        </nav>



        <!-- STAT CARDS -->
        <div class="row g-4">

            <div class="col-md-3">
                <div class="card card-stat p-4">
                    <small class="text-muted">
                        Asset Records
                    </small>

                    <h2>
                        <?= $totalItems ?>
                    </h2>

                </div>
            </div>



            <div class="col-md-3">
                <div class="card card-stat p-4">
                    <small class="text-muted">
                        Total Quantity
                    </small>

                    <h2>
                        <?= $totalQty ?>
                    </h2>

                </div>
            </div>



            <div class="col-md-3">
                <div class="card card-stat p-4">
                    <small class="text-muted">
                        Borrowed Items
                    </small>

                    <h2>
                        <?= $totalBorrowed ?>
                    </h2>

                </div>
            </div>



            <div class="col-md-3">
                <div class="card card-stat p-4">
                    <small class="text-muted">
                        Returned Items
                    </small>

                    <h2>
                        <?= $totalReturned ?>
                    </h2>

                </div>
            </div>

        </div>




        <div class="row g-4 mt-1">

            <div class="col-md-6">
                <div class="card card-stat p-4">

                    <small class="text-muted">
                        Devices Registered
                    </small>

                    <h2>
                        <?= $totalDevices ?>
                    </h2>

                </div>
            </div>


            <div class="col-md-6">
                <div class="card card-stat p-4">

                    <small class="text-muted">
                        Low Quantity Assets
                    </small>

                    <h2>
                        <?= $lowAssets->num_rows ?>
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
                <a href="inventory_items.php"
                    class="quick-btn">

                    <h5>Add Equipment</h5>

                    <p class="text-muted small mb-0">
                        Manage inventory assets
                    </p>

                </a>
            </div>


            <div class="col-md-4">
                <a href="borrow_records.php"
                    class="quick-btn">

                    <h5>Borrow Device</h5>

                    <p class="text-muted small mb-0">
                        Issue equipment
                    </p>

                </a>
            </div>


            <div class="col-md-4">
                <a href="return_records.php"
                    class="quick-btn">

                    <h5>Return Device</h5>

                    <p class="text-muted small mb-0">
                        Process returns
                    </p>

                </a>
            </div>

        </div>




        <!-- RECENT BORROW RECORDS -->
        <div class="card shadow-sm border-0 rounded-4">

            <div class="card-header bg-white">
                <h5 class="fw-bold mb-0">
                    Recent Borrow Records
                </h5>
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
                        if ($recent->num_rows == 0) {
                            echo "
<tr>
<td colspan='4'>
No records yet
</td>
</tr>";
                        }

                        while ($r = $recent->fetch_assoc()) {
                        ?>

                            <tr>

                                <td>
                                    <?= $r['description']; ?>
                                </td>

                                <td>
                                    <?= $r['quantity_borrowed']; ?>
                                </td>

                                <td>
                                    <?= $r['borrow_date']; ?>
                                </td>

                                <td>

                                    <?php
                                    if ($r['status'] == "Borrowed") {
                                        echo "<span class='badge badge-borrowed'>Borrowed</span>";
                                    } else {
                                        echo "<span class='badge badge-returned'>Returned</span>";
                                    }
                                    ?>

                                </td>

                            </tr>

                        <?php } ?>

                    </tbody>

                </table>

            </div>
        </div>



        <footer class="text-center mt-5 border-top pt-4 text-muted">
            <small>
                © <?= date('Y'); ?>
                PSA Inventory Management System
            </small>
        </footer>

    </div>

</body>

</html>