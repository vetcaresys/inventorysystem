<!-- Adding a Forms -->
<div class="modal fade" id="addFormModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">

            <div class="modal-header">
                <h5>Add Form</h5>
            </div>

            <div class="modal-body">
                <input type="text" name="item_name" class="form-control mb-2" placeholder="Form Name" required>
                <textarea name="description" class="form-control mb-2" placeholder="Description"></textarea>
                <input type="number" name="bundle_size" class="form-control mb-2" placeholder="Bundle Size">
                <input type="number" step="0.01" name="price_per_bundle" class="form-control mb-2" placeholder="Bundle Price">
                <input type="number" step="0.01" name="price_per_piece" class="form-control" placeholder="Piece Price">
                <input type="number" step="0.01" name="price" class="form-control mb-2" placeholder="Price">
                <input type="number" name="quantity" class="form-control mb-2" placeholder="Quantity">

                <select name="status" class="form-control mb-2">
                    <option value="Available">Available</option>
                    <option value="Unavailable">Unavailable</option>
                </select>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary" name="add_form">Save</button>
            </div>

        </form>
    </div>
</div>

<!-- Adding Sale -->
 <div class="modal fade" id="posModal">
    <div class="modal-dialog modal-lg">
        <form method="POST" class="modal-content">

            <div class="modal-header">
                <h5>Form Sales (POS)</h5>
            </div>

            <div class="modal-body">

                <!-- SELECT FORM -->
                <select name="item_id" id="itemSelect" class="form-control mb-2" required>
                    <option value="">Select Form</option>
                    <?php
                    $formList = $conn->query("
                        SELECT i.item_id, i.item_name, f.price_per_piece
                        FROM psa_forms f
                        JOIN inventory_items i ON i.item_id = f.item_id
                    ");
                    while ($f = $formList->fetch_assoc()):
                    ?>
                        <option value="<?= $f['item_id'] ?>" data-price="<?= $f['price_per_piece'] ?>">
                            <?= $f['item_name'] ?> - ₱<?= $f['price_per_piece'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <!-- QTY -->
                <input type="number" name="qty" id="qty" class="form-control mb-2" placeholder="Quantity" required>

                <!-- TOTAL -->
                <input type="text" id="total" class="form-control mb-2" placeholder="Total" readonly>

                <!-- BUYER -->
                <input type="text" name="buyer_name" class="form-control mb-2" placeholder="Buyer Name">
                <textarea name="address" class="form-control mb-2" placeholder="Address"></textarea>

            </div>

            <div class="modal-footer">
                <button class="btn btn-success" name="process_sale">Checkout</button>
            </div>

        </form>
    </div>
</div>

<!-- Adding a Device -->
<div class="modal fade" id="addDeviceModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">

            <div class="modal-header">
                <h5>Add Device</h5>
            </div>

            <div class="modal-body">
                <input type="text" name="item_name" class="form-control mb-2" placeholder="Device Name" required>
                <input type="text" name="property_no" class="form-control mb-2" placeholder="Property No" required>
                <input type="text" name="serial_no" class="form-control mb-2" placeholder="Serial No">
                <input type="text" name="location" class="form-control" placeholder="Location">
                <input type="text" name="description" class="form-control mb-2" placeholder="Description">
                <input type="number" step="0.01" name="price" class="form-control mb-2" placeholder="Price">

                <select name="status" class="form-control mb-2">
                    <option value="Available">Available</option>
                    <option value="Unavailable">Unavailable</option>
                </select>
            </div>

            <div class="modal-footer">
                <button class="btn btn-success" name="add_device">Save</button>
            </div>

        </form>
    </div>
</div>

<!-- Adding an Asset -->
<div class="modal fade" id="addAssetModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">

            <div class="modal-header">
                <h5>Add Asset</h5>
            </div>

            <div class="modal-body">
                <input type="text" name="item_name" class="form-control mb-2" placeholder="Asset Name" required>
                <input type="text" name="property_no" class="form-control mb-2" placeholder="Property No" required>
                <input type="text" name="brand" class="form-control mb-2" placeholder="Brand">
                <input type="text" name="condition_status" class="form-control mb-2" placeholder="Condition">
                <input type="text" name="location" class="form-control" placeholder="Location">
                <input type="text" name="description" class="form-control mb-2" placeholder="Description">
                <input type="number" step="0.01" name="price" class="form-control mb-2" placeholder="Price">

                <select name="status" class="form-control mb-2">
                    <option value="Available">Available</option>
                    <option value="Unavailable">Unavailable</option>
                </select>
            </div>

            <div class="modal-footer">
                <button class="btn btn-warning" name="add_asset">Save</button>
            </div>

        </form>
    </div>
</div>