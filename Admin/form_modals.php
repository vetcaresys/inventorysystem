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
                <input type="number" step="0.01" name="price_per_bundle" class="form-control mb-2"
                    placeholder="Bundle Price">
                <input type="number" step="0.01" name="price_per_piece" class="form-control mb-2"
                    placeholder="Piece Price">
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
                                SELECT i.item_id, i.item_name, f.price_per_piece
                                FROM psa_forms f
                                JOIN inventory_items i ON i.item_id = f.item_id
                            ");
                            while ($f = $formList->fetch_assoc()):
                                ?>
                                <option value="<?= $f['item_id'] ?>" data-name="<?= $f['item_name'] ?>"
                                    data-price="<?= $f['price_per_piece'] ?>">
                                    <?= $f['item_name'] ?> - ₱<?= $f['price_per_piece'] ?>
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

                <!-- CASH INPUT -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <input type="number" id="cashInput" class="form-control" placeholder="Cash Received">
                    </div>

                    <div class="col-md-6">
                        <input type="text" id="changeField" class="form-control" placeholder="Change" readonly>
                    </div>
                </div>
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