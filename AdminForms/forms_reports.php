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
REPORT DATA
========================= */

// ALL FORMS
$forms = $conn->query("
SELECT *
FROM forms
ORDER BY form_name ASC
");


// TOTAL SOLD PER FORM
$sales = $conn->query("
SELECT 
f.form_name,
COALESCE(SUM(s.quantity_sold),0) as total_sold
FROM forms f
LEFT JOIN form_sales s
ON f.form_id = s.form_id
GROUP BY f.form_id
");

$soldData = [];
while ($row = $sales->fetch_assoc()) {
    $soldData[] = $row;
}


// TOTAL RESTOCK PER FORM
$restock = $conn->query("
SELECT 
f.form_name,
COALESCE(SUM(r.quantity_received),0) as total_received
FROM forms f
LEFT JOIN form_restock r
ON f.form_id = r.form_id
GROUP BY f.form_id
");

$restockData = [];
while ($row = $restock->fetch_assoc()) {
    $restockData[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width,initial-scale=1">

    <title>Forms Reports</title>

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

        .table th {
            background: #f5f7fb;
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
            <small>Reports Module</small>
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
                class="nav-link">
                Sell Forms
            </a>

            <a href="forms_reports.php"
                class="nav-link active">
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
                Forms Inventory Report
            </h4>

        </nav>



        <!-- SUMMARY CARDS -->
        <div class="row g-4 mb-4">

            <?php
            $totalForms = $conn->query("
SELECT COUNT(*) t FROM forms
")->fetch_assoc()['t'];

            $totalStock = $conn->query("
SELECT SUM(current_stock) s FROM forms
")->fetch_assoc()['s'];

            $totalSold = $conn->query("
SELECT SUM(quantity_sold) s FROM form_sales
")->fetch_assoc()['s'];

            ?>

            <div class="col-md-4">
                <div class="card card-box p-4">
                    <small>Total Form Types</small>
                    <h2><?= $totalForms ?></h2>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-box p-4">
                    <small>Total Stock</small>
                    <h2><?= $totalStock ?></h2>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-box p-4">
                    <small>Total Sold</small>
                    <h2><?= $totalSold ?></h2>
                </div>
            </div>

        </div>



        <!-- REPORT TABLE -->
        <div class="card card-box">

            <div class="card-header bg-white">
                <h5 class="fw-bold mb-0">
                    Detailed Form Report
                </h5>
            </div>


            <div class="card-body p-0">

                <table class="table text-center mb-0">

                    <thead>
                        <tr>
                            <th>Form</th>
                            <th>Received</th>
                            <th>Sold</th>
                            <th>Current Stock</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php while ($f = $forms->fetch_assoc()) { ?>

                            <?php
                            $received = 0;
                            $sold = 0;

                            /* match received */
                            foreach ($restockData as $r) {
                                if ($r['form_name'] == $f['form_name']) {
                                    $received = $r['total_received'];
                                }
                            }

                            /* match sold */
                            foreach ($soldData as $s) {
                                if ($s['form_name'] == $f['form_name']) {
                                    $sold = $s['total_sold'];
                                }
                            }

                            $current = $f['current_stock'];
                            ?>

                            <tr>

                                <td>
                                    <?= $f['form_name']; ?>
                                </td>

                                <td>
                                    <?= $received; ?>
                                </td>

                                <td>
                                    <?= $sold; ?>
                                </td>

                                <td>
                                    <strong>
                                        <?= $current; ?>
                                    </strong>
                                </td>

                            </tr>

                        <?php } ?>

                    </tbody>

                </table>

            </div>
        </div>

    </div>

</body>

</html>