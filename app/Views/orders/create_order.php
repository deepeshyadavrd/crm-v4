<div class="container-fluid py-3">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">Create Order</h4>
            <small class="text-muted"> Create a new CRM order </small>
        </div>

        <a href="<?= base_url('orders'); ?>" class="btn btn-outline-secondary"> Back to Orders</a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= esc(session()->getFlashdata('error')); ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= esc(session()->getFlashdata('success')); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($validation)): ?>
        <?php if ($validation->getErrors()): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($validation->getErrors() as $error ): ?>
                        <li><?= esc($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <form method="post" action="<?= base_url('orders/create'); ?>" enctype="multipart/form-data" id="createOrderForm">
        <?= csrf_field(); ?>

        <!-- CUSTOMER -->
        <div class="card mb-3">
            <div class="card-header">
                <strong>Customer Details</strong>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label"> First Name <span class="text-danger">*</span> </label>
                        <input type="text" name="firstname" class="form-control" value="<?= esc( $formData['firstname'] ?? '' ); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"> Last Name </label>
                        <input type="text" name="lastname" class="form-control" value="<?= esc( $formData['lastname'] ?? '' ); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"> Contact <span class="text-danger">*</span> </label>
                        <input type="text" name="telephone" class="form-control" value="<?= esc( $formData['telephone'] ?? '' ); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"> Email Address </label>
                        <input type="email" name="email" class="form-control" value="<?= esc( $formData['email'] ?? '' ); ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- BILLING ADDRESS -->
        <div class="card mb-3">
            <div class="card-header">
                <strong>Customer Address</strong>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label"> First Name </label>
                        <input type="text" name="payment_firstname" class="form-control" value="<?= esc( $formData['payment_firstname'] ?? '' ); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"> Last Name </label>
                        <input type="text" name="payment_lastname" class="form-control" value="<?= esc( $formData['payment_lastname'] ?? '' ); ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label"> Address <span class="text-danger">*</span> </label>
                        <input type="text" name="payment_address_1" class="form-control" value="<?= esc( $formData['payment_address_1'] ?? '' ); ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label"> Address 2 </label>
                        <input type="text" name="payment_address_2" class="form-control" value="<?= esc( $formData['payment_address_2'] ?? '' ); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"> City <span class="text-danger">*</span> </label>
                        <input type="text" name="payment_city" class="form-control" value="<?= esc( $formData['payment_city'] ?? '' ); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"> Postcode <span class="text-danger">*</span> </label>
                        <input type="text" name="payment_postcode" class="form-control" value="<?= esc( $formData['payment_postcode'] ?? '' ); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"> Country <span class="text-danger">*</span> </label>
                        <select name="payment_country_id" id="payment_country_id" class="form-select" required >
                            <option value="">Select Country</option>
                            <?php 
                            $india = null;
                            $otherCountries = [];
                            foreach ($countries as $country) {
                                if (strtolower(trim($country['name'])) === 'india') {
                                    $india = $country;
                                } else {
                                    $otherCountries[] = $country;
                                }
                            }
                            ?>

                            <?php if ($india) { ?>
                                <option value="<?= $india['country_id']; ?>" selected >
                                    <?= esc($india['name']); ?>
                                </option>
                            <?php } ?>
                            
                            <?php foreach ($otherCountries as $country) { ?>
                                <option value="<?= $country['country_id']; ?>" >
                                    <?= esc($country['name']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"> State <span class="text-danger">*</span> </label>
                        <select name="payment_zone_id" id="payment_zone_id" class="form-select" required>
                            <option value="">Select State</option>
                            <?php foreach ($zones as $zone) { ?>
                                <option value="<?= $zone['zone_id']; ?>">
                                    <?= esc($zone['name']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- ORDER DETAILS -->
        <div class="card mb-3">
            <div class="card-header">
                <strong>Order Details</strong>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label"> Order Source </label>
                        <select name="order_source" class="form-select">
                            <option value="">Select Source</option>
                            <option value="CRM" 
                                <?= (($formData['order_source'] ?? '') === 'CRM' ) ? 'selected' : '' ?>> CRM 
                            </option>
                            <option value="Website"
                                <?= (($formData['order_source'] ?? '') === 'Website') ? 'selected' : '' ?>> Website
                            </option>
                            <option value="Phone"
                                <?= (($formData['order_source'] ?? '') === 'Phone' ) ? 'selected' : '' ?>> Phone
                            </option>
                            <option value="WhatsApp"
                                <?= (($formData['order_source'] ?? '')) ? 'selected' : '' ?>>WhatsApp
                            </option>
                            <option value="Walk-in"
                                <?= (($formData['order_source'] ?? '') === 'Walk-in') ? 'selected' : '' ?>> Walk-in
                            </option>
                            <option value="Other"
                                <?= (($formData['order_source'] ?? '') === 'Other' ) ? 'selected' : '' ?>> Other
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"> Dispatch Deadline </label>
                        <input type="date" name="dispatch_deadline" class="form-control" value="<?= esc( $formData['dispatch_deadline'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Delivery Date</label>
                        <input type="date" name="delivery_date" class="form-control" value="<?= esc( $formData['delivery_date'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Final Status<span class="text-danger">*</span></label>
                        <select name="order_status_id" class="form-select" required>
                            <option value=""> Select Status </option>
                            <?php foreach ($orderStatuses ?? [] as $status): ?>
                                <option value="<?= (int) $status['order_status_id']; ?>"
                                    <?= ((int) ($formData['order_status_id'] ?? 0) === (int) $status['order_status_id']) ? 'selected' : '' ?> >
                                    <?= esc($status['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- PRODUCTS -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Products</strong>
                <button type="button" class="btn btn-primary btn-sm" id="addProduct"> + Add Product </button>
            </div>
            <div class="card-body">
                <div id="productRows"></div>
                <div id="noProducts" class="text-center text-muted py-4">
                    No products added.
                </div>
            </div>
        </div>

        <!-- PAYMENT -->
        <div class="card mb-3">
            <div class="card-header">
                <strong>Payment Details</strong>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label"> Total Bill Value </label>
                        <input type="text" id="grandTotalDisplay" class="form-control" value="0.00" readonly>
                        <input type="hidden" name="calculated_total" id="calculatedTotal" value="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Advance</label>
                        <input type="number" name="advance" id="advance" class="form-control" value="<?= esc( $formData['advance'] ?? '0' ); ?>" min="0" step="0.01">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"> Due Amount </label>
                        <input type="text" id="dueAmount" class="form-control" value="0.00" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"> Payment Status </label>
                        <input type="text" id="paymentStatus" class="form-control" value="Pending" readonly>
                    </div>


                    <div class="col-md-4">
                        <label class="form-label">
                            Payment Method
                        </label>
                        <select name="payment_method" id="payment_method" class="form-select">
                            <option value=""> Select Payment Method </option>
                            <option value="Cash"> Cash </option>
                            <option value="UPI"> UPI </option>
                            <option value="Bank Transfer"> Bank Transfer </option>
                            <option value="Card"> Card </option>
                            <option value="Cheque"> Cheque </option>
                            <option value="Other"> Other </option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label"> Payment Reference </label>
                        <input type="text" name="payment_reference" class="form-control" value="<?= esc( $formData['payment_reference' ] ?? '' ); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"> Payment Date </label>
                        <input type="date" name="payment_date" class="form-control" value="<?= esc($formData[ 'payment_date'] ?? date('Y-m-d')); ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label"> Payment Comment </label>

                        <input type="text" name="payment_comment" class="form-control" value="<?= esc($formData['payment_comment'] ?? ''); ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- INTERNAL COMMENT -->
        <div class="card mb-3">
            <div class="card-header">
                <strong>Internal Comment</strong>
            </div>
            <div class="card-body">
                <textarea name="comment" class="form-control" rows="3"><?= esc($formData['comment'] ?? '' ); ?></textarea>
            </div>
        </div>

        <!-- SUBMIT -->
        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="<?= base_url('orders'); ?>" class="btn btn-secondary"> Cancel </a>
            <button type="submit" class="btn btn-success" id="createOrderButton"> Create Order </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let productIndex = 0;

    /* Add product row */
    document.getElementById('addProduct').addEventListener('click', function () {
        addProductRow();
    });

    function addProductRow() {

        const container = document.getElementById('productRows');
        const noProducts = document.getElementById('noProducts');

        noProducts.classList.add('d-none');

        const index = productIndex++;
        const row = document.createElement('div');
        row.className = 'border rounded p-3 mb-3';
        row.dataset.productRow = index;
        row.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <strong>Product ${index + 1}</strong>
                <button type="button" class="btn btn-outline-danger btn-sm removeProduct"> Remove </button>
            </div>
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label"> Product Name <span class="text-danger">*</span> </label>
                    <input type="text" name="products[${index}][name]" class="form-control productSearch" placeholder="Search product or enter custom product name" autocomplete="off" required>

                    <div class="list-group productSearchResults mt-1"></div>
                    <input type="hidden" name="products[${index}][product_id]" class="productId" value="0">
                    <input type="hidden" name="products[${index}][model]" class="productModel">
                    <small class="text-muted"> Select an existing product or enter a custom product name. </small>
                </div>

                <div class="col-md-4">
                    <label class="form-label"> Quantity <span class="text-danger">*</span> </label>
                    <input type="number" name="products[${index}][quantity]" class="form-control productQuantity" value="1" min="1" step="1" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label"> Selling Price <span class="text-danger">*</span> </label>
                    <input type="number" name="products[${index}][price]" class="form-control productPrice" value="0" min="0" step="0.01" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label"> Product Total </label>
                    <input type="text" class="form-control productTotal" value="0.00" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label"> Vendor </label>
                    <input type="text" name="products[${index}][vendor]" class="form-control" placeholder="Vendor / Manufacturer">
                </div>

                <div class="col-md-4">
                    <label class="form-label"> Vendor Price </label>
                    <input type="number" name="products[${index}][vendor_price]" class="form-control" value="" min="0" step="0.01" placeholder="Not decided">
                    <small class="text-muted"> Per unit </small>
                </div>

                <div class="col-md-8">
                    <label class="form-label"> Approved 3D Design Files </label>

                    <input type="file" name="design_files[${index}][]" class="form-control" multiple accept="image/*,.pdf">
                    <small class="text-muted"> You can select multiple files. </small>
                </div>
            </div>
        `;

        container.appendChild(row);
        attachProductEvents(row);
    }


    /* Product row events */
    function attachProductEvents(row) {
        const quantity = row.querySelector('.productQuantity');
        const price = row.querySelector('.productPrice');
        const total = row.querySelector('.productTotal');
        function calculateProductTotal() {
            const qty = parseFloat(quantity.value) || 0;
            const sellingPrice = parseFloat(price.value) || 0;
            const productTotal = qty * sellingPrice;
            total.value = productTotal.toFixed(2);

            calculateGrandTotal();
        }

        quantity.addEventListener('input', calculateProductTotal);
        price.addEventListener('input', calculateProductTotal );

        /* Remove product */
        row.querySelector('.removeProduct') .addEventListener('click', function () {
            row.remove();
            calculateGrandTotal();

            if (document.querySelectorAll('[data-product-row]').length === 0 ) {
                document.getElementById('noProducts').classList.remove('d-none');
            }
        });

        /* Product search */
        const searchInput = row.querySelector('.productSearch');
        const results = row.querySelector('.productSearchResults');

        let searchTimer = null;

        searchInput.addEventListener( 'input',
            function () {
                const term = searchInput.value.trim();
                clearTimeout(searchTimer);
                if (term.length < 2) {
                    results.innerHTML = '';

                    return;
                }

                searchTimer = setTimeout(
                    function () {

                        fetch('<?= base_url('leads/searchProduct'); ?>?term=' + encodeURIComponent(term))
                        .then(response => response.json())
                        .then(data => {

                            results.innerHTML = '';

                            data.forEach(function (product) {
                                const item = document.createElement('button');
                                item.type = 'button';
                                item.className = 'list-group-item list-group-item-action';
                                item.innerHTML = `<strong>${escapeHtml(product.name)}</strong>`;
                                item.addEventListener('click',
                                    function () {
                                        searchInput.value = product.name;
                                        row.querySelector('.productId').value = product.id;
                                        // row.querySelector('.productName').value = product.name;
                                        row.querySelector('.productModel').value = product.model || '';

                                        results.innerHTML = '';

                                        /* Use catalogue price as initial selling price. Salesperson can then change it. */
                                        const priceValue = product.special_price ? product.special_price : product.original_price;
                                        const initialPrice = parseFloat(String(priceValue).replace(/,/g, ''));
                                        price.value = isNaN(initialPrice) ? 0 : initialPrice;

                                        calculateProductTotal();

                                    }
                                );

                                results.appendChild(item);
                            });
                        })
                        .catch(function () {
                            results.innerHTML = '<div class="list-group-item text-danger">' + 'Unable to search products.' + '</div>';
                        });
                    },
                    300
                );
            }
        );
    }

    /* Grand total */
    function calculateGrandTotal() {
        let grandTotal = 0;
        document .querySelectorAll('.productTotal') .forEach(function (field) {
            grandTotal += parseFloat(field.value) || 0;
        });
        document.getElementById('grandTotalDisplay').value = grandTotal.toFixed(2);
        document.getElementById('calculatedTotal').value = grandTotal.toFixed(2);
        calculateDue();
    }

    /* Advance / Due / Payment Status */
    function calculateDue() {
        const total = parseFloat(document.getElementById('calculatedTotal').value ) || 0;
        let advance = parseFloat( document.getElementById('advance').value) || 0;

        if (advance < 0) {
            advance = 0;
        }

        if (advance > total) {
            advance = total;
            document.getElementById('advance').value = total.toFixed(2);
        }

        const due = Math.max(0, total - advance );
        document.getElementById('dueAmount').value = due.toFixed(2);
        let status = 'Pending';
        if (advance >= total && total > 0) {
            status = 'Paid';
        } else if (advance > 0) {
            status = 'Partially Paid';
        }

        document.getElementById('paymentStatus').value = status;
    }


    document.getElementById('advance').addEventListener('input',calculateDue);

    /* HTML escape */
    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';

        return div.innerHTML;
    }

    /* Add first product automatically */
    addProductRow();

    /* Prevent accidental submit without products */
    document.getElementById('createOrderForm').addEventListener('submit',
        function (event) {
            const products = document.querySelectorAll('[data-product-row]');
            if (products.length === 0) {
                event.preventDefault();
                alert('Please add at least one product.');

                return;
            }

            let invalidProduct = false;

            products.forEach(function (row) {
                const productName = row.querySelector('.productSearch').value.trim();
                if (!productName) {
                    invalidProduct = true;
                }
            });

            if (invalidProduct) {
                event.preventDefault();
                alert('Please enter a product for every product row.');

                return;
            }
            document.getElementById('createOrderButton').disabled = true;
        }
    );
});
</script>