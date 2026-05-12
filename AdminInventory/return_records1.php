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
   INIT RETURN CART
========================= */
if (!isset($_SESSION['return_cart'])) {
    $_SESSION['return_cart'] = [];
}

/* =========================
   ADD TO RETURN CART
========================= */
if (isset($_POST['add_to_return'])) {

    $borrow_id = $_POST['borrow_id'];

    $borrow = $conn->query("
        SELECT item_id, quantity_borrowed
        FROM borrow_records
        WHERE borrow_id = '$borrow_id'
    ")->fetch_assoc();

    if ($borrow) {

        // prevent duplicate
        foreach ($_SESSION['return_cart'] as $c) {
            if ($c['borrow_id'] == $borrow_id) {
                header("Location: return_records1.php?error=duplicate");
                exit;
            }
        }

        $_SESSION['return_cart'][] = [
            'borrow_id' => $borrow_id,
            'item_id' => $borrow['item_id'],
            'quantity' => $borrow['quantity_borrowed']
        ];
    }

    header("Location: return_records1.php");
    exit;
}

/* =========================
   REMOVE FROM CART
========================= */
if (isset($_GET['remove'])) {

    $index = $_GET['remove'];

    unset($_SESSION['return_cart'][$index]);

    $_SESSION['return_cart'] = array_values($_SESSION['return_cart']);

    header("Location: return_records1.php");
    exit;
}

/* =========================
   CONFIRM RETURN (MULTIPLE)
========================= */
if (isset($_POST['confirm_return'])) {

    $return_date = $_POST['return_date'];
    $condition = $_POST['returned_condition'];
    $remarks = $_POST['remarks'];

    foreach ($_SESSION['return_cart'] as $cart) {

        $borrow_id = $cart['borrow_id'];
        $item_id = $cart['item_id'];
        $qty = $cart['quantity'];

        // insert return record
        $conn->query("
            INSERT INTO return_records (
                borrow_id,
                actual_return_date,
                returned_condition,
                remarks
            )
            VALUES (
                '$borrow_id',
                '$return_date',
                '$condition',
                '$remarks'
            )
        ");

        // update borrow status
        $conn->query("
            UPDATE borrow_records
            SET status = 'Returned'
            WHERE borrow_id = '$borrow_id'
        ");

        // restore stock
        $conn->query("
            UPDATE equipment_inventory
            SET quantity = quantity + $qty
            WHERE item_id = '$item_id'
        ");
    }

    unset($_SESSION['return_cart']);

    header("Location: return_records1.php?returned=success");
    exit;
}

/* =========================
   BORROWED ITEMS
========================= */
$borrowed = $conn->query("
SELECT 
b.borrow_id,
e.description,
e.property_no,
b.quantity_borrowed,
emp.employee_name
FROM borrow_records b
JOIN equipment_inventory e ON b.item_id = e.item_id
JOIN employees emp ON b.employee_id = emp.employee_id
WHERE b.status != 'Returned'
");

/* =========================
   RETURN LOGS
========================= */
$returns = $conn->query("
SELECT 
r.return_id,
e.description,
emp.employee_name,
r.actual_return_date,
r.returned_condition,
r.remarks
FROM return_records r
JOIN borrow_records b ON r.borrow_id = b.borrow_id
JOIN equipment_inventory e ON b.item_id = e.item_id
JOIN employees emp ON b.employee_id = emp.employee_id
ORDER BY r.return_id DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <title>Return Records</title>

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

        .select-borrow,
        .select2-container .select2-selection--single {
            height: 48px !important;
            border-radius: 10px !important;
        }

        .select2-container--default .select2-selection--single {
            border: 1px solid #ced4da !important;
            padding-top: 9px;
        }

        .select2-container {
            width: 100% !important;
        }

        .btn-primary {
            height: 48px;
            border-radius: 10px;
            font-weight: 600;
        }
    </style>

</head>

<body>

    <!-- SIDEBAR -->
    <div id="sidebar">

        <div class="p-4 text-center border-bottom">
            <h5 class="fw-bold mb-0">PSA INVENTORY ADMIN</h5>
            <small>Return Module</small>
        </div>

        <nav class="nav flex-column mt-4">
            <a href="userprofile.php" class="nav-link">
                <i class="bi bi-person-circle"></i>
                User Profile
            </a>

            <a href="inventory_dashboard.php" class="nav-link">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <a href="employees.php" class="nav-link">
                <i class="bi bi-people"></i> Employees
            </a>

            <a href="receiving_batches.php" class="nav-link">
                <i class="bi bi-box-arrow-in-down"></i>
                Receiving Batches
            </a>

            <a href="inventory_items.php" class="nav-link">
                <i class="bi bi-box-seam"></i> Inventory Items
            </a>

            <a href="borrow_records.php" class="nav-link">
                <i class="bi bi-journal-arrow-up"></i> Borrow Records
            </a>

            <a href="return_records1.php" class="nav-link active">
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

        <h3 class="fw-bold mb-4">Return Management</h3>

        <!-- ADD TO CART -->
        <div class="card card-box p-4 mb-4 border-0">

            <div class="mb-4">
                <h5 class="fw-bold mb-1">Add Return Item</h5>
            </div>

            <form method="POST">

                <div class="row g-3 align-items-end">

                    <!-- SEARCHABLE SELECT -->
                    <div class="col-md-9">

                        <label class="form-label fw-semibold">
                            Borrowed Item
                        </label>

                        <select name="borrow_id" class="form-control select-borrow" required>

                            <option value="">
                                -- Search Borrowed Item --
                            </option>

                            <?php while ($b = $borrowed->fetch_assoc()) { ?>

                                <option value="<?= $b['borrow_id']; ?>">

                                    Property #: <?= $b['property_no']; ?> |
                                    Item: <?= $b['description']; ?> |
                                    Borrower: <?= $b['employee_name']; ?> |
                                    Qty: <?= $b['quantity_borrowed']; ?>

                                </option>

                            <?php } ?>

                        </select>

                    </div>

                    <!-- BUTTON -->
                    <div class="col-md-3">

                        <button name="add_to_return" class="btn btn-primary w-100 fw-semibold py-2">

                            <i class="bi bi-cart-plus me-1"></i>
                            Add to Cart

                        </button>

                    </div>

                </div>

            </form>

        </div>

        <!-- RETURN CART -->
        <div class="card card-box p-4 mb-4 border-0">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <div>
                    <h5 class="fw-bold mb-0">Return Cart</h5>
                    <small class="text-muted">Items ready for processing</small>
                </div>

                <span class="badge bg-primary px-3 py-2">
                    <?= count($_SESSION['return_cart']); ?> items
                </span>

            </div>

            <?php if (!empty($_SESSION['return_cart'])) { ?>

                <div class="table-responsive">

                    <table class="table table-hover align-middle text-center">

                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th width="120">Quantity</th>
                                <th width="100">Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($_SESSION['return_cart'] as $i => $c) {

                                $item = $conn->query("
                            SELECT description
                            FROM equipment_inventory
                            WHERE item_id = '{$c['item_id']}'
                        ")->fetch_assoc();
                                ?>

                                <tr>
                                    <td class="text-start fw-semibold">
                                        <?= $item['description']; ?>
                                    </td>

                                    <td>
                                        <span class="badge bg-secondary px-3 py-2">
                                            <?= $c['quantity']; ?>
                                        </span>
                                    </td>

                                    <td>
                                        <a href="?remove=<?= $i; ?>" class="btn btn-outline-danger btn-sm">
                                            Remove
                                        </a>
                                    </td>
                                </tr>

                            <?php } ?>

                        </tbody>

                    </table>

                </div>

                <hr>

                <!-- CONFIRM RETURN -->
                <form method="POST">

                    <div class="row g-2">

                        <div class="col-md-4">
                            <input type="date" name="return_date" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <select name="returned_condition" class="form-control" required>
                                <option>Good</option>
                                <option>Damaged</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <button name="confirm_return" class="btn btn-success w-100 fw-semibold">
                                Confirm All Returns
                            </button>
                        </div>

                    </div>

                    <textarea name="remarks" class="form-control mt-3" placeholder="Remarks (optional)"></textarea>

                </form>

            <?php } else { ?>

                <div class="text-center py-4 text-muted">
                    <i class="bi bi-inbox fs-1"></i>
                    <p class="mb-0">No items in return cart</p>
                </div>

            <?php } ?>

        </div>

        <!-- RETURN LOGS -->
        <div class="card card-box p-3">

            <h5 class="mb-3">Return Logs</h5>

            <table class="table table-hover table-striped text-center">

                <tr>
                    <th>Item</th>
                    <th>Employee</th>
                    <th>Date</th>
                    <th>Condition</th>
                    <th>Remarks</th>
                </tr>

                <?php while ($r = $returns->fetch_assoc()) { ?>

                    <tr>
                        <td><?= $r['description']; ?></td>
                        <td><?= $r['employee_name']; ?></td>
                        <td><?= $r['actual_return_date']; ?></td>
                        <td><?= $r['returned_condition']; ?></td>
                        <td><?= $r['remarks']; ?></td>
                    </tr>

                <?php } ?>

            </table>

        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if (isset($_GET['returned']) && $_GET['returned'] == "success") { ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Return Processed!',
                text: 'Item returned successfully.',
                confirmButtonColor: '#198754'
            });
        </script>
    <?php } ?>

    <script>
        $(document).ready(function () {

            $('.select-borrow').select2({
                placeholder: "Search borrowed item...",
                width: '100%'
            });

        });
    </script>

</body>

</html>