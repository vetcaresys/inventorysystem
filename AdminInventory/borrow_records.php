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

    // insert borrow record
    $conn->query("
        INSERT INTO borrow_records (
            item_id,
            employee_id,
            quantity_borrowed,
            borrow_date,
            status
        )
        VALUES (
            '$item_id',
            '$employee_id',
            '$quantity',
            '$borrow_date',
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
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <a href="employees.php" class="nav-link">
                <i class="bi bi-people"></i> Employees
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

        <h3 class="fw-bold mb-4">Borrow Equipment</h3>

        <!-- BORROW FORM -->
        <div class="card card-box p-4 mb-4">

            <form method="POST">

                <div class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label">Select Item</label>
                        <select name="item_id" class="form-control select-item" required>
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
                        <select name="employee_id" class="form-control select-employee" required>
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

                </div>

                <button name="borrow" class="btn btn-primary mt-3">
                    Borrow Item
                </button>

            </form>

        </div>

        <!-- TABLE -->
        <div class="card card-box p-3">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h5 class="mb-0">Borrow Records</h5>

                <div style="width:300px;">
                    <input
                        type="text"
                        id="searchBorrow"
                        class="form-control"
                        placeholder="Search borrow record...">
                </div>

            </div>

            <table class="table table-hover text-center" id="borrowTable">
                <thead class="table-light">
                    <tr>
                        <th>Item</th>
                        <th>Employee</th>
                        <th>Qty</th>
                        <th>Borrow Date</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                    <?php while ($r = $records->fetch_assoc()) { ?>

                        <tr class="borrow-row">
                            <td><?= $r['description']; ?></td>
                            <td><?= $r['employee_name']; ?></td>
                            <td><?= $r['quantity_borrowed']; ?></td>
                            <td><?= $r['borrow_date']; ?></td>
                            <td>
                                <?php if ($r['status'] == "Returned") { ?>
                                    <span class="badge bg-success">Returned</span>

                                <?php } elseif ($r['status'] == "Overdue") { ?>
                                    <span class="badge bg-danger">Overdue</span>

                                <?php } else { ?>
                                    <span class="badge bg-primary">Borrowed</span>

                                <?php } ?>
                            </td>
                        </tr>

                    <?php } ?>

                    <tr id="noResultRow" style="display:none;">
                        <td colspan="5" class="text-center text-muted py-3">
                            No borrow record found
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

                    let rows = document.querySelectorAll(".borrow-row");
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

                        // visual disable style
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

    <script>
        $(document).ready(function() {
            $('.select-item').select2({
                placeholder: "Search item...",
                allowClear: true,
                width: '100%'
            });
        });
    </script>

    <script>
        $(document).ready(function() {

            $('.select-item').select2({
                placeholder: "Search item...",
                allowClear: true,
                width: '100%'
            });

            $('.select-employee').select2({
                placeholder: "Search employee...",
                allowClear: true,
                width: '100%'
            });

        });
    </script>

    <script>
        document.getElementById('searchBorrow').addEventListener('keyup', function() {

            let value = this.value.toLowerCase();
            let rows = document.querySelectorAll("#borrowTable tbody tr");
            let found = false;

            rows.forEach(function(row) {

                if (row.id === "noResultRow") return;

                let text = row.textContent.toLowerCase();

                if (text.includes(value)) {
                    row.style.display = "";
                    found = true;
                } else {
                    row.style.display = "none";
                }
            });

            document.getElementById("noResultRow").style.display =
                found ? "none" : "";

        });
    </script>
</body>

</html>