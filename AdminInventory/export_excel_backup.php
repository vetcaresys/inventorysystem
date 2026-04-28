<?php
require '../connectiondb.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$excel = new Spreadsheet();


/* =========================
SHEET 1 INVENTORY
========================= */

$sheet = $excel->getActiveSheet();
$sheet->setTitle("Inventory");

$sheet->fromArray([
    [
        'Item ID',
        'Property No',
        'Tag No',
        'Description',
        'Category',
        'Serial No',
        'Quantity',
        'Condition',
        'Location'
    ]
], NULL, 'A1');

$r = 2;

$q = $conn->query("
SELECT *
FROM equipment_inventory
");

while ($row = $q->fetch_assoc()) {

    $sheet->fromArray([
        [
            $row['item_id'],
            $row['property_no'],
            $row['inventory_tag_no'],
            $row['description'],
            $row['category'],
            $row['serial_no'],
            $row['quantity'],
            $row['item_condition'],
            $row['location']
        ]
    ], NULL, "A$r");

    $r++;
}



/* =========================
SHEET 2 EMPLOYEES
========================= */

$sheet2 =
    $excel->createSheet();

$sheet2->setTitle("Employees");

$sheet2->fromArray([
    [
        'ID',
        'Name',
        'Office',
        'Position',
        'Contact'
    ]
], NULL, 'A1');

$r = 2;

$q = $conn->query("
SELECT * FROM employees
");

while ($row = $q->fetch_assoc()) {

    $sheet2->fromArray([
        [
            $row['employee_id'],
            $row['employee_name'],
            $row['office_unit'],
            $row['position'],
            $row['contact_no']
        ]
    ], NULL, "A$r");

    $r++;
}



/* =========================
SHEET 3 BORROW
========================= */

$sheet3 =
    $excel->createSheet();

$sheet3->setTitle("Borrow Records");

$sheet3->fromArray([
    [
        'Item',
        'Borrower',
        'Qty',
        'Borrow Date',
        'Status'
    ]
], NULL, 'A1');

$r = 2;

$q = $conn->query("
SELECT
e.description,
emp.employee_name,
b.quantity_borrowed,
b.borrow_date,
b.status
FROM borrow_records b
JOIN equipment_inventory e
ON b.item_id=e.item_id
JOIN employees emp
ON b.employee_id=emp.employee_id
");

while ($row = $q->fetch_assoc()) {

    $sheet3->fromArray([
        [
            $row['description'],
            $row['employee_name'],
            $row['quantity_borrowed'],
            $row['borrow_date'],
            $row['status']
        ]
    ], NULL, "A$r");

    $r++;
}



/* =========================
SHEET 4 RETURNS
========================= */

$sheet4 =
    $excel->createSheet();

$sheet4->setTitle("Returns");

$sheet4->fromArray([
    [
        'Item',
        'Borrower',
        'Return Date',
        'Condition',
        'Remarks'
    ]
], NULL, 'A1');

$r = 2;

$q = $conn->query("
SELECT
e.description,
emp.employee_name,
r.actual_return_date,
r.returned_condition,
r.remarks
FROM return_records r
JOIN borrow_records b
ON r.borrow_id=b.borrow_id
JOIN equipment_inventory e
ON b.item_id=e.item_id
JOIN employees emp
ON b.employee_id=emp.employee_id
");

while ($row = $q->fetch_assoc()) {

    $sheet4->fromArray([
        [
            $row['description'],
            $row['employee_name'],
            $row['actual_return_date'],
            $row['returned_condition'],
            $row['remarks']
        ]
    ], NULL, "A$r");

    $r++;
}


/* DOWNLOAD */
$file = 'PSA_Master_Backup.xlsx';

header(
    'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
);

header(
    "Content-Disposition: attachment; filename=\"$file\""
);

$writer =
    new Xlsx($excel);

$writer->save('php://output');
exit;
