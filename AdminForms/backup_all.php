<?php
session_start();
require '../connectiondb.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'forms_admin') {
    header("Location: ../login.php");
    exit;
}

$spreadsheet = new Spreadsheet();

/* =========================
HELPER FUNCTION
========================= */
function exportSheet($spreadsheet, $sheetIndex, $title, $conn, $query)
{
    if ($sheetIndex == 0) {
        $sheet = $spreadsheet->getActiveSheet();
    } else {
        $sheet = $spreadsheet->createSheet();
    }

    $sheet->setTitle($title);

    $result = $conn->query($query);
    $data = $result->fetch_all(MYSQLI_ASSOC);

    if (!$data) {
        $sheet->setCellValue('A1', 'No Data');
        return;
    }

    // headers
    $sheet->fromArray(array_keys($data[0]), NULL, 'A1');

    // data
    $sheet->fromArray($data, NULL, 'A2');
}

/* =========================
EXPORT ALL TABLES
========================= */

exportSheet($spreadsheet, 0, 'Users',
$conn, "SELECT user_id, fullname, username, role, email, contact_no, position, office_unit, status FROM users");

exportSheet($spreadsheet, 1, 'Forms',
$conn, "SELECT * FROM forms");

exportSheet($spreadsheet, 2, 'Inventory',
$conn, "SELECT * FROM equipment_inventory");

exportSheet($spreadsheet, 3, 'Borrow',
$conn, "SELECT * FROM borrow_records");

exportSheet($spreadsheet, 4, 'Employees',
$conn, "SELECT * FROM employees");

exportSheet($spreadsheet, 5, 'Restock',
$conn, "SELECT * FROM form_restock");

exportSheet($spreadsheet, 6, 'Sales',
$conn, "SELECT * FROM form_sales");

exportSheet($spreadsheet, 7, 'Returned Forms',
$conn, "SELECT * FROM returned_forms");

exportSheet($spreadsheet, 8, 'Returned Records',
$conn, "SELECT * FROM return_records");

/* =========================
DOWNLOAD FILE
========================= */

$filename = "inventory_system_backup_" . date('Y-m-d') . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>