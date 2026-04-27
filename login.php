<?php
session_start();
require 'connectiondb.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("
        SELECT * FROM users
        WHERE username = ?
        LIMIT 1
    ");

    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $user = $result->fetch_assoc();

        // plain text check (change to password_verify later if hashed)
        if ($password == $user['password']) {

            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['fullname']  = $user['fullname'];

            /* ROLE REDIRECT */
            if ($user['role'] == "forms_admin") {
                header("Location: AdminForms/forms_dashboard.php");
                exit;
            }

            if ($user['role'] == "inventory_admin") {
                header("Location: AdminInventory/inventory_dashboard.php");
                exit;
            }
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
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>PSA Ozamiz | Admin Portal</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        :root {
            --egov-navy: #0d2c6c;
            --egov-blue: #0d6efd;
            --egov-cyan: #27c5ff;
        }

        body {
            background: #eef3fb;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
        }

        .login-container {
            max-width: 1100px;
            width: 100%;
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0, 0, 0, .08);
        }

        .border-top-gov {
            border-top: 8px solid #ffc107;
        }

        .egov-banner {
            background: linear-gradient(135deg, #0d2c6c, #1549b6);
            color: #fff;
            padding: 70px;
            display: flex;
            align-items: center;
        }

        .form-section {
            padding: 60px;
        }

        .btn-egov {
            background: var(--egov-blue);
            color: white;
            border: none;
        }

        .btn-egov:hover {
            background: #084298;
            color: white;
        }

        .form-control {
            border-radius: 12px;
            height: 58px;
        }

        .form-floating label {
            padding-left: 18px;
        }

        @media(max-width:768px) {
            .form-section {
                padding: 35px;
            }
        }
    </style>
</head>

<body>

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
                        <span style="color:var(--egov-cyan);">
                            Admin Portal
                        </span>
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

                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-warning fs-4 me-3"></i>
                            <span>Role-based admin access</span>
                        </div>

                    </div>
                </div>
            </div>


            <!-- RIGHT PANEL -->
            <div class="col-md-6 form-section">

                <div class="text-center mb-4">

                    <div class="d-inline-flex p-3 rounded-circle mb-3"
                        style="background:rgba(13,110,253,.1);">
                        <i class="bi bi-fingerprint fs-1"
                            style="color:var(--egov-navy);"></i>
                    </div>

                    <h4 class="fw-bold text-dark">
                        Welcome Back
                    </h4>

                    <p class="text-muted small">
                        Log in with your admin credentials
                    </p>

                </div>


                <?php if ($error): ?>
                    <div class="alert alert-danger text-center">
                        <?= $error ?>
                    </div>
                <?php endif; ?>


                <form method="POST">

                    <div class="form-floating mb-3">
                        <input
                            type="text"
                            name="username"
                            class="form-control"
                            placeholder="Username"
                            required>

                        <label>
                            <i class="bi bi-person me-2"></i>
                            Username
                        </label>
                    </div>


                    <div class="form-floating mb-3">
                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            placeholder="Password"
                            required>

                        <label>
                            <i class="bi bi-lock me-2"></i>
                            Password
                        </label>
                    </div>


                    <div class="d-flex justify-content-between align-items-center mb-4">

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox">
                            <label class="form-check-label small text-muted">
                                Remember computer
                            </label>
                        </div>

                        <a href="#" class="small text-decoration-none">
                            Reset Key?
                        </a>

                    </div>


                    <button
                        type="submit"
                        class="btn btn-egov w-100 py-3 fw-bold rounded-3 shadow-sm">

                        Sign In
                        <i class="bi bi-arrow-right-short fs-5"></i>

                    </button>

                </form>


                <div class="text-center mt-4">
                    <hr>

                    <small class="text-muted">
                        Need help?
                        <a href="#" class="text-decoration-none">
                            Contact IT Support
                        </a>
                    </small>

                    <br>

                    <small class="text-muted">
                        <a href="index.php" class="text-decoration-none">
                            Back to Home
                        </a>
                    </small>

                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>