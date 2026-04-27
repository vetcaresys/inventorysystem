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
    $serial_no = $_POST['serial_no'];
    $date_acquired = $_POST['date_acquired'];
    $acquisition_cost = $_POST['acquisition_cost'];
    $quantity = $_POST['quantity'];
    $unit = $_POST['unit'];
    $item_condition = $_POST['item_condition'];
    $location = $_POST['location'];
    $accountable_officer = $_POST['accountable_officer'];

    $conn->query("
        INSERT INTO equipment_inventory (
            property_no,
            inventory_tag_no,
            description,
            category,
            serial_no,
            date_acquired,
            acquisition_cost,
            quantity,
            unit,
            item_condition,
            location,
            accountable_officer
        )
        VALUES (
            '$property_no',
            '$inventory_tag_no',
            '$description',
            '$category',
            '$serial_no',
            '$date_acquired',
            '$acquisition_cost',
            '$quantity',
            '$unit',
            '$item_condition',
            '$location',
            '$accountable_officer'
        )
    ");

    header("Location: inventory_items.php");
    exit;
}


/* =========================
   DELETE ITEM
========================= */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    $conn->query("DELETE FROM equipment_inventory WHERE item_id = $id");

    header("Location: inventory_items.php");
    exit;
}


/* =========================
   FETCH ITEMS
========================= */
$items = $conn->query("SELECT * FROM equipment_inventory ORDER BY item_id DESC");
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

            <a href="inventory_dashboard.php" class="nav-link">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <a href="inventory_items.php" class="nav-link active">
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

            <hr>

            <a href="../logout.php" class="nav-link text-warning">
                <i class="bi bi-box-arrow-left"></i> Logout
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
                        <input name="property_no" class="form-control" placeholder="Property No" required>
                    </div>

                    <div class="col-md-3">
                        <input name="inventory_tag_no" class="form-control" placeholder="Tag No" required>
                    </div>

                    <div class="col-md-6">
                        <input name="description" class="form-control" placeholder="Description" required>
                    </div>

                    <div class="col-md-3">
                        <select name="category" class="form-control" required>
                            <option>Device</option>
                            <option>Furniture and Fixtures</option>
                            <option>Office Equipment</option>
                            <option>Supplies</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <input name="serial_no" class="form-control" placeholder="Serial No">
                    </div>

                    <div class="col-md-3">
                        <input type="date" name="date_acquired" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <input name="acquisition_cost" class="form-control" placeholder="Cost" required>
                    </div>

                    <div class="col-md-2">
                        <input name="quantity" class="form-control" placeholder="Qty" required>
                    </div>

                    <div class="col-md-2">
                        <input name="unit" class="form-control" placeholder="Unit">
                    </div>

                    <div class="col-md-3">
                        <select name="item_condition" class="form-control">
                            <option>Good</option>
                            <option>Repair Needed</option>
                            <option>Unserviceable</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <input name="location" class="form-control" placeholder="Location">
                    </div>

                    <div class="col-md-6">
                        <input name="accountable_officer" class="form-control" placeholder="Accountable Officer">
                    </div>

                </div>

                <button name="add_item" class="btn btn-primary mt-3">
                    Add Item
                </button>

            </form>

        </div>

        <!-- TABLE -->
        <div class="card card-custom p-3">

            <table class="table table-hover text-center">
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

                        <tr>
                            <td><?= $row['description']; ?></td>
                            <td><?= $row['category']; ?></td>
                            <td><?= $row['quantity']; ?></td>
                            <td><?= $row['item_condition']; ?></td>
                            <td><?= $row['location']; ?></td>
                            <td>
                                <a href="?delete=<?= $row['item_id']; ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Delete this item?')">
                                    Delete
                                </a>
                            </td>
                        </tr>

                    <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</body>

</html>