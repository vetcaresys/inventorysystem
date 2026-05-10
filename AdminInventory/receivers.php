<?php
session_start();
require '../connectiondb.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'inventory_admin') {
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

    // duplicate check
    $check = $conn->prepare("SELECT receiver_id FROM receivers WHERE name=? AND contact_no=?");
    $check->bind_param("ss", $name, $contact_no);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        header("Location: receivers.php?duplicate=1");
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO receivers (name, office_unit, position, contact_no)
        VALUES (?,?,?,?)
    ");

    $stmt->bind_param("ssss", $name, $office_unit, $position, $contact_no);
    $stmt->execute();

    header("Location: receivers.php?added=1");
    exit;
}

/* =========================
UPDATE RECEIVER
========================= */
if (isset($_POST['update_receiver'])) {

    $id = $_POST['receiver_id'];
    $name = $_POST['name'];
    $office_unit = $_POST['office_unit'];
    $position = $_POST['position'];
    $contact_no = $_POST['contact_no'];

    $check = $conn->prepare("
        SELECT receiver_id FROM receivers 
        WHERE name=? AND contact_no=? AND receiver_id != ?
    ");
    $check->bind_param("ssi", $name, $contact_no, $id);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        header("Location: receivers.php?duplicate=1");
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

    $stmt->bind_param("ssssi", $name, $office_unit, $position, $contact_no, $id);
    $stmt->execute();

    header("Location: receivers.php?updated=1");
    exit;
}

/* =========================
DELETE RECEIVER
========================= */
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM receivers WHERE receiver_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: receivers.php?deleted=1");
    exit;
}

/* =========================
FETCH RECEIVERS
========================= */
$receivers = $conn->query("SELECT * FROM receivers ORDER BY receiver_id DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Receivers</title>

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
            <h5 class="fw-bold mb-0">PSA INVENTORY ADMIN</h5>
            <small>Equipment Module</small>
        </div>

        <nav class="nav flex-column mt-4">

            <a href="inventory_dashboard.php" class="nav-link">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <a href="receivers.php" class="nav-link active">
                <i class="bi bi-person-badge"></i> Receivers
            </a>

            <a href="inventory_items.php" class="nav-link">
                <i class="bi bi-box-seam"></i> Inventory Items
            </a>

            <a href="../logout.php" class="nav-link text-warning">
                <i class="bi bi-box-arrow-left"></i> Logout
            </a>

        </nav>
    </div>

    <!-- MAIN -->
    <div id="main">

        <h3 class="mb-4 fw-bold">Receivers</h3>

        <!-- ADD -->
        <div class="card card-custom p-4 mb-4">

            <h5>Add Receiver</h5>

            <form method="POST">
                <div class="row g-3">

                    <div class="col-md-3">
                        <input name="name" class="form-control" placeholder="Name" required>
                    </div>

                    <div class="col-md-3">
                        <input name="office_unit" class="form-control" placeholder="Office Unit" required>
                    </div>

                    <div class="col-md-3">
                        <input name="position" class="form-control" placeholder="Position" required>
                    </div>

                    <div class="col-md-3">
                        <input name="contact_no" class="form-control" placeholder="Contact No" required>
                    </div>

                </div>

                <button name="add_receiver" class="btn btn-primary mt-3">
                    Add Receiver
                </button>

            </form>

        </div>

        <!-- TABLE -->
        <div class="card card-custom p-3">

            <div class="d-flex justify-content-between mb-3">

                <h5>Receiver List</h5>

                <input type="text" id="search" class="form-control w-25" placeholder="Search...">

            </div>

            <table class="table table-hover text-center">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Office Unit</th>
                        <th>Position</th>
                        <th>Contact</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody id="tableBody">

                    <?php while ($r = $receivers->fetch_assoc()) { ?>

                        <tr>
                            <td><?= $r['name']; ?></td>
                            <td><?= $r['office_unit']; ?></td>
                            <td><?= $r['position']; ?></td>
                            <td><?= $r['contact_no']; ?></td>

                            <td>
                                <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#view<?= $r['receiver_id']; ?>">
                                    View
                                </button>

                                <!-- EDIT -->
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#edit<?= $r['receiver_id']; ?>">
                                    Edit
                                </button>

                                <!-- DELETE -->
                                <a href="?delete=<?= $r['receiver_id']; ?>" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Delete this receiver?')">
                                    Delete
                                </a>

                            </td>
                        </tr>
                        <!-- VIEW MODAL -->
                        <div class="modal fade" id="view<?= $r['receiver_id']; ?>" tabindex="-1">

                            <div class="modal-dialog">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            Receiver Details
                                        </h5>

                                        <button class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">

                                        <div class="mb-3">
                                            <label class="fw-bold">Name</label>
                                            <div class="form-control">
                                                <?= $r['name']; ?>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="fw-bold">Office Unit</label>
                                            <div class="form-control">
                                                <?= $r['office_unit']; ?>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="fw-bold">Position</label>
                                            <div class="form-control">
                                                <?= $r['position']; ?>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="fw-bold">Contact No</label>
                                            <div class="form-control">
                                                <?= $r['contact_no']; ?>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="modal-footer">
                                        <button class="btn btn-secondary" data-bs-dismiss="modal">
                                            Close
                                        </button>
                                    </div>

                                </div>
                            </div>

                        </div>

                        <!-- EDIT MODAL -->
                        <div class="modal fade" id="edit<?= $r['receiver_id']; ?>">

                            <div class="modal-dialog">
                                <div class="modal-content">

                                    <form method="POST">

                                        <div class="modal-header">
                                            <h5>Edit Receiver</h5>
                                            <button class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">

                                            <input type="hidden" name="receiver_id" value="<?= $r['receiver_id']; ?>">

                                            <input name="name" class="form-control mb-2" value="<?= $r['name']; ?>">
                                            <input name="office_unit" class="form-control mb-2"
                                                value="<?= $r['office_unit']; ?>">
                                            <input name="position" class="form-control mb-2" value="<?= $r['position']; ?>">
                                            <input name="contact_no" class="form-control mb-2"
                                                value="<?= $r['contact_no']; ?>">

                                        </div>

                                        <div class="modal-footer">
                                            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button name="update_receiver" class="btn btn-primary">Update</button>
                                        </div>

                                    </form>

                                </div>
                            </div>
                        </div>

                    <?php } ?>

                </tbody>
            </table>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.getElementById("search").addEventListener("keyup", function () {
            let value = this.value.toLowerCase();
            let rows = document.querySelectorAll("#tableBody tr");

            rows.forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(value) ? "" : "none";
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if (isset($_GET['added'])): ?>
        <script>
            Swal.fire("Success", "Receiver added successfully", "success");
        </script>
    <?php endif; ?>

    <?php if (isset($_GET['updated'])): ?>
        <script>
            Swal.fire("Updated", "Receiver updated successfully", "success");
        </script>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <script>
            Swal.fire("Deleted", "Receiver removed", "success");
        </script>
    <?php endif; ?>

    <?php if (isset($_GET['duplicate'])): ?>
        <script>
            Swal.fire("Warning", "Duplicate receiver found", "warning");
        </script>
    <?php endif; ?>

</body>

</html>