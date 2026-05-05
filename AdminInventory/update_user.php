<?php
session_start();
require '../connectiondb.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* =========================
   GET FORM DATA (SAFE)
========================= */
$fullname = $_POST['fullname'] ?? '';
$email    = $_POST['email'] ?? '';
$contact  = $_POST['contact_no'] ?? '';
$position = $_POST['position'] ?? '';
$office   = $_POST['office_unit'] ?? '';

/* =========================
   FIXED UPLOAD PATH (IMPORTANT)
========================= */
// base path mo balik sa root (inventorypsa folder)
$uploadDir = dirname(__DIR__) . "/uploads/";

// create folder if wala
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

/* =========================
   FILE UPLOAD
========================= */
$newName = null;

if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {

    $picName = $_FILES['profile_picture']['name'];
    $tmpFile = $_FILES['profile_picture']['tmp_name'];

    $allowed = ['jpg', 'jpeg', 'png'];

    $ext = strtolower(pathinfo($picName, PATHINFO_EXTENSION));

    // extra security: check real MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmpFile);
    finfo_close($finfo);

    $validMime = [
        'image/jpeg',
        'image/png'
    ];

    if (!in_array($ext, $allowed) || !in_array($mime, $validMime)) {
        header("Location: userprofile.php?error=invalid_file");
        exit;
    }

    $newName = time() . "_" . preg_replace("/[^a-zA-Z0-9.\-_]/", "", $picName);

    $target = $uploadDir . $newName;

    if (!move_uploaded_file($tmpFile, $target)) {
        die("Upload failed. Check uploads folder permission.");
    }
}

/* =========================
   SQL UPDATE
========================= */
if ($newName) {

    $stmt = $conn->prepare("
        UPDATE users SET
            fullname=?,
            email=?,
            contact_no=?,
            position=?,
            office_unit=?,
            profile_picture=?,
            updated_at = NOW()
        WHERE user_id=?
    ");

    $stmt->bind_param(
        "ssssssi",
        $fullname,
        $email,
        $contact,
        $position,
        $office,
        $newName,
        $user_id
    );
} else {

    $stmt = $conn->prepare("
        UPDATE users SET
            fullname=?,
            email=?,
            contact_no=?,
            position=?,
            office_unit=?,
            updated_at = NOW()
        WHERE user_id=?
    ");

    $stmt->bind_param(
        "sssssi",
        $fullname,
        $email,
        $contact,
        $position,
        $office,
        $user_id
    );
}

/* =========================
   EXECUTE (WITH REAL ERROR)
========================= */
if ($stmt->execute()) {

    $_SESSION['fullname'] = $fullname;
    header("Location: userprofile.php?success=1");
    exit;
} else {

    die("SQL ERROR: " . $stmt->error);
}
