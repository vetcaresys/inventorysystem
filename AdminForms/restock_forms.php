<?php
session_start();
require '../connectiondb.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'forms_admin') {
    header("Location: ../login.php");
    exit;
}

/* =========================
RESTOCK PROCESS
========================= */
if (isset($_POST['restock'])) {

    $form_id = $_POST['form_id'];
    $dr   = trim($_POST['dr_no']);
    $qty  = intval($_POST['quantity']);
    $date = $_POST['date_received'];

    if (empty($form_id) || empty($dr) || empty($date) || empty($qty)) {
        header("Location: restock_forms.php?error=empty_fields");
        exit;
    }

    if ($qty <= 0) {
        header("Location: restock_forms.php?error=invalid_quantity");
        exit;
    }

    // CHECK DUPLICATE DR
    $check = $conn->prepare("
        SELECT 1 FROM form_restock 
        WHERE delivery_receipt_no = ? AND form_id = ?
        LIMIT 1
    ");
    $check->bind_param("si", $dr, $form_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        header("Location: restock_forms.php?error=duplicate_dr");
        exit;
    }

    $conn->begin_transaction();

    try {

        // INSERT
        $stmt = $conn->prepare("
            INSERT INTO form_restock(
                form_id, delivery_receipt_no, quantity_received, date_received
            ) VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isis", $form_id, $dr, $qty, $date);
        $stmt->execute();

        // UPDATE STOCK
        $update = $conn->prepare("
            UPDATE forms 
            SET current_stock = current_stock + ?
            WHERE form_id = ?
        ");
        $update->bind_param("ii", $qty, $form_id);
        $update->execute();

        $conn->commit();

        $_SESSION['success'] = "Restock successful!";
        header("Location: restock_forms.php");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: restock_forms.php?error=failed");
        exit;
    }
}

/* =========================
SEARCH + PAGINATION
========================= */
$limit = 10;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? "");
$like = "%$search%";

/* COUNT */
if ($search) {
    $countStmt = $conn->prepare("
        SELECT COUNT(*) total
        FROM form_restock r
        JOIN forms f ON r.form_id = f.form_id
        WHERE r.delivery_receipt_no LIKE ?
        OR f.form_name LIKE ?
    ");
    $countStmt->bind_param("ss", $like, $like);
} else {
    $countStmt = $conn->prepare("SELECT COUNT(*) total FROM form_restock");
}

$countStmt->execute();
$totalRows = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

/* FETCH HISTORY */
if ($search) {
    $stmt = $conn->prepare("
        SELECT r.*, f.form_name
        FROM form_restock r
        JOIN forms f ON r.form_id = f.form_id
        WHERE r.delivery_receipt_no LIKE ?
        OR f.form_name LIKE ?
        ORDER BY r.restock_id DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("ssii", $like, $like, $limit, $offset);
} else {
    $stmt = $conn->prepare("
        SELECT r.*, f.form_name
        FROM form_restock r
        JOIN forms f ON r.form_id = f.form_id
        ORDER BY r.restock_id DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$history = $stmt->get_result();

/* FORMS LIST */
$forms = $conn->query("SELECT * FROM forms ORDER BY form_name ASC");

/* HELPER */
function buildPageUrl($page, $search)
{
    return "?page=$page&search=" . urlencode($search);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width,initial-scale=1">

    <title>Restock Forms</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"
        rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
            background: #0d2c6c;
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

    <?php if (isset($_SESSION['success'])) { ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '<?= $_SESSION['success'] ?>',
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    <?php unset($_SESSION['success']);
    } ?>


    <?php if (isset($_GET['error'])) { ?>
        <script>
            let msg = "";

            <?php if ($_GET['error'] == 'empty_fields') { ?>
                msg = "Please fill all fields!";
            <?php } elseif ($_GET['error'] == 'invalid_quantity') { ?>
                msg = "Invalid quantity!";
            <?php } elseif ($_GET['error'] == 'duplicate_dr') { ?>
                msg = "DR number already exists!";
            <?php } elseif ($_GET['error'] == 'failed') { ?>
                msg = "Something went wrong!";
            <?php } ?>

            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: msg
            });
        </script>
    <?php } ?>


    <!-- SIDEBAR -->
    <div id="sidebar">

        <div class="p-4 text-center border-bottom text-white">
            <h5 class="fw-bold mb-0">
                PSA FORMS ADMIN
            </h5>
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

            <a href="restock_forms.php" class="nav-link active">
                <i class="bi bi-box-seam"></i>
                Restock Forms
            </a>

            <a href="sales.php" class="nav-link">
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

            <h4 class="mb-0">
                Restock Forms
            </h4>

            <button
                class="btn btn-primary rounded-pill"
                data-bs-toggle="modal"
                data-bs-target="#restockModal">

                <i class="bi bi-plus-circle"></i>
                New Restock

            </button>

        </nav>

        <!-- RESTOCK HISTORY -->
        <div class="card card-box">

            <div class="card-header bg-white d-flex justify-content-between align-items-center">

                <h5 class="fw-bold mb-0">
                    Restock History
                </h5>

                <form class="d-flex gap-2 m-0" style="max-width: 350px; width: 100%;">
                    <input type="text"
                        id="searchInput"
                        class="form-control form-control-sm"
                        placeholder="Search DR or Form..."
                        value="<?= htmlspecialchars($search) ?>">
                </form>

            </div>

            <div class="card-body p-0">

                <table class="table table-hover text-center mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>DR No.</th>
                            <th>Form</th>
                            <th>Qty</th>
                            <th>Date Received</th>
                        </tr>
                    </thead>

                    <tbody id="tableBody">

                        <?php
                        if ($history->num_rows == 0) {
                            echo "
<tr>
<td colspan='4'>
No restock records yet
</td>
</tr>";
                        }

                        while ($r = $history->fetch_assoc()) {
                        ?>

                            <tr>

                                <td>
                                    <?= $r['delivery_receipt_no']; ?>
                                </td>

                                <td>
                                    <?= $r['form_name']; ?>
                                </td>

                                <td>
                                    <?= $r['quantity_received']; ?>
                                </td>

                                <td>
                                    <?= $r['date_received']; ?>
                                </td>

                            </tr>

                        <?php } ?>

                    </tbody>

                </table>

                <div class="d-flex justify-content-between p-3 bg-light">

                    <small>Page <?= $page ?> of <?= $totalPages ?></small>

                    <ul class="pagination mb-0">

                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link"
                                href="<?= buildPageUrl($page - 1, $search) ?>">
                                Prev
                            </a>
                        </li>

                        <li class="page-item active">
                            <span class="page-link"><?= $page ?></span>
                        </li>

                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link"
                                href="<?= buildPageUrl($page + 1, $search) ?>">
                                Next
                            </a>
                        </li>

                    </ul>
                </div>

            </div>
        </div>

    </div>




    <!-- RESTOCK MODAL -->
    <div class="modal fade"
        id="restockModal">

        <div class="modal-dialog">
            <div class="modal-content rounded-4">

                <form method="POST">

                    <div class="modal-header">
                        <h5>Restock Forms</h5>

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

                                    <option value="<?= $form['form_id']; ?>">
                                        <?= $form['form_name']; ?>
                                        (Stock: <?= $form['current_stock']; ?>)
                                    </option>

                                <?php } ?>

                            </select>
                        </div>



                        <div class="mb-3">
                            <label>Delivery Receipt No.</label>

                            <input
                                name="dr_no"
                                class="form-control"
                                required>

                        </div>



                        <div class="mb-3">
                            <label>Quantity Received</label>

                            <input
                                type="number"
                                name="quantity"
                                class="form-control"
                                required>

                        </div>



                        <div class="mb-3">
                            <label>Date Received</label>

                            <input
                                type="date"
                                name="date_received"
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
                            name="restock"
                            class="btn btn-primary">
                            Save Restock
                        </button>

                    </div>

                </form>

            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let timer;

        document.getElementById("searchInput").addEventListener("input", function() {

            clearTimeout(timer);

            let query = this.value;

            timer = setTimeout(() => {

                fetch("search_history_stock.php?search=" + encodeURIComponent(query))
                    .then(res => res.text())
                    .then(data => {
                        document.getElementById("tableBody").innerHTML = data;
                    });

            }, 300); // smooth typing delay

        });
    </script>

</body>

</html>