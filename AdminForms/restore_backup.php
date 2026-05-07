<?php
require 'connectiondb.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if(!isset($_FILES['file']['tmp_name'])){
    die("No file uploaded");
}

$file = $_FILES['file']['tmp_name'];
$spreadsheet = IOFactory::load($file);

/* =========================
CLEAR TABLES FIRST (SAFE RESET)
========================= */
$conn->query("SET FOREIGN_KEY_CHECKS=0");

$conn->query("TRUNCATE users");
$conn->query("TRUNCATE equipment_inventory");
$conn->query("TRUNCATE forms");
$conn->query("TRUNCATE form_restock");
$conn->query("TRUNCATE form_sales");
$conn->query("TRUNCATE borrow_records");
$conn->query("TRUNCATE employees");
$conn->query("TRUNCATE returned_forms");
$conn->query("TRUNCATE returned_records");

$conn->query("SET FOREIGN_KEY_CHECKS=1");


/* =========================
GENERIC IMPORT FUNCTION
========================= */
function importSheet($conn, $sheetName, $table, $columns){

    $sheet = IOFactory::load($_FILES['file']['tmp_name'])->getSheetByName($sheetName);
    if(!$sheet) return;

    $rows = $sheet->toArray();

    for($i=1; $i<count($rows); $i++){
        $data = $rows[$i];

        $placeholders = implode(',', array_fill(0, count($columns), '?'));
        $colNames = implode(',', $columns);

        $stmt = $conn->prepare("INSERT INTO $table ($colNames) VALUES ($placeholders)");

        $types = str_repeat('s', count($columns));
        $stmt->bind_param($types, ...$data);

        $stmt->execute();
    }
}


/* =========================
IMPORT ALL TABLES
========================= */
importSheet($conn,'users','users',[
'user_id','fullname','username','password','role','created_at','email','contact_no','profile_picture','position','office_unit','last_login','status','updated_at'
]);

importSheet($conn,'equipment_inventory','equipment_inventory',[
'item_id','property_no','inventory_tag_no','description','category','serial_no','date_acquired','acquisition_cost','quantity','unit','item_condition','location','accountable_officer'
]);

importSheet($conn,'forms','forms',[
'form_id','form_code','form_name','unit_price','beginning_inventory','current_stock','status','min_stock'
]);

importSheet($conn,'form_restock','form_restock',[
'restock_id','form_id','delivery_receipt_no','quantity_received','date_received'
]);

importSheet($conn,'form_sales','form_sales',[
'sale_id','form_id','buyer_name','department','address','quantity_sold','total_amount','date_sold','sold_by'
]);

importSheet($conn,'borrow_records','borrow_records',[
'borrow_id','item_id','employee_id','quantity_borrowed','borrow_date','status'
]);

importSheet($conn,'employees','employees',[
'employee_id','employee_name','office_unit','position','contact_no'
]);

importSheet($conn,'returned_forms','returned_forms',[
'return_id','form_id','quantity_returned','return_date','remarks'
]);

importSheet($conn,'returned_records','return_records',[
'return_id','borrow_id','actual_return_date','returned_condition','remarks'
]);

header("Location: restore_backup.php?success=1");
exit;
?>