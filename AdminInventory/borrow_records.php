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
   BORROW ITEM
========================= */
if (isset($_POST['borrow'])) {

    $item_id = $_POST['item_id'];
    $employee_id = $_POST['employee_id'];
    $quantity = $_POST['quantity'];
    $borrow_date = $_POST['borrow_date'];
    $expected_return = $_POST['expected_return'];

    // insert borrow record
    $conn->query("
        INSERT INTO borrow_records (
            item_id,
            employee_id,
            quantity_borrowed,
            borrow_date,
            expected_return,
            status
        )
        VALUES (
            '$item_id',
            '$employee_id',
            '$quantity',
            '$borrow_date',
            '$expected_return',
            'Borrowed'
        )
    ");

    // reduce stock
    $conn->query("
        UPDATE equipment_inventory
        SET quantity = quantity - $quantity
        WHERE item_id = $item_id
    ");

    header("Location: borrow_records.php");
    exit;
}


/* =========================
   AUTO OVERDUE UPDATE
========================= */
$conn->query("
    UPDATE borrow_records
    SET status = 'Overdue'
    WHERE expected_return < CURDATE()
    AND status = 'Borrowed'
");


/* =========================
   DATA FETCH
========================= */

$items = $conn->query("SELECT * FROM equipment_inventory WHERE quantity > 0");
$employees = $conn->query("SELECT * FROM employees");

$records = $conn->query("
SELECT 
b.borrow_id,
e.description,
emp.employee_name,
b.quantity_borrowed,
b.borrow_date,
b.expected_return,
b.status
FROM borrow_records b
JOIN equipment_inventory e ON b.item_id = e.item_id
JOIN employees emp ON b.employee_id = emp.employee_id
ORDER BY b.borrow_id DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <title>Borrow Records</title>

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
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <a href="inventory_items.php" class="nav-link">
                <i class="bi bi-box-seam"></i> Inventory Items
            </a>

            <a href="borrow_records.php" class="nav-link active">
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

        <h3 class="fw-bold mb-4">Borrow Equipment</h3>

        <!-- BORROW FORM -->
        <div class="card card-box p-4 mb-4">

            <form method="POST">

                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label">Select Item</label>
                        <select name="item_id" class="form-control" required>
                            <option value="">-- Select Item --</option>
                            <?php while ($i = $items->fetch_assoc()) { ?>
                                <option value="<?= $i['item_id']; ?>">
                                    <?= $i['description']; ?> (<?= $i['quantity']; ?> available)
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Employee</label>
                        <select name="employee_id" class="form-control" required>
                            <option value="">-- Select Employee --</option>
                            <?php while ($e = $employees->fetch_assoc()) { ?>
                                <option value="<?= $e['employee_id']; ?>">
                                    <?= $e['employee_name']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Borrow Date</label>
                        <input type="date" name="borrow_date" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Expected Return</label>
                        <input type="date" name="expected_return" class="form-control" required>
                    </div>

                </div>

                <button name="borrow" class="btn btn-primary mt-3">
                    Borrow Item
                </button>

            </form>

        </div>

        <!-- TABLE -->
        <div class="card card-box p-3">

            <h5 class="mb-3">Borrow Records</h5>

            <table class="table table-hover text-center">
                <thead class="table-light">
                    <tr>
                        <th>Item</th>
                        <th>Employee</th>
                        <th>Qty</th>
                        <th>Borrow Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                    <?php while ($r = $records->fetch_assoc()) { ?>

                        <tr>
                            <td><?= $r['description']; ?></td>
                            <td><?= $r['employee_name']; ?></td>
                            <td><?= $r['quantity_borrowed']; ?></td>
                            <td><?= $r['borrow_date']; ?></td>
                            <td><?= $r['expected_return']; ?></td>
                            <td>
                                <?php if ($r['status'] == 'Overdue') { ?>
                                    <span class="badge bg-danger">Overdue</span>
                                <?php } else { ?>
                                    <span class="badge bg-primary">Borrowed</span>
                                <?php } ?>
                            </td>
                        </tr>

                    <?php } ?>

                </tbody>
            </table>

        </div>

    </div>

</body>

</html>