<?php
session_start();
require '../connectiondb.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../psa_login.html");
    exit;
}

/* =========================
   FLASH MESSAGE DEFAULT
========================= */
function setMessage($msg, $type = "success")
{
    $_SESSION['message'] = $msg;
    $_SESSION['message_type'] = $type;
}

/* =========================
   CIVIL REGISTRY FORM
========================= */

// ADD ITEM (FORM)
if (isset($_POST['create_form_item'])) {

    $name = $_POST['item_name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $qty = $_POST['qty'];

    // DUPLICATE CHECK
    $check = $conn->prepare("SELECT item_id FROM psa_items WHERE item_name=? AND category='Form'");
    $check->bind_param("s", $name);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        setMessage("Duplicate entry! Form already exists.", "danger");
        header("Location: admin_inventory.php");
        exit;
    }

    // INSERT ITEM
    $stmt = $conn->prepare("
        INSERT INTO psa_items (item_name, category, description, price, quantity)
        VALUES (?, 'Form', ?, ?, ?)
    ");
    $stmt->bind_param("ssdi", $name, $desc, $price, $qty);
    $stmt->execute();

    $item_id = $conn->insert_id;

    // LEDGER
    $stmt2 = $conn->prepare("
        INSERT INTO psa_inventory_ledger (item_id, trans_type, qty, trans_date)
        VALUES (?, 'Received', ?, CURDATE())
    ");
    $stmt2->bind_param("ii", $item_id, $qty);
    $stmt2->execute();

    setMessage("Form added successfully!");
    header("Location: admin_inventory.php");
    exit;
}

/* =========================
   SOLD FORM
========================= */
if (isset($_POST['sold_form'])) {

    $item_id = $_POST['item_id'];
    $qty = $_POST['qty'];
    $buyer = $_POST['buyer'];
    $address = $_POST['address'];

    $stmt = $conn->prepare("
        INSERT INTO psa_sales (item_id, buyer_name, address, qty_sold, date_sold)
        VALUES (?, ?, ?, ?, CURDATE())
    ");
    $stmt->bind_param("issi", $item_id, $buyer, $address, $qty);
    $stmt->execute();

    $stmt2 = $conn->prepare("
        INSERT INTO psa_inventory_ledger (item_id, trans_type, qty, trans_date)
        VALUES (?, 'Sold', ?, CURDATE())
    ");
    $stmt2->bind_param("ii", $item_id, $qty);
    $stmt2->execute();

    setMessage("Item sold successfully!");
    header("Location: admin_inventory.php");
    exit;
}

/* =========================
   RETURN FORM
========================= */
if (isset($_POST['return_form'])) {

    $item_id = $_POST['item_id'];
    $qty = $_POST['qty'];

    $stmt = $conn->prepare("
        INSERT INTO psa_returns (item_id, qty_returned, date_returned)
        VALUES (?, ?, CURDATE())
    ");
    $stmt->bind_param("ii", $item_id, $qty);
    $stmt->execute();

    $stmt2 = $conn->prepare("
        INSERT INTO psa_inventory_ledger (item_id, trans_type, qty, trans_date)
        VALUES (?, 'Returned_from_PSA', ?, CURDATE())
    ");
    $stmt2->bind_param("ii", $item_id, $qty);
    $stmt2->execute();

    setMessage("Item returned successfully!");
    header("Location: admin_inventory.php");
    exit;
}

/* =========================
   RESTOCK FORM
========================= */
if (isset($_POST['restock_form'])) {

    $item_id = $_POST['item_id'];
    $qty = $_POST['qty'];

    $stmt = $conn->prepare("
        INSERT INTO psa_inventory_ledger (item_id, trans_type, qty, trans_date)
        VALUES (?, 'Received', ?, CURDATE())
    ");
    $stmt->bind_param("ii", $item_id, $qty);
    $stmt->execute();

    setMessage("Restocked successfully!");
    header("Location: admin_inventory.php");
    exit;
}

/* =========================
   ADD DEVICE
========================= */
if (isset($_POST['add_device'])) {

    $type = $_POST['device_type'];
    $tag = $_POST['inventory_tag'];
    $property = $_POST['property_no'];
    $officer = $_POST['officer'];
    $brand = $_POST['brand_model'];
    $serial = $_POST['serial'];
    $date = $_POST['date_acquired'];
    $cost = $_POST['cost'];
    $location = $_POST['location'];
    $desc = $_POST['description'];

    // INSERT ITEM
    $stmt = $conn->prepare("
        INSERT INTO psa_items (item_name, category, description)
        VALUES (?, 'Device', ?)
    ");
    $stmt->bind_param("ss", $type, $desc);
    $stmt->execute();

    $item_id = $conn->insert_id;

    // DEVICE DETAILS
    $stmt2 = $conn->prepare("
        INSERT INTO psa_item_devices
        (item_id, inventory_tag, property_no, accountable_officer, brand_model, serial_no, date_acquired, acquisition_cost, location)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt2->bind_param(
        "issssssds",
        $item_id,
        $tag,
        $property,
        $officer,
        $brand,
        $serial,
        $date,
        $cost,
        $location
    );

    $stmt2->execute();

    setMessage("Device added successfully!");
    header("Location: admin_inventory.php");
    exit;
}

/* =========================
   BORROW DEVICE
========================= */
if (isset($_POST['borrow_device'])) {

    $device_id = $_POST['device_id'];
    $borrower = $_POST['borrower'];
    $date = $_POST['date_borrowed'];

    $stmt = $conn->prepare("
        UPDATE psa_item_devices
        SET status='Borrowed'
        WHERE device_id=?
    ");
    $stmt->bind_param("i", $device_id);
    $stmt->execute();

    $stmt2 = $conn->prepare("
        INSERT INTO psa_device_borrow (device_id, borrower_name, date_borrowed)
        VALUES (?, ?, ?)
    ");
    $stmt2->bind_param("iss", $device_id, $borrower, $date);
    $stmt2->execute();

    setMessage("Device borrowed successfully!");
    header("Location: admin_inventory.php");
    exit;
}

/* =========================
   RETURN DEVICE
========================= */
if (isset($_POST['return_device'])) {

    $device_id = $_POST['device_id'];
    $borrower = $_POST['borrower'];
    $date = $_POST['date_returned'];

    $stmt = $conn->prepare("
        UPDATE psa_item_devices
        SET status='Available'
        WHERE device_id=?
    ");
    $stmt->bind_param("i", $device_id);
    $stmt->execute();

    $stmt2 = $conn->prepare("
        INSERT INTO psa_device_return (device_id, borrower_name, date_returned)
        VALUES (?, ?, ?)
    ");
    $stmt2->bind_param("iss", $device_id, $borrower, $date);
    $stmt2->execute();

    setMessage("Device returned successfully!");
    header("Location: admin_inventory.php");
    exit;
}

/* =========================
   ADD ASSET
========================= */
if (isset($_POST['add_asset'])) {

    $name = $_POST['asset_name'];
    $desc = $_POST['description'];
    $qty = $_POST['quantity'];
    $price = $_POST['price'];

    $stmt = $conn->prepare("
        INSERT INTO psa_items (item_name, category, description, price, quantity, status)
        VALUES (?, 'Asset', ?, ?, ?, 'Available')
    ");

    $stmt->bind_param("ssdi", $name, $desc, $price, $qty);
    $stmt->execute();

    setMessage("Asset added successfully!");
    header("Location: admin_inventory.php");
    exit;
}

/* =========================
   FETCH DATA
========================= */

$forms = $conn->query("SELECT * FROM psa_items WHERE category='Form'");
$devices = $conn->query("
    SELECT i.item_name, d.*
    FROM psa_items i
    JOIN psa_item_devices d ON i.item_id = d.item_id
");
$assets = $conn->query("SELECT * FROM psa_items WHERE category='Asset'");
$allItems = $conn->query("SELECT * FROM psa_items");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventory | PSA Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <div class="sidebar-overlay" id="overlay"></div>

    <!-- SIDEBAR -->
    <div id="sidebar">
        <div class="p-4 text-center border-bottom border-secondary">
            <h5 class="fw-bold text-white">PSA <span class="text-info">ADMIN</span></h5>
        </div>

        <nav class="nav flex-column mt-3">
            <a href="admin_dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <a href="admin_inventory.php" class="nav-link active"><i class="bi bi-database"></i> Inventory</a>
            <a href="admin_reports.php" class="nav-link"><i class="bi bi-graph-up"></i> Reports</a>
            <hr class="mx-3 border-secondary">
            <a href="../psa_login.html" class="nav-link text-danger"><i class="bi bi-box-arrow-left"></i> Logout</a>
        </nav>
    </div>

    <!-- MAIN -->
    <div id="main-content">

        <nav class="navbar navbar-custom shadow-sm mb-4 px-3 rounded-3">
            <button id="sidebar-toggle" class="btn btn-light border-0">
                <i class="bi bi-list fs-4"></i>
            </button>
            <span class="fw-bold text-muted">Inventory Management</span>
        </nav>

        <!-- for alert -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show">
                <?= $_SESSION['message'] ?>
                <button class="btn-close" data-bs-dismiss="alert"></button>
            </div>

            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>

        <div class="container-fluid">

            <!-- CATEGORY BUTTONS -->
            <div class="mb-4">
                <button class="btn btn-outline-primary" onclick="showSection('forms')">Forms</button>
                <button class="btn btn-outline-success" onclick="showSection('devices')">Devices</button>
                <button class="btn btn-outline-warning" onclick="showSection('assets')">Assets</button>
                <button class="btn btn-outline-dark" onclick="showSection('all')">All</button>
            </div>

            <!-- FORMS -->
            <div id="forms" class="inventory-section">

                <div class="d-flex gap-2 mb-3">

                    <!-- SEARCH -->
                    <input type="text" class="form-control mb-2 search-box" data-target="formsTable"
                        placeholder="Search Forms...">

                </div>
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        Civil Registry Forms

                        <div>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addFormModal">
                                Add Form
                            </button>

                            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#soldFormModal">
                                Sold Form
                            </button>

                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#returnFormModal">
                                Return Form
                            </button>

                            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#restockFormModal">
                                Restock Form
                            </button>
                        </div>
                    </div>
                    <!-- FORMS TABLE -->
                    <table id="formsTable" class="table table-bordered text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Form Name</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Stock</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr class="data-row">
                                <?php while ($f = $forms->fetch_assoc()): ?>

                                    <?php
                                    $stmt = $conn->prepare("
                SELECT COALESCE(SUM(
                    CASE
                        WHEN trans_type='Received' THEN qty
                        WHEN trans_type='Sold' THEN -qty
                        WHEN trans_type='Returned_from_PSA' THEN qty
                        ELSE 0
                    END
                ),0) AS stock
                FROM psa_inventory_ledger
                WHERE item_id=?
            ");
                                    $stmt->bind_param("i", $f['item_id']);
                                    $stmt->execute();
                                    $stock = $stmt->get_result()->fetch_assoc()['stock'];
                                    ?>

                            <tr>
                                <td><?= htmlspecialchars($f['item_name']) ?></td>
                                <td><?= htmlspecialchars($f['description']) ?></td>
                                <td>₱<?= number_format($f['price'], 2) ?></td>
                                <td><span class="badge bg-primary"><?= $stock ?></span></td>
                            </tr>

                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- DEVICES -->
            <div id="devices" class="inventory-section d-none">
                <div class="d-flex gap-2 mb-3">

                    <!-- SEARCH -->
                    <input type="text" class="form-control mb-2 search-box" data-target="devicesTable"
                        placeholder="Search Devices...">

                </div>
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        IT Devices

                        <div>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDeviceModal">
                                Add Device
                            </button>

                            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#borrowDeviceModal">
                                Borrow Device
                            </button>

                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#returnDeviceModal">
                                Return Device
                            </button>
                        </div>
                    </div>
                    <!-- DEVICES TABLE -->
                    <table class="table table-bordered text-center align-middle" id="devicesTable" class="table table-bordered">
                        <thead class="table-light">
                            <tr class="data-row">
                                <th>Device</th>
                                <th>Tag</th>
                                <th>Property No</th>
                                <th>Brand / Model</th>
                                <th>Serial</th>
                                <th>Officer</th>
                                <th>Location</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php while ($d = $devices->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($d['item_name']) ?></td>
                                    <td><?= htmlspecialchars($d['inventory_tag']) ?></td>
                                    <td><?= htmlspecialchars($d['property_no']) ?></td>
                                    <td><?= htmlspecialchars($d['brand_model']) ?></td>
                                    <td><?= htmlspecialchars($d['serial_no']) ?></td>
                                    <td><?= htmlspecialchars($d['accountable_officer']) ?></td>
                                    <td><?= htmlspecialchars($d['location']) ?></td>
                                    <td>
                                        <?php if ($d['status'] == 'Available'): ?>
                                            <span class="badge bg-success">Available</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Borrowed</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ASSETS -->
            <div id="assets" class="inventory-section d-none">
                <div class="d-flex gap-2 mb-3">

                    <!-- SEARCH -->
                    <input type="text" class="form-control mb-2 search-box" data-target="assetsTable"
                        placeholder="Search Assets...">

                </div>
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        General Assets

                        <div>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAssetModal">
                                Add Asset
                            </button>
                        </div>
                    </div>
                    <!-- ASSETS TABLE -->
                    <table class="table table-bordered text-center align-middle" id="assetsTable" class="table table-bordered">
                        <thead class="table-light">
                            <tr class="data-row">
                                <th>Asset Name</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php while ($a = $assets->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($a['item_name']) ?></td>
                                    <td><?= htmlspecialchars($a['description']) ?></td>
                                    <td>₱<?= number_format($a['price'], 2) ?></td>
                                    <td><?= $a['quantity'] ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?= $a['status'] ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ALL -->
            <div id="all" class="inventory-section d-none">
                <div class="d-flex gap-2 mb-3">

                    <!-- SEARCH -->
                    <input type="text" class="form-control mb-2 search-box" data-target="allTable"
                        placeholder="Search All Items...">

                </div>
                <div class="card">
                    <div class="card-header">All Items</div>

                    <div class="table-responsive">
                        <!-- ALL ITEMS TABLE -->
                        <table class="table table-bordered text-center align-middle" id="allTable" class="table table-bordered">
                            <thead class="table-light">
                                <tr class="data-row">
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php while ($i = $allItems->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $i['item_id'] ?></td>
                                        <td><?= htmlspecialchars($i['item_name']) ?></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?= $i['category'] ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($i['description']) ?></td>
                                        <td>₱<?= number_format($i['price'], 2) ?></td>
                                        <td><?= $i['quantity'] ?></td>
                                        <td>
                                            <?php if ($i['status'] == 'Available'): ?>
                                                <span class="badge bg-success">Available</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">
                                                    <?= $i['status'] ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>
    </div>
    <?php include 'form_modals.php'; ?>

    <script>
        function showSection(section) {
            document.querySelectorAll('.inventory-section').forEach(e => e.classList.add('d-none'));
            document.getElementById(section).classList.remove('d-none');
        }

        document.getElementById('sidebar-toggle').onclick = () => {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('overlay').classList.toggle('active');
        };
    </script>

    <script>
        document.querySelectorAll('.search-box').forEach(input => {

            input.addEventListener('keyup', function() {

                const table = document.getElementById(this.dataset.target);
                const keyword = this.value.toLowerCase();

                const rows = table.querySelectorAll('tbody tr');

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();

                    row.style.display = text.includes(keyword) ? '' : 'none';
                });

            });

        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>