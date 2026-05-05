<?php
require '../connectiondb.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$like = "%$search%";

if ($search != "") {
    $stmt = $conn->prepare("
        SELECT * FROM forms
        WHERE form_code LIKE ? OR form_name LIKE ?
        ORDER BY form_name ASC
    ");
    $stmt->bind_param("ss", $like, $like);
} else {
    $stmt = $conn->prepare("
        SELECT * FROM forms
        ORDER BY form_name ASC
    ");
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<tr><td colspan='6'>No forms found</td></tr>";
    exit;
}

while ($row = $result->fetch_assoc()) {

    $stock = $row['current_stock'];

    $status = ($stock < 50)
        ? "<span class='badge badge-low'>Low Stock</span>"
        : "<span class='badge badge-good'>Available</span>";

    echo "
    <tr>
        <td>{$row['form_code']}</td>
        <td>{$row['form_name']}</td>
        <td>₱" . number_format($row['unit_price'], 2) . "</td>
        <td>{$stock}</td>
        <td>{$status}</td>
        <td>
            <button class='btn btn-sm btn-info'>View</button>
            <button class='btn btn-sm btn-danger'>Delete</button>
        </td>
    </tr>
    ";
}
?>