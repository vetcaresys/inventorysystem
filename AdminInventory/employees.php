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

            <a href="inventory_items.php" class="nav-link">
                <i class="bi bi-box-seam"></i> Inventory Items
            </a>

            <a href="borrow_records.php" class="nav-link">
                <i class="bi bi-journal-arrow-up"></i> Borrow Records
            </a>

            <a href="return_records.php" class="nav-link">
                <i class="bi bi-journal-arrow-down"></i> Return Records
            </a>

            <a href="employees.php" class="nav-link active">
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

            <h5 class="mb-3">Employee List</h5>

            <table class="table table-hover text-center">
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

                        <tr>
                            <td><?= $e['employee_name']; ?></td>
                            <td><?= $e['office_unit']; ?></td>
                            <td><?= $e['position']; ?></td>
                            <td><?= $e['contact_no']; ?></td>
                            <td>
                                <a href="?delete=<?= $e['employee_id']; ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Delete this employee?')">
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