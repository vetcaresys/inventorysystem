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

                <input type="text" name="bundle_size" class="form-control mb-2" placeholder="Bundle Size">
                <input type="number" step="0.01" name="price_per_bundle" class="form-control mb-2"
                    placeholder="Bundle Price">
                <input type="text" name="custodian" class="form-control mb-2" placeholder="Custodian / Accountable Person" required>

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

<!-- View Form details -->
<div class="modal fade" id="viewFormModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Form Details</h5>
            </div>

            <div class="modal-body">
                <p><strong>Name:</strong> <span id="v_name"></span></p>
                <p><strong>Bundle Size:</strong> <span id="v_bundle"></span></p>
                <p><strong>Bundle Price:</strong> ₱<span id="v_bprice"></span></p>
                <p><strong>Custodian:</strong> <span id="v_custodian"></span></p>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

<!-- update form details -->
<div class="modal fade" id="editFormModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">

            <div class="modal-header">
                <h5>Edit Form</h5>
            </div>

            <div class="modal-body">

                <input type="hidden" name="form_id" id="ef_id">

                <input type="text" name="item_name" id="ef_name" class="form-control mb-2" placeholder="Name">

                <input type="text" name="bundle_size" id="ef_bundle" class="form-control mb-2">

                <input type="number" name="price_per_bundle" id="ef_bprice" class="form-control mb-2">

                <input type="text" name="custodian" id="ef_custodian" class="form-control mb-2">

            </div>

            <div class="modal-footer">
                <button class="btn btn-success" name="update_form">Update</button>
            </div>

        </form>
    </div>
</div>

<!-- POS -->
<div class="modal fade" id="posModal">
    <div class="modal-dialog modal-lg">
        <form method="POST" class="modal-content">

            <div class="modal-header">
                <h5>POS - Multi Item</h5>
            </div>

            <div class="modal-body">

                <!-- SELECT FORM -->
                <div class="row mb-2">
                    <div class="col-md-6">
                        <select id="itemSelect" class="form-control">
                            <option value="">Select Form</option>
                            <?php
                            $formList = $conn->query("
    SELECT 
        i.item_id, 
        i.item_name, 
        f.price_per_bundle, 
        f.bundle_size
    FROM psa_forms f
    JOIN inventory_items i ON i.item_id = f.item_id
");
                            while ($f = $formList->fetch_assoc()):
                            ?>
                                <option value="<?= $f['item_id'] ?>"
                                    data-name="<?= $f['item_name'] ?>"
                                    data-price="<?= $f['price_per_bundle'] ?>">

                                    <?= $f['item_name'] ?> - ₱<?= $f['price_per_bundle'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <input type="number" id="qty" class="form-control" placeholder="Qty">
                    </div>

                    <div class="col-md-3">
                        <button type="button" class="btn btn-primary w-100" onclick="addToCart()">Add</button>
                    </div>
                </div>

                <!-- CART TABLE -->
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="cartTable"></tbody>
                </table>

                <!-- TOTAL -->
                <h4 class="text-end">Total: ₱ <span id="grandTotal">0.00</span></h4>

                <!-- BUYER -->
                <input type="text" name="buyer_name" class="form-control mb-2" placeholder="Buyer Name">
                <textarea name="address" class="form-control" placeholder="Address"></textarea>

                <!-- hidden cart -->
                <input type="hidden" name="cart_data" id="cartData">
            </div>

            <div class="modal-footer">
                <button class="btn btn-success" name="checkout" onclick="return validatePayment()">Checkout</button>
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
                <input type="text" name="location" class="form-control mb-2" placeholder="Location">
                <input type="text" name="description" class="form-control mb-2" placeholder="Description">
                <input type="text" name="custodian" class="form-control mb-2" placeholder="Custodian / Accountable Person" required>
                <input type="text" name="inventory_tag" class="form-control mb-2" placeholder="Inventory Tag">
                <input type="text" name="brand_model" class="form-control mb-2" placeholder="Brand / Model">
                <input type="date" name="date_acquired" class="form-control mb-2">
                <input type="number" step="0.01" name="acquisition_cost" class="form-control mb-2" placeholder="Acquisition Cost">

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

<!-- view device details -->
<div class="modal fade" id="viewDeviceModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Device Details</h5>
            </div>

            <div class="modal-body">
                <p><strong>Name:</strong> <span id="vd_name"></span></p>
                <p><strong>Inventory Tag:</strong> <span id="vd_tag"></span></p>
                <p><strong>Property No:</strong> <span id="vd_property"></span></p>
                <p><strong>Custodian:</strong> <span id="vd_custodian"></span></p>
                <p><strong>Brand/Model:</strong> <span id="vd_brand"></span></p>
                <p><strong>Serial No:</strong> <span id="vd_serial"></span></p>
                <p><strong>Date Acquired:</strong> <span id="vd_date"></span></p>
                <p><strong>Acquisition Cost:</strong> ₱<span id="vd_cost"></span></p>
                <p><strong>Location:</strong> <span id="vd_location"></span></p>
                <p><strong>Status:</strong> <span id="vd_status"></span></p>
                <p><strong>Remark:</strong> <span id="vd_remark"></span></p>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

<!-- update device details -->
<div class="modal fade" id="editDeviceModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">

            <div class="modal-header">
                <h5>Edit Device</h5>
            </div>

            <div class="modal-body">

                <input type="hidden" name="device_id" id="ed_id">

                <input type="text" name="item_name" id="ed_name" class="form-control mb-2" placeholder="Name">

                <input type="text" name="inventory_tag" id="ed_tag" class="form-control mb-2">

                <input type="text" name="property_no" id="ed_property" class="form-control mb-2">

                <input type="text" name="custodian" id="ed_custodian" class="form-control mb-2">

                <input type="text" name="brand_model" id="ed_brand" class="form-control mb-2">

                <input type="text" name="serial_no" id="ed_serial" class="form-control mb-2">

                <input type="date" name="date_acquired" id="ed_date" class="form-control mb-2">

                <input type="number" name="acquisition_cost" id="ed_cost" class="form-control mb-2">

                <input type="text" name="location" id="ed_location" class="form-control mb-2">

                <input type="text" name="status" id="ed_status" class="form-control mb-2">

                <textarea name="remark" id="ed_remark" class="form-control mb-2"></textarea>

            </div>

            <div class="modal-footer">
                <button class="btn btn-success" name="update_device">Update</button>
            </div>

        </form>
    </div>
</div>

<!-- For Borrowing a device -->
<div class="modal fade" id="borrowDeviceModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">

            <div class="modal-header">
                <h5>Borrow Device</h5>
            </div>

            <div class="modal-body">

                <!-- SELECT DEVICE -->
                <select name="device_id" id="deviceSelect" class="form-control mb-2" required>
                    <option value="">Select Device</option>

                    <?php
                    $availableDevices = $conn->query("
                            SELECT device_id, property_no, serial_no 
                            FROM psa_devices 
                            WHERE status = 'Available'
                        ");

                    while ($d = $availableDevices->fetch_assoc()):
                    ?>
                        <option
                            value="<?= $d['device_id'] ?>"
                            data-serial="<?= htmlspecialchars($d['serial_no']) ?>">
                            <?= $d['property_no'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <input type="text" id="serialField" class="form-control mb-2" placeholder="Serial Number" readonly>

                <input type="text" name="borrower_name" class="form-control mb-2" placeholder="Borrower Name" required>

                <input type="date" name="date_borrowed" class="form-control" required>

            </div>

            <div class="modal-footer">
                <button class="btn btn-dark" name="borrow_device">Borrow</button>
            </div>

        </form>
    </div>
</div>

<!-- Return a Device -->
<div class="modal fade" id="returnDeviceModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">

            <div class="modal-header">
                <h5>Return Device</h5>
            </div>

            <div class="modal-body">

                <!-- SELECT BORROWED DEVICE -->
                <select name="device_id" id="returnDeviceSelect" class="form-control mb-2" required>
                    <option value="">Select Device</option>

                    <?php
                    $borrowedDevices = $conn->query("
                            SELECT d.device_id, d.property_no, d.serial_no, b.borrower_name
                            FROM psa_devices d
                            JOIN psa_device_borrow b ON b.device_id = d.device_id
                            WHERE d.status = 'Borrowed'
                            AND b.borrow_id = (
                                SELECT MAX(b2.borrow_id)
                                FROM psa_device_borrow b2
                                WHERE b2.device_id = d.device_id
                            )
                        ");

                    while ($d = $borrowedDevices->fetch_assoc()):
                    ?>
                        <option
                            value="<?= $d['device_id'] ?>"
                            data-serial="<?= htmlspecialchars($d['serial_no']) ?>"
                            data-borrower="<?= htmlspecialchars($d['borrower_name']) ?>">
                            <?= $d['property_no'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <!-- SERIAL AUTO -->
                <input type="text" id="returnSerialField" class="form-control mb-2" placeholder="Serial Number" readonly>

                <!-- ADD BORROWER FIELD -->
                <input type="text" id="returnBorrowerField" class="form-control mb-2" placeholder="Borrower Name" readonly>

                <!-- RETURN DATE -->
                <input type="date" name="date_returned" class="form-control" required>

            </div>

            <div class="modal-footer">
                <button class="btn btn-success" name="return_device">Confirm Return</button>
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

                <select name="condition_status" class="form-control mb-2">
                    <option value="Good">Good</option>
                    <option value="Fair">Fair</option>
                    <option value="Damaged">Damaged</option>
                    <option value="For Repair">For Repair</option>
                    <option value="Unserviceable">Unserviceable</option>
                </select>

                <input type="text" name="location" class="form-control mb-2" placeholder="Location">
                <input type="text" name="description" class="form-control mb-2" placeholder="Description">

                <input type="date" name="date_acquired" class="form-control mb-2">
                <input type="number" step="0.01" name="acquisition_cost" class="form-control mb-2" placeholder="Acquisition Cost">

                <input type="text" name="custodian" class="form-control mb-2" placeholder="Custodian / Accountable Person" required>
            </div>

            <div class="modal-footer">
                <button class="btn btn-warning" name="add_asset">Save</button>
            </div>

        </form>
    </div>
</div>

<!-- view asset details -->
<div class="modal fade" id="viewAssetModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Asset Details</h5>
            </div>

            <div class="modal-body">
                <p><strong>Name:</strong> <span id="va_name"></span></p>
                <p><strong>Property No:</strong> <span id="va_property"></span></p>
                <p><strong>Brand:</strong> <span id="va_brand"></span></p>
                <p><strong>Condition:</strong> <span id="va_condition"></span></p>
                <p><strong>Location:</strong> <span id="va_location"></span></p>
                <p><strong>Date Acquired:</strong> <span id="va_date"></span></p>
                <p><strong>Acquisition Cost:</strong> ₱<span id="va_cost"></span></p>
                <p><strong>Custodian:</strong> <span id="va_custodian"></span></p>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

<!-- update asset details -->
<div class="modal fade" id="editAssetModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">

            <div class="modal-header">
                <h5>Edit Asset</h5>
            </div>

            <div class="modal-body">

                <input type="hidden" name="asset_id" id="ea_id">

                <input type="text" name="item_name" id="ea_name" class="form-control mb-2">

                <input type="text" name="property_no" id="ea_property" class="form-control mb-2">

                <input type="text" name="brand" id="ea_brand" class="form-control mb-2">

                <input type="text" name="condition_status" id="ea_condition" class="form-control mb-2">

                <input type="text" name="location" id="ea_location" class="form-control mb-2">

                <input type="date" name="acquisition_date" id="ea_date" class="form-control mb-2">

                <input type="number" name="acquisition_cost" id="ea_cost" class="form-control mb-2">

                <input type="text" name="custodian" id="ea_custodian" class="form-control mb-2">

            </div>

            <div class="modal-footer">
                <button class="btn btn-success" name="update_asset">Update</button>
            </div>

        </form>
    </div>
</div>