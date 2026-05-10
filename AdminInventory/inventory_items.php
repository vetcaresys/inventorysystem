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
ADD ITEM
========================= */
if (isset($_POST['add_item'])) {

    $property_no = $_POST['property_no'];
    $inventory_tag_no = $_POST['inventory_tag_no'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $equipment_type = $_POST['equipment_type'];
    $serial_no = $_POST['serial_no'];
    $date_acquired = $_POST['date_acquired'];
    $acquisition_cost = $_POST['acquisition_cost'];
    $quantity = $_POST['quantity'];
    $unit = $_POST['unit'];
    $item_condition = $_POST['item_condition'];
    $location = $_POST['location'];
    $batch_id = $_POST['batch_id'];

    /* duplicate check */
    $check = $conn->prepare("SELECT item_id FROM equipment_inventory WHERE property_no=?");
    $check->bind_param("s", $property_no);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        header("Location: inventory_items.php?duplicate=1");
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO equipment_inventory(
            property_no,
            inventory_tag_no,
            description,
            category,
            equipment_type,
            serial_no,
            date_acquired,
            acquisition_cost,
            quantity,
            unit,
            item_condition,
            location,
            batch_id
        )
        VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");

    $stmt->bind_param(
        "sssssssdisssi",
        $property_no,
        $inventory_tag_no,
        $description,
        $category,
        $equipment_type,
        $serial_no,
        $date_acquired,
        $acquisition_cost,
        $quantity,
        $unit,
        $item_condition,
        $location,
        $batch_id
    );

    $stmt->execute();

    header("Location: inventory_items.php?added=1");
    exit;
}


/* =========================
UPDATE ITEM
========================= */
if (isset($_POST['update_item'])) {

    $id = $_POST['item_id'];

    $property_no = $_POST['property_no'];
    $inventory_tag_no = $_POST['inventory_tag_no'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $equipment_type = $_POST['equipment_type'];
    $serial_no = $_POST['serial_no'];
    $date_acquired = $_POST['date_acquired'];
    $acquisition_cost = $_POST['acquisition_cost'];
    $quantity = $_POST['quantity'];
    $unit = $_POST['unit'];
    $item_condition = $_POST['item_condition'];
    $location = $_POST['location'];
    $batch_id = $_POST['batch_id'];

    /* prevent duplicate property no */
    $check = $conn->prepare("
        SELECT item_id FROM equipment_inventory 
        WHERE property_no=? AND item_id != ?
    ");
    $check->bind_param("si", $property_no, $id);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        header("Location: inventory_items.php?duplicate=1");
        exit;
    }

    $stmt = $conn->prepare("
       UPDATE equipment_inventory SET
        property_no=?,
        inventory_tag_no=?,
        description=?,
        category=?,
        equipment_type=?,
        serial_no=?,
        date_acquired=?,
        acquisition_cost=?,
        quantity=?,
        unit=?,
        item_condition=?,
        location=?,
        batch_id=?
    WHERE item_id=?
    ");

    $stmt->bind_param(
        "sssssssdisssii",
        $property_no,
        $inventory_tag_no,
        $description,
        $category,
        $equipment_type,
        $serial_no,
        $date_acquired,
        $acquisition_cost,
        $quantity,
        $unit,
        $item_condition,
        $location,
        $batch_id,
        $id
    );

    $stmt->execute();

    header("Location: inventory_items.php?updated=1");
    exit;
}

/* =========================
FETCH ITEMS WITH BATCH + RECEIVER
========================= */
$items = $conn->query("
    SELECT 
        equipment_inventory.*,
        rb.received_date,
        r.name AS receiver_name
    FROM equipment_inventory

    LEFT JOIN receiving_batches rb
    ON equipment_inventory.batch_id = rb.batch_id

    LEFT JOIN receivers r
    ON rb.receiver_id = r.receiver_id

    ORDER BY equipment_inventory.item_id DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <title>Inventory Items</title>

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

        .card-custom {
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
            <small>Equipment Module</small>
        </div>

        <nav class="nav flex-column mt-4">

            <a href="userprofile.php" class="nav-link">
                <i class="bi bi-person-circle"></i>
                User Profile
            </a>

            <a href="inventory_dashboard.php" class="nav-link">
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </a>

            <a href="employees.php" class="nav-link">
                <i class="bi bi-people"></i>
                Employees
            </a>

            <a href="receivers.php" class="nav-link">
                <i class="bi bi-person-badge"></i>
                Receivers
            </a>

            <a href="inventory_items.php" class="nav-link active">
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

        <h3 class="mb-4 fw-bold">Inventory Items</h3>

        <!-- ADD FORM -->
        <div class="card card-custom p-4 mb-4">

            <h5 class="mb-3">Add New Item</h5>

            <form method="POST">

                <div class="row g-3">

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            Property No
                        </label>
                        <input name="property_no" class="form-control" placeholder="Property No" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            Inventory Tag No
                        </label>
                        <input name="inventory_tag_no" class="form-control" placeholder="Tag No" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Description
                        </label>
                        <input name="description" class="form-control" placeholder="Description" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            Category
                        </label>
                        <select name="category" id="category" class="form-control" required>
                            <option>Device</option>
                            <option>Furniture and Fixtures</option>
                            <option>Office Equipment</option>
                            <option>Vehicles</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            Equipment Type
                        </label>
                        <select name="equipment_type" id="equipmentType" class="form-control" required>
                            <option value="">Select Type</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            Serial No
                        </label>
                        <input name="serial_no" class="form-control" placeholder="Serial No">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            Date Acquired
                        </label>
                        <input type="date" name="date_acquired" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            Acquisition Cost
                        </label>
                        <input type="number" step="0.01" name="acquisition_cost" class="form-control" placeholder="Cost"
                            required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">
                            Quantity
                        </label>
                        <input type="hidden" name="quantity" value="1">
                        <div class="form-control bg-light">
                            1 (Fixed per item)
                        </div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">
                            Unit
                        </label>
                        <select name="unit" class="form-control" required>

                            <option value=""> Select Unit </option>

                            <option value="pc">Piece (pc)</option>
                            <option value="unit">Unit</option>
                            <option value="set">Set</option>
                            <option value="box">Box</option>
                            <option value="pack">Pack</option>

                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            Item Condition
                        </label>
                        <select name="item_condition" class="form-control" required>

                            <option value=""> Select Condition </option>

                            <option value="Good">Good</option>
                            <option value="Fair">Fair</option>
                            <option value="Needs Repair">Needs Repair</option>
                            <option value="Unserviceable">Unserviceable</option>

                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            Location
                        </label>
                        <input name="location" class="form-control" placeholder="Location" required>
                    </div>

                    <div class="col-md-3">

                        <label class="form-label fw-semibold">
                            Receiving Batch
                        </label>

                        <select name="batch_id" class="form-control" required>

                            <option value="">Select Batch</option>

                            <?php
                            $batches = $conn->query("
                                SELECT 
                                    rb.batch_id,
                                    rb.received_date,
                                    r.name
                                FROM receiving_batches rb
                                LEFT JOIN receivers r
                                ON rb.receiver_id = r.receiver_id
                                ORDER BY rb.batch_id DESC
                            ");

                            while ($b = $batches->fetch_assoc()) {
                                ?>

                                <option value="<?= $b['batch_id']; ?>">

                                    Batch #<?= $b['batch_id']; ?>
                                    -
                                    <?= $b['name']; ?>
                                    -
                                    <?= $b['received_date']; ?>

                                </option>

                            <?php } ?>

                        </select>
                    </div>

                </div>

                <button name="add_item" class="btn btn-primary mt-3">
                    Add Item
                </button>

            </form>

        </div>

        <!-- TABLE -->
        <div class="card card-custom p-3">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h5 class="mb-0">Inventory Item List</h5>

                <div style="width:300px;">
                    <input type="text" id="searchItem" class="form-control" placeholder="Search item...">
                </div>

            </div>

            <table class="table table-hover text-center" id="itemTable">
                <thead class="table-light">
                    <tr>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Qty</th>
                        <th>Condition</th>
                        <th>Location</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    <?php while ($row = $items->fetch_assoc()) { ?>

                        <tr class="item-row">
                            <td><?= $row['description']; ?></td>
                            <td><?= $row['category']; ?></td>
                            <td><?= $row['quantity']; ?></td>
                            <td><?= $row['item_condition']; ?></td>
                            <td><?= $row['location']; ?></td>

                            <td>

                                <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#viewModal<?= $row['item_id']; ?>">
                                    View
                                </button>

                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#editModal<?= $row['item_id']; ?>">
                                    Edit
                                </button>

                                <!-- <a href="?delete=<?= $row['item_id']; ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Delete this item?')">
                                    Delete
                                </a> -->

                            </td>
                        </tr>


                        <!-- EDIT MODAL -->
                        <div class="modal fade" id="editModal<?= $row['item_id']; ?>" tabindex="-1">

                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">

                                    <form method="POST">

                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                Edit Inventory Item
                                            </h5>

                                            <button type="button" class="btn-close" data-bs-dismiss="modal">
                                            </button>

                                        </div>


                                        <div class="modal-body">

                                            <input type="hidden" name="item_id" value="<?= $row['item_id']; ?>">

                                            <div class="row g-3">

                                                <div class="col-md-6">
                                                    <label>Property No</label>
                                                    <input name="property_no" class="form-control"
                                                        value="<?= $row['property_no']; ?>">
                                                </div>

                                                <div class="col-md-6">
                                                    <label>Inventory Tag No</label>
                                                    <input name="inventory_tag_no" class="form-control"
                                                        value="<?= $row['inventory_tag_no']; ?>">
                                                </div>

                                                <div class="col-md-12">
                                                    <label>Description</label>
                                                    <input name="description" class="form-control"
                                                        value="<?= $row['description']; ?>">
                                                </div>

                                                <div class="col-md-6">
                                                    <label>Category</label>
                                                    <select name="category" class="form-control">
                                                        <option <?= ($row['category'] == "Device") ? 'selected' : ''; ?>>
                                                            Device
                                                        </option>
                                                        <option <?= ($row['category'] == "Furniture and Fixtures") ? 'selected' : ''; ?>>
                                                            Furniture and Fixtures
                                                        </option>
                                                        <option <?= ($row['category'] == "Office Equipment") ? 'selected' : ''; ?>>
                                                            Office Equipment
                                                        </option>
                                                        <option <?= ($row['category'] == "Vehicles") ? 'selected' : ''; ?>>
                                                            Vehicles
                                                        </option>
                                                    </select>
                                                </div>

                                                <div class="col-md-6">
                                                    <label>Equipment Type</label>
                                                    <input name="equipment_type" class="form-control"
                                                        value="<?= $row['equipment_type']; ?>">
                                                </div>

                                                <div class="col-md-6">
                                                    <label>Serial No</label>
                                                    <input name="serial_no" class="form-control"
                                                        value="<?= $row['serial_no']; ?>">
                                                </div>

                                                <div class="col-md-3">
                                                    <label>Date Acquired</label>
                                                    <input type="date" name="date_acquired" class="form-control"
                                                        value="<?= $row['date_acquired']; ?>">
                                                </div>

                                                <div class="col-md-3">
                                                    <label>Acquisition Cost</label>
                                                    <input type="number" step="0.01" name="acquisition_cost"
                                                        class="form-control" value="<?= $row['acquisition_cost']; ?>">
                                                </div>

                                                <div class="col-md-4">
                                                    <label>Quantity</label>
                                                    <input type="number" name="quantity" class="form-control"
                                                        value="<?= $row['quantity']; ?>" min="1">
                                                </div>

                                                <div class="col-md-4">
                                                    <label>Unit</label>
                                                    <input name="unit" class="form-control" value="<?= $row['unit']; ?>">
                                                </div>

                                                <div class="col-md-4">
                                                    <label>Condition</label>
                                                    <select name="item_condition" class="form-control">

                                                        <option <?= ($row['item_condition'] == "Good") ? 'selected' : ''; ?>>
                                                            Good
                                                        </option>

                                                        <option <?= ($row['item_condition'] == "Repair Needed") ? 'selected' : ''; ?>>
                                                            Repair Needed
                                                        </option>

                                                        <option <?= ($row['item_condition'] == "Unserviceable") ? 'selected' : ''; ?>>
                                                            Unserviceable
                                                        </option>

                                                    </select>
                                                </div>

                                                <div class="col-md-6">
                                                    <label>Location</label>
                                                    <input name="location" class="form-control"
                                                        value="<?= $row['location']; ?>">
                                                </div>

                                                <div class="col-md-6">

                                                    <label>Receiving Batch</label>

                                                    <select name="batch_id" class="form-control">

                                                        <?php
                                                        $batchList = $conn->query("
                                                            SELECT rb.batch_id, rb.received_date, r.name
                                                            FROM receiving_batches rb
                                                            LEFT JOIN receivers r
                                                            ON rb.receiver_id = r.receiver_id
                                                            ORDER BY rb.batch_id DESC
                                                        ");

                                                        while ($b = $batchList->fetch_assoc()) {
                                                            ?>

                                                            <option value="<?= $b['batch_id']; ?>"
                                                                <?= ($row['batch_id'] == $b['batch_id']) ? 'selected' : ''; ?>>

                                                                Batch #<?= $b['batch_id']; ?>
                                                                -
                                                                <?= $b['name']; ?>
                                                                -
                                                                <?= $b['received_date']; ?>

                                                            </option>

                                                        <?php } ?>

                                                    </select>

                                                </div>

                                            </div>
                                        </div>


                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                Cancel
                                            </button>

                                            <button name="update_item" class="btn btn-primary">
                                                Update
                                            </button>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- VIEW MODAL -->
                        <div class="modal fade" id="viewModal<?= $row['item_id']; ?>" tabindex="-1">

                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            Inventory Item Details
                                        </h5>

                                        <button type="button" class="btn-close" data-bs-dismiss="modal">
                                        </button>

                                    </div>


                                    <div class="modal-body">

                                        <div class="row g-3">

                                            <div class="col-md-6">
                                                <label class="fw-bold">
                                                    Property No
                                                </label>
                                                <div class="form-control">
                                                    <?= $row['property_no']; ?>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="fw-bold">
                                                    Inventory Tag No
                                                </label>
                                                <div class="form-control">
                                                    <?= $row['inventory_tag_no']; ?>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <label class="fw-bold">
                                                    Description
                                                </label>
                                                <div class="form-control">
                                                    <?= $row['description']; ?>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="fw-bold">
                                                    Category
                                                </label>
                                                <div class="form-control">
                                                    <?= $row['category']; ?>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="fw-bold">
                                                    Serial No
                                                </label>
                                                <div class="form-control">
                                                    <?= $row['serial_no']; ?>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="fw-bold">
                                                    Quantity
                                                </label>
                                                <div class="form-control">
                                                    <?= $row['quantity']; ?>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="fw-bold">
                                                    Unit
                                                </label>
                                                <div class="form-control">
                                                    <?= $row['unit']; ?>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="fw-bold">
                                                    Condition
                                                </label>
                                                <div class="form-control">
                                                    <?= $row['item_condition']; ?>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="fw-bold">
                                                    Date Acquired
                                                </label>
                                                <div class="form-control">
                                                    <?= $row['date_acquired']; ?>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="fw-bold">
                                                    Acquisition Cost
                                                </label>
                                                <div class="form-control">
                                                    ₱<?= number_format($row['acquisition_cost'], 2); ?>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="fw-bold">
                                                    Location
                                                </label>
                                                <div class="form-control">
                                                    <?= $row['location']; ?>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="fw-bold">
                                                    Receiving Batch
                                                </label>
                                                <div class="form-control">
                                                    Batch #<?= $row['batch_id']; ?>
                                                    -
                                                    <?= $row['receiver_name']; ?>
                                                    -
                                                    <?= $row['received_date']; ?>
                                                </div>
                                            </div>

                                        </div>

                                    </div>


                                    <div class="modal-footer">

                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            Close
                                        </button>

                                        <!-- DIRECT TO EDIT -->
                                        <button type="button" class="btn btn-warning" data-bs-dismiss="modal"
                                            data-bs-toggle="modal" data-bs-target="#editModal<?= $row['item_id']; ?>">
                                            Edit This Item
                                        </button>

                                    </div>

                                </div>
                            </div>
                        </div>

                    <?php } ?>

                </tbody>

                <tr id="noResultRow" style="display:none;">
                    <td colspan="6" class="text-center text-muted py-3">
                        No item found
                    </td>
                </tr>

            </table>

            <div class="d-flex justify-content-right align-items-center gap-3 mt-3">

                <span id="prevBtn" style="cursor:pointer; font-weight:500;">
                    ← Prev
                </span>

                <span id="pageInfo" class="fw-bold"></span>

                <span id="nextBtn" style="cursor:pointer; font-weight:500;">
                    Next →
                </span>

            </div>

            <script>
                document.addEventListener("DOMContentLoaded", function () {

                    let rows = document.querySelectorAll(".item-row");
                    let perPage = 5;
                    let currentPage = 1;

                    let totalPages = Math.ceil(rows.length / perPage);

                    let prevBtn = document.getElementById("prevBtn");
                    let nextBtn = document.getElementById("nextBtn");
                    let pageInfo = document.getElementById("pageInfo");

                    function showPage(page) {

                        let start = (page - 1) * perPage;
                        let end = start + perPage;

                        rows.forEach((row, index) => {
                            row.style.display = (index >= start && index < end) ? "" : "none";
                        });

                        pageInfo.innerText = `Page ${page} / ${totalPages}`;

                        // disable style
                        prevBtn.style.opacity = (page === 1) ? "0.4" : "1";
                        nextBtn.style.opacity = (page === totalPages) ? "0.4" : "1";

                        prevBtn.style.pointerEvents = (page === 1) ? "none" : "auto";
                        nextBtn.style.pointerEvents = (page === totalPages) ? "none" : "auto";
                    }

                    prevBtn.addEventListener("click", function () {
                        if (currentPage > 1) {
                            currentPage--;
                            showPage(currentPage);
                        }
                    });

                    nextBtn.addEventListener("click", function () {
                        if (currentPage < totalPages) {
                            currentPage++;
                            showPage(currentPage);
                        }
                    });

                    showPage(currentPage);

                });
            </script>

        </div>

        <footer class="text-center mt-5 py-3 border-top text-muted">
            <small>
                © <?php echo date("Y"); ?> PSA Inventory Management System. All Rights Reserved. <br>
                Developed for internal use only.
            </small>
        </footer>

    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- FOR THE SEARCHABLE TABLE -->
    <script>
        document.getElementById('searchItem').addEventListener('keyup', function () {

            let value = this.value.toLowerCase();
            let rows = document.querySelectorAll(".item-row");
            let found = false;

            rows.forEach(function (row) {

                let text = row.textContent.toLowerCase();

                if (text.includes(value)) {
                    row.style.display = "";
                    found = true;
                } else {
                    row.style.display = "none";
                }

            });

            let noResult = document.getElementById("noResultRow");

            noResult.style.display = found ? "none" : "";

        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if (isset($_GET['duplicate'])): ?>
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Duplicate Record',
                text: 'Property number already exists.',
                confirmButtonColor: '#0d6efd'
            });
        </script>
    <?php endif; ?>


    <?php if (isset($_GET['added'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Item Added',
                text: 'Inventory item added successfully.',
                confirmButtonColor: '#198754'
            });
        </script>
    <?php endif; ?>


    <?php if (isset($_GET['updated'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Updated Successfully',
                text: 'Inventory item updated.',
                confirmButtonColor: '#198754'
            });
        </script>
    <?php endif; ?>

    <!-- FOR THE SELECTION OF EQUIPMENT TYPE -->
    <script>
        const equipmentTypes = {
            "Device": [
                "Computer (Desktop/Laptop)",
                "Printer",
                "Scanner",
                "Projector",
                "CCTV Camera",
                "Network Router / Switch",
                "UPS"
            ],
            "Furniture and Fixtures": [
                "Office Chair",
                "Executive Table",
                "Workstation Desk",
                "Cabinet",
                "Bookshelf",
                "Sofa"
            ],
            "Office Equipment": [
                "Photocopier",
                "Fax Machine",
                "Laminator",
                "Shredder",
                "Air Conditioner",
                "Telephone System"
            ],
            "Supplies": [
                "Bond Paper",
                "Ink / Toner",
                "Ballpen",
                "Envelopes",
                "Notebooks"
            ],
            "Vehicles": [
                "Motorcycle",
                "Car",
                "Van",
                "Truck",
                "Service Vehicle"
            ]
        };

        document.getElementById("category").addEventListener("change", function () {
            let typeSelect = document.getElementById("equipmentType");
            let selected = this.value;

            typeSelect.innerHTML = '<option value="">-- Select Type --</option>';

            if (equipmentTypes[selected]) {
                equipmentTypes[selected].forEach(type => {
                    let opt = document.createElement("option");
                    opt.value = type;
                    opt.textContent = type;
                    typeSelect.appendChild(opt);
                });
            }
        });
    </script>

</body>

</html>