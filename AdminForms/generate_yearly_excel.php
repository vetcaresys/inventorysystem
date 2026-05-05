<?php
require '../connectiondb.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

if (!isset($_GET['year'])) {
    die("Year required");
}

$year = $_GET['year'];
$start = "$year-01-01";
$end   = "$year-12-31";

/* FETCH DATA */
$forms = $conn->query("SELECT * FROM forms ORDER BY form_name ASC");

$salesQ = $conn->query("
SELECT form_id, SUM(quantity_sold) total_sold
FROM form_sales
WHERE date_sold BETWEEN '$start' AND '$end'
GROUP BY form_id
");

$sold = [];
while ($r = $salesQ->fetch_assoc()) {
    $sold[$r['form_id']] = $r['total_sold'];
}

$restockQ = $conn->query("
SELECT form_id, SUM(quantity_received) total_received
FROM form_restock
WHERE date_received BETWEEN '$start' AND '$end'
GROUP BY form_id
");

$received = [];
while ($r = $restockQ->fetch_assoc()) {
    $received[$r['form_id']] = $r['total_received'];
}

/* DR LIST */
$drQ = $conn->query("
SELECT form_id,
GROUP_CONCAT(DISTINCT delivery_receipt_no ORDER BY date_received SEPARATOR ', ') dr_list
FROM form_restock
WHERE date_received BETWEEN '$start' AND '$end'
GROUP BY form_id
");

$drList = [];
while ($r = $drQ->fetch_assoc()) {
    $drList[$r['form_id']] = $r['dr_list'];
}

/* CREATE EXCEL */
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

/* HEADER */
$sheet->mergeCells('A1:H1');
$sheet->setCellValue('A1', 'PHILIPPINE STATISTICS AUTHORITY');

$sheet->mergeCells('A2:H2');
$sheet->setCellValue('A2', 'MISAMIS OCCIDENTAL PROVINCIAL OFFICE');

$sheet->mergeCells('A3:H3');
$sheet->setCellValue('A3', 'ANNUAL REPORT OF CIVIL REGISTRY FORMS');

$sheet->mergeCells('A4:H4');
$sheet->setCellValue('A4', "For the Year $year");

$sheet->getStyle('A1:A4')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1:A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

/* TABLE HEADER (same style as monthly) */
$row = 6;

/* MAIN HEADER */
$sheet->setCellValue('A6', 'TYPE OF FORM');
$sheet->mergeCells('A6:A7');

$sheet->setCellValue('B6', 'BEGINNING INVENTORY');
$sheet->mergeCells('B6:B7');

/* FORMS RECEIVED */
$sheet->setCellValue('C6', 'FORMS RECEIVED');
$sheet->mergeCells('C6:D6');

$sheet->setCellValue('C7', 'DR NO.');
$sheet->setCellValue('D7', 'QUANTITY');

$sheet->setCellValue('E6', 'AVAILABLE');
$sheet->mergeCells('E6:E7');

$sheet->setCellValue('F6', 'RETURNED');
$sheet->mergeCells('F6:F7');

$sheet->setCellValue('G6', 'SOLD');
$sheet->mergeCells('G6:G7');

$sheet->setCellValue('H6', 'ENDING INVENTORY');
$sheet->mergeCells('H6:H7');

$sheet->getStyle("A6:H7")->getFont()->setBold(true);
$sheet->getStyle("A6:H7")->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
    ->setVertical(Alignment::VERTICAL_CENTER);

$sheet->getStyle("A6:H7")->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setARGB('FFD9EAF7');

/* DATA */
$row = 8;
while ($f = $forms->fetch_assoc()) {

    $beginning = $f['beginning_inventory'];
    $rec = $received[$f['form_id']] ?? 0;
    $soldQty = $sold[$f['form_id']] ?? 0;
    $returned = 0;

    // optional skip if no activity
    if ($rec == 0 && $soldQty == 0) {
        continue;
    }

    $available = $beginning + $rec;
    $ending = $available - $soldQty - $returned;

    $sheet->setCellValue("A$row", $f['form_name']);
    $sheet->setCellValue("B$row", $beginning);
    $sheet->setCellValue("C$row", $drList[$f['form_id']] ?? 'N/A');
    $sheet->setCellValue("D$row", $rec);
    $sheet->setCellValue("E$row", $available);
    $sheet->setCellValue("F$row", $returned);
    $sheet->setCellValue("G$row", $soldQty);
    $sheet->setCellValue("H$row", $ending);

    $row++;
}

/* BORDERS */
$lastRow = $row - 1;
$sheet->getStyle("A6:H$lastRow")
    ->getBorders()
    ->getAllBorders()
    ->setBorderStyle(Border::BORDER_THIN);

$sheet->getStyle("B7:H$lastRow")
    ->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

/* SIGNATURES */
$row += 2;
$sheet->setCellValue("B$row", "Prepared by:");
$row += 2;
$sheet->setCellValue("B$row", "DAPHNE D. VILLA");
$row++;
$sheet->setCellValue("B$row", "Registration Officer I");

$row -= 3;
$sheet->setCellValue("F$row", "Certified correct:");
$row += 2;
$sheet->setCellValue("F$row", "JULIETA M. NACARIO");
$row++;
$sheet->setCellValue("F$row", "Supervising Statistical Specialist");

/* AUTO SIZE */
foreach (range('A', 'H') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$sheet->getPageSetup()->setOrientation(
    \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
);

$sheet->getPageSetup()->setPaperSize(
    \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4
);

/* DOWNLOAD */
$fileName = "Yearly_Forms_Report_$year.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$fileName\"");

$writer = new Xlsx($spreadsheet);
$writer->save("php://output");
exit;
?>