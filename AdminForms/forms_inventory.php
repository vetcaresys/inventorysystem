<?php
session_start();
require '../connectiondb.php';

/* =========================
AUTH CHECK
========================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'forms_admin') {
    header("Location: ../login.php");
    exit;
}

/* =========================
ADD FORM
========================= */
if (isset($_POST['add_form'])) {

    $form_code = trim($_POST['form_code']);
    $form_name = trim($_POST['form_name']);
    $price     = floatval($_POST['unit_price']);
    $stock     = intval($_POST['stock']);

    if (!$form_code || !$form_name || $price < 0 || $stock < 0) {
        header("Location: forms_inventory.php?error=invalid");
        exit;
    }

    // CHECK DUPLICATE
    $check = $conn->prepare("SELECT form_id FROM forms WHERE form_code = ?");
    $check->bind_param("s", $form_code);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        header("Location: forms_inventory.php?error=duplicate");
        exit;
    }

    // INSERT
    $stmt = $conn->prepare("
        INSERT INTO forms 
        (form_code, form_name, unit_price, beginning_inventory, current_stock, status)
        VALUES (?, ?, ?, ?, ?, 'Available')
    ");

    $stmt->bind_param("ssdii", $form_code, $form_name, $price, $stock, $stock);

    if ($stmt->execute()) {
        header("Location: forms_inventory.php?success=1");
    } else {
        header("Location: forms_inventory.php?error=failed");
    }
    exit;
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
        FROM forms 
        WHERE form_code LIKE ? OR form_name LIKE ?
    ");
    $countStmt->bind_param("ss", $like, $like);
} else {
    $countStmt = $conn->prepare("SELECT COUNT(*) total FROM forms");
}

$countStmt->execute();
$totalRows = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

/* FETCH */
if ($search) {
    $stmt = $conn->prepare("
        SELECT * FROM forms
        WHERE form_code LIKE ? OR form_name LIKE ?
        ORDER BY form_name ASC
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("ssii", $like, $like, $limit, $offset);
} else {
    $stmt = $conn->prepare("
        SELECT * FROM forms
        ORDER BY form_name ASC
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("ii", $limit, $offset);
}

$stmt->execute();
$forms = $stmt->get_result();

/* =========================
SUMMARY CARDS
========================= */
$form_cards = $conn->query("
    SELECT form_name, SUM(current_stock) total_stock 
    FROM forms 
    GROUP BY form_name
");

/* =========================
HELPER
========================= */
function e($str)
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

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

    <title>Forms Inventory</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

        .form-card {
            border: none;
            border-radius: 18px;
            padding: 20px;
            color: #222;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .06);
            transition: .2s;
        }

        .form-card:hover {
            transform: translateY(-3px);
        }

        .card-1 {
            background: #ffffff;
            border-left: 5px solid #4e73df;
        }

        .card-2 {
            background: #ffffff;
            border-left: 5px solid #1cc88a;
        }

        .card-3 {
            background: #ffffff;
            border-left: 5px solid #f6c23e;
        }

        .card-4 {
            background: #ffffff;
            border-left: 5px solid #e74a3b;
        }

        .card-5 {
            background: #ffffff;
            border-left: 5px solid #6f42c1;
        }
    </style>
</head>

<body>

    <?php if (isset($_GET['success'])) { ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Form added successfully!',
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    <?php } ?>

    <?php if (isset($_GET['updated'])) { ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Updated',
                text: 'Form updated successfully!',
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    <?php } ?>

    <?php if (isset($_GET['deleted'])) { ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Deleted',
                text: 'Form deleted successfully!',
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    <?php } ?>

    <?php if (isset($_GET['error'])) { ?>

        <script>
            let msg = "";

            <?php if ($_GET['error'] == 'duplicate') { ?>
                msg = "Form code already exists!";
            <?php } elseif ($_GET['error'] == 'empty_fields') { ?>
                msg = "Please fill all fields!";
            <?php } elseif ($_GET['error'] == 'invalid_input') { ?>
                msg = "Invalid input detected!";
            <?php } elseif ($_GET['error'] == 'failed') { ?>
                msg = "Something went wrong!";
            <?php } else { ?>
                msg = "Unknown error occurred!";
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

            <a href="forms_inventory.php" class="nav-link active">
                <i class="bi bi-file-earmark-text"></i>
                Forms Inventory
            </a>

            <a href="restock_forms.php" class="nav-link">
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


        <!-- FORM TYPE CARDS -->
        <div class="row g-4 mb-4">
            <?php
            $count = 1;
            while ($card = $form_cards->fetch_assoc()) {
                $class = "card-" . (($count % 5) == 0 ? 5 : ($count % 5));
            ?>
                <div class="col-md-3">
                    <div class="form-card <?= $class ?>">
                        <small class="text-muted d-block mb-2">
                            <?= $card['form_name']; ?>
                        </small>

                        <h3 class="fw-bold mb-0">
                            <?= $card['total_stock']; ?>
                        </h3>

                        <small class="text-muted">
                            Total Quantity
                        </small>
                    </div>
                </div>
            <?php
                $count++;
            }
            ?>
        </div>



        <!-- INVENTORY TABLE -->
        <div class="card card-box">

            <div class="card-header bg-white d-flex justify-content-between align-items-center">

                <h5 class="fw-bold mb-0">
                    Available Forms
                </h5>

                <form method="GET" class="d-flex gap-2 m-0" style="max-width: 350px; width: 100%;">

                    <input type="text"
                        id="searchInput"
                        class="form-control form-control-sm"
                        placeholder="Search code or name...">

                    <button class="btn btn-primary btn-sm">
                        <i class="bi bi-search"></i>
                    </button>

                </form>

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

                    <tbody id="tableBody">

                        <?php
                        if ($forms->num_rows == 0) {
                            echo "<tr><td colspan='6'>No forms available</td></tr>";
                        }

                        while ($row = $forms->fetch_assoc()) {

                            $stock = $row['current_stock'];

                            $status =
                                ($stock < 50)
                                ? "<span class='badge badge-low'> Low Stock</span>"
                                : "<span class='badge badge-good'>Available</span>";
                        ?>

                            <tr>

                                <td>
                                    <?= $row['form_code']; ?>
                                </td>

                                <td>
                                    <?= e($row['form_name']); ?>
                                </td>

                                <td>
                                    ₱<?= number_format($row['unit_price'], 2); ?>
                                </td>

                                <td>
                                    <?= $stock; ?>
                                </td>

                                <td>
                                    <?= $status; ?>
                                </td>

                                <td>

                                    <button
                                        class="btn btn-sm btn-info"
                                        data-bs-toggle="modal"
                                        data-bs-target="#viewModal<?= $row['form_id']; ?>">
                                        View
                                    </button>

                                    <!-- <button
                                        class="btn btn-sm btn-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editModal<?= $row['form_id']; ?>">
                                        Edit
                                    </button> -->

                                    <button
                                        class="btn btn-sm btn-danger delete-btn"
                                        data-id="<?= $row['form_id']; ?>">
                                        Delete
                                    </button>

                                </td>

                            </tr>

                            <!-- VIEW MODAL -->
                            <div class="modal fade" id="viewModal<?= $row['form_id']; ?>">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 shadow">

                                        <!-- HEADER -->
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title">Form Details</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>

                                        <!-- BODY -->
                                        <div class="modal-body p-4">

                                            <div class="mb-3">
                                                <small class="text-muted">Form Code</small>
                                                <h6 class="mb-0"><?= $row['form_code']; ?></h6>
                                            </div>

                                            <div class="mb-3">
                                                <small class="text-muted">Form Name</small>
                                                <h6 class="mb-0"><?= $row['form_name']; ?></h6>
                                            </div>

                                            <div class="mb-3">
                                                <small class="text-muted">Price</small>
                                                <h6 class="mb-0">₱<?= number_format($row['unit_price'], 2); ?></h6>
                                            </div>

                                            <div class="mb-3">
                                                <small class="text-muted">Stock</small>
                                                <h6 class="mb-0"><?= $row['current_stock']; ?></h6>
                                            </div>

                                            <div class="mb-3">
                                                <small class="text-muted">Status</small>
                                                <h6 class="mb-0"><?= $row['status']; ?></h6>
                                            </div>

                                        </div>

                                        <!-- FOOTER BUTTONS -->
                                        <div class="modal-footer d-flex justify-content-end gap-2">

                                            <!-- BACK -->
                                            <button type="button"
                                                class="btn btn-secondary"
                                                data-bs-dismiss="modal">
                                                <i class="bi bi-arrow-left"></i> Back
                                            </button>

                                            <!-- EDIT -->
                                            <button type="button"
                                                class="btn btn-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editModal<?= $row['form_id']; ?>"
                                                data-bs-dismiss="modal">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>

                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- EDIT MODAL -->
                            <div class="modal fade" id="editModal<?= $row['form_id']; ?>">
                                <div class="modal-dialog">
                                    <div class="modal-content rounded-4">

                                        <form method="POST" action="update_form.php">

                                            <div class="modal-header">
                                                <h5>Edit Form</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body">

                                                <input type="hidden" name="form_id" value="<?= $row['form_id']; ?>">

                                                <!-- READ ONLY -->
                                                <div class="mb-2">
                                                    <label>Form Code</label>
                                                    <input class="form-control" value="<?= $row['form_code']; ?>" disabled>
                                                </div>

                                                <div class="mb-2">
                                                    <label>Stock</label>
                                                    <input class="form-control" value="<?= $row['current_stock']; ?>" disabled>
                                                </div>

                                                <div class="mb-2">
                                                    <label>Status</label>
                                                    <input class="form-control" value="<?= $row['status']; ?>" disabled>
                                                </div>

                                                <!-- EDITABLE -->
                                                <div class="mb-2">
                                                    <label>Form Name</label>
                                                    <input name="form_name" class="form-control"
                                                        value="<?= $row['form_name']; ?>" required>
                                                </div>

                                                <div class="mb-2">
                                                    <label>Unit Price</label>
                                                    <input type="number" step="0.01"
                                                        name="unit_price"
                                                        class="form-control"
                                                        value="<?= $row['unit_price']; ?>"
                                                        required>
                                                </div>

                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button class="btn btn-primary" name="update_form">Save</button>
                                            </div>

                                        </form>

                                    </div>
                                </div>
                            </div>

                        <?php } ?>

                    </tbody>

                </table>

                <!-- ✅ PAGINATION (FIXED DESIGN) -->
                <div class="d-flex justify-content-between align-items-center p-3 border-top bg-light">

                    <div class="text-muted small">
                        Page <b><?= $page ?></b> of <b><?= $totalPages ?></b>
                    </div>

                    <nav>
                        <ul class="pagination mb-0">

                            <!-- PREVIOUS -->
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link"
                                    href="<?= ($page > 1) ? buildPageUrl($page - 1, $search) : '#' ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>

                            <!-- CURRENT -->
                            <li class="page-item active">
                                <span class="page-link"><?= $page ?></span>
                            </li>

                            <!-- NEXT -->
                            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                <a class="page-link"
                                    href="<?= ($page < $totalPages) ? buildPageUrl($page + 1, $search) : '#' ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>

                        </ul>
                    </nav>

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

    <!-- FOR DELETE -->
    <script>
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function() {

                let id = this.getAttribute('data-id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This form will be permanently deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {

                        window.location.href = "delete_form.php?id=" + id;

                    }
                });

            });
        });
    </script>

    <script>
document.getElementById("searchInput").addEventListener("keyup", function() {

    let query = this.value;

    fetch("search_forms.php?search=" + encodeURIComponent(query))
        .then(res => res.text())
        .then(data => {
            document.getElementById("tableBody").innerHTML = data;
        });

});
</script>
</body>

</html>