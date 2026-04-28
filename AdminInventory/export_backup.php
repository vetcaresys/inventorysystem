<?php
session_start();

if(
 !isset($_SESSION['role']) ||
 $_SESSION['role']!='inventory_admin'
){
 header("Location: ../login.php");
 exit;
}

$dbname="psa_inventory_management_system";
$user="root";
$password="";

/* XAMPP path */
$dump="C:\\xampp\\mysql\\bin\\mysqldump.exe";

if(!file_exists($dump)){
die("mysqldump not found.");
}

$filename=
"backup_".date("Ymd_His").".sql";

/* force direct download */
header("Content-Type: application/sql");
header(
"Content-Disposition: attachment; filename=\"$filename\""
);

/* IMPORTANT:
2>&1 lets you see errors */
$command =
"\"$dump\" -u$user $dbname 2>&1";

passthru($command,$result);

if($result!=0){
echo "\nBackup failed.";
}

exit;
?>