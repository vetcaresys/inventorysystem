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



/* =========================
PROCESS SALE
========================= */

$error = "";

if (isset($_POST['sell_form'])) {

    $form_id = $_POST['form_id'];
    $qty = $_POST['quantity'];
    $date = $_POST['date_sold'];
    $admin = $_SESSION['user_id'];


    /* check stock */
    $get = $conn->query("
SELECT *
FROM forms
WHERE form_id='$form_id'
");

    $form = $get->fetch_assoc();

    $current_stock = $form['current_stock'];
    $price = $form['unit_price'];

    if ($qty > $current_stock) {

        $error = "Insufficient stock.";
    } else {

        $total = $qty * $price;


        /* save sale */
        $stmt = $conn->prepare("
INSERT INTO form_sales(
form_id,
quantity_sold,
total_amount,
date_sold,
sold_by
)
VALUES(?,?,?,?,?)
");

        $stmt->bind_param(
            "iidsi",
            $form_id,
            $qty,
            $total,
            $date,
            $admin
        );

        $stmt->execute();


        /* deduct stock */
        $update = $conn->prepare("
UPDATE forms
SET current_stock=
current_stock - ?
WHERE form_id=?
");

        $update->bind_param(
            "ii",
            $qty,
            $form_id
        );

        $update->execute();


        header("Location: sales.php");
        exit;
    }
}



/* recent sales */
$sales = $conn->query("
SELECT
s.*,
f.form_name
FROM form_sales s
JOIN forms f
ON s.form_id=f.form_id
ORDER BY sale_id DESC
");


$totalSales = $conn->query("
SELECT COALESCE(
SUM(total_amount),0
) total
FROM form_sales
")->fetch_assoc()['total'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width,initial-scale=1">

    <title>Sell Forms</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"
        rel="stylesheet">

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
                Dashboard
            </a>

            <a href="forms_inventory.php"
                class="nav-link">
                Forms Inventory
            </a>

            <a href="restock_forms.php"
                class="nav-link">
                Restock Forms
            </a>

            <a href="sales.php"
                class="nav-link active">
                Sell Forms
            </a>

            <a href="forms_reports.php"
                class="nav-link">
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
                Forms Sales
            </h4>

            <button
                class="btn btn-primary rounded-pill"
                data-bs-toggle="modal"
                data-bs-target="#saleModal">

                <i class="bi bi-cart-check"></i>
                New Sale

            </button>

        </nav>



        <?php if ($error != "") { ?>
            <div class="alert alert-danger">
                <?= $error ?>
            </div>
        <?php } ?>



        <!-- SUMMARY -->
        <div class="row g-4 mb-4">

            <div class="col-md-6">
                <div class="card card-box p-4">

                    <small class="text-muted">
                        Total Sales Revenue
                    </small>

                    <h2>
                        ₱<?= number_format($totalSales, 2); ?>
                    </h2>

                </div>
            </div>


            <div class="col-md-6">
                <div class="card card-box p-4">

                    <small class="text-muted">
                        Transactions
                    </small>

                    <h2>
                        <?= $sales->num_rows ?>
                    </h2>

                </div>
            </div>

        </div>



        <!-- SALES TABLE -->
        <div class="card card-box">

            <div class="card-header bg-white">
                <h5 class="fw-bold mb-0">
                    Sales Transactions
                </h5>
            </div>


            <div class="card-body p-0">

                <table class="table table-hover text-center mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>Form</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Date</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php
                        if ($sales->num_rows == 0) {
                            echo "
<tr>
<td colspan='4'>
No sales transactions yet
</td>
</tr>";
                        }

                        while ($s = $sales->fetch_assoc()) {
                        ?>

                            <tr>

                                <td>
                                    <?= $s['form_name']; ?>
                                </td>

                                <td>
                                    <?= $s['quantity_sold']; ?>
                                </td>

                                <td>
                                    ₱<?= number_format(
                                            $s['total_amount'],
                                            2
                                        ); ?>
                                </td>

                                <td>
                                    <?= $s['date_sold']; ?>
                                </td>

                            </tr>

                        <?php } ?>

                    </tbody>

                </table>

            </div>
        </div>

    </div>




    <!-- SELL MODAL -->
    <div class="modal fade"
        id="saleModal">

        <div class="modal-dialog">
            <div class="modal-content rounded-4">

                <form method="POST">

                    <div class="modal-header">
                        <h5>Sell Form</h5>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal">
                        </button>

                    </div>



                    <div class="modal-body">


                        <div class="mb-3">
                            <label>Select Form</label>

                            <select
                                name="form_id"
                                class="form-control"
                                required>

                                <option value="">
                                    Select Form
                                </option>

                                <?php
                                $f = $conn->query("
SELECT *
FROM forms
ORDER BY form_name
");

                                while ($form = $f->fetch_assoc()) {
                                ?>

                                    <option
                                        value="<?= $form['form_id']; ?>">

                                        <?= $form['form_name']; ?>
                                        (
                                        Stock:
                                        <?= $form['current_stock']; ?>
                                        )

                                    </option>

                                <?php } ?>

                            </select>

                        </div>



                        <div class="mb-3">
                            <label>Quantity Sold</label>

                            <input
                                type="number"
                                name="quantity"
                                class="form-control"
                                required>

                        </div>



                        <div class="mb-3">
                            <label>Date Sold</label>

                            <input
                                type="date"
                                name="date_sold"
                                class="form-control"
                                required>

                        </div>

                    </div>



                    <div class="modal-footer">

                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                            Cancel
                        </button>


                        <button
                            name="sell_form"
                            class="btn btn-primary">
                            Save Sale
                        </button>

                    </div>

                </form>

            </div>
        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>