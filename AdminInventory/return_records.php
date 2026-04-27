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
   PROCESS RETURN
========================= */
if (isset($_POST['return_item'])) {

    $borrow_id = $_POST['borrow_id'];
    $return_date = $_POST['return_date'];
    $condition = $_POST['returned_condition'];
    $remarks = $_POST['remarks'];

    // get borrow info
    $borrow = $conn->query("
        SELECT item_id, quantity_borrowed 
        FROM borrow_records 
        WHERE borrow_id = $borrow_id
    ")->fetch_assoc();

    $item_id = $borrow['item_id'];
    $qty = $borrow['quantity_borrowed'];

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
        WHERE borrow_id = $borrow_id
    ");

    // restore stock
    $conn->query("
        UPDATE equipment_inventory
        SET quantity = quantity + $qty
        WHERE item_id = $item_id
    ");

    header("Location: return_records.php");
    exit;
}


/* =========================
   DATA FETCH
========================= */

// only show borrowed items
$borrowed = $conn->query("
SELECT b.borrow_id, e.description, b.quantity_borrowed
FROM borrow_records b
JOIN equipment_inventory e ON b.item_id = e.item_id
WHERE b.status != 'Returned'
");


// return logs
$returns = $conn->query("
SELECT 
r.return_id,
e.description,
r.actual_return_date,
r.returned_condition,
r.remarks
FROM return_records r
JOIN borrow_records b ON r.borrow_id = b.borrow_id
JOIN equipment_inventory e ON b.item_id = e.item_id
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
            <small>Return Module</small>
        </div>

        <nav class="nav flex-column mt-4">

            <a href="inventory_dashboard.php" class="nav-link">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <a href="inventory_items.php" class="nav-link">
                <i class="bi bi-box-seam"></i> Inventory Items
            </a>

            <a href="borrow_records.php" class="nav-link">
                <i class="bi bi-journal-arrow-up"></i> Borrow Records
            </a>

            <a href="return_records.php" class="nav-link active">
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

        <h3 class="fw-bold mb-4">Return Items</h3>

        <!-- RETURN FORM -->
        <div class="card card-box p-4 mb-4">

            <form method="POST">

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Borrowed Item</label>
                        <select name="borrow_id" class="form-control" required>
                            <option value="">-- Select Borrowed Item --</option>
                            <?php while ($b = $borrowed->fetch_assoc()) { ?>
                                <option value="<?= $b['borrow_id']; ?>">
                                    <?= $b['description']; ?> (Qty: <?= $b['quantity_borrowed']; ?>)
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Return Date</label>
                        <input type="date" name="return_date" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Condition</label>
                        <select name="returned_condition" class="form-control">
                            <option>Good</option>
                            <option>Damaged</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control"></textarea>
                    </div>

                </div>

                <button name="return_item" class="btn btn-success mt-3">
                    Process Return
                </button>

            </form>

        </div>

        <!-- RETURN TABLE -->
        <div class="card card-box p-3">

            <h5 class="mb-3">Return Logs</h5>

            <table class="table table-hover text-center">
                <thead class="table-light">
                    <tr>
                        <th>Item</th>
                        <th>Date Returned</th>
                        <th>Condition</th>
                        <th>Remarks</th>
                    </tr>
                </thead>

                <tbody>

                    <?php while ($r = $returns->fetch_assoc()) { ?>

                        <tr>
                            <td><?= $r['description']; ?></td>
                            <td><?= $r['actual_return_date']; ?></td>
                            <td>
                                <?php if ($r['returned_condition'] == 'Damaged') { ?>
                                    <span class="badge bg-danger">Damaged</span>
                                <?php } else { ?>
                                    <span class="badge bg-success">Good</span>
                                <?php } ?>
                            </td>
                            <td><?= $r['remarks']; ?></td>
                        </tr>

                    <?php } ?>

                </tbody>
            </table>

        </div>

    </div>

</body>

</html>