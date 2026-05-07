<?php
session_start();
require '../connectiondb.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'forms_admin') {
    header("Location: ../login.php");
    exit;
}

/* =========================
LIVE SEARCH AJAX (SAME FILE)
========================= */
if (isset($_GET['ajax_search'])) {

    $search = $_GET['search'] ?? '';
    $like = "%$search%";

    $stmt = $conn->prepare("
        SELECT s.*, f.form_name
        FROM form_sales s
        JOIN forms f ON s.form_id = f.form_id
        WHERE f.form_name LIKE ?
           OR s.buyer_name LIKE ?
           OR s.department LIKE ?
        ORDER BY s.sale_id DESC
        LIMIT 10
    ");

    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "<tr><td colspan='7'>No results found</td></tr>";
        exit;
    }

    while ($s = $result->fetch_assoc()) {
        echo "
        <tr>
            <td>{$s['form_name']}</td>
            <td>{$s['buyer_name']}</td>
            <td>{$s['department']}</td>
            <td>{$s['address']}</td>
            <td>{$s['quantity_sold']}</td>
            <td>₱" . number_format($s['total_amount'], 2) . "</td>
            <td>{$s['date_sold']}</td>
        </tr>
        ";
    }

    exit;
}

$error = "";

/* =========================
PROCESS SALE
========================= */
if (isset($_POST['sell_form'])) {

    $form_id = $_POST['form_id'];
    $qty = intval($_POST['quantity']);
    $date = $_POST['date_sold'];
    $buyer_name = $_POST['buyer_name'];
    $department = $_POST['department'];
    $address = $_POST['address'];
    $admin = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT current_stock, unit_price FROM forms WHERE form_id = ?");
    $stmt->bind_param("i", $form_id);
    $stmt->execute();
    $form = $stmt->get_result()->fetch_assoc();

    if (!$form) {
        $error = "Invalid form selected.";
    } else {

        if ($qty > $form['current_stock']) {
            $error = "Insufficient stock.";
        } else {

            $total = $qty * $form['unit_price'];

            $stmt = $conn->prepare("
                INSERT INTO form_sales
                (form_id, buyer_name, department, address, quantity_sold, total_amount, date_sold, sold_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "isssidsi",
                $form_id,
                $buyer_name,
                $department,
                $address,
                $qty,
                $total,
                $date,
                $admin
            );

            $stmt->execute();

            $stmt = $conn->prepare("
                UPDATE forms
                SET current_stock = current_stock - ?
                WHERE form_id = ?
            ");

            $stmt->bind_param("ii", $qty, $form_id);
            $stmt->execute();

            header("Location: sales.php?added=1");
            exit;
        }
    }
}

/* =========================
PAGINATION (NORMAL MODE)
========================= */

$limit = 10;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? "");
$like = "%$search%";

/* COUNT */
if (!empty($search)) {

    $countStmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM form_sales s
        JOIN forms f ON s.form_id = f.form_id
        WHERE f.form_name LIKE ?
           OR s.buyer_name LIKE ?
           OR s.department LIKE ?
    ");

    $countStmt->bind_param("sss", $like, $like, $like);
} else {

    $countStmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM form_sales
    ");
}

$countStmt->execute();
$totalRows = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = max(1, ceil($totalRows / $limit));

/* DATA */
if (!empty($search)) {

    $stmt = $conn->prepare("
        SELECT s.*, f.form_name
        FROM form_sales s
        JOIN forms f ON s.form_id = f.form_id
        WHERE f.form_name LIKE ?
           OR s.buyer_name LIKE ?
           OR s.department LIKE ?
        ORDER BY s.sale_id DESC
        LIMIT ? OFFSET ?
    ");

    $stmt->bind_param("sssii", $like, $like, $like, $limit, $offset);
} else {

    $stmt = $conn->prepare("
        SELECT s.*, f.form_name
        FROM form_sales s
        JOIN forms f ON s.form_id = f.form_id
        ORDER BY s.sale_id DESC
        LIMIT ? OFFSET ?
    ");

    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$sales = $stmt->get_result();

$transactionCount = $conn->query("
    SELECT COUNT(*) AS total FROM form_sales
")->fetch_assoc()['total'];

function buildPageUrl($page, $search)
{
    return "?page=$page&search=" . urlencode($search);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Sell Forms</title>

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
            border-radius: 18px;
            overflow: hidden;
            background: white;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .06);
        }

        /* MODERN STAT CARD */
        .card-stat {
            border: none;
            border-radius: 18px;
            overflow: hidden;
            background: white;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .06);
            transition: .25s;
        }

        .card-stat:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 28px rgba(0, 0, 0, .10);
        }

        .card-strip {
            height: 5px;
            width: 100%;
        }

        .strip-green {
            background: #198754;
        }

        .strip-blue {
            background: #0d6efd;
        }

        .card-stat-body {
            padding: 24px;
        }

        .card-stat small {
            color: #6c757d;
            font-size: 14px;
        }

        .card-stat h2 {
            margin-top: 8px;
            margin-bottom: 0;
            font-weight: bold;
            color: #1b2a4e;
            font-size: 32px;
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <div id="sidebar">

        <div class="p-4 text-center border-bottom text-white">
            <h5 class="fw-bold mb-0">PSA FORMS ADMIN</h5>
            <small>Selling Inventory</small>
        </div>

        <nav class="nav flex-column mt-4">

            <a href="forms_userprofile.php" class="nav-link">
                <i class="bi bi-person-circle"></i> My Profile
            </a>

            <a href="forms_dashboard.php" class="nav-link">
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </a>

            <a href="forms_inventory.php" class="nav-link">
                <i class="bi bi-file-earmark-text"></i>
                Forms Inventory
            </a>

            <a href="restock_forms.php" class="nav-link">
                <i class="bi bi-box-seam"></i>
                Restock Forms
            </a>

            <a href="sales.php" class="nav-link active">
                <i class="bi bi-cart-check"></i>
                Sell Forms
            </a>

            <a href="forms_reports.php" class="nav-link">
                <i class="bi bi-bar-chart-line"></i>
                Reports
            </a>

            <hr>

            <a href="../logout.php" class="nav-link text-warning">
                <i class="bi bi-box-arrow-left"></i>
                Logout
            </a>

        </nav>
    </div>


    <!-- MAIN -->
    <div id="main">

        <nav class="navbar bg-white rounded-4 shadow-sm px-4 mb-4">
            <h4 class="mb-0">Forms Sales</h4>

            <button
                class="btn btn-primary rounded-pill"
                data-bs-toggle="modal"
                data-bs-target="#saleModal">

                <i class="bi bi-cart-check"></i>
                New Sale

            </button>
        </nav>


        <?php if ($error != "") { ?>
            <div class="alert alert-danger rounded-4">
                <?= $error ?>
            </div>
        <?php } ?>


        <!-- SUMMARY -->
        <div class="row g-4 mb-3">

            <div class="col-md-4">
                <div class="card card-stat">
                    <div class="card-strip strip-blue"></div>
                    <div class="card-stat-body py-3">
                        <small>Total Transactions</small>
                        <h4 class="mb-0 fw-bold"><?= $transactionCount ?></h4>
                    </div>
                </div>
            </div>

        </div>


        <!-- SALES TABLE -->
        <div class="card card-box">

            <div class="card-header bg-white d-flex justify-content-between align-items-center">

                <h5 class="fw-bold mb-0">Sales Transactions</h5>

                <form method="GET" class="d-flex" style="width: 320px;">

                    <input
                        type="text"
                        id="searchInput"
                        class="form-control"
                        placeholder="Search form, buyer, department...">

                </form>

            </div>

            <div class="card-body p-0">

                <table class="table table-hover text-center mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>Form</th>
                            <th>Buyer</th>
                            <th>Department</th>
                            <th>Address</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Date</th>
                        </tr>
                    </thead>

                    <tbody id="salesTable">

                        <?php if ($sales->num_rows == 0) { ?>
                            <tr>
                                <td colspan="7">No sales transactions yet</td>
                            </tr>
                        <?php } ?>

                        <?php while ($s = $sales->fetch_assoc()) { ?>

                            <tr>
                                <td><?= $s['form_name']; ?></td>
                                <td><?= $s['buyer_name']; ?></td>
                                <td><?= $s['department']; ?></td>
                                <td><?= $s['address']; ?></td>
                                <td><?= $s['quantity_sold']; ?></td>
                                <td>₱<?= number_format($s['total_amount'], 2); ?></td>
                                <td><?= $s['date_sold']; ?></td>
                            </tr>

                        <?php } ?>

                    </tbody>

                </table>

                <div class="d-flex justify-content-between align-items-center p-3 bg-white border-top">

                    <!-- Page Info -->
                    <small class="text-muted fw-semibold">
                        Page <?= $page ?> of <?= $totalPages ?>
                    </small>

                    <!-- Pagination -->
                    <ul class="pagination mb-0">

                        <!-- Prev -->
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link rounded-start-pill px-3"
                                href="<?= buildPageUrl($page - 1, $search) ?>">
                                <i class="bi bi-chevron-left"></i> Prev
                            </a>
                        </li>

                        <!-- Current Page -->
                        <li class="page-item active">
                            <span class="page-link px-3">
                                <?= $page ?>
                            </span>
                        </li>

                        <!-- Next -->
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link rounded-end-pill px-3"
                                href="<?= buildPageUrl($page + 1, $search) ?>">
                                Next <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>

                    </ul>

                </div>

            </div>
        </div>


        <!-- SELL MODAL -->
        <div class="modal fade" id="saleModal">
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

                                    <option value="">Select Form</option>

                                    <?php
                                    $f = $conn->query("
                                SELECT *
                                FROM forms
                                ORDER BY form_name
                                ");

                                    while ($form = $f->fetch_assoc()) {
                                    ?>

                                        <option value="<?= $form['form_id']; ?>">
                                            <?= $form['form_name']; ?>
                                            (Stock: <?= $form['current_stock']; ?>)
                                        </option>

                                    <?php } ?>

                                </select>
                            </div>

                            <div class="mb-3">
                                <label>Buyer Name</label>
                                <input
                                    type="text"
                                    name="buyer_name"
                                    class="form-control"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label>Department / Office Unit</label>
                                <input
                                    type="text"
                                    name="department"
                                    class="form-control"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label>Address</label>
                                <input
                                    type="text"
                                    name="address"
                                    class="form-control"
                                    required>
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if (isset($_GET['added'])) { ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Sale added successfully',
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    <?php } ?>

    <?php if (isset($_GET['updated'])) { ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text: 'Sale updated successfully',
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    <?php } ?>

    <script>
        let timer;

        document.getElementById("searchInput").addEventListener("keyup", function() {

            clearTimeout(timer);

            timer = setTimeout(() => {

                let search = this.value;

                fetch("sales.php?ajax_search=1&search=" + encodeURIComponent(search))
                    .then(res => res.text())
                    .then(data => {
                        document.getElementById("salesTable").innerHTML = data;
                    });

            }, 300);

        });
    </script>

</body>

</html>