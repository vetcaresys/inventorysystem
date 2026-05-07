<?php
require '../connectiondb.php';

$term = $_GET['term'] ?? '';

$stmt = $conn->prepare("
    SELECT name 
    FROM receivers 
    WHERE name LIKE CONCAT('%', ?, '%')
    LIMIT 10
");

$stmt->bind_param("s", $term);
$stmt->execute();

$res = $stmt->get_result();

$data = [];

while ($row = $res->fetch_assoc()) {
    $data[] = $row['name'];
}

echo json_encode($data);
?>