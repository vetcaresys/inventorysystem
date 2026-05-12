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
   CREATE CART SESSION
========================= */
if (!isset($_SESSION['borrow_cart'])) {
    $_SESSION['borrow_cart'] = [];
}

/* =========================
   ADD TO CART
========================= */
if (isset($_POST['add_to_cart'])) {

    $item_id = $_POST['item_id'];
    $quantity = $_POST['quantity'];

    // CHECK STOCK
    $checkStock = $conn->query("
        SELECT quantity
        FROM equipment_inventory
        WHERE item_id = '$item_id'
    ");

    $stockData = $checkStock->fetch_assoc();

    if ($quantity > $stockData['quantity']) {

        header("Location: borrow_records1.php?error=stock");
        exit;
    }

    // CHECK IF ITEM ALREADY IN CART
    foreach ($_SESSION['borrow_cart'] as $cart) {

        if ($cart['item_id'] == $item_id) {

            header("Location: borrow_records1.php?error=duplicate");
            exit;
        }
    }

    // ADD TO CART
    $_SESSION['borrow_cart'][] = [
        'item_id' => $item_id,
        'quantity' => 1
    ];

    header("Location: borrow_records1.php");
    exit;
}

/* =========================
   REMOVE CART ITEM
========================= */
if (isset($_GET['remove'])) {

    $index = $_GET['remove'];

    unset($_SESSION['borrow_cart'][$index]);

    $_SESSION['borrow_cart'] = array_values($_SESSION['borrow_cart']);

    header("Location: borrow_records1.php");
    exit;
}

/* =========================
   CONFIRM BORROW
========================= */
if (isset($_POST['confirm_borrow'])) {

    $employee_id = $_POST['employee_id'];
    $borrow_date = $_POST['borrow_date'];

    foreach ($_SESSION['borrow_cart'] as $cart) {

        $item_id = $cart['item_id'];
        $quantity = $cart['quantity'];

        // INSERT BORROW RECORD
        $conn->query("
            INSERT INTO borrow_records(
                item_id,
                employee_id,
                quantity_borrowed,
                borrow_date,
                status
            )
            VALUES(
                '$item_id',
                '$employee_id',
                '$quantity',
                '$borrow_date',
                'Borrowed'
            )
        ");

        // UPDATE STOCK
        $conn->query("
            UPDATE equipment_inventory
            SET quantity = quantity - $quantity
            WHERE item_id = '$item_id'
        ");
    }

    // CLEAR CART
    unset($_SESSION['borrow_cart']);

    header("Location: borrow_records1.php?borrowed=success");
    exit;
}

/* =========================
   FETCH DATA
========================= */

$items = $conn->query("
    SELECT *
    FROM equipment_inventory
    WHERE quantity > 0
");

$employees = $conn->query("
    SELECT *
    FROM employees
");

$records = $conn->query("
SELECT
b.borrow_id,
e.property_no,
e.description,
e.serial_no,
emp.employee_name,
b.quantity_borrowed,
b.borrow_date,
b.status

FROM borrow_records b

JOIN equipment_inventory e
ON b.item_id = e.item_id

JOIN employees emp
ON b.employee_id = emp.employee_id

ORDER BY b.borrow_id DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Borrow Records</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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
            <small>Borrow Module</small>
        </div>

        <nav class="nav flex-column mt-4">

            <a href="inventory_dashboard.php" class="nav-link">
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </a>

            <a href="inventory_items.php" class="nav-link">
                <i class="bi bi-box-seam"></i>
                Inventory Items
            </a>

            <a href="borrow_records1.php" class="nav-link active">
                <i class="bi bi-journal-arrow-up"></i>
                Borrow Records
            </a>

            <a href="../logout.php" class="nav-link text-warning">
                <i class="bi bi-box-arrow-left"></i>
                Logout
            </a>

        </nav>

    </div>

    <!-- MAIN -->
    <div id="main">

        <h3 class="fw-bold mb-4">
            Borrow Equipment
        </h3>

        <!-- ADD TO CART -->
        <div class="card card-box p-4 mb-4">

            <form method="POST">

                <div class="row g-3">

                    <div class="col-md-6">

                        <label class="form-label">
                            Select Equipment
                        </label>

                        <select name="item_id" class="form-control select-item" required>

                            <option value="">
                                -- Select Item --
                            </option>

                            <?php
                            while ($i = $items->fetch_assoc()) {

                                $alreadyInCart = false;

                                foreach ($_SESSION['borrow_cart'] as $cart) {

                                    if ($cart['item_id'] == $i['item_id']) {
                                        $alreadyInCart = true;
                                        break;
                                    }
                                }

                                if ($alreadyInCart) {
                                    continue;
                                }
                                ?>

                                <option value="<?= $i['item_id']; ?>" data-qty="<?= $i['quantity']; ?>">

                                    <?= $i['property_no']; ?>
                                    -
                                    <?= $i['description']; ?>
                                    -
                                    <?= $i['serial_no']; ?>

                                    (<?= $i['quantity']; ?> available)

                                </option>

                            <?php } ?>

                        </select>

                    </div>

                    <div class="col-md-2">

                        <label class="form-label">
                            Quantity
                        </label>

                        <input type="number" name="quantity" id="quantityInput" class="form-control" value="1" readonly>

                    </div>

                    <div class="col-md-3 d-flex align-items-end">

                        <button type="submit" name="add_to_cart" class="btn btn-primary w-100">

                            <i class="bi bi-cart-plus"></i>
                            Add To Cart

                        </button>

                    </div>

                </div>

            </form>

        </div>

        <!-- BORROW CART -->
        <div class="card card-box p-4 mb-4">

            <h5 class="mb-3">
                Borrow Cart
            </h5>

            <table class="table table-bordered text-center">

                <thead class="table-light">

                    <tr>
                        <th>Property No</th>
                        <th>Description</th>
                        <th>Serial No</th>
                        <th>Qty</th>
                        <th>Action</th>
                    </tr>

                </thead>

                <tbody>

                    <?php
                    if (!empty($_SESSION['borrow_cart'])) {

                        foreach ($_SESSION['borrow_cart'] as $index => $cart) {

                            $item_id = $cart['item_id'];

                            $getItem = $conn->query("
                                SELECT *
                                FROM equipment_inventory
                                WHERE item_id = '$item_id'
                            ");

                            $item = $getItem->fetch_assoc();
                            ?>

                            <tr>

                                <td>
                                    <?= $item['property_no']; ?>
                                </td>

                                <td>
                                    <?= $item['description']; ?>
                                </td>

                                <td>
                                    <?= $item['serial_no']; ?>
                                </td>

                                <td>
                                    <?= $cart['quantity']; ?>
                                </td>

                                <td>

                                    <a href="?remove=<?= $index; ?>" class="btn btn-danger btn-sm">

                                        Remove

                                    </a>

                                </td>

                            </tr>

                            <?php
                        }
                    } else {
                        ?>

                        <tr>

                            <td colspan="5" class="text-muted">
                                No items in borrow cart
                            </td>

                        </tr>

                    <?php } ?>

                </tbody>

            </table>

            <?php if (!empty($_SESSION['borrow_cart'])) { ?>

                <form method="POST">

                    <div class="row g-3 mt-2">

                        <div class="col-md-4">

                            <label class="form-label">
                                Employee
                            </label>

                            <select name="employee_id" class="form-control select-employee" required>

                                <option value="">
                                    Select Employee
                                </option>

                                <?php while ($e = $employees->fetch_assoc()) { ?>

                                    <option value="<?= $e['employee_id']; ?>">

                                        <?= $e['employee_name']; ?>

                                    </option>

                                <?php } ?>

                            </select>

                        </div>

                        <div class="col-md-3">

                            <label class="form-label">
                                Borrow Date
                            </label>

                            <input type="date" name="borrow_date" class="form-control" required>

                        </div>

                        <div class="col-md-3 d-flex align-items-end">

                            <button type="submit" name="confirm_borrow" class="btn btn-success w-100">

                                <i class="bi bi-check-circle"></i>
                                Confirm Borrow

                            </button>

                        </div>

                    </div>

                </form>

            <?php } ?>

        </div>

        <!-- BORROW RECORDS -->
        <div class="card card-box p-4">

            <h5 class="mb-3">
                Borrow Records
            </h5>

            <table class="table table-hover text-center">

                <thead class="table-light">

                    <tr>
                        <th>Property No</th>
                        <th>Description</th>
                        <th>Serial</th>
                        <th>Employee</th>
                        <th>Qty</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>

                </thead>

                <tbody>

                    <?php while ($r = $records->fetch_assoc()) { ?>

                        <tr>

                            <td>
                                <?= $r['property_no']; ?>
                            </td>

                            <td>
                                <?= $r['description']; ?>
                            </td>

                            <td>
                                <?= $r['serial_no']; ?>
                            </td>

                            <td>
                                <?= $r['employee_name']; ?>
                            </td>

                            <td>
                                <?= $r['quantity_borrowed']; ?>
                            </td>

                            <td>
                                <?= $r['borrow_date']; ?>
                            </td>

                            <td>

                                <?php if ($r['status'] == "Returned") { ?>

                                    <span class="badge bg-success">
                                        Returned
                                    </span>

                                <?php } else { ?>

                                    <span class="badge bg-primary">
                                        Borrowed
                                    </span>

                                <?php } ?>

                            </td>

                        </tr>

                    <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {

            $('.select-item').select2({
                placeholder: "Search equipment...",
                width: '100%'
            });

            $('.select-employee').select2({
                placeholder: "Search employee...",
                width: '100%'
            });

        });
    </script>       

    <script>

        $('.select-item').on('change', function () {

            let selected = $(this).find(':selected');

            let qty = selected.data('qty');

            // if no stock
            if (qty <= 0 || qty === undefined) {

                $('#quantityInput').val('');

                Swal.fire({
                    icon: 'error',
                    title: 'Out of Stock',
                    text: 'This item is no longer available.'
                });

                return;
            }

            // automatic quantity = 1
            $('#quantityInput').val(1);

        });

    </script>

</body>

</html>