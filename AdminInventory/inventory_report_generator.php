<?php
session_start();
require '../connectiondb.php';

$items = $conn->query("
SELECT DISTINCT description
FROM equipment_inventory
ORDER BY description
");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Generate Inventory Report</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #eef3fb;
        }

        .box {
            max-width: 700px;
            margin: 60px auto;
            background: white;
            padding: 40px;
            border-radius: 18px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .08);
        }
    </style>

</head>

<body>

    <div class="box">

        <h3 class="mb-4">
            Generate Inventory Excel Report
        </h3>

        <form action="generate_inventory_report.php" method="GET">

            <div class="mb-3">
                <label>Year</label>
                <select name="year" class="form-control">
                    <?php
                    for ($y = 2024; $y <= 2035; $y++) {
                        echo "<option>$y</option>";
                    }
                    ?>
                </select>
            </div>


            <div class="mb-3">
                <label>Month (Optional)</label>
                <select name="month" class="form-control">
                    <option value="">All Months</option>
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


            <div class="mb-3">
                <label>Item (Optional)</label>
                <select name="item" class="form-control">
                    <option value="">All Items</option>

                    <?php while ($i = $items->fetch_assoc()) { ?>
                        <option value="<?= $i['description']; ?>">
                            <?= $i['description']; ?>
                        </option>
                    <?php } ?>

                </select>
            </div>


            <div class="mb-3">
                <label>Condition (Optional)</label>
                <select name="condition" class="form-control">
                    <option value="">All</option>
                    <option>Good</option>
                    <option>Repair Needed</option>
                    <option>Unserviceable</option>
                </select>
            </div>


            <button class="btn btn-success">
                Generate Excel Report
            </button>

            <a href="inventory_reports.php"
                class="btn btn-secondary">
                Cancel
            </a>

        </form>

    </div>

</body>

</html>