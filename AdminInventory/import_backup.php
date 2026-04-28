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

if (isset($_POST['import'])) {

    if (
        !isset($_FILES['sql_file']) ||
        $_FILES['sql_file']['error'] != 0
    ) {
        die("No backup file selected.");
    }

    $file =
        $_FILES['sql_file']['tmp_name'];

    $sql =
        file_get_contents($file);

    if (!$sql) {
        die("Invalid SQL backup file.");
    }

    /* run imported sql */
    if ($conn->multi_query($sql)) {

        do {
        } while (
            $conn->more_results()
            && $conn->next_result()
        );

        echo "
<script>
alert('Backup restored successfully.');
window.location='backup_restore.php';
</script>
";
    } else {

        echo "
<script>
alert('Import failed.');
window.location='backup_restore.php';
</script>
";
    }
} else {

    header("Location: backup_restore.php");
    exit;
}
