<?php
session_start();
require '../connectiondb.php';

$items = $conn->query("
SELECT item_id,description
FROM equipment_inventory
");

$employees = $conn->query("
SELECT employee_id,employee_name
FROM employees
");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Borrow Report Generator</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #eef3fb;
            font-family: Arial;
        }

        .report-box {
            max-width: 850px;
            margin: 50px auto;
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, .05);
        }
    </style>
</head>

<body>

    <div class="report-box">

        <h3 class="mb-4 fw-bold">
            Generate Borrowed Items Report
        </h3>

        <form action="generate_borrow_report.php" method="GET">

            <div class="row g-3">

                <div class="col-md-4">
                    <label>Year</label>
                    <select name="year" class="form-control">
                        <?php
                        for ($y = date('Y'); $y >= 2020; $y--) {
                            echo "<option>$y</option>";
                        }
                        ?>
                    </select>
                </div>


                <div class="col-md-4">
                    <label>Month</label>
                    <select name="month" class="form-control">
                        <option value="">All</option>
                        <option value="1">January</option>
                        <option value="2">February</option>
                        <option value="3">March</option>
                        <option value="4">April</option>
                        <option value="5">May</option>
                        <option value="6">June</option>
                        <option value="7">July</option>
                        <option value="8">August</option>
                        <option value="9">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                </div>


                <div class="col-md-4">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">All</option>
                        <option>Borrowed</option>
                        <option>Returned</option>
                        <option>Overdue</option>
                    </select>
                </div>


                <div class="col-md-6">
                    <label>Item</label>

                    <select name="item" class="form-control">
                        <option value="">All Items</option>

                        <?php while ($i = $items->fetch_assoc()) { ?>
                            <option value="<?= $i['item_id'] ?>">
                                <?= $i['description'] ?>
                            </option>
                        <?php } ?>

                    </select>
                </div>


                <div class="col-md-6">
                    <label>Borrower</label>

                    <select name="employee" class="form-control">
                        <option value="">All Employees</option>

                        <?php while ($e = $employees->fetch_assoc()) { ?>
                            <option value="<?= $e['employee_id'] ?>">
                                <?= $e['employee_name'] ?>
                            </option>
                        <?php } ?>

                    </select>

                </div>

            </div>


            <button class="btn btn-primary mt-4">
                Generate Excel Report
            </button>

            <a href="inventory_reports.php"
                class="btn btn-secondary mt-4">
                Back
            </a>

        </form>

    </div>

</body>

</html>