<?php
session_start();
require 'connectiondb.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // ✅ plain text password check
        if ($password === $user['password']) {

            // ✅ FIXED SESSION
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin'] = $user['username'];

            header("Location: Admin/admin_dashboard.php");
            exit;

        } else {
            $error = "Invalid password!";
        }

    } else {
        $error = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PSA Ozamiz | Admin Portal</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="login-page-body">

<div class="login-container border-top-gov">
    <div class="row g-0">

        <!-- LEFT PANEL -->
        <div class="col-md-6 d-none d-md-flex egov-banner">
            <div>
                <span class="badge bg-warning text-dark mb-3 px-3 py-2 rounded-pill">
                    Republic of the Philippines
                </span>

                <h1 class="display-6 fw-bold mb-3">
                    PSA Ozamiz <br>
                    <span style="color: var(--egov-cyan);">Admin Portal</span>
                </h1>

                <p class="lead text-white-50">
                    Secure access to inventory and civil registry systems.
                </p>

                <div class="mt-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-check-circle-fill text-warning fs-4 me-3"></i>
                        <span>Secure Government Gateway</span>
                    </div>

                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-check-circle-fill text-warning fs-4 me-3"></i>
                        <span>Audit-ready transaction tracking</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL -->
        <div class="col-md-6 form-section">

            <div class="text-center mb-4">
                <div class="d-inline-flex p-3 rounded-circle mb-3" style="background: rgba(13, 110, 253, 0.1);">
                    <i class="bi bi-fingerprint fs-1" style="color: var(--egov-navy);"></i>
                </div>

                <h4 class="fw-bold text-dark">Welcome Back</h4>
                <p class="text-muted small">Log in with your admin credentials</p>
            </div>

            <!-- ERROR MESSAGE -->
            <?php if($error): ?>
                <div class="alert alert-danger text-center"><?= $error ?></div>
            <?php endif; ?>

            <!-- FORM -->
            <form method="POST" action="">

                <div class="form-floating mb-3">
                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                    <label><i class="bi bi-person me-2"></i>Username</label>
                </div>

                <div class="form-floating mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                    <label><i class="bi bi-lock me-2"></i>Password</label>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox">
                        <label class="form-check-label small text-muted">
                            Remember computer
                        </label>
                    </div>

                    <a href="#" class="small text-decoration-none" style="color: var(--egov-blue);">
                        Reset Key?
                    </a>
                </div>

                <button type="submit" class="btn btn-egov w-100 py-3 fw-bold rounded-3 shadow-sm">
                    Sign In <i class="bi bi-arrow-right-short fs-5"></i>
                </button>

            </form>

            <div class="text-center mt-4">
                <hr>
                <small class="text-muted">
                    Need help? 
                    <a href="#" class="text-decoration-none">Contact IT Support</a>
                </small>
                <br>
                <small class="text-muted">
                    <a href="index.php" class="text-decoration-none">Back to Home</a>
                </small>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>