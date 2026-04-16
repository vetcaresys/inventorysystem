<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PSA Ozamiz | Online Services</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body style="background-color: var(--egov-bg-light);">

<nav class="navbar navbar-expand-lg navbar-main sticky-top py-3">
    <div class="container">
        <a class="navbar-brand fw-bold text-egov-navy">
            <span class="text-primary">gov</span>PH <span class="text-muted">| PSA Ozamiz</span>
        </a>

        <div class="d-flex align-items-center">
            <?php if(isset($_SESSION['admin'])): ?>
                <span class="me-3">Welcome, <?= $_SESSION['admin']; ?></span>
                <a href="logout.php" class="btn btn-danger rounded-pill px-4">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline-primary me-2 rounded-pill px-4">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<header class="hero-section text-center">
    <div class="container">
        <h1 class="display-4 fw-bold mb-3">Request Your Civil Registry Documents Online</h1>
        <p class="lead mb-4 opacity-75">Secure, fast, and reliable document processing.</p>
    </div>
</header>

<main class="container my-5">
    <h3 class="fw-bold mb-3">Document Services</h3>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card p-4 shadow-sm">
                <h4>Birth Certificate</h4>
                <p class="text-muted small">Official PSA copy</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-4 shadow-sm">
                <h4>Marriage Certificate</h4>
                <p class="text-muted small">Official PSA copy</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-4 shadow-sm">
                <h4>CENOMAR</h4>
                <p class="text-muted small">No marriage record</p>
            </div>
        </div>
    </div>
</main>

<footer class="py-5 bg-white border-top text-center">
    <p class="text-muted small">© 2026 PSA Ozamiz</p>
</footer>

</body>
</html>