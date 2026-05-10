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

$limit = 5;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : "";

// recent borrow transactions
$sql = "
SELECT
e.description,
b.quantity_borrowed,
b.borrow_date,
b.status
FROM borrow_records b
JOIN equipment_inventory e
ON b.item_id = e.item_id
";

if ($search != "") {
    $sql .= " WHERE e.description LIKE '%" . $conn->real_escape_string($search) . "%'";
}

$sql .= " ORDER BY b.borrow_id DESC LIMIT $limit OFFSET $offset";

$recent = $conn->query($sql);

$countSql = "
SELECT COUNT(*) as total
FROM borrow_records b
JOIN equipment_inventory e
ON b.item_id = e.item_id
";

if ($search != "") {
    $countSql .= " WHERE e.description LIKE '%" . $conn->real_escape_string($search) . "%'";
}

$countResult = $conn->query($countSql);
$totalRows = $countResult->fetch_assoc()['total'];

$totalPages = ceil($totalRows / $limit);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <title>Inventory Dashboard</title>

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

            <a href="inventory_dashboard.php" class="nav-link active">
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </a>

            <a href="employees.php" class="nav-link">
                <i class="bi bi-people"></i>
                Employees
            </a>

            <a href="receiving_batches.php" class="nav-link">
                <i class="bi bi-box-arrow-in-down"></i>
                Receiving Batches
            </a>

            <a href="inventory_items.php" class="nav-link">
                <i class="bi bi-box-seam"></i>
                Inventory Items
            </a>

            <a href="borrow_records.php" class="nav-link">
                <i class="bi bi-journal-arrow-up"></i>
                Borrow Records
            </a>

            <a href="return_records.php" class="nav-link">
                <i class="bi bi-journal-arrow-down"></i>
                Return Records
            </a>

            <a href="inventory_reports.php" class="nav-link">
                <i class="bi bi-bar-chart-line"></i>
                Reports
            </a>

            <a href="backup_restore.php" class="nav-link">
                <i class="bi bi-database-fill-gear"></i>
                Backup & Restore
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

        <nav class="navbar bg-white rounded-4 shadow-sm px-4 py-3 mb-4">

            <h4 class="mb-0 fw-bold">
                Inventory Dashboard
            </h4>

        </nav>


        <!-- STAT CARDS -->
        <div class="row g-4">

            <!-- ASSET RECORDS -->
            <div class="col-md-3">
                <div class="card text-white p-4 bg-primary shadow-sm rounded-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small>Asset Records</small>
                            <h2 class="fw-bold mb-0"><?= $totalItems ?></h2>
                        </div>
                        <i class="bi bi-box-seam fs-1 opacity-75"></i>
                    </div>
                </div>
            </div>

            <!-- TOTAL QUANTITY -->
            <div class="col-md-3">
                <div class="card text-white p-4 bg-success shadow-sm rounded-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small>Total Quantity</small>
                            <h2 class="fw-bold mb-0"><?= $totalQty ?></h2>
                        </div>
                        <i class="bi bi-collection fs-1 opacity-75"></i>
                    </div>
                </div>
            </div>

            <!-- BORROWED ITEMS -->
            <div class="col-md-3">
                <div class="card text-white p-4 bg-warning shadow-sm rounded-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small>Borrowed Items</small>
                            <h2 class="fw-bold mb-0"><?= $totalBorrowed ?></h2>
                        </div>
                        <i class="bi bi-arrow-up-circle fs-1 opacity-75"></i>
                    </div>
                </div>
            </div>

            <!-- RETURNED ITEMS -->
            <div class="col-md-3">
                <div class="card text-white p-4 bg-danger shadow-sm rounded-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small>Returned Items</small>
                            <h2 class="fw-bold mb-0"><?= $totalReturned ?></h2>
                        </div>
                        <i class="bi bi-arrow-down-circle fs-1 opacity-75"></i>
                    </div>
                </div>
            </div>

        </div>


        <!-- SECOND ROW -->
        <div class="row g-4 mt-1">

            <!-- DEVICES -->
            <div class="col-md-6">
                <div class="card text-white p-4 bg-info shadow-sm rounded-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small>Devices Registered</small>
                            <h2 class="fw-bold mb-0"><?= $totalDevices ?></h2>
                        </div>
                        <i class="bi bi-phone fs-1 opacity-75"></i>
                    </div>
                </div>
            </div>

            <!-- LOW ASSETS -->
            <div class="col-md-6">
                <div class="card text-white p-4 bg-dark shadow-sm rounded-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small>Low Quantity Assets</small>
                            <h2 class="fw-bold mb-0"><?= $lowAssets->num_rows ?></h2>
                        </div>
                        <i class="bi bi-exclamation-triangle fs-1 opacity-75"></i>
                    </div>
                </div>
            </div>

        </div>



        <!-- QUICK ACTIONS -->
        <h5 class="mt-5 mb-3 fw-bold">
            Quick Actions
        </h5>

        <div class="row g-4 mb-5">

            <div class="col-md-4">
                <a href="inventory_items.php" class="quick-btn">

                    <h5>Add Equipment</h5>

                    <p class="text-muted small mb-0">
                        Manage inventory assets
                    </p>

                </a>
            </div>


            <div class="col-md-4">
                <a href="borrow_records.php" class="quick-btn">

                    <h5>Borrow Device</h5>

                    <p class="text-muted small mb-0">
                        Issue equipment
                    </p>

                </a>
            </div>


            <div class="col-md-4">
                <a href="return_records.php" class="quick-btn">

                    <h5>Return Device</h5>

                    <p class="text-muted small mb-0">
                        Process returns
                    </p>

                </a>
            </div>

        </div>


        <!-- RECENT BORROW RECORDS -->
        <div class="card shadow-sm border-0 rounded-4">

            <div class="d-flex justify-content-between align-items-center p-3">

                <!-- LEFT: TITLE -->
                <h5 class="fw-bold mb-0">
                    Recent Borrow Records
                </h5>

                <!-- RIGHT: SEARCH -->
                <form method="GET" class="d-flex gap-2" style="max-width: 300px;">

                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search item..."
                        value="<?= htmlspecialchars($search) ?>">

                    <button class="btn btn-primary btn-sm">
                        <i class="bi bi-search"></i>
                    </button>

                </form>

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

                <div class="p-3 d-flex justify-content-between align-items-center">

                    <small class="text-muted">
                        Page <?= $page ?> of <?= $totalPages ?>
                    </small>

                    <div>

                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>&search=<?= $search ?>" class="btn btn-sm btn-outline-primary">
                                Prev
                            </a>
                        <?php endif; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?>&search=<?= $search ?>" class="btn btn-sm btn-primary">
                                Next
                            </a>
                        <?php endif; ?>

                    </div>

                </div>

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