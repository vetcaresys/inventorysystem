<?php
session_start();
require '../connectiondb.php';

if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] != 'forms_admin'
) {
    header("Location: ../login.php");
    exit;
}


/* ADD FORM */
if (isset($_POST['add_form'])) {

    $form_code = $_POST['form_code'];
    $form_name = $_POST['form_name'];
    $price = $_POST['unit_price'];
    $stock = $_POST['stock'];

    $stmt = $conn->prepare("
INSERT INTO forms(
form_code,
form_name,
unit_price,
beginning_inventory,
current_stock,
status
)
VALUES(?,?,?,?,?,'Available')
");

    $stmt->bind_param(
        "ssdii",
        $form_code,
        $form_name,
        $price,
        $stock,
        $stock
    );

    $stmt->execute();

    header("Location: forms_inventory.php");
    exit;
}



/* FETCH FORMS */
$forms = $conn->query("
SELECT *
FROM forms
ORDER BY form_name ASC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width,initial-scale=1">

    <title>Forms Inventory</title>

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
            background: #0d2c6c;
            position: fixed;
            left: 0;
            top: 0;
        }

        #sidebar .nav-link {
            color: #dbe7ff;
            padding: 14px 25px;
            display: block;
        }

        #sidebar .nav-link:hover,
        #sidebar .active {
            background: #1f4fb8;
            color: white;
        }

        #main {
            margin-left: 260px;
            padding: 30px;
        }

        .card-box {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .05);
        }

        .badge-low {
            background: #dc3545;
        }

        .badge-good {
            background: #198754;
        }
    </style>
</head>

<body>


    <!-- SIDEBAR -->
    <div id="sidebar">

        <div class="p-4 text-center border-bottom text-white">
            <h5 class="fw-bold mb-0">
                PSA FORMS ADMIN
            </h5>
            <small>Selling Inventory</small>
        </div>

        <nav class="nav flex-column mt-4">

            <a href="forms_dashboard.php"
                class="nav-link">
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </a>

            <a href="forms_inventory.php"
                class="nav-link active">
                <i class="bi bi-file-earmark-text"></i>
                Forms Inventory
            </a>

            <a href="restock_forms.php"
                class="nav-link">
                <i class="bi bi-box-seam"></i>
                Restock Forms
            </a>

            <a href="sales.php"
                class="nav-link">
                <i class="bi bi-cart-check"></i>
                Sell Forms
            </a>

            <a href="forms_reports.php"
                class="nav-link">
                <i class="bi bi-bar-chart-line"></i>
                Reports
            </a>

            <hr>

            <a href="../logout.php"
                class="nav-link text-warning">
                Logout
            </a>

        </nav>

    </div>



    <!-- MAIN -->
    <div id="main">

        <nav class="navbar bg-white rounded-4 shadow-sm px-4 mb-4">
            <h4 class="mb-0">
                Forms Inventory
            </h4>

            <button
                class="btn btn-primary rounded-pill"
                data-bs-toggle="modal"
                data-bs-target="#addModal">

                <i class="bi bi-plus-circle"></i>
                Add Form

            </button>

        </nav>



        <!-- INVENTORY TABLE -->
        <div class="card card-box">

            <div class="card-header bg-white">
                <h5 class="fw-bold mb-0">
                    Available Forms
                </h5>
            </div>

            <div class="card-body p-0">

                <table class="table table-hover text-center mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Form Name</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php
                        if ($forms->num_rows == 0) {
                            echo "
<tr>
<td colspan='6'>
No forms available
</td>
</tr>";
                        }

                        while ($row = $forms->fetch_assoc()) {

                            $stock = $row['current_stock'];

                            $status =
                                ($stock < 50)
                                ? "<span class='badge badge-low'>
Low Stock
</span>"
                                : "<span class='badge badge-good'>
Available
</span>";
                        ?>

                            <tr>

                                <td>
                                    <?= $row['form_code']; ?>
                                </td>

                                <td>
                                    <?= $row['form_name']; ?>
                                </td>

                                <td>
                                    ₱<?= number_format(
                                            $row['unit_price'],
                                            2
                                        ); ?>
                                </td>

                                <td>
                                    <?= $stock; ?>
                                </td>

                                <td>
                                    <?= $status; ?>
                                </td>

                                <td>

                                    <button class="btn btn-sm btn-outline-primary">
                                        Edit
                                    </button>

                                </td>

                            </tr>

                        <?php } ?>

                    </tbody>

                </table>

            </div>
        </div>



        <!-- SUMMARY -->
        <div class="row mt-4 g-4">

            <?php
            $total = $conn->query("
SELECT COUNT(*) t FROM forms
")->fetch_assoc()['t'];

            $stock = $conn->query("
SELECT SUM(current_stock) s
FROM forms
")->fetch_assoc()['s'];
            ?>

            <div class="col-md-6">
                <div class="card card-box p-4">
                    <small class="text-muted">
                        Total Form Types
                    </small>

                    <h2>
                        <?= $total ?>
                    </h2>
                </div>
            </div>


            <div class="col-md-6">
                <div class="card card-box p-4">
                    <small class="text-muted">
                        Total Stocks
                    </small>

                    <h2>
                        <?= $stock ?>
                    </h2>
                </div>
            </div>

        </div>

    </div>



    <!-- ADD MODAL -->
    <div class="modal fade"
        id="addModal">

        <div class="modal-dialog">
            <div class="modal-content rounded-4">

                <form method="POST">

                    <div class="modal-header">
                        <h5>Add New Form</h5>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal">
                        </button>

                    </div>


                    <div class="modal-body">

                        <div class="mb-3">
                            <label>Form Code</label>

                            <input
                                name="form_code"
                                class="form-control"
                                required>
                        </div>


                        <div class="mb-3">
                            <label>Form Name</label>

                            <input
                                name="form_name"
                                class="form-control"
                                required>
                        </div>


                        <div class="mb-3">
                            <label>Unit Price</label>

                            <input
                                type="number"
                                step=".01"
                                name="unit_price"
                                class="form-control"
                                required>
                        </div>


                        <div class="mb-3">
                            <label>Beginning Stock</label>

                            <input
                                type="number"
                                name="stock"
                                class="form-control"
                                required>
                        </div>

                    </div>


                    <div class="modal-footer">

                        <button
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                            type="button">
                            Cancel
                        </button>

                        <button
                            name="add_form"
                            class="btn btn-primary">
                            Save Form
                        </button>

                    </div>

                </form>

            </div>
        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>