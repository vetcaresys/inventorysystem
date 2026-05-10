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
ADD RECEIVER
========================= */
if (isset($_POST['add_receiver'])) {

    $name = $_POST['name'];
    $office_unit = $_POST['office_unit'];
    $position = $_POST['position'];
    $contact_no = $_POST['contact_no'];

    $check = $conn->prepare("
        SELECT receiver_id
        FROM receivers
        WHERE name=? AND contact_no=?
    ");

    $check->bind_param("ss", $name, $contact_no);
    $check->execute();

    $res = $check->get_result();

    if ($res->num_rows > 0) {

        header("Location: receiving_batches.php?duplicate=1");
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO receivers(
            name,
            office_unit,
            position,
            contact_no
        )
        VALUES(?,?,?,?)
    ");

    $stmt->bind_param(
        "ssss",
        $name,
        $office_unit,
        $position,
        $contact_no
    );

    $stmt->execute();

    header("Location: receiving_batches.php?receiver_added=1");
    exit;
}

/* =========================
UPDATE RECEIVER
========================= */
if (isset($_POST['update_receiver'])) {

    $receiver_id = $_POST['receiver_id'];
    $name = $_POST['name'];
    $office_unit = $_POST['office_unit'];
    $position = $_POST['position'];
    $contact_no = $_POST['contact_no'];

    $check = $conn->prepare("
        SELECT receiver_id
        FROM receivers
        WHERE name=? 
        AND contact_no=?
        AND receiver_id != ?
    ");

    $check->bind_param(
        "ssi",
        $name,
        $contact_no,
        $receiver_id
    );

    $check->execute();

    $res = $check->get_result();

    if ($res->num_rows > 0) {

        header("Location: receiving_batches.php?duplicate=1");
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE receivers SET
            name=?,
            office_unit=?,
            position=?,
            contact_no=?
        WHERE receiver_id=?
    ");

    $stmt->bind_param(
        "ssssi",
        $name,
        $office_unit,
        $position,
        $contact_no,
        $receiver_id
    );

    $stmt->execute();

    header("Location: receiving_batches.php?receiver_updated=1");
    exit;
}

/* =========================
ADD BATCH
========================= */
if (isset($_POST['add_batch'])) {

    $receiver_id = $_POST['receiver_id'];
    $received_date = $_POST['received_date'];
    $remarks = trim($_POST['remarks']);

    $attachment1 = "";
    $attachment2 = "";

    $allowed = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];

    /* VALIDATE REQUIRED FIELDS */
    if (empty($receiver_id) || empty($received_date)) {

        header("Location: receiving_batches.php?error=1");
        exit;
    }

    /* ATTACHMENT 1 */
    if (!empty($_FILES['attachment1']['name'])) {

        $ext1 = strtolower(pathinfo(
            $_FILES['attachment1']['name'],
            PATHINFO_EXTENSION
        ));

        if (!in_array($ext1, $allowed)) {

            header("Location: receiving_batches.php?invalid_file=1");
            exit;
        }

        $attachment1 =
            time() . "_1_" .
            basename($_FILES['attachment1']['name']);

        move_uploaded_file(
            $_FILES['attachment1']['tmp_name'],
            "../uploads/" . $attachment1
        );
    }

    /* ATTACHMENT 2 */
    if (!empty($_FILES['attachment2']['name'])) {

        $ext2 = strtolower(pathinfo(
            $_FILES['attachment2']['name'],
            PATHINFO_EXTENSION
        ));

        if (!in_array($ext2, $allowed)) {

            header("Location: receiving_batches.php?invalid_file=1");
            exit;
        }

        $attachment2 =
            time() . "_2_" .
            basename($_FILES['attachment2']['name']);

        move_uploaded_file(
            $_FILES['attachment2']['tmp_name'],
            "../uploads/" . $attachment2
        );
    }

    $stmt = $conn->prepare("
        INSERT INTO receiving_batches(
            receiver_id,
            received_date,
            attachment1,
            attachment2,
            remarks
        )
        VALUES(?,?,?,?,?)
    ");

    $stmt->bind_param(
        "issss",
        $receiver_id,
        $received_date,
        $attachment1,
        $attachment2,
        $remarks
    );

    $stmt->execute();

    header("Location: receiving_batches.php?added=1");
    exit;
}

/* =========================
UPDATE BATCH
========================= */
if (isset($_POST['update_batch'])) {

    $batch_id = $_POST['batch_id'];
    $receiver_id = $_POST['receiver_id'];
    $received_date = $_POST['received_date'];
    $remarks = $_POST['remarks'];

    /* GET OLD FILES */
    $stmtOld = $conn->prepare("
        SELECT attachment1, attachment2
        FROM receiving_batches
        WHERE batch_id=?
    ");

    $stmtOld->bind_param("i", $batch_id);
    $stmtOld->execute();

    $old = $stmtOld->get_result()->fetch_assoc();

    $attachment1 = $old['attachment1'];
    $attachment2 = $old['attachment2'];

    /* UPDATE ATTACHMENT1 */
    if (!empty($_FILES['attachment1']['name'])) {

        $attachment1 = time() . "_" . $_FILES['attachment1']['name'];

        move_uploaded_file(
            $_FILES['attachment1']['tmp_name'],
            "../uploads/" . $attachment1
        );
    }

    /* UPDATE ATTACHMENT2 */
    if (!empty($_FILES['attachment2']['name'])) {

        $attachment2 = time() . "_" . $_FILES['attachment2']['name'];

        move_uploaded_file(
            $_FILES['attachment2']['tmp_name'],
            "../uploads/" . $attachment2
        );
    }

    $stmt = $conn->prepare("
        UPDATE receiving_batches SET
            receiver_id=?,
            received_date=?,
            attachment1=?,
            attachment2=?,
            remarks=?
        WHERE batch_id=?
    ");

    $stmt->bind_param(
        "issssi",
        $receiver_id,
        $received_date,
        $attachment1,
        $attachment2,
        $remarks,
        $batch_id
    );

    $stmt->execute();

    header("Location: receiving_batches.php?updated=1");
    exit;
}

/* =========================
DELETE BATCH
========================= */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    $stmt = $conn->prepare("
        DELETE FROM receiving_batches
        WHERE batch_id=?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: receiving_batches.php?deleted=1");
    exit;
}

/* =========================
FETCH BATCHES
========================= */
$batches = $conn->query("
    SELECT 
        rb.*,
        r.name
    FROM receiving_batches rb
    LEFT JOIN receivers r
    ON rb.receiver_id = r.receiver_id
    ORDER BY rb.batch_id DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <title>Receiving Batches</title>

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

        .card-custom {
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
            <h5 class="fw-bold mb-0">
                PSA INVENTORY ADMIN
            </h5>
            <small>Equipment Module</small>
        </div>

        <nav class="nav flex-column mt-4">

            <a href="userprofile.php" class="nav-link">
                <i class="bi bi-person-circle"></i>
                User Profile
            </a>

            <a href="inventory_dashboard.php" class="nav-link">
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </a>

            <a href="employees.php" class="nav-link">
                <i class="bi bi-people"></i>
                Employees
            </a>

            <a href="receiving_batches.php" class="nav-link active">
                <i class="bi bi-box-arrow-in-down"></i>
                Receiving Batches
            </a>

            <a href="inventory_items.php" class="nav-link">
                <i class="bi bi-box-seam"></i>
                Inventory Items
            </a>

            <a href="borrow_records.php" class="nav-link">
                <i class="bi bi-journal-arrow-up"></i>
                Borrow Records
            </a>

            <a href="return_records.php" class="nav-link">
                <i class="bi bi-journal-arrow-down"></i>
                Return Records
            </a>

            <a href="inventory_reports.php" class="nav-link">
                <i class="bi bi-bar-chart-line"></i>
                Reports
            </a>

            <a href="backup_restore.php" class="nav-link">
                <i class="bi bi-database-fill-gear"></i>
                Backup & Restore
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

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h3 class="fw-bold mb-0">
                Receiving Batches
            </h3>

            <div class="d-flex gap-2">

                <!-- ADD RECEIVER -->
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addReceiverModal">

                    <i class="bi bi-plus-circle"></i>
                    Add Receiver

                </button>

                <!-- MANAGE RECEIVERS -->
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal"
                    data-bs-target="#manageReceiversModal">

                    <i class="bi bi-gear"></i>
                    Manage Receivers

                </button>

            </div>

        </div>

        <!-- ADD RECEIVING BATCH FORM -->
        <div class="card card-custom p-4 mb-4">

            <h5 class="mb-3">
                Add Receiving Batch
            </h5>

            <form method="POST" enctype="multipart/form-data">

                <div class="row g-3">

                    <div class="col-md-4">

                        <label class="form-label fw-semibold">
                            Receiver
                        </label>

                        <select name="receiver_id" class="form-control" required>

                            <option value="">
                                Select Receiver
                            </option>

                            <?php
                            $receivers = $conn->query("
                                SELECT *
                                FROM receivers
                                ORDER BY name ASC
                            ");

                            while ($r = $receivers->fetch_assoc()) {
                                ?>

                                <option value="<?= $r['receiver_id']; ?>">
                                    <?= $r['name']; ?>
                                </option>

                            <?php } ?>

                        </select>

                    </div>

                    <div class="col-md-3">

                        <label class="form-label fw-semibold">
                            Received Date
                        </label>

                        <input type="date" name="received_date" class="form-control" max="<?= date('Y-m-d'); ?>"
                            required>

                    </div>

                    <div class="col-md-5">

                        <label class="form-label fw-semibold">
                            Remarks
                        </label>

                        <input type="text" name="remarks" class="form-control" placeholder="Remarks">

                    </div>

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            Attachment 1
                        </label>

                        <input type="file" name="attachment1" class="form-control"
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">

                    </div>

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">
                            Attachment 2
                        </label>

                        <input type="file" name="attachment2" class="form-control">

                    </div>

                </div>

                <button name="add_batch" class="btn btn-primary mt-3">
                    Add Batch
                </button>

            </form>

        </div>

        <!-- TABLE -->
        <div class="card card-custom p-3">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h5 class="mb-0">
                    Batch List
                </h5>

                <div style="width:300px;">
                    <input type="text" id="searchBatch" class="form-control" placeholder="Search batch...">
                </div>

            </div>

            <table class="table table-hover text-center">

                <thead class="table-light">

                    <tr>
                        <th>Batch ID</th>
                        <th>Receiver</th>
                        <th>Date Received</th>
                        <th>Remarks</th>
                        <th>Action</th>
                    </tr>

                </thead>

                <tbody id="batchTable">

                    <?php while ($row = $batches->fetch_assoc()) { ?>

                        <tr class="batch-row">

                            <td>
                                Batch #<?= $row['batch_id']; ?>
                            </td>

                            <td>
                                <?= $row['name']; ?>
                            </td>

                            <td>
                                <?= $row['received_date']; ?>
                            </td>

                            <td>
                                <?= $row['remarks']; ?>
                            </td>

                            <td>

                                <!-- VIEW -->
                                <button class="btn btn-info btn-sm viewBtn" data-id="<?= $row['batch_id']; ?>"
                                    data-receiver="<?= $row['name']; ?>" data-date="<?= $row['received_date']; ?>"
                                    data-remarks="<?= $row['remarks']; ?>" data-att1="<?= $row['attachment1']; ?>"
                                    data-att2="<?= $row['attachment2']; ?>">
                                    View
                                </button>

                                <!-- EDIT -->
                                <button class="btn btn-warning btn-sm editBatchBtn" data-bs-toggle="modal"
                                    data-bs-target="#editBatchModal" data-id="<?= $row['batch_id']; ?>"
                                    data-receiver="<?= $row['receiver_id']; ?>" data-date="<?= $row['received_date']; ?>"
                                    data-remarks="<?= $row['remarks']; ?>">
                                    Edit
                                </button>

                                <!-- DELETE -->
                                <!-- <a href="?delete=<?= $row['batch_id']; ?>" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Delete batch?')">
                                    Delete
                                </a> -->

                            </td>

                        </tr>

                    <?php } ?>

                </tbody>

            </table>

            <nav class="mt-3">
                <ul class="pagination justify-content-left" id="receiverPagination"></ul>
            </nav>

        </div>

    </div>

    <!-- EDIT BATCH MODAL -->
    <div class="modal fade" id="editBatchModal" tabindex="-1">

        <div class="modal-dialog">

            <div class="modal-content">

                <form method="POST" enctype="multipart/form-data">

                    <div class="modal-header bg-warning">

                        <h5 class="modal-title">
                            Update Batch
                        </h5>

                        <button class="btn-close" data-bs-dismiss="modal"></button>

                    </div>

                    <div class="modal-body">

                        <input type="hidden" name="batch_id" id="edit_batch_id">

                        <!-- RECEIVER -->
                        <label class="form-label">
                            Receiver
                        </label>

                        <select name="receiver_id" id="edit_batch_receiver" class="form-control mb-2" required>

                            <option value="">
                                Select Receiver
                            </option>

                            <?php
                            $receivers = $conn->query("
                            SELECT *
                            FROM receivers
                            ORDER BY name ASC
                        ");

                            while ($r = $receivers->fetch_assoc()) {
                                ?>

                                <option value="<?= $r['receiver_id']; ?>">
                                    <?= $r['name']; ?>
                                </option>

                            <?php } ?>

                        </select>

                        <!-- RECEIVED DATE -->
                        <label class="form-label">
                            Received Date
                        </label>

                        <input type="date" name="received_date" id="edit_batch_date" class="form-control mb-2"
                            max="<?= date('Y-m-d'); ?>" required>

                        <!-- REMARKS -->
                        <label class="form-label">
                            Remarks
                        </label>

                        <input type="text" name="remarks" id="edit_batch_remarks" class="form-control mb-2"
                            placeholder="Enter remarks" maxlength="255">

                        <!-- ATTACHMENT 1 -->
                        <label class="form-label">
                            Attachment 1
                        </label>

                        <input type="file" name="attachment1" class="form-control mb-2"
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">

                        <!-- ATTACHMENT 2 -->
                        <label class="form-label">
                            Attachment 2
                        </label>

                        <input type="file" name="attachment2" class="form-control"
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">

                        <small class="text-muted">
                            Leave this field empty if you don’t want to change the file.
                        </small>

                    </div>

                    <div class="modal-footer">

                        <button class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button name="update_batch" class="btn btn-primary">
                            Update Batch
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

    <!-- VIEW MODAL (SINGLE REUSABLE ONLY) -->
    <div class="modal fade" id="viewBatchModal" tabindex="-1">

        <div class="modal-dialog modal-xl modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-header bg-primary text-white">

                    <h5 class="modal-title" id="modalTitle">
                        Batch Details
                    </h5>

                    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>

                </div>

                <div class="modal-body">

                    <!-- BATCH INFO TABLE -->
                    <table class="table table-bordered">

                        <tr>
                            <th width="25%">Batch ID</th>
                            <td id="v_batch_id"></td>
                        </tr>

                        <tr>
                            <th>Receiver</th>
                            <td id="v_receiver"></td>
                        </tr>

                        <tr>
                            <th>Date Received</th>
                            <td id="v_date"></td>
                        </tr>

                        <tr>
                            <th>Remarks</th>
                            <td id="v_remarks"></td>
                        </tr>

                        <tr>
                            <th>Attachment 1</th>
                            <td id="v_att1"></td>
                        </tr>

                        <tr>
                            <th>Attachment 2</th>
                            <td id="v_att2"></td>
                        </tr>

                    </table>

                    <hr>

                    <h6 class="fw-bold">Equipment Under This Batch</h6>

                    <div id="equipmentTable"></div>

                </div>

            </div>

        </div>

    </div>

    <!-- ADD RECEIVER MODAL -->
    <div class="modal fade" id="addReceiverModal" tabindex="-1">

        <div class="modal-dialog">

            <div class="modal-content">

                <form method="POST">

                    <div class="modal-header">

                        <h5 class="modal-title">
                            Add Receiver
                        </h5>

                        <button class="btn-close" data-bs-dismiss="modal"></button>

                    </div>

                    <div class="modal-body">

                        <!-- NAME -->
                        <label class="form-label">
                            Name
                        </label>

                        <input type="text" name="name" class="form-control mb-2" placeholder="Enter full name"
                            minlength="3" maxlength="100" pattern="[A-Za-zÑñ.\- ]+"
                            title="Name should contain letters only." required>

                        <!-- OFFICE UNIT -->
                        <label class="form-label">
                            Office Unit
                        </label>

                        <input type="text" name="office_unit" class="form-control mb-2" placeholder="Enter office unit"
                            minlength="2" maxlength="100" required>

                        <!-- POSITION -->
                        <label class="form-label">
                            Position
                        </label>

                        <input type="text" name="position" class="form-control mb-2" placeholder="Enter position"
                            minlength="2" maxlength="100" required>

                        <!-- CONTACT -->
                        <label class="form-label">
                            Contact No
                        </label>

                        <input type="number" name="contact_no" class="form-control" placeholder="09XXXXXXXXX" min="0"
                            oninput="if(this.value.length > 11) this.value = this.value.slice(0,11)" required>

                        <small class="text-muted">
                            Enter an 11-digit mobile number.
                        </small>

                    </div>

                    <div class="modal-footer">

                        <button class="btn btn-secondary" data-bs-dismiss="modal">
                            Close
                        </button>

                        <button name="add_receiver" class="btn btn-primary">
                            Save Receiver
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

    <!-- RECEIVER TABLE MODAL -->
    <div class="modal fade" id="manageReceiversModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title">Manage Receivers</h5>
                    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <!-- SEARCH -->
                    <div class="d-flex justify-content-end mb-3">
                        <input type="text" id="receiverSearch" class="form-control w-25"
                            placeholder="Search receiver...">
                    </div>

                    <table class="table table-bordered table-hover text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Office Unit</th>
                                <th>Position</th>
                                <th>Contact No</th>
                                <th width="180">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $receiverList = $conn->query("SELECT * FROM receivers ORDER BY receiver_id DESC");

                            while ($rec = $receiverList->fetch_assoc()) {
                                ?>
                                <tr class="receiverRow">
                                    <td><?= $rec['name']; ?></td>
                                    <td><?= $rec['office_unit']; ?></td>
                                    <td><?= $rec['position']; ?></td>
                                    <td><?= $rec['contact_no']; ?></td>
                                    <td>

                                        <!-- EDIT BUTTON -->
                                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#editReceiverModal" data-id="<?= $rec['receiver_id']; ?>"
                                            data-name="<?= $rec['name']; ?>" data-office="<?= $rec['office_unit']; ?>"
                                            data-position="<?= $rec['position']; ?>"
                                            data-contact="<?= $rec['contact_no']; ?>">
                                            Edit
                                        </button>

                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>

                </div>

            </div>
        </div>
    </div>

    <!-- UPDATE MODAL RECEIVER -->
    <div class="modal fade" id="editReceiverModal" tabindex="-1">

        <div class="modal-dialog">

            <div class="modal-content">

                <form method="POST">

                    <div class="modal-header bg-warning">

                        <h5 class="modal-title">
                            Update Receiver
                        </h5>

                        <button class="btn-close" data-bs-dismiss="modal"></button>

                    </div>

                    <div class="modal-body">

                        <input type="hidden" name="receiver_id" id="edit_id">

                        <!-- NAME -->
                        <label class="form-label">
                            Name
                        </label>

                        <input type="text" name="name" id="edit_name" class="form-control mb-2" minlength="3"
                            maxlength="100" pattern="[A-Za-zÑñ.\- ]+" title="Name should contain letters only."
                            required>

                        <!-- OFFICE UNIT -->
                        <label class="form-label">
                            Office Unit
                        </label>

                        <input type="text" name="office_unit" id="edit_office" class="form-control mb-2" minlength="2"
                            maxlength="100" required>

                        <!-- POSITION -->
                        <label class="form-label">
                            Position
                        </label>

                        <input type="text" name="position" id="edit_position" class="form-control mb-2" minlength="2"
                            maxlength="100" required>

                        <!-- CONTACT -->
                        <label class="form-label">
                            Contact No
                        </label>

                        <input type="number" name="contact_no" id="edit_contact" class="form-control"
                            placeholder="09XXXXXXXXX" min="0"
                            oninput="if(this.value.length > 11) this.value = this.value.slice(0,11)" required>

                        <small class="text-muted">
                            Enter an 11-digit mobile number.
                        </small>

                    </div>

                    <div class="modal-footer">

                        <button class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button name="update_receiver" class="btn btn-primary">
                            Update
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SEARCH -->
    <script>
        document.getElementById('searchBatch')
            .addEventListener('keyup', function () {

                let value = this.value.toLowerCase();

                let rows = document.querySelectorAll(".batch-row");

                rows.forEach(function (row) {

                    let text = row.textContent.toLowerCase();

                    row.style.display =
                        text.includes(value) ?
                            "" :
                            "none";

                });

            });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if (isset($_GET['added'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Batch Added',
                text: 'Receiving batch added successfully.'
            });
        </script>
    <?php endif; ?>

    <?php if (isset($_GET['updated'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Updated',
                text: 'Batch updated successfully.'
            });
        </script>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Deleted',
                text: 'Batch deleted successfully.'
            });
        </script>
    <?php endif; ?>

    <script>
        document.querySelectorAll('.viewBtn').forEach(btn => {

            btn.addEventListener('click', function () {

                let batchId = this.dataset.id;

                document.getElementById('modalTitle').innerText = "Batch #" + batchId + " Details";

                document.getElementById('v_batch_id').innerText = batchId;
                document.getElementById('v_receiver').innerText = this.dataset.receiver;
                document.getElementById('v_date').innerText = this.dataset.date;
                document.getElementById('v_remarks').innerText = this.dataset.remarks || 'N/A';

                document.getElementById('v_att1').innerHTML =
                    this.dataset.att1 ? `<a href="../uploads/${this.dataset.att1}" target="_blank">${this.dataset.att1}</a>` : 'No file';

                document.getElementById('v_att2').innerHTML =
                    this.dataset.att2 ? `<a href="../uploads/${this.dataset.att2}" target="_blank">${this.dataset.att2}</a>` : 'No file';

                // LOAD EQUIPMENT VIA AJAX STYLE (PHP INSIDE SAME FILE)
                fetch('get_equipment.php?batch_id=' + batchId)
                    .then(res => res.text())
                    .then(data => {
                        document.getElementById('equipmentTable').innerHTML = data;
                    });

                new bootstrap.Modal(document.getElementById('viewBatchModal')).show();

            });

        });
    </script>

    <?php if (isset($_GET['receiver_added'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Receiver Added',
                text: 'Receiver added successfully.'
            });
        </script>
    <?php endif; ?>

    <?php if (isset($_GET['duplicate'])): ?>
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Duplicate Receiver',
                text: 'Receiver already exists.'
            });
        </script>
    <?php endif; ?>

    <?php if (isset($_GET['receiver_updated'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Receiver Updated',
                text: 'Receiver updated successfully.'
            });
        </script>
    <?php endif; ?>

    <!-- MANAGE RECEIVER -->
    <script>

        /* SEARCH */
        document.getElementById("receiverSearch")
            .addEventListener("keyup", function () {

                let value = this.value.toLowerCase();

                let rows = document.querySelectorAll(".receiverRow");

                rows.forEach(row => {

                    row.style.display =
                        row.innerText.toLowerCase().includes(value)
                            ? ""
                            : "none";

                });

            });


        /* PAGINATION */
        const rowsPerPage = 5;
        const rows = document.querySelectorAll(".receiverRow");
        const pagination = document.getElementById("receiverPagination");

        let currentPage = 1;

        function displayRows(page) {

            let start = (page - 1) * rowsPerPage;
            let end = start + rowsPerPage;

            rows.forEach((row, index) => {

                row.style.display =
                    (index >= start && index < end)
                        ? ""
                        : "none";

            });

        }

        function setupPagination() {

            let pageCount = Math.ceil(rows.length / rowsPerPage);

            for (let i = 1; i <= pageCount; i++) {

                let li = document.createElement("li");

                li.className = "page-item";

                li.innerHTML = `
            <a href="#" class="page-link">${i}</a>
        `;

                li.addEventListener("click", function (e) {

                    e.preventDefault();

                    currentPage = i;

                    displayRows(currentPage);

                });

                pagination.appendChild(li);

            }

        }

        displayRows(currentPage);
        setupPagination();

    </script>

    <script>
        document.getElementById('editReceiverModal').addEventListener('show.bs.modal', function (event) {
            let btn = event.relatedTarget;

            document.getElementById('edit_id').value = btn.getAttribute('data-id');
            document.getElementById('edit_name').value = btn.getAttribute('data-name');
            document.getElementById('edit_office').value = btn.getAttribute('data-office');
            document.getElementById('edit_position').value = btn.getAttribute('data-position');
            document.getElementById('edit_contact').value = btn.getAttribute('data-contact');
        });
    </script>

    <script>
        document.querySelectorAll('.editBatchBtn').forEach(btn => {

            btn.addEventListener('click', function () {

                document.getElementById('edit_batch_id').value = this.dataset.id;
                document.getElementById('edit_batch_receiver').value = this.dataset.receiver;
                document.getElementById('edit_batch_date').value = this.dataset.date;
                document.getElementById('edit_batch_remarks').value = this.dataset.remarks;

            });

        });
    </script>

    <?php if (isset($_GET['invalid_file'])): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Invalid File',
                text: 'Only PDF, DOC, DOCX, JPG, JPEG, and PNG files are allowed.'
            });
        </script>
    <?php endif; ?>
</body>

</html>