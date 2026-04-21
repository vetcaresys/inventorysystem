<?php
session_start();
require '../connectiondb.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../psa_login.html");
    exit;
}

/* =========================
FETCH DATA
========================= */

// ALL ITEMS
$items = $conn->query("SELECT * FROM inventory_items ORDER BY created_at DESC");

// FORMS
$forms = $conn->query("
        SELECT i.item_name, f.bundle_size, f.price_per_bundle, f.price_per_piece
        FROM psa_forms f
        JOIN inventory_items i ON i.item_id = f.item_id
    ");

// DEVICES
$devices = $conn->query("
        SELECT i.item_name, d.property_no, d.serial_no, d.status, d.location
        FROM psa_devices d
        JOIN inventory_items i ON i.item_id = d.item_id
    ");

// ASSETS
$assets = $conn->query("
        SELECT i.item_name, a.property_no, a.brand, a.condition_status, a.location
        FROM psa_assets a
        JOIN inventory_items i ON i.item_id = a.item_id
    ");

/* =========================
INSERT LOGIC
========================= */
// for adding a forms
if (isset($_POST['add_form'])) {

    $name = $_POST['item_name'];
    $desc = $_POST['description'];
    $qty = $_POST['quantity'];
    $status = $_POST['status'];

    $bundle = $_POST['bundle_size'];
    $bundle_price = $_POST['price_per_bundle'];
    $piece_price = $_POST['price_per_piece'];
    $custodian = $_POST['custodian'];

    // FULL insert
    $stmt = $conn->prepare("
            INSERT INTO inventory_items 
            (item_name, category, description, price, quantity, status) 
            VALUES (?, 'Form', ?, ?, ?, ?)
        ");
    $stmt->bind_param("ssdis", $name, $desc, $piece_price, $qty, $status);
    $stmt->execute();

    $item_id = $stmt->insert_id;

    $stmt2 = $conn->prepare("
    INSERT INTO psa_forms 
    (item_id, bundle_size, price_per_bundle, price_per_piece, custodian) 
    VALUES (?, ?, ?, ?, ?)
");
    $stmt2->bind_param("iidds", $item_id, $bundle, $bundle_price, $piece_price, $custodian);
    $stmt2->execute();

    header("Location: admin_inventory.php");
    exit;
}

// for adding a device
if (isset($_POST['add_device'])) {

    $name = $_POST['item_name'];
    $desc = $_POST['description'];
    $status = $_POST['status'];

    $property = $_POST['property_no'];
    $serial = $_POST['serial_no'];
    $location = $_POST['location'];
    $custodian = $_POST['custodian'];

    // NEW FIELDS
    $inventory_tag = $_POST['inventory_tag'];
    $brand_model = $_POST['brand_model'];
    $date_acquired = $_POST['date_acquired'];
    $cost = $_POST['acquisition_cost'];

    // 1. INSERT INTO inventory_items
    $stmt = $conn->prepare("
        INSERT INTO inventory_items 
        (item_name, category, description, price, quantity, status) 
        VALUES (?, 'Device', ?, 0, 1, ?)
    ");

    $stmt->bind_param("sss", $name, $desc, $status);
    $stmt->execute();

    $item_id = $conn->insert_id;

    // 2. INSERT INTO psa_devices (FIXED: custodian na)
    $stmt2 = $conn->prepare("
        INSERT INTO psa_devices 
        (item_id, inventory_tag, property_no, custodian, brand_model, serial_no, date_acquired, acquisition_cost, location) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt2->bind_param(
        "issssssds",
        $item_id,
        $inventory_tag,
        $property,
        $custodian,
        $brand_model,
        $serial,
        $date_acquired,
        $cost,
        $location
    );

    $stmt2->execute();

    header("Location: admin_inventory.php");
    exit;
}

// for adding an asset
if (isset($_POST['add_asset'])) {

    $name = $_POST['item_name'];
    $desc = $_POST['description'];
    $status = $_POST['status'];

    $property = $_POST['property_no'];
    $brand = $_POST['brand'];
    $condition = $_POST['condition_status'];
    $location = $_POST['location'];

    // NEW FIELDS
    $date_acquired = $_POST['date_acquired'];
    $cost = $_POST['acquisition_cost'];

    // 1. INSERT INTO inventory_items (NO PRICE)
    $stmt = $conn->prepare("
        INSERT INTO inventory_items 
        (item_name, category, description, price, quantity, status) 
        VALUES (?, 'Asset', ?, 0, 1, ?)
    ");

    $stmt->bind_param("sss", $name, $desc, $status);
    $stmt->execute();

    $item_id = $conn->insert_id;

    // 2. INSERT INTO psa_assets
    $stmt2 = $conn->prepare("
        INSERT INTO psa_assets 
        (item_id, property_no, brand, condition_status, location, acquisition_date, acquisition_cost) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt2->bind_param(
        "isssssd",
        $item_id,
        $property,
        $brand,
        $condition,
        $location,
        $date_acquired,
        $cost
    );

    $stmt2->execute();

    header("Location: admin_inventory.php");
    exit;
}

// for POS
if (isset($_POST['process_sale'])) {

    $item_id = $_POST['item_id'];
    $qty = $_POST['qty'];
    $buyer = $_POST['buyer_name'];
    $address = $_POST['address'];

    $today = date('Y-m-d');
    $ref = "SALE-" . time();

    // SAVE SALE
    $stmt = $conn->prepare("
            INSERT INTO psa_sales 
            (item_id, buyer_name, address, qty_sold, ref_no, date_sold)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
    $stmt->bind_param("ississ", $item_id, $buyer, $address, $qty, $ref, $today);
    $stmt->execute();

    // UPDATE LEDGER (IMPORTANT)
    $stmt2 = $conn->prepare("
            INSERT INTO psa_inventory_ledger 
            (item_id, trans_type, qty, trans_date, ref_no)
            VALUES (?, 'Sold', ?, ?, ?)
        ");
    $stmt2->bind_param("iiss", $item_id, $qty, $today, $ref);
    $stmt2->execute();

    header("Location: admin_inventory.php");
    exit;
}

// for checkout
if (isset($_POST['checkout'])) {

    $cart = json_decode($_POST['cart_data'], true);

    $buyer = $_POST['buyer_name'];
    $address = $_POST['address'];
    $today = date('Y-m-d');
    $ref = "SALE-" . time();

    foreach ($cart as $item) {

        $item_id = $item['id'];
        $qty = $item['qty'];

        // SAVE SALES
        $stmt = $conn->prepare("
            INSERT INTO psa_sales 
            (item_id, buyer_name, address, qty_sold, ref_no, date_sold)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ississ", $item_id, $buyer, $address, $qty, $ref, $today);
        $stmt->execute();

        // LEDGER UPDATE
        $stmt2 = $conn->prepare("
            INSERT INTO psa_inventory_ledger 
            (item_id, trans_type, qty, trans_date, ref_no)
            VALUES (?, 'Sold', ?, ?, ?)
        ");
        $stmt2->bind_param("iiss", $item_id, $qty, $today, $ref);
        $stmt2->execute();
    }

    header("Location: admin_inventory.php");
    exit;
}

// Borrow a Device
if (isset($_POST['borrow_device'])) {

    $device_id = $_POST['device_id'];
    $borrower = $_POST['borrower_name'];
    $date = $_POST['date_borrowed'];

    // 1. SAVE BORROW RECORD
    $stmt = $conn->prepare("
        INSERT INTO psa_device_borrow 
        (device_id, borrower_name, date_borrowed)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("iss", $device_id, $borrower, $date);
    $stmt->execute();

    // 2. UPDATE DEVICE STATUS
    $stmt2 = $conn->prepare("
        UPDATE psa_devices 
        SET status = 'Borrowed' 
        WHERE device_id = ?
    ");
    $stmt2->bind_param("i", $device_id);
    $stmt2->execute();

    header("Location: admin_inventory.php");
    exit;
}

// Return a Device
if (isset($_POST['return_device'])) {

    $device_id = $_POST['device_id'];
    $date = $_POST['date_returned'];

    // 1. GET BORROWER NAME (latest borrow record)
    $getBorrower = $conn->prepare("
        SELECT borrower_name 
        FROM psa_device_borrow 
        WHERE device_id = ?
        ORDER BY borrow_id DESC
        LIMIT 1
    ");
    $getBorrower->bind_param("i", $device_id);
    $getBorrower->execute();
    $result = $getBorrower->get_result();
    $row = $result->fetch_assoc();

    $borrower = $row['borrower_name'] ?? 'Unknown';

    // 2. SAVE RETURN
    $stmt = $conn->prepare("
        INSERT INTO psa_device_return 
        (device_id, borrower_name, date_returned)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("iss", $device_id, $borrower, $date);
    $stmt->execute();

    // 3. UPDATE STATUS BACK TO AVAILABLE
    $stmt2 = $conn->prepare("
        UPDATE psa_devices 
        SET status = 'Available' 
        WHERE device_id = ?
    ");
    $stmt2->bind_param("i", $device_id);
    $stmt2->execute();

    header("Location: admin_inventory.php");
    exit;
}
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
            <a href="admin_dashboard.php" class="nav-link">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="admin_inventory.php" class="nav-link active">
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
            <span class="fw-bold text-muted">Inventory Management</span>
        </nav>

        <div class="container-fluid px-4">

            <!-- TABS -->
            <ul class="nav nav-tabs mb-3" id="inventoryTabs">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#all">All Items</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#forms">Forms</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#devices">Devices</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#assets">Assets</button>
                </li>
            </ul>

            <div class="tab-content">

                <!-- ================= ALL ITEMS ================= -->
                <div class="tab-pane fade show active" id="all">
                    <input type="text" class="form-control mb-2" placeholder="Search..." onkeyup="searchTable('allTable', this.value)">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body">
                            <table id="allTable" class="table table-bordered text-center">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Qty</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $items->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['item_name']) ?></td>
                                            <td><?= $row['category'] ?></td>
                                            <td>
                                                <?php
                                                if ($row['category'] == 'Form') {
                                                    echo $row['quantity'];
                                                } else {
                                                    echo '—'; // or 1 if you want
                                                }
                                                ?>
                                            </td>
                                            <td><?= $row['status'] ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-between">
                                <button class="btn btn-secondary btn-sm" onclick="prevPage('allTable')">Previous</button>
                                <button class="btn btn-secondary btn-sm" onclick="nextPage('allTable')">Next</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= FORMS ================= -->
                <div class="tab-pane fade" id="forms">
                    <input type="text" class="form-control mb-2" placeholder="Search..." onkeyup="searchTable('formsTable', this.value)">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body">
                            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addFormModal">
                                <i class="bi bi-plus-circle"></i> Add Form
                            </button>
                            <button class="btn btn-dark mb-3" data-bs-toggle="modal" data-bs-target="#posModal">
                                <i class="bi bi-cart"></i> Open POS
                            </button>
                            <table id="formsTable" class="table table-bordered text-center">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Form Name</th>
                                        <th>Bundle Size</th>
                                        <th>Bundle Price</th>
                                        <th>Piece Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $forms->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $row['item_name'] ?></td>
                                            <td><?= $row['bundle_size'] ?></td>
                                            <td><?= $row['price_per_bundle'] ?></td>
                                            <td><?= $row['price_per_piece'] ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-between">
                                <button class="btn btn-secondary btn-sm" onclick="prevPage('formsTable')">Previous</button>
                                <button class="btn btn-secondary btn-sm" onclick="nextPage('formsTable')">Next</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= DEVICES ================= -->
                <div class="tab-pane fade" id="devices">
                    <input type="text" class="form-control mb-2" placeholder="Search..." onkeyup="searchTable('devicesTable', this.value)">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body">
                            <button class="btn btn-success mb-3" data-bs-toggle="modal"
                                data-bs-target="#addDeviceModal">
                                <i class="bi bi-plus-circle"></i> Add Device
                            </button>

                            <button class="btn btn-dark mb-3" data-bs-toggle="modal" data-bs-target="#borrowDeviceModal">
                                <i class="bi bi-arrow-right-circle"></i> Borrow Device
                            </button>

                            <button class="btn btn-secondary mb-3" data-bs-toggle="modal" data-bs-target="#returnDeviceModal">
                                <i class="bi bi-arrow-return-left"></i> Return Device
                            </button>
                            <table id="devicesTable" class="table table-bordered text-center">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Property No</th>
                                        <th>Serial</th>
                                        <th>Status</th>
                                        <th>Location</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $devices->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $row['item_name'] ?></td>
                                            <td><?= $row['property_no'] ?></td>
                                            <td><?= $row['serial_no'] ?></td>
                                            <td><?= $row['status'] ?></td>
                                            <td><?= $row['location'] ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-between">
                                <button class="btn btn-secondary btn-sm" onclick="prevPage('devicesTable')">Previous</button>
                                <button class="btn btn-secondary btn-sm" onclick="nextPage('devicesTable')">Next</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= ASSETS ================= -->
                <div class="tab-pane fade" id="assets">
                    <input type="text" class="form-control mb-2" placeholder="Search..." onkeyup="searchTable('assetsTable', this.value)">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body">
                            <button class="btn btn-warning mb-3" data-bs-toggle="modal" data-bs-target="#addAssetModal">
                                <i class="bi bi-plus-circle"></i> Add Asset
                            </button>
                            <table id="assetsTable" class="table table-bordered text-center">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Property No</th>
                                        <th>Brand</th>
                                        <th>Condition</th>
                                        <th>Location</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $assets->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $row['item_name'] ?></td>
                                            <td><?= $row['property_no'] ?></td>
                                            <td><?= $row['brand'] ?></td>
                                            <td><?= $row['condition_status'] ?></td>
                                            <td><?= $row['location'] ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-between">
                                <button class="btn btn-secondary btn-sm" onclick="prevPage('assetsTable')">Previous</button>
                                <button class="btn btn-secondary btn-sm" onclick="nextPage('assetsTable')">Next</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php include 'form_modals.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('sidebar-toggle');
        const overlay = document.getElementById('overlay');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        });
    </script>

    <script>
        let cart = [];

        function addToCart() {
            let select = document.getElementById("itemSelect");
            let qty = document.getElementById("qty").value;

            let option = select.options[select.selectedIndex];

            if (!option.value || qty <= 0) {
                alert("Select item and valid qty");
                return;
            }

            let item = {
                id: option.value,
                name: option.dataset.name,
                price: parseFloat(option.dataset.price),
                qty: parseInt(qty)
            };

            let existing = cart.find(i => i.id === item.id);
            if (existing) {
                existing.qty += item.qty;
            } else {
                cart.push(item);
            }

            renderCart();
        }

        function renderCart() {
            let table = document.getElementById("cartTable");
            let total = 0;

            table.innerHTML = "";

            cart.forEach((item, index) => {
                let subtotal = item.price * item.qty;
                total += subtotal;

                table.innerHTML += `
        <tr>
            <td>${item.name}</td>
            <td>₱${item.price}</td>
            <td>${item.qty}</td>
            <td>₱${subtotal.toFixed(2)}</td>
            <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeItem(${index})">X</button>
            </td>
        </tr>
        `;
            });

            document.getElementById("grandTotal").innerText = total.toFixed(2);

            // store cart JSON
            document.getElementById("cartData").value = JSON.stringify(cart);
        }

        function removeItem(index) {
            cart.splice(index, 1);
            renderCart();
        }
    </script>

    <!-- for auto display -->
    <script>
        document.getElementById("deviceSelect").addEventListener("change", function() {
            let selected = this.options[this.selectedIndex];

            let serial = selected.getAttribute("data-serial") || "";

            document.getElementById("serialField").value = serial;
        });
    </script>

    <script>
        document.getElementById("returnDeviceSelect").addEventListener("change", function() {

            let selected = this.options[this.selectedIndex];

            let serial = selected.getAttribute("data-serial") || "";
            let borrower = selected.getAttribute("data-borrower") || "";

            document.getElementById("returnSerialField").value = serial;
            document.getElementById("returnBorrowerField").value = borrower;
        });
    </script>

    <!-- search and pagination -->
    <script>
        let currentPage = {};
        let rowsPerPage = 5;

        function getRows(tableId) {
            let table = document.getElementById(tableId);
            return table.querySelectorAll("tbody tr");
        }

        function showPage(tableId) {
            let rows = getRows(tableId);
            if (!currentPage[tableId]) currentPage[tableId] = 1;

            let start = (currentPage[tableId] - 1) * rowsPerPage;
            let end = start + rowsPerPage;

            rows.forEach((row, index) => {
                row.style.display = (index >= start && index < end) ? "" : "none";
            });
        }

        function nextPage(tableId) {
            let rows = getRows(tableId);
            let maxPage = Math.ceil(rows.length / rowsPerPage);

            if (currentPage[tableId] < maxPage) {
                currentPage[tableId]++;
                showPage(tableId);
            }
        }

        function prevPage(tableId) {
            if (currentPage[tableId] > 1) {
                currentPage[tableId]--;
                showPage(tableId);
            }
        }

        function searchTable(tableId, query) {
            let rows = getRows(tableId);
            query = query.toLowerCase();

            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                row.style.display = text.includes(query) ? "" : "none";
            });
        }

        // INIT all tables
        window.onload = function() {
            ["allTable", "formsTable", "devicesTable", "assetsTable"].forEach(id => {
                currentPage[id] = 1;
                showPage(id);
            });
        };
    </script>
</body>

</html>