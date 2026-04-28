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
SELECT 
b.borrow_id,
e.description,
b.quantity_borrowed,
emp.employee_name
FROM borrow_records b
JOIN equipment_inventory e 
ON b.item_id = e.item_id
JOIN employees emp
ON b.employee_id = emp.employee_id
WHERE b.status != 'Returned'
");


// return logs
$returns = $conn->query("
SELECT 
r.return_id,
e.description,
emp.employee_name,
r.actual_return_date,
r.returned_condition,
r.remarks

FROM return_records r

JOIN borrow_records b 
ON r.borrow_id = b.borrow_id

JOIN equipment_inventory e 
ON b.item_id = e.item_id

JOIN employees emp
ON b.employee_id = emp.employee_id

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

            <a href="employees.php" class="nav-link">
                <i class="bi bi-people"></i> Employees
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

        <h3 class="fw-bold mb-4">Return Items</h3>

        <!-- RETURN FORM -->
        <div class="card card-box p-4 mb-4">

            <form method="POST">

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Borrowed Item</label>
                        <select name="borrow_id" id="borrowSelect" class="form-control select-borrow" required>
                            <option value="">-- Select Borrowed Item --</option>

                            <?php while ($b = $borrowed->fetch_assoc()) { ?>
                                <option
                                    value="<?= $b['borrow_id']; ?>"
                                    data-employee="<?= $b['employee_name']; ?>">

                                    <?= $b['description']; ?>
                                    (Qty: <?= $b['quantity_borrowed']; ?>)

                                </option>
                            <?php } ?>

                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Employee</label>
                        <input
                            type="text"
                            id="employee_name"
                            class="form-control"
                            placeholder="Auto-filled employee"
                            readonly>
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

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h5 class="mb-0">Return Logs</h5>

                <div style="width:300px;">
                    <input
                        type="text"
                        id="searchReturn"
                        class="form-control"
                        placeholder="Search return logs...">
                </div>

            </div>

            <table class="table table-hover text-center" id="returnTable">
                <thead class="table-light">
                    <tr>
                        <th>Item</th>
                        <th>Date Returned</th>
                        <th>Condition</th>
                        <th>Remarks</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    <?php while ($r = $returns->fetch_assoc()) { ?>

                        <tr class="return-row">
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
                            <td>
                                <button
                                    class="btn btn-info btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#viewModal<?= $r['return_id']; ?>">
                                    View
                                </button>
                            </td>
                        </tr>

                        <div class="modal fade" id="viewModal<?= $r['return_id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title">Return Details</h5>
                                        <button type="button"
                                            class="btn-close"
                                            data-bs-dismiss="modal">
                                        </button>
                                    </div>

                                    <div class="modal-body">

                                        <p>
                                            <strong>Item:</strong>
                                            <?= $r['description']; ?>
                                        </p>

                                        <p>
                                            <strong>Employee:</strong>
                                            <?= $r['employee_name']; ?>
                                        </p>

                                        <p>
                                            <strong>Date Returned:</strong>
                                            <?= $r['actual_return_date']; ?>
                                        </p>

                                        <p>
                                            <strong>Condition:</strong>
                                            <?= $r['returned_condition']; ?>
                                        </p>

                                        <p>
                                            <strong>Remarks:</strong>
                                            <?= $r['remarks']; ?>
                                        </p>

                                    </div>

                                    <div class="modal-footer">
                                        <button class="btn btn-secondary"
                                            data-bs-dismiss="modal">
                                            Close
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>

                    <?php } ?>

                    <tr id="noResultRow" style="display:none;">
                        <td colspan="5" class="text-center text-muted py-3">
                            No item found
                        </td>
                    </tr>

                </tbody>

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
                document.addEventListener("DOMContentLoaded", function() {

                    let rows = document.querySelectorAll(".return-row");
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

                    prevBtn.addEventListener("click", function() {
                        if (currentPage > 1) {
                            currentPage--;
                            showPage(currentPage);
                        }
                    });

                    nextBtn.addEventListener("click", function() {
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



    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SELECTION FOR THE BORROWED ITEMS -->
    <script>
        $(document).ready(function() {

            $('.select-borrow').select2({
                placeholder: "Search borrowed item...",
                allowClear: true,
                width: '100%'
            });

            $('#borrowSelect').on('change', function() {

                let employee =
                    $(this).find(':selected').data('employee');

                $('#employee_name').val(
                    employee ? employee : ''
                );

            });

        });
    </script>

    <!-- TO HAVE A SEARCHABLE TABLE -->
    <script>
        document.getElementById('searchReturn').addEventListener('keyup', function() {

            let value = this.value.toLowerCase();
            let rows = document.querySelectorAll("#returnTable tbody tr");
            let found = false;

            let count = 0;

            rows.forEach(function(row) {

                if (row.id === "noResultRow") return;

                let text = row.textContent.toLowerCase();

                if (text.indexOf(value) > -1) {

                    if (value === "" && count >= 10) {
                        row.style.display = "none";
                    } else {
                        row.style.display = "";
                        count++;
                    }

                    found = true;

                } else {
                    row.style.display = "none";
                }

            });

            let noResult = document.getElementById('noResultRow');

            if (found) {
                noResult.style.display = "none";
            } else {
                noResult.style.display = "";
            }

        });
    </script>

</body>

</html>