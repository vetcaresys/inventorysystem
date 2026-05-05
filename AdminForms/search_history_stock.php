<?php
require '../connectiondb.php';

$search = trim($_GET['search'] ?? "");
$like = "%$search%";

$stmt = $conn->prepare("
    SELECT r.*, f.form_name
    FROM form_restock r
    JOIN forms f ON r.form_id = f.form_id
    WHERE r.delivery_receipt_no LIKE ?
       OR f.form_name LIKE ?
    ORDER BY r.restock_id DESC
");

$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<tr><td colspan='4'>No results found</td></tr>";
    exit;
}

while ($r = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$r['delivery_receipt_no']}</td>
        <td>{$r['form_name']}</td>
        <td>{$r['quantity_received']}</td>
        <td>{$r['date_received']}</td>
    </tr>";
}
?>