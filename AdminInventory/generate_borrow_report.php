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
$status = $_GET['status'] ?? '';
$item = $_GET['item'] ?? '';
$employee = $_GET['employee'] ?? '';

$sql = "
SELECT
e.description,
emp.employee_name,
b.quantity_borrowed,
b.borrow_date,
b.status
FROM borrow_records b
JOIN equipment_inventory e ON b.item_id = e.item_id
JOIN employees emp ON b.employee_id = emp.employee_id
WHERE YEAR(b.borrow_date)='$year'
";

if ($month != "") {
    $sql .= " AND MONTH(b.borrow_date)='$month'";
}

if ($status != "") {
    $sql .= " AND b.status='$status'";
}

if ($item != "") {
    $sql .= " AND b.item_id='$item'";
}

if ($employee != "") {
    $sql .= " AND b.employee_id='$employee'";
}

$result = $conn->query($sql);

/* ---------- EXCEL ---------- */
$excel = new Spreadsheet();
$sheet = $excel->getActiveSheet();
$sheet->setTitle("Borrow Report");

/* ---------- COLUMN WIDTHS ---------- */
$widths = [
    'A' => 25,
    'B' => 25,
    'C' => 10,
    'D' => 20,
    'E' => 15
];

foreach ($widths as $col => $w) {
    $sheet->getColumnDimension($col)->setWidth($w);
}

/* ---------- HEADER ---------- */
$sheet->mergeCells('A1:E1');
$sheet->setCellValue('A1', 'PSA BORROWED PROPERTY REPORT');

$sheet->mergeCells('A2:E2');
$sheet->setCellValue('A2', 'Borrowed Items Monitoring Sheet');

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
    'Status'
];

$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col . $row, $h);
    $col++;
}

$sheet->getStyle("A5:E5")->applyFromArray([
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
    $sheet->setCellValue("E$row", $r['status']);

    $row++;
    $count++;
}

/* ---------- EMPTY STATE ---------- */
if ($count == 0) {
    for ($x = 6; $x <= 15; $x++) {
        for ($c = 'A'; $c <= 'E'; $c++) {
            $sheet->setCellValue($c . $x, '');
        }
    }
    $row = 16;
}

/* ---------- BORDERS ---------- */
$lastRow = max($row - 1, 15);

$sheet->getStyle("A5:E$lastRow")->applyFromArray([
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

$sheet->mergeCells("B{$sig}:C{$sig}");
$sheet->setCellValue("B{$sig}", "Prepared by:");

$sheet->mergeCells("D{$sig}:E{$sig}");
$sheet->setCellValue("D{$sig}", "Certified Correct:");

$sig2 = $sig + 3;

$sheet->mergeCells("B{$sig2}:C{$sig2}");
$sheet->setCellValue("B{$sig2}", "Inventory Administrator");

$sheet->mergeCells("D{$sig2}:E{$sig2}");
$sheet->setCellValue("D{$sig2}", "Office Head");

$sig3 = $sig2 + 1;

$sheet->mergeCells("B{$sig3}:C{$sig3}");
$sheet->setCellValue("B{$sig3}", "DAPHNE D. VILLA");

$sheet->mergeCells("D{$sig3}:E{$sig3}");
$sheet->setCellValue("D{$sig3}", "JULIETA M. NACARIO");

$sig4 = $sig3 + 1;

$sheet->mergeCells("B{$sig4}:C{$sig4}");
$sheet->setCellValue("B{$sig4}", "Registration Officer I");

$sheet->mergeCells("D{$sig4}:E{$sig4}");
$sheet->setCellValue("D{$sig4}", "Supervising Statistical Specialist");

/* ---------- DOWNLOAD ---------- */
$file = "Borrow_Report.xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$file\"");

$writer = new Xlsx($excel);
$writer->save('php://output');
exit;