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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width,initial-scale=1">

    <title>Backup and Restore</title>

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

        .box {
            background: white;
            border: none;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, .05);
            height: 100%;
        }

        .warning-box {
            background: #fff3cd;
            border-left: 5px solid #ffc107;
            padding: 15px;
            border-radius: 10px;
        }

        .box i {
            font-size: 55px;
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
            <small>Equipment & Borrowing Module</small>
        </div>

        <nav class="nav flex-column mt-4">

            <a href="inventory_dashboard.php" class="nav-link">
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </a>

            <a href="employees.php" class="nav-link">
                <i class="bi bi-people"></i>
                Employees
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

            <a href="backup_restore.php" class="nav-link active">
                <i class="bi bi-database-fill-gear"></i>
                Backup & Restore
            </a>

            <hr>

            <a href="../logout.php"
                class="nav-link text-warning">
                <i class="bi bi-box-arrow-left"></i>
                Logout
            </a>

        </nav>
    </div>



    <!-- MAIN -->
    <div id="main">

        <nav class="navbar bg-white rounded-4 shadow-sm px-4 mb-4">
            <h4 class="mb-0">
                Backup & Restore Center
            </h4>
            <span>
                <?= $_SESSION['fullname']; ?>
            </span>
        </nav>


        <div class="warning-box mb-4">
            <strong>Important:</strong>
            Always create backup before restoring or importing files.
        </div>



        <div class="row g-4">

            <!-- SQL EXPORT -->
            <div class="col-md-6">
                <div class="box text-center">

                    <i class="bi bi-database-down"></i>

                    <h4 class="fw-bold mt-3">
                        Full SQL Backup
                    </h4>

                    <p class="text-muted">
                        Export complete database backup
                        (items, employees, borrow, returns).
                    </p>

                    <a href="export_backup.php"
                        class="btn btn-success btn-lg">
                        Export SQL Backup
                    </a>

                </div>
            </div>



            <!-- SQL RESTORE -->
            <div class="col-md-6">
                <div class="box">

                    <h4 class="fw-bold mb-3">
                        Restore SQL Backup
                    </h4>

                    <p class="text-muted">
                        Import SQL backup file to restore database.
                    </p>

                    <form
                        action="import_backup.php"
                        method="POST"
                        enctype="multipart/form-data">

                        <input
                            type="file"
                            name="sql_file"
                            accept=".sql"
                            class="form-control mb-4"
                            required>

                        <button
                            type="submit"
                            name="import"
                            class="btn btn-primary btn-lg w-100"
                            onclick="return confirm(
'Import may overwrite data. Continue?'
);">

                            Restore Backup

                        </button>

                    </form>

                </div>
            </div>



            <!-- EXCEL EXPORT -->
            <div class="col-md-6">
                <div class="box text-center">

                    <i class="bi bi-file-earmark-excel"></i>

                    <h4 class="fw-bold mt-3">
                        Excel Data Backup
                    </h4>

                    <p class="text-muted">
                        Export inventory, employees,
                        borrow and returns to Excel.
                    </p>

                    <a href="export_excel_backup.php"
                        class="btn btn-success btn-lg">
                        Export Excel Backup
                    </a>

                </div>
            </div>



            <!-- EXCEL IMPORT -->
            <div class="col-md-6">
                <div class="box text-center">

                    <i class="bi bi-upload"></i>

                    <h4 class="fw-bold mt-3">
                        Import Excel Backup
                    </h4>

                    <p class="text-muted">
                        Restore inventory records
                        using Excel backup file.
                    </p>

                    <a href="import_inventory_excel.php"
                        class="btn btn-warning btn-lg">
                        Import Excel Backup
                    </a>

                </div>
            </div>

        </div>



        <footer class="text-center mt-5 py-3 border-top text-muted">
            <small>
                © <?= date('Y'); ?> PSA Inventory Management System.
                All Rights Reserved.
            </small>
        </footer>

    </div>

</body>

</html>