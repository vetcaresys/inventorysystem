<!-- ADD FORM ITEM -->
<div class="modal fade" id="addFormModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">

            <div class="modal-header">
                <h5>Add Form Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <input type="text" name="item_name" class="form-control mb-2" placeholder="Form Name" required>

                <textarea name="description" class="form-control mb-2" placeholder="Description"></textarea>

                <input type="number" step="0.01" name="price" class="form-control mb-2" placeholder="Price" required>

                <input type="number" name="qty" class="form-control mb-2" placeholder="Initial Stock" required>

            </div>

            <div class="modal-footer">
                <button name="create_form_item" class="btn btn-primary w-100">Save</button>
            </div>

        </form>
    </div>
</div>

<!-- SOLD FORM -->
<div class="modal fade" id="soldFormModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">

            <div class="modal-header">
                <h5>Sell Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <select name="item_id" class="form-control mb-2" required>
                    <option value="">Select Form</option>
                    <?php
                    $q = $conn->query("SELECT * FROM psa_items WHERE category='Form'");
                    while ($x = $q->fetch_assoc()):
                    ?>
                        <option value="<?= $x['item_id'] ?>">
                            <?= $x['item_name'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <input type="number" name="qty" class="form-control mb-2" placeholder="Quantity" required>

                <input type="text" name="buyer" class="form-control mb-2" placeholder="Buyer Name" required>

                <input type="text" name="address" class="form-control mb-2" placeholder="Address" required>

            </div>

            <div class="modal-footer">
                <button name="sold_form" class="btn btn-success w-100">Confirm Sale</button>
            </div>

        </form>
    </div>
</div>

<!-- RETURN FORM -->
<div class="modal fade" id="returnFormModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">

            <div class="modal-header">
                <h5>Return Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <select name="item_id" class="form-control mb-2" required>
                    <option value="">Select Form</option>
                    <?php
                    $q = $conn->query("SELECT * FROM psa_items WHERE category='Form'");
                    while ($x = $q->fetch_assoc()):
                    ?>
                        <option value="<?= $x['item_id'] ?>">
                            <?= $x['item_name'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <input type="number" name="qty" class="form-control" placeholder="Quantity" required>

            </div>

            <div class="modal-footer">
                <button name="return_form" class="btn btn-warning w-100">Return</button>
            </div>

        </form>
    </div>
</div>

<!-- RESTOCK FORM -->
<!-- RESTOCK FORM -->
<div class="modal fade" id="restockFormModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">

            <div class="modal-header">
                <h5>Restock Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <select name="item_id" class="form-control mb-2" required>
                    <option value="">Select Form</option>
                    <?php
                    $q = $conn->query("SELECT * FROM psa_items WHERE category='Form'");
                    while ($x = $q->fetch_assoc()):
                    ?>
                        <option value="<?= $x['item_id'] ?>">
                            <?= $x['item_name'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <input type="number" name="qty" class="form-control mb-2" placeholder="Quantity to Restock" required>

            </div>

            <div class="modal-footer">
                <button name="restock_form" class="btn btn-info w-100">Restock</button>
            </div>

        </form>
    </div>
</div>

<!-- ADD DEVICE -->
<div class="modal fade" id="addDeviceModal">
    <div class="modal-dialog modal-lg">
        <form method="POST" class="modal-content">

            <div class="modal-header">
                <h5>Add Device</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body row">

                <div class="col-md-6">
                    <input type="text" name="device_type" class="form-control mb-2" placeholder="Device Type (Laptop, etc)" required>
                    <input type="text" name="brand_model" class="form-control mb-2" placeholder="Brand / Model">
                    <input type="text" name="serial" class="form-control mb-2" placeholder="Serial No" required>
                    <input type="text" name="inventory_tag" class="form-control mb-2" placeholder="Inventory Tag" required>
                </div>

                <div class="col-md-6">
                    <input type="text" name="property_no" class="form-control mb-2" placeholder="Property No" required>
                    <input type="text" name="officer" class="form-control mb-2" placeholder="Accountable Officer" required>
                    <input type="date" name="date_acquired" class="form-control mb-2" required>
                    <input type="number" step="0.01" name="cost" class="form-control mb-2" placeholder="Cost">
                    <input type="text" name="location" class="form-control mb-2" placeholder="Location">
                </div>

                <div class="col-12">
                    <textarea name="description" class="form-control" placeholder="Description"></textarea>
                </div>

            </div>

            <div class="modal-footer">
                <button name="add_device" class="btn btn-primary w-100">Save Device</button>
            </div>

        </form>
    </div>
</div>

<!-- BORROW DEVICE -->
<div class="modal fade" id="borrowDeviceModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">

            <div class="modal-header">
                <h5>Borrow Device</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <select name="device_id" class="form-control mb-2" required>
                    <option value="">Select Device</option>
                    <?php
                    $q = $conn->query("SELECT device_id, serial_no FROM psa_item_devices WHERE status='Available'");
                    while ($x = $q->fetch_assoc()):
                    ?>
                        <option value="<?= $x['device_id'] ?>">
                            <?= $x['serial_no'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <input type="text" name="borrower" class="form-control mb-2" placeholder="Borrower Name" required>

                <input type="date" name="date_borrowed" class="form-control" required>

            </div>

            <div class="modal-footer">
                <button name="borrow_device" class="btn btn-success w-100">Borrow</button>
            </div>

        </form>
    </div>
</div>

<!-- RETURN DEVICE -->
<div class="modal fade" id="returnDeviceModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">

            <div class="modal-header">
                <h5>Return Device</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <select name="device_id" class="form-control mb-2" required>
                    <option value="">Select Device</option>
                    <?php
                    $q = $conn->query("SELECT device_id, serial_no FROM psa_item_devices WHERE status='Borrowed'");
                    while ($x = $q->fetch_assoc()):
                    ?>
                        <option value="<?= $x['device_id'] ?>">
                            <?= $x['serial_no'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <input type="text" name="borrower" class="form-control mb-2" placeholder="Borrower Name" required>

                <input type="date" name="date_returned" class="form-control" required>

            </div>

            <div class="modal-footer">
                <button name="return_device" class="btn btn-warning w-100">Return</button>
            </div>

        </form>
    </div>
</div>

<!-- ADD ASSETS -->
<div class="modal fade" id="addAssetModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">

            <div class="modal-header">
                <h5>Add Asset</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <input type="text" name="asset_name" class="form-control mb-2" placeholder="Asset Name" required>

                <textarea name="description" class="form-control mb-2" placeholder="Description"></textarea>

                <input type="number" name="quantity" class="form-control mb-2" placeholder="Quantity" required>

                <input type="number" step="0.01" name="price" class="form-control" placeholder="Price">

            </div>

            <div class="modal-footer">
                <button name="add_asset" class="btn btn-primary w-100">Save Asset</button>
            </div>

        </form>
    </div>
</div>