<?php
session_start();
require_once __DIR__.'/../connectiondb.php';
require_once __DIR__.'/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

$year=$_GET['year'] ?? date('Y');
$month=$_GET['month'] ?? '';
$item=$_GET['item'] ?? '';
$condition=$_GET['condition'] ?? '';

$sql="
SELECT *
FROM equipment_inventory
WHERE YEAR(date_acquired)='$year'
";

if($month!=""){
$sql.=" AND MONTH(date_acquired)='$month'";
}

if($item!=""){
$sql.=" AND description='$item'";
}

if($condition!=""){
$sql.=" AND item_condition='$condition'";
}

$result=$conn->query($sql);

$excel = new Spreadsheet();
$sheet = $excel->getActiveSheet();
$sheet->setTitle('PSA Inventory Report');


/* ---------- COLUMN WIDTHS ---------- */
$widths=[
'A'=>18,'B'=>18,'C'=>28,'D'=>24,
'E'=>18,'F'=>18,'G'=>18,'H'=>16,'I'=>18
];

foreach($widths as $col=>$w){
$sheet->getColumnDimension($col)->setWidth($w);
}


/* ---------- HEADER ---------- */

$sheet->mergeCells('A1:I1');
$sheet->setCellValue('A1','PHILIPPINE STATISTICS AUTHORITY');

$sheet->mergeCells('A2:I2');
$sheet->setCellValue('A2','MISAMIS OCCIDENTAL PROVINCIAL OFFICE');

$sheet->mergeCells('A4:I4');
$sheet->setCellValue('A4','MONTHLY REPORT OF CIVIL REGISTRATION');

$sheet->mergeCells('A5:I5');
$sheet->setCellValue(
'A5',
'For the period of '.($month?$month.'/'.$year:$year)
);

$sheet->getStyle('A1:I5')->applyFromArray([
'font'=>[
'bold'=>true,
'size'=>14
],
'alignment'=>[
'horizontal'=>Alignment::HORIZONTAL_CENTER
]
]);


/* ---------- TABLE HEADERS ---------- */

$row=8;

$headers=[
'Inventory Tag #',
'Current Property #',
'Accountable Officer',
'Description',
'Serial #',
'Date Acquired',
'Acquisition Cost',
'Location',
'Remark'
];

$col='A';

foreach($headers as $h){
$sheet->setCellValue($col.$row,$h);
$col++;
}

$sheet->getStyle("A8:I8")->applyFromArray([
'font'=>['bold'=>true],
'alignment'=>[
'horizontal'=>Alignment::HORIZONTAL_CENTER,
'vertical'=>Alignment::VERTICAL_CENTER
],
'borders'=>[
'allBorders'=>[
'borderStyle'=>Border::BORDER_THIN
]
]
]);

$sheet->getRowDimension(8)->setRowHeight(30);


/* ---------- DATA ROWS ---------- */

$row=9;
$dataCount=0;

while($r=$result->fetch_assoc()){

$sheet->setCellValue("A$row",$r['inventory_tag_no']);
$sheet->setCellValue("B$row",$r['property_no']);
$sheet->setCellValue("C$row",$r['accountable_officer']);
$sheet->setCellValue("D$row",$r['description']);
$sheet->setCellValue("E$row",$r['serial_no']);
$sheet->setCellValue("F$row",$r['date_acquired']);
$sheet->setCellValue("G$row",$r['acquisition_cost']);
$sheet->setCellValue("H$row",$r['location']);
$sheet->setCellValue("I$row","");

$row++;
$dataCount++;
}


/* if no data, keep blank boxes */
if($dataCount==0){
for($x=9;$x<=18;$x++){
for($c='A';$c<='I';$c++){
$sheet->setCellValue($c.$x,'');
}
}
$row=19;
}


/* borders for table */
$lastData=max($row-1,18);

$sheet->getStyle("A8:I$lastData")->applyFromArray([
'borders'=>[
'allBorders'=>[
'borderStyle'=>Border::BORDER_THIN
]
]
]);


/* center selected columns */
$sheet->getStyle("A8:I$lastData")
->getAlignment()
->setHorizontal(Alignment::HORIZONTAL_CENTER);


/* ---------- SIGNATURES ---------- */

$sig=$lastData+3;

$sheet->mergeCells("B{$sig}:C{$sig}");
$sheet->setCellValue("B{$sig}","Prepared by:");

$sheet->mergeCells("G{$sig}:H{$sig}");
$sheet->setCellValue("G{$sig}","Certified Correct:");

$sig2=$sig+3;

$sheet->mergeCells("B{$sig2}:D{$sig2}");
$sheet->setCellValue("B{$sig2}","DAPHNE D. VILLA");

$sheet->mergeCells("G{$sig2}:I{$sig2}");
$sheet->setCellValue("G{$sig2}","JULIETA M. NACARIO");

$sig3=$sig2+1;

$sheet->mergeCells("B{$sig3}:D{$sig3}");
$sheet->setCellValue("B{$sig3}","Registration Officer I");

$sheet->mergeCells("G{$sig3}:I{$sig3}");
$sheet->setCellValue("G{$sig3}","Supervising Statistical Specialist");


/* ---------- DOWNLOAD ---------- */

$file='PSA_Inventory_Report.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$file\"");
header('Cache-Control: max-age=0');

$writer=new Xlsx($excel);
$writer->save('php://output');
exit;