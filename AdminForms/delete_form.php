<?php
session_start();
require '../connectiondb.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'forms_admin') {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id'])) {

    $id = intval($_GET['id']);

    $stmt = $conn->prepare("DELETE FROM forms WHERE form_id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: forms_inventory.php?deleted=1");
        exit;
    } else {
        header("Location: forms_inventory.php?error=failed");
        exit;
    }
}
?>