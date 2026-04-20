admin_dashboard.php
<?php
session_start();
require '../connectiondb.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../psa_login.html");
    exit;
}

/* =========================
   DASHBOARD DATA
========================= */

// TOTAL FORMS
$formRes = $conn->query("
    SELECT COALESCE(SUM(
        (
            SELECT COALESCE(SUM(
                CASE 
                    WHEN trans_type='Received' THEN qty
                    WHEN trans_type='Sold' THEN -qty
                    WHEN trans_type='Returned_from_PSA' THEN qty
                    ELSE 0
                END
            ),0)
            FROM psa_inventory_ledger 
            WHERE item_id = inventory_items.item_id
        )
    ),0) as total
    FROM inventory_items 
    WHERE category='Form'
");

$totalForms = 0;
if ($formRes && $row = $formRes->fetch_assoc()) {
    $totalForms = $row['total'];
}


// TOTAL DEVICES
$deviceRes = $conn->query("
    SELECT COUNT(*) as total 
    FROM psa_devices
");

$totalDevices = 0;
if ($deviceRes && $row = $deviceRes->fetch_assoc()) {
    $totalDevices = $row['total'];
}


// TOTAL ASSETS
$assetRes = $conn->query("
    SELECT COALESCE(SUM(quantity),0) as total 
    FROM inventory_items 
    WHERE category='Asset'
");

$totalAssets = 0;
if ($assetRes && $row = $assetRes->fetch_assoc()) {
    $totalAssets = $row['total'];
}


// LOW STOCK
$lowStockQuery = "
SELECT i.item_name,
COALESCE(SUM(
    CASE 
        WHEN l.trans_type='Received' THEN l.qty
        WHEN l.trans_type='Sold' THEN -l.qty
        WHEN l.trans_type='Returned_from_PSA' THEN l.qty
        ELSE 0
    END
),0) as current_stock
FROM inventory_items i
LEFT JOIN psa_inventory_ledger l ON i.item_id = l.item_id
WHERE i.category='Form'
GROUP BY i.item_id
HAVING current_stock < 50
";

$lowStockRes = $conn->query($lowStockQuery);
$lowStock = [];

if ($lowStockRes) {
    while ($row = $lowStockRes->fetch_assoc()) {
        $lowStock[] = $row;
    }
}


// BORROWED DEVICES
$borrowedQuery = "
SELECT i.item_name, d.serial_no, d.status
FROM psa_devices d
JOIN inventory_items i ON i.item_id = d.item_id
WHERE d.status = 'Borrowed'
";

$borrowedRes = $conn->query($borrowedQuery);
$borrowedDevices = [];

if ($borrowedRes) {
    while ($row = $borrowedRes->fetch_assoc()) {
        $borrowedDevices[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Dashboard | PSA Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar-overlay" id="overlay"></div>

<div id="sidebar">
    <div class="p-4 text-center border-bottom border-secondary">
        <h5 class="fw-bold text-white mb-0">
            PSA <span class="text-info">ADMIN</span>
        </h5>
        <small class="text-white-50">Inventory System</small>
    </div>

    <nav class="nav flex-column mt-3">
        <a href="admin_dashboard.php" class="nav-link active">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="admin_inventory.php" class="nav-link">
            <i class="bi bi-database"></i> Inventory
        </a>
        <a href="admin_reports.php" class="nav-link">
            <i class="bi bi-bar-chart-line"></i> Reports
        </a>
        <hr class="mx-3 border-secondary">
        <a href="../psa_login.html" class="nav-link text-danger">
            <i class="bi bi-box-arrow-left"></i> Logout
        </a>
    </nav>
</div>

<!-- MAIN -->
<div id="main-content">

    <!-- NAV -->
    <nav class="navbar navbar-custom shadow-sm mb-4 px-3 rounded-3">
        <button id="sidebar-toggle" class="btn btn-light border-0">
            <i class="bi bi-list fs-4"></i>
        </button>
        <span class="fw-bold text-muted">Dashboard Overview</span>
    </nav>

    <!-- CARDS -->
    <div class="container-fluid px-4 mt-4">
        <div class="row g-3">

            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-4 p-3">
                    <small class="text-muted">Total Forms Stock</small>
                    <h3><?= $totalForms ?></h3>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-4 p-3">
                    <small class="text-muted">Total Devices</small>
                    <h3><?= $totalDevices ?></h3>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-4 p-3">
                    <small class="text-muted">Total Assets Qty</small>
                    <h3><?= $totalAssets ?></h3>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-4 p-3">
                    <small class="text-muted">Low Stock Items</small>
                    <h3><?= count($lowStock) ?></h3>
                </div>
            </div>

        </div>
    </div>

    <!-- BORROWED DEVICES -->
    <div class="card shadow-sm border-0 rounded-4 mt-4 mx-4">
        <div class="card-header bg-white">
            <h5 class="fw-bold mb-0">Borrowed Devices</h5>
        </div>

        <div class="card-body p-0">
            <table class="table table-bordered mb-0 text-center">
                <thead class="bg-light">
                    <tr>
                        <th>Device</th>
                        <th>Serial</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (empty($borrowedDevices)): ?>
                    <tr>
                        <td colspan="3">No borrowed devices</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($borrowedDevices as $d): ?>
                        <tr>
                            <td><?= htmlspecialchars($d['item_name']) ?></td>
                            <td><?= htmlspecialchars($d['serial_no']) ?></td>
                            <td>
                                <span class="badge bg-danger"><?= $d['status'] ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>

            </table>
        </div>
    </div>

</div>

<script>
const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('sidebar-toggle');
const overlay = document.getElementById('overlay');

toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
});
</script>

</body>
</html>