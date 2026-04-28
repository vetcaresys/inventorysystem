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
   ADD EMPLOYEE
========================= */
if (isset($_POST['add_employee'])) {

    $name = $_POST['employee_name'];
    $office = $_POST['office_unit'];
    $position = $_POST['position'];
    $contact = $_POST['contact_no'];

    $conn->query("
        INSERT INTO employees (
            employee_name,
            office_unit,
            position,
            contact_no
        )
        VALUES (
            '$name',
            '$office',
            '$position',
            '$contact'
        )
    ");

    header("Location: employees.php");
    exit;
}

// EDIT EMPLOYEE
if (isset($_POST['update_employee'])) {

    $id = $_POST['employee_id'];
    $name = $_POST['employee_name'];
    $office = $_POST['office_unit'];
    $position = $_POST['position'];
    $contact = $_POST['contact_no'];

    $conn->query("
        UPDATE employees SET
        employee_name='$name',
        office_unit='$office',
        position='$position',
        contact_no='$contact'
        WHERE employee_id=$id
    ");

    header("Location: employees.php");
    exit;
}


/* =========================
   DELETE EMPLOYEE
========================= */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    $conn->query("
        DELETE FROM employees 
        WHERE employee_id = $id
    ");

    header("Location: employees.php");
    exit;
}


/* =========================
   FETCH EMPLOYEES
========================= */
$employees = $conn->query("
    SELECT * FROM employees 
    ORDER BY employee_id DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <title>Employees</title>

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
            <small>Employees Module</small>
        </div>

        <nav class="nav flex-column mt-4">

            <a href="inventory_dashboard.php" class="nav-link">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <a href="employees.php" class="nav-link active">
                <i class="bi bi-people"></i> Employees
            </a>

            <a href="inventory_items.php" class="nav-link">
                <i class="bi bi-box-seam"></i> Inventory Items
            </a>

            <a href="borrow_records.php" class="nav-link">
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

        <h3 class="fw-bold mb-4">Employees</h3>

        <!-- ADD EMPLOYEE -->
        <div class="card card-box p-4 mb-4">

            <h5 class="mb-3">Add Employee</h5>

            <form method="POST">

                <div class="row g-3">

                    <div class="col-md-4">
                        <input type="text" name="employee_name" class="form-control" placeholder="Full Name" required>
                    </div>

                    <div class="col-md-3">
                        <input type="text" name="office_unit" class="form-control" placeholder="Office / Unit" required>
                    </div>

                    <div class="col-md-3">
                        <input type="text" name="position" class="form-control" placeholder="Position" required>
                    </div>

                    <div class="col-md-2">
                        <input type="text" name="contact_no" class="form-control" placeholder="Contact No">
                    </div>

                </div>

                <button name="add_employee" class="btn btn-primary mt-3">
                    Add Employee
                </button>

            </form>

        </div>



        <!-- TABLE -->
        <div class="card card-box p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">

                <h5 class="mb-0">Employee List</h5>

                <div style="width:300px;">
                    <input
                        type="text"
                        id="searchEmployee"
                        class="form-control"
                        placeholder="Search employee...">
                </div>

            </div>
            <table class="table table-hover text-center" id="employeeTable">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Office</th>
                        <th>Position</th>
                        <th>Contact</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    <?php while ($e = $employees->fetch_assoc()) { ?>

                        <tr class="employee-row">
                            <td><?= $e['employee_name']; ?></td>
                            <td><?= $e['office_unit']; ?></td>
                            <td><?= $e['position']; ?></td>
                            <td><?= $e['contact_no']; ?></td>

                            <td>
                                <!-- VIEW -->
                                <button class="btn btn-info btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#viewModal<?= $e['employee_id']; ?>">
                                    View
                                </button>

                                <!-- EDIT -->
                                <button class="btn btn-warning btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editModal<?= $e['employee_id']; ?>">
                                    Edit
                                </button>

                                <!-- DELETE -->
                                <a href="?delete=<?= $e['employee_id']; ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Delete this employee?')">
                                    Delete
                                </a>
                            </td>
                        </tr>

                        <!-- VIEW MODAL -->
                        <div class="modal fade" id="viewModal<?= $e['employee_id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title">Employee Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">
                                        <p><strong>Name:</strong> <?= $e['employee_name']; ?></p>
                                        <p><strong>Office:</strong> <?= $e['office_unit']; ?></p>
                                        <p><strong>Position:</strong> <?= $e['position']; ?></p>
                                        <p><strong>Contact:</strong> <?= $e['contact_no']; ?></p>
                                    </div>

                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- EDIT MODAL -->
                        <div class="modal fade" id="editModal<?= $e['employee_id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">

                                    <form method="POST">

                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Employee</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">

                                            <input type="hidden" name="employee_id" value="<?= $e['employee_id']; ?>">

                                            <div class="mb-2">
                                                <label>Name</label>
                                                <input type="text" name="employee_name" class="form-control"
                                                    value="<?= $e['employee_name']; ?>">
                                            </div>

                                            <div class="mb-2">
                                                <label>Office</label>
                                                <input type="text" name="office_unit" class="form-control"
                                                    value="<?= $e['office_unit']; ?>">
                                            </div>

                                            <div class="mb-2">
                                                <label>Position</label>
                                                <input type="text" name="position" class="form-control"
                                                    value="<?= $e['position']; ?>">
                                            </div>

                                            <div class="mb-2">
                                                <label>Contact</label>
                                                <input type="text" name="contact_no" class="form-control"
                                                    value="<?= $e['contact_no']; ?>">
                                            </div>

                                        </div>

                                        <div class="modal-footer">

                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                Cancel
                                            </button>

                                            <button name="update_employee" class="btn btn-primary">
                                                Update
                                            </button>

                                        </div>

                                    </form>

                                </div>
                            </div>
                        </div>

                    <?php } ?>

                    <tr id="noResultRow" style="display:none;">
                        <td colspan="5" class="text-center text-muted">
                            No employee found
                        </td>
                    </tr>

                </tbody>

            </table>

            <div class="d-flex justify-content-right align-items-center gap-3 mt-3">

                <span id="empPrevBtn" style="cursor:pointer; font-weight:500;">
                    ← Prev
                </span>

                <span id="empPageInfo" class="fw-bold"></span>

                <span id="empNextBtn" style="cursor:pointer; font-weight:500;">
                    Next →
                </span>

            </div>

            <script>
                document.addEventListener("DOMContentLoaded", function() {

                    let rows = document.querySelectorAll(".employee-row");
                    let perPage = 5;
                    let currentPage = 1;

                    let totalPages = Math.ceil(rows.length / perPage);

                    let prevBtn = document.getElementById("empPrevBtn");
                    let nextBtn = document.getElementById("empNextBtn");
                    let pageInfo = document.getElementById("empPageInfo");

                    function showPage(page) {

                        let start = (page - 1) * perPage;
                        let end = start + perPage;

                        rows.forEach((row, index) => {
                            row.style.display = (index >= start && index < end) ? "" : "none";
                        });

                        pageInfo.innerText = `Page ${page} / ${totalPages}`;

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.getElementById('searchEmployee').addEventListener('keyup', function() {

            let value = this.value.toLowerCase();
            let rows = document.querySelectorAll("#employeeTable tbody tr");
            let found = false;

            rows.forEach(function(row) {

                if (row.id === "noResultRow") return;

                let text = row.textContent.toLowerCase();

                if (text.indexOf(value) > -1) {
                    row.style.display = "";
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