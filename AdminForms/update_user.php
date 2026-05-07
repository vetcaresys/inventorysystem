<?php
session_start();
require '../connectiondb.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* =========================
FETCH CURRENT USER
========================= */
$stmt = $conn->prepare("SELECT profile_picture FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$current = $stmt->get_result()->fetch_assoc();

$currentImage = $current['profile_picture'];

/* =========================
GET FORM DATA
========================= */
$fullname   = trim($_POST['fullname']);
$email      = trim($_POST['email']);
$contact_no = trim($_POST['contact_no']);

/* =========================
VALIDATION
========================= */
if (empty($fullname)) {
    header("Location: forms_userprofile.php?error=empty");
    exit;
}

/* =========================
IMAGE UPLOAD
========================= */
$uploadDir = "../uploads/";
$newFileName = $currentImage; // default = old image

if (!empty($_FILES['profile_picture']['name'])) {

    $file = $_FILES['profile_picture'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    $allowed = ['jpg', 'jpeg', 'png'];

    if (!in_array($ext, $allowed)) {
        header("Location: forms_userprofile.php?error=invalid_file");
        exit;
    }

    // generate unique filename
    $newFileName = "user_" . $user_id . "_" . time() . "." . $ext;
    $targetPath = $uploadDir . $newFileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {

        // delete old image (if not default)
        if (!empty($currentImage) && file_exists($uploadDir . $currentImage)) {
            unlink($uploadDir . $currentImage);
        }

    } else {
        header("Location: forms_userprofile.php?error=upload_failed");
        exit;
    }
}

/* =========================
UPDATE USER
========================= */
$stmt = $conn->prepare("
UPDATE users 
SET fullname=?, email=?, contact_no=?, profile_picture=? 
WHERE user_id=?
");

$stmt->bind_param("ssssi", $fullname, $email, $contact_no, $newFileName, $user_id);

if ($stmt->execute()) {

    // update session name (important!)
    $_SESSION['fullname'] = $fullname;

    header("Location: forms_userprofile.php?success=1");
    exit;

} else {
    header("Location: forms_userprofile.php?error=db");
    exit;
}
?>