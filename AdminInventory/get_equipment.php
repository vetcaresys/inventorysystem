<?php
require '../connectiondb.php';

$batch_id = $_GET['batch_id'];

$items = $conn->query("
    SELECT *
    FROM equipment_inventory
    WHERE batch_id='$batch_id'
");

if ($items->num_rows > 0) {
?>

<table class="table table-striped table-bordered text-center">

    <thead class="table-dark">
        <tr>
            <th>Description</th>
            <th>Category</th>
            <th>Qty</th>
            <th>Condition</th>
            <th>Location</th>
        </tr>
    </thead>

    <tbody>

    <?php while ($item = $items->fetch_assoc()) { ?>

        <tr>
            <td><?= $item['description']; ?></td>
            <td><?= $item['category']; ?></td>
            <td><?= $item['quantity']; ?></td>
            <td><?= $item['item_condition']; ?></td>
            <td><?= $item['location']; ?></td>
        </tr>

    <?php } ?>

    </tbody>

</table>

<?php
} else {
    echo "<p class='text-muted'>No equipment found in this batch.</p>";
}
?>