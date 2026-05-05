<?php
session_start();
require '../connectiondb.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'forms_admin') {
    header("Location: ../login.php");
    exit;
}

if (isset($_POST['update_form'])) {

    $id    = $_POST['form_id'];
    $name  = trim($_POST['form_name']);
    $price = $_POST['unit_price'];

    // VALIDATION
    if (empty($id) || empty($name) || empty($price)) {
        header("Location: forms_inventory.php?error=empty_update");
        exit;
    }

    if ($price < 0) {
        header("Location: forms_inventory.php?error=invalid_price");
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE forms 
        SET form_name = ?, unit_price = ?
        WHERE form_id = ?
    ");

    $stmt->bind_param("sdi", $name, $price, $id);

    if ($stmt->execute()) {
        header("Location: forms_inventory.php?updated=1");
        exit;
    } else {
        header("Location: forms_inventory.php?error=update_failed");
        exit;
    }
}
?>