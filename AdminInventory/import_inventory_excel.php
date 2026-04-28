<?php
session_start();
require '../connectiondb.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if(
 !isset($_SESSION['role']) ||
 $_SESSION['role']!='inventory_admin'
){
header("Location: ../login.php");
exit;
}


if(isset($_POST['import'])){

$file =
$_FILES['excel_file']['tmp_name'];

$spreadsheet =
IOFactory::load($file);

/*
Sheet 1 = Inventory
*/
$sheet=
$spreadsheet->getSheet(0);

$data=
$sheet->toArray();

$count=0;

/* skip header row */
for($i=1;$i<count($data);$i++){

if(empty($data[$i][3])){
continue;
}

/*
Columns:
0 item_id
1 property_no
2 inventory_tag_no
3 description
4 category
5 serial_no
6 quantity
7 condition
8 location
*/

/* optional duplicate check */
$desc=
$conn->real_escape_string($data[$i][3]);

$check=
$conn->query("
SELECT item_id
FROM equipment_inventory
WHERE description='$desc'
");

if($check->num_rows>0){
continue;
}

$sql="
INSERT INTO equipment_inventory(
property_no,
inventory_tag_no,
description,
category,
serial_no,
quantity,
item_condition,
location
)
VALUES(
'".$data[$i][1]."',
'".$data[$i][2]."',
'".$data[$i][3]."',
'".$data[$i][4]."',
'".$data[$i][5]."',
'".$data[$i][6]."',
'".$data[$i][7]."',
'".$data[$i][8]."'
)
";

$conn->query($sql);

$count++;

}

echo "
<script>
alert('$count inventory records imported successfully');
window.location='inventory_items.php';
</script>
";
exit;

}
?>


<!DOCTYPE html>
<html>
<head>
<title>Import Inventory Excel</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
background:#eef3fb;
font-family:Arial;
}

.box{
max-width:700px;
margin:60px auto;
background:#fff;
padding:40px;
border-radius:20px;
box-shadow:0 8px 20px rgba(0,0,0,.05);
}
</style>

</head>
<body>

<div class="box">

<h3 class="fw-bold mb-4">
Import Inventory Backup
</h3>

<form
method="POST"
enctype="multipart/form-data"
>

<label class="fw-bold mb-2">
Upload Excel Backup
</label>

<input
type="file"
name="excel_file"
accept=".xlsx,.xls"
class="form-control mb-4"
required
>

<button
class="btn btn-primary"
name="import"
>
Import Excel
</button>

<a
href="backup_restore.php"
class="btn btn-secondary"
>
Back
</a>

</form>

</div>

</body>
</html>