<?php
session_start();
require '../connectiondb.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ✅ FETCH USER DATA
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$imgFile = !empty($user['profile_picture']) ? $user['profile_picture'] : null;

$imgPath = "../uploads/" . $imgFile;

if ($imgFile && file_exists($imgPath)) {
    $img = $imgFile . "?t=" . time();
} else {
    $img = "default.png";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <title>User Profile</title>

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
            position: fixed;
            left: 0;
            top: 0;
            background: #123a5a;
        }

        #sidebar .nav-link {
            color: #dbe7ff;
            padding: 14px 25px;
        }

        #sidebar .nav-link:hover,
        #sidebar .active {
            background: #1f6fb8;
            color: #fff;
        }

        #main {
            margin-left: 260px;
            padding: 30px;
        }

        .card-box {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .05);
            padding: 25px;
        }

        .profile-img {
            width: 130px;
            height: 130px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #1f6fb8;
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <div id="sidebar">

        <div class="p-4 text-center border-bottom text-white">
            <h5 class="fw-bold mb-0">
                PSA INVENTORY ADMIN
            </h5>

            <small>
                User Profile Module
            </small>

        </div>


        <nav class="nav flex-column mt-4">

            <a href="userprofile.php" class="nav-link active">
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

            <a href="receiving_batches.php" class="nav-link">
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

        <h3 class="fw-bold mb-3">My Profile</h3>

        <?php if (isset($_GET['success'])): ?>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Profile updated successfully!',
                    confirmButtonColor: '#1f6fb8'
                });
            </script>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Something went wrong!',
                    confirmButtonColor: '#dc3545'
                });
            </script>
        <?php endif; ?>

        <div class="row g-4">

            <!-- LEFT CARD -->
            <div class="col-md-4">
                <div class="card-box text-center">

                    <?php
                    $img = !empty($user['profile_picture'])
                        ? $user['profile_picture'] . "?t=" . time()
                        : "default.png";
                    ?>

                    <img src="../uploads/<?= $img ?>" class="profile-img mb-3">

                    <h5 class="fw-bold"><?= $user['fullname'] ?? 'No name'; ?></h5>
                    <p class="text-muted"><?= $user['role'] ?? '-'; ?></p>

                    <hr>

                    <p><i class="bi bi-envelope"></i> <?= $user['email'] ?? 'No email'; ?></p>
                    <p><i class="bi bi-telephone"></i> <?= $user['contact_no'] ?? 'No contact'; ?></p>

                    <button class="btn btn-primary w-100 mt-3" data-bs-toggle="modal" data-bs-target="#editModal">
                        Edit Profile
                    </button>

                </div>
            </div>

            <!-- RIGHT DETAILS -->
            <div class="col-md-8">
                <div class="card-box">

                    <h5 class="fw-bold mb-3">Account Details</h5>

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label>Full Name</label>
                            <input class="form-control" value="<?= $user['fullname']; ?>" disabled>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Username</label>
                            <input class="form-control" value="<?= $user['username']; ?>" disabled>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Position</label>
                            <input class="form-control" value="<?= $user['position'] ?? '-'; ?>" disabled>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Office</label>
                            <input class="form-control" value="<?= $user['office_unit'] ?? '-'; ?>" disabled>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Status</label>
                            <input class="form-control" value="<?= $user['status']; ?>" disabled>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Last Login</label>
                            <input class="form-control"
                                value="<?= $user['last_login'] ? date('F d, Y h:i A', strtotime($user['last_login'])) : 'No login record yet'; ?>"
                                disabled>
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- EDIT MODAL -->
    <div class="modal fade" id="editModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <form action="update_user.php" method="POST" enctype="multipart/form-data">

                    <div class="modal-header">
                        <h5>Edit Profile</h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <input type="text" name="fullname" class="form-control mb-2" value="<?= $user['fullname']; ?>"
                            required>

                        <input type="email" name="email" class="form-control mb-2" value="<?= $user['email']; ?>">

                        <input type="text" name="contact_no" class="form-control mb-2"
                            value="<?= $user['contact_no']; ?>">

                        <input type="text" name="position" class="form-control mb-2" value="<?= $user['position']; ?>">

                        <input type="text" name="office_unit" class="form-control mb-2"
                            value="<?= $user['office_unit']; ?>">

                        <input type="file" name="profile_picture" class="form-control" accept=".jpg,.jpeg,.png">

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-primary w-100">
                            Save Changes
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <?php if (isset($_GET['error']) && $_GET['error'] == 'invalid_file'): ?>
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Invalid File!',
                text: 'Only JPG, JPEG, and PNG files are allowed.'
            });
        </script>
    <?php endif; ?>

    <?php if (isset($_GET['login']) && $_GET['login'] == 'success'): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Login Successful!',
                text: 'Welcome back, <?= $_SESSION['fullname'] ?? '' ?>',
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    <?php endif; ?>
</body>

</html>