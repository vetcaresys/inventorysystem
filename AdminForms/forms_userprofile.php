<?php
session_start();
require '../connectiondb.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'forms_admin') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* FETCH USER */
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

/* IMAGE */
$img = (!empty($user['profile_picture']) && file_exists("../uploads/" . $user['profile_picture']))
    ? $user['profile_picture'] . "?t=" . time()
    : "default.png";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Forms Admin Profile</title>

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
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .06);
            padding: 25px;
        }

        .profile-img {
            width: 130px;
            height: 130px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #1f4fb8;
        }

        .alert {
            border-radius: 12px;
        }

        .btn {
            border-radius: 12px;
            font-weight: 500;
        }
    </style>
</head>

<body>

    <?php if (isset($_GET['restore'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Restored!',
                text: 'Backup restored successfully.'
            });
        </script>
    <?php endif; ?>


    <!-- SIDEBAR -->
    <div id="sidebar">

        <div class="p-4 text-center border-bottom text-white">
            <h5 class="fw-bold mb-0">
                PSA FORMS ADMIN
            </h5>
            <small>
                User Profile Module
            </small>
        </div>

        <nav class="nav flex-column mt-4">

            <a href="forms_userprofile.php" class="nav-link active">
                <i class="bi bi-person-circle"></i> My Profile
            </a>

            <a href="forms_dashboard.php" class="nav-link">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <a href="forms_inventory.php" class="nav-link">
                <i class="bi bi-file-earmark-text"></i> Forms Inventory
            </a>

            <a href="restock_forms.php" class="nav-link">
                <i class="bi bi-box-seam"></i> Restock
            </a>

            <a href="sales.php" class="nav-link">
                <i class="bi bi-cart-check"></i> Sell Forms
            </a>

            <a href="forms_reports.php" class="nav-link">
                <i class="bi bi-bar-chart-line"></i> Reports
            </a>

            <hr>

            <a href="#" class="nav-link text-warning" onclick="confirmLogout(event)">
                <i class="bi bi-box-arrow-left"></i> Logout
            </a>

        </nav>
    </div>

    <!-- MAIN -->
    <div id="main">

        <h4 class="fw-bold mb-4">My Profile</h4>

        <div class="row g-4">

            <!-- LEFT -->
            <div class="col-md-4">
                <div class="card-box text-center">

                    <img src="../uploads/<?= $img ?>" class="profile-img mb-3">

                    <h5 class="fw-bold"><?= $user['fullname']; ?></h5>
                    <p class="text-muted"><?= $user['role']; ?></p>

                    <hr>

                    <p><i class="bi bi-envelope"></i> <?= $user['email']; ?></p>
                    <p><i class="bi bi-telephone"></i> <?= $user['contact_no']; ?></p>

                    <button class="btn btn-primary w-100 mt-3"
                        data-bs-toggle="modal"
                        data-bs-target="#editModal">
                        Edit Profile
                    </button>

                </div>
            </div>

            <!-- RIGHT -->
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
                            <label>Status</label>
                            <input class="form-control" value="<?= $user['status']; ?>" disabled>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Last Login</label>
                            <input class="form-control"
                                value="<?= $user['last_login'] ? date('F d, Y h:i A', strtotime($user['last_login'])) : 'No record'; ?>"
                                disabled>
                        </div>

                    </div>

                </div>
            </div>

            <!-- BACKUP SECTION (FIXED DESIGN) -->
            <div class="col-md-12 mt-4">
                <div class="card-box">

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h5 class="fw-bold mb-0">Backup & Restore</h5>
                            <small class="text-muted">System data management tools</small>
                        </div>

                        <i class="bi bi-database fs-2 text-primary"></i>
                    </div>

                    <div class="alert alert-warning py-2 mb-3">
                        ⚠ Restoring will overwrite all system data
                    </div>

                    <div class="row g-2">

                        <div class="col-md-6">
                            <a href="backup_all.php" class="btn btn-success w-100">
                                <i class="bi bi-download"></i> Backup System
                            </a>
                        </div>

                        <div class="col-md-6">
                            <label class="btn btn-primary w-100 mb-0">
                                <i class="bi bi-upload"></i> Restore Backup
                                <form action="restore_backup.php" method="POST" enctype="multipart/form-data"
                                    style="display:none;" onchange="this.submit();">
                                    <input type="file" name="file" required>
                                </form>
                            </label>
                        </div>

                    </div>

                </div>
            </div>
        </div>

    </div>

    <!-- EDIT MODAL AND UPDATE-->
    <div class="modal fade" id="editModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <form action="update_user.php" method="POST" enctype="multipart/form-data">

                    <div class="modal-header">
                        <h5 class="fw-bold">Edit Profile</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <!-- FULL NAME -->
                        <label class="form-label">Full Name</label>
                        <input type="text"
                            name="fullname"
                            class="form-control mb-3"
                            value="<?= $user['fullname']; ?>"
                            placeholder="Enter your full name"
                            required>

                        <!-- EMAIL -->
                        <label class="form-label">Email Address</label>
                        <input type="email"
                            name="email"
                            class="form-control mb-3"
                            value="<?= $user['email']; ?>"
                            placeholder="example@gmail.com"
                            required>

                        <!-- CONTACT NUMBER -->
                        <label class="form-label">Contact Number</label>
                        <input type="text"
                            name="contact_no"
                            class="form-control mb-3"
                            value="<?= $user['contact_no']; ?>"
                            placeholder="09xxxxxxxxx"
                            pattern="^[0-9]{10,15}$"
                            title="Numbers only (10-15 digits)"
                            required>

                        <!-- PROFILE PICTURE -->
                        <label class="form-label">Profile Picture</label>
                        <input type="file"
                            name="profile_picture"
                            class="form-control"
                            accept="image/png, image/jpeg, image/jpg">

                        <small class="text-muted">
                            Allowed formats: JPG, JPEG, PNG
                        </small>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary w-100">
                            Save Changes
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <?php if (isset($_GET['success'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text: 'Profile updated successfully.',
                confirmButtonColor: '#1f4fb8'
            });
        </script>
    <?php endif; ?>

    <?php if (isset($_GET['error']) && $_GET['error'] == 'invalid_file'): ?>
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Invalid file!',
                text: 'Only JPG, JPEG, PNG allowed.'
            });
        </script>
    <?php endif; ?>

    <script>
        function confirmLogout(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Do you want to logout?',
                text: "You will need to login again to access the system.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, logout',
                cancelButtonText: 'Stay logged in'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "../logout.php";
                }
            });
        }
    </script>

    <?php if (isset($_SESSION['login_success'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Welcome back!',
                text: 'You have successfully logged in.',
                confirmButtonColor: '#1f4fb8',
                timer: 2000,
                showConfirmButton: false
            });
        </script>
        <?php unset($_SESSION['login_success']); ?>
    <?php endif; ?>
</body>

</html>