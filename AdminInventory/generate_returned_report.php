<?php
require '../connectiondb.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/* ---------- FILTERS ---------- */
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? '';

$sql = "
SELECT
e.description,
emp.employee_name,
b.quantity_borrowed,
b.borrow_date,
r.actual_return_date,
r.returned_condition,
r.remarks
FROM return_records r
JOIN borrow_records b ON r.borrow_id = b.borrow_id
JOIN equipment_inventory e ON b.item_id = e.item_id
JOIN employees emp ON b.employee_id = emp.employee_id
WHERE YEAR(r.actual_return_date)='$year'
";

if ($month != "") {
    $sql .= " AND MONTH(r.actual_return_date)='$month'";
}

$result = $conn->query($sql);

/* ---------- EXCEL ---------- */
$excel = new Spreadsheet();
$sheet = $excel->getActiveSheet();
$sheet->setTitle("Returned Report");

/* ---------- COLUMN WIDTHS ---------- */
$widths = [
    'A' => 25,
    'B' => 25,
    'C' => 10,
    'D' => 18,
    'E' => 18,
    'F' => 18,
    'G' => 25
];

foreach ($widths as $col => $w) {
    $sheet->getColumnDimension($col)->setWidth($w);
}

/* ---------- HEADER ---------- */
$sheet->mergeCells('A1:G1');
$sheet->setCellValue('A1', 'PSA RETURNED PROPERTY REPORT');

$sheet->mergeCells('A2:G2');
$sheet->setCellValue('A2', 'Returned Items Monitoring Sheet');

$sheet->getStyle('A1:A2')->applyFromArray([
    'font' => ['bold' => true, 'size' => 14],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER
    ]
]);

/* ---------- TABLE HEADERS ---------- */
$row = 5;

$headers = [
    'Item',
    'Borrower',
    'Qty',
    'Borrow Date',
    'Return Date',
    'Condition',
    'Remarks'
];

$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col . $row, $h);
    $col++;
}

$sheet->getStyle("A5:G5")->applyFromArray([
    'font' => ['bold' => true],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN
        ]
    ]
]);

/* ---------- DATA ---------- */
$row = 6;
$count = 0;

while ($r = $result->fetch_assoc()) {

    $sheet->setCellValue("A$row", $r['description']);
    $sheet->setCellValue("B$row", $r['employee_name']);
    $sheet->setCellValue("C$row", $r['quantity_borrowed']);
    $sheet->setCellValue("D$row", $r['borrow_date']);
    $sheet->setCellValue("E$row", $r['actual_return_date']);
    $sheet->setCellValue("F$row", $r['returned_condition']);
    $sheet->setCellValue("G$row", $r['remarks']);

    $row++;
    $count++;
}

/* ---------- EMPTY STATE ---------- */
if ($count == 0) {
    for ($x = 6; $x <= 15; $x++) {
        for ($c = 'A'; $c <= 'G'; $c++) {
            $sheet->setCellValue($c . $x, '');
        }
    }
    $row = 16;
}

/* ---------- BORDER + ALIGNMENT ---------- */
$lastRow = max($row - 1, 15);

$sheet->getStyle("A5:G$lastRow")->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN
        ]
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER
    ]
]);

 /* ---------- SIGNATURES ---------- */

$sig = $lastRow + 3;

/* Row 1 labels */
$sheet->mergeCells("B{$sig}:C{$sig}");
$sheet->setCellValue("B{$sig}", "Prepared by:");

$sheet->mergeCells("D{$sig}:E{$sig}");
$sheet->setCellValue("D{$sig}", "Certified Correct:");

/* Row 2 names */
$sig2 = $sig + 3;

$sheet->mergeCells("B{$sig2}:C{$sig2}");
$sheet->setCellValue("B{$sig2}", "DAPHNE D. VILLA");

$sheet->mergeCells("D{$sig2}:E{$sig2}");
$sheet->setCellValue("D{$sig2}", "JULIETA M. NACARIO");

/* Row 3 positions */
$sig3 = $sig2 + 1;

$sheet->mergeCells("B{$sig3}:C{$sig3}");
$sheet->setCellValue("B{$sig3}", "Registration Officer I");

$sheet->mergeCells("D{$sig3}:E{$sig3}");
$sheet->setCellValue("D{$sig3}", "Supervising Statistical Specialist");

/* ---------- DOWNLOAD ---------- */
$file = "Returned_Report.xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$file\"");

$writer = new Xlsx($excel);
$writer->save('php://output');
exit;