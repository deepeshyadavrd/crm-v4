<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1"> Order #<?= (int) $order['order_id'] ?> </h4>
            <div class="text-muted small">
                <?= esc($order['date_added']) ?>
            </div>
        </div>
        <a href="<?= site_url('orders') ?>" class="btn btn-outline-secondary" > Back to Orders </a>
    </div>
    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <!-- Customer + CRM Details -->
    <div class="row g-3 mb-3">
        <!-- Customer -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                    <strong>Customer Details</strong>
                    <?php if ($canEdit): ?>
                        <button type="button" class="btn btn-sm btn-outline-primary edit-section" data-section="customer" data-bs-toggle="modal" data-bs-target="#editSectionModal"> Edit </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Name:</strong>
                        <div id="customer-name">
                            <?= esc(($order['firstname'])  . ' ' . ($order['lastname']) ?? '-') ?>
                        </div>
                    </div>
                    <div class="mb-2">
                        <strong>Email:</strong>
                        <div id="customer-email">
                            <?= esc($order['email'] ?? '-') ?>
                        </div>
                    </div>
                    <div>
                        <strong>Telephone:</strong>
                        <div id="customer-telephone">
                            <?= esc($order['telephone'] ?? '-') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CRM Details -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between">
                    <strong>CRM Details</strong>
                    <?php if ($canEdit): ?>
                        <button type="button" class="btn btn-sm btn-outline-primary edit-section" data-section="crm" data-bs-toggle="modal" data-bs-target="#editSectionModal">
                            Edit
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if ($ownership): ?>
                        <div class="mb-2">
                            <strong>Created By:</strong>
                            <?= esc(trim(($ownership['firstname'] ?? '') . ' ' . ($ownership['lastname'] ?? ''))) ?>
                        </div>
                        <div class="mb-2">
                            <strong>CRM Date:</strong>
                            <?= esc($ownership['date_added'] ?? '-') ?>
                        </div>
                        <div class="mb-2">
                            <strong>Order Source:</strong>
                            <div id="crm-order-source">
                                <?= esc($ownership['order_source'] ?? '-') ?>
                            </div>
                        </div>
                        <div class="mb-2">
                            <strong>Dispatch Deadline:</strong>
                            <div id="crm-dispatch-deadline" >
                            <?= esc($ownership['dispatch_deadline'] ?? '-') ?>
                            </div>
                        </div>
                        <div>
                            <strong>Delivery Date:</strong>
                            <div id="crm-delivery-date" >
                                <?= esc($ownership['delivery_date'] ?? '-') ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <span class="text-muted">No CRM record found.</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Status -->
    <div class="card mb-3">
        <div class="card-header">
            <strong>Order Status</strong>
        </div>
        <div class="card-body">
            <?php if ($canEdit): ?>
                <form method="post" id="orderStatusForm" >
                    <?= csrf_field() ?>
                    <input type="hidden" value="status" name="section">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label"> Status </label>
                            <select name="order_status_id" class="form-select">
                                <?php foreach ($statuses as $status): ?>
                                    <option value="<?= (int) $status['order_status_id'] ?>" <?= ( (int) $status['order_status_id'] === (int) $order['order_status_id'] )  ? 'selected' : '' ?> >
                                        <?= esc($status['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"> Comment </label>
                            <input type="text" name="comment" class="form-control" placeholder="Optional comment">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100" id="updateStatusButton"> Update Status </button>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <span class="badge bg-secondary">
                    <?= esc( $order['order_status'] ?? 'Unknown' ) ?>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Payment Summary -->
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Payment</strong>
            <?php if ($canEdit): ?>
                <button type="button" class="btn btn-sm btn-primary" data-section="payment" data-bs-toggle="modal" data-bs-target="#editSectionModal"> Add Payment </button>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <div class="text-muted small">
                            Order Total
                        </div>
                        <div class="fs-5 fw-semibold" id="orderGrandTotal">
                            <?= esc($order['currency_code'] ?? '') ?>
                            <?= number_format((float) $order['total'],2) ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <div class="text-muted small">Received</div>
                        <div class="fs-5 fw-semibold text-success" id="paymentReceived">
                            <?= esc($order['currency_code'] ?? '') ?>
                            <?= number_format($paymentReceived,2) ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <div class="text-muted small">Due</div>
                        <div class="fs-5 fw-semibold text-danger" id="paymentDue">
                            <?= esc($order['currency_code'] ?? '') ?>
                            <?= number_format($paymentDue, 2 ) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th>Comment</th>
                            <th>Added By</th>
                        </tr>
                    </thead>
                    <tbody id="paymentList">
                    <?php if ($payments): ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td>
                                    <?= esc( $payment['payment_date'] ) ?>
                                </td>
                                <td>
                                    <?= esc($order['currency_code'] ?? '') ?>
                                    <?= number_format((float) $payment['amount'],2) ?>
                                </td>
                                <td>
                                    <?= esc($payment['payment_method'] ?? '-') ?>
                                </td>
                                <td>
                                    <?= esc($payment['payment_reference'] ?? '-') ?>
                                </td>
                                <td>
                                    <?= esc($payment['comment'] ?? '-') ?>
                                </td>
                                <td>
                                    <?= esc($payment['created_by_name'] ?? '-') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?> 
                        <tr id="noPaymentsRow"> 
                            <td colspan="6" class="text-muted"> No CRM payments recorded. </td> 
                        </tr> 
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Products -->
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Products</strong>
            <?php if ($canEdit): ?>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editSectionModal" data-section="product-add"> + Add Product </button>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0 align-middle" id="productsTable">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                            <?php if ($canEdit): ?>
                            <th>Vendor</th>
                            <th>Vendor Price</th>
                            <th>Action</th>
                            <?php endif; ?>
                            <th>Production PDF</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <?php
                                $orderProductId = (int) $product['order_product_id'];
                                $crmProduct = $crmProducts[$orderProductId] ?? [];
                            ?>
                            <tr id="product-row-<?= $orderProductId ?>">
                                <td>
                                    <div id="product-name-<?= $orderProductId ?>">
                                        <strong> <?= esc( $product['name'] ) ?> </strong>
                                    </div>
                                </td>
                                <td id="product-quantity-<?= $orderProductId ?>">
                                    <?= (int) $product['quantity'] ?>
                                </td>
                                <td>
                                    <span id="product-price-<?= $orderProductId ?>">
                                        <?= esc( $order['currency_code'] ?? '' ) ?>
                                        <?= number_format( (float) $product['price'], 2 ) ?>
                                    </span>
                                </td>
                                <td>
                                    <span id="product-total-<?= $orderProductId ?>">
                                        <?= esc( $order['currency_code'] ?? '' ) ?>
                                        <?= number_format( (float) $product['total'], 2 ) ?>
                                    </span>
                                </td>
                                <?php if ($canEdit): ?>
                                <td>
                                    <div id="product-vendor-<?= $orderProductId ?>" >
                                        <?= esc( $crmProduct['vendor'] ?? '' ) ?>
                                    </div>
                                </td>
                                <td>
                                    <div id="product-vendor-price-<?= $orderProductId ?>" >
                                        <?php if ($crmProduct['vendor_price'] !== null && $crmProduct['vendor_price'] !== ''): ?>
                                        <?= esc($order['currency_code'] ?? '') ?>
                                        <?= number_format((float) $crmProduct['vendor_price'], 2) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <button type="button"class="btn btn-sm btn-outline-primary edit-section" data-section="product"  data-order-product-id="<?= $orderProductId ?>" data-bs-toggle="modal" data-bs-target="#editSectionModal">Edit</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-product" data-order-product-id="<?= $orderProductId ?>"> Remove </button>
                                </td>
                                <?php endif; ?>
                                <td>
                                    <?php $files = $crmProduct['files'] ?? []; ?>
                                    <?php if ($files): ?>
                                        <?php foreach ( $files as $file ): ?>
                                            <div class="mb-1">
                                                <a href="<?= site_url('orders/file/download/' . (int) $file['crm_order_product_file_id']) ?>" class="text-decoration-none" >
                                                    <?= esc( $file['file_name'] ) ?>
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted"> No file </span>
                                    <?php endif; ?>
                                    <?php if ($canEdit): ?>
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary" > Upload PDF </button>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Addresses -->
    <div class="row g-3 mb-3">
        <!-- Payment Address -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <strong>Payment Address</strong>
                </div>
                <div class="card-body">
                    <div>
                        <?= esc($order['payment_firstname'] ?? '') ?>
                        <?= esc($order['payment_lastname'] ?? '') ?>
                    </div>
                    <?php if (!empty($order['payment_company'])): ?>
                        <div>
                            <?= esc($order['payment_company']) ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <?= esc($order['payment_address_1'] ?? '' ) ?>
                    </div>
                    <?php if (!empty($order['payment_address_2'])): ?>
                        <div>
                            <?= esc($order['payment_address_2'] ) ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <?= esc($order['payment_city'] ?? '' ) ?>
                        <?php if (!empty($order['payment_postcode'])): ?>
                        -
                        <?= esc($order['payment_postcode']) ?>
                    <?php endif; ?>
                </div>
                <div>
                    <?= esc($order['payment_zone'] ?? '') ?>
                    <?php if (!empty($order['payment_country'])): ?>
                    , <?= esc($order['payment_country']) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Shipping Address -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <strong>Shipping Address</strong>
            </div>
            <div class="card-body">
                <div>
                <?= esc($order['shipping_firstname'] ?? '' ) ?>
                <?= esc($order['shipping_lastname'] ?? '' ) ?>
            </div>
            <?php if (!empty($order['shipping_company'])): ?>
                <div>
                    <?= esc($order['shipping_company']) ?>
                </div>
            <?php endif; ?>
            <div>
                <?= esc($order['shipping_address_1'] ?? '' ) ?>
            </div>
            <?php if (!empty($order['shipping_address_2'])): ?>
                <div>
                    <?= esc($order['shipping_address_2']) ?>
                </div>
            <?php endif; ?>
            <div>
                <?= esc($order['shipping_city'] ?? '') ?>
                <?php if (!empty($order['shipping_postcode'])): ?>
                    -
                <?= esc($order['shipping_postcode']) ?>
                <?php endif; ?>
            </div>
            <div>
                <?= esc($order['shipping_zone'] ?? '') ?>
                <?php if (!empty($order['shipping_country'])): ?>
                    , <?= esc($order['shipping_country']) ?>
                <?php endif; ?>
            </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Totals -->
    <div class="card mb-3">
        <div class="card-header">
            <strong>Order Totals</strong>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <tbody>
                        <?php foreach ($totals as $total): ?>
                            <tr>
                                <td class="text-end">
                                    <?= esc($total['title']) ?>
                                </td>
                                <td class="text-end" id="order-total-<?= esc($total['code']) ?>">
                                    <?= esc($order['currency_code'] ?? '') ?>
                                    <?= number_format((float) $total['value'],2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Order History -->
    <div class="card mb-4">
        <div class="card-header">
            <strong>Order History</strong>
        </div>
        <div class="card-body p-0">
            <?php if ($history): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Comment</th>
                                <th>Added By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $item): ?>
                                <tr>
                                    <td>
                                        <?= esc($item['date_added']) ?>
                                    </td>
                                    <td>
                                        <?= esc($item['status']?? '-') ?>
                                    </td>
                                    <td>
                                        <?= esc($item['comment']?? '-') ?>
                                    </td>
                                    <td>
                                        <?= esc($item['username']?? '-') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-3 text-muted">
                    No order history found.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($canEdit): ?>

<div class="modal fade" id="editSectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editSectionForm">
                <?= csrf_field() ?>
                <input type="hidden" name="section" id="editSection">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSectionTitle"> Edit </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" id="editSectionBody">
                </div>
                <div id="editSectionMessage" class="px-3">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"> Cancel </button>
                    <button type="submit" class="btn btn-primary" id="editSectionSave"> Save </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
    <div id="crmSuccessToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="crmSuccessToastMessage" >
                Details updated successfully.
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
<?php endif; ?>
<?php if ($canEdit): ?>

<script>
function showSuccessToast( message ) {
    const toast = document.getElementById( 'crmSuccessToast' );
    const toastMessage = document.getElementById( 'crmSuccessToastMessage' );

    if (!toast) {
        return;
    }
    if (toastMessage) {
        toastMessage.textContent = message;
    }

    toast.classList.add( 'show' );

    setTimeout(
        function() {
            toast.classList.remove( 'show' );
        },
        4500
    );
}
document.querySelectorAll( '.product-update-form' ).forEach(function(form) {
    form.addEventListener( 'submit', function(event) {
            event.preventDefault();

            const orderId = form.dataset.orderId;
            const orderProductId = form.dataset.orderProductId;
            const message = form.querySelector( '.product-update-message' );

            const formData = new FormData(form);
            const vendorInput = document.querySelector( '#vendor-' + orderProductId + ' input[name="vendor"]' );
            const vendorPriceInput = document.querySelector( '#vendor-price-' + orderProductId + ' input[name="vendor_price"]' );

            const button = form.querySelector( 'button[type="submit"]' );
            button.disabled = true;
            message.textContent = 'Saving...';

            fetch('<?= site_url('orders/product/update') ?>/' + orderId + '/' + orderProductId,
                {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With':
                            'XMLHttpRequest'
                    }
                }
            )
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (!data.success) {
                    message.classList.add('text-danger');
                    message.textContent = data.message;

                    return;
                }

                /* Update only this product's displayed values. */
                vendorInput.value = data.vendor;
                vendorPriceInput.value = data.vendor_price;

                message .classList .remove('text-danger');
                message .classList .add('text-success');
                message.textContent = 'Saved';

                setTimeout(
                    function() {
                        message.textContent = '';
                    },
                    2000
                );

            })
            .catch(function(error) {
                console.error(error);
                message .classList .remove('text-success');
                message .classList .add('text-danger');
                message.textContent = 'Something went wrong.';

            })
            .finally(function() {
                button.disabled = false;
            });
        }
    );
});

</script>

<?php endif; ?>
<?php if ($canEdit): ?>

<script>

const editSectionModal = document.getElementById( 'editSectionModal' );
const editSectionForm = document.getElementById( 'editSectionForm' );
const editSectionBody = document.getElementById( 'editSectionBody' );
const editSectionTitle = document.getElementById( 'editSectionTitle' );
const editSectionMessage = document.getElementById( 'editSectionMessage' );
const editSectionSave = document.getElementById( 'editSectionSave' );

    editSectionModal.addEventListener( 'show.bs.modal', function(event) {

        const button = event.relatedTarget;
        const section = button.dataset.section;

        document.getElementById('editSection').value = section;

        editSectionMessage.textContent = '';
        editSectionMessage.className ='px-3';

        /* CRM section */
        if (section === 'crm') {
            editSectionTitle.textContent ='Edit CRM Details';

            editSectionBody.innerHTML = `
                <div class="mb-3">
                    <label for="editOrderSource" class="form-label"> Order Source </label>
                    <input type="text" class="form-control" name="order_source" id="editOrderSource" >
                </div>
                <div class="mb-3">
                    <label for="editDispatchDeadline" class="form-label"> Dispatch Deadline </label>
                    <input type="date" class="form-control" name="dispatch_deadline" id="editDispatchDeadline">
                </div>
                <div class="mb-3">
                    <label for="editDeliveryDate" class="form-label"> Delivery Date </label>
                    <input type="date" class="form-control" name="delivery_date" id="editDeliveryDate">
                </div>
            `;

            /* Read current values directly from the page. */
            const orderSource = document.getElementById('crm-order-source').textContent.trim();
            const dispatchDeadline = document.getElementById( 'crm-dispatch-deadline' ).textContent.trim();
            const deliveryDate = document.getElementById( 'crm-delivery-date' ).textContent.trim();


            /* Populate modal. */
            document.getElementById('editOrderSource').value = orderSource === '-' ? '' : orderSource;
            document.getElementById('editDispatchDeadline').value = dispatchDeadline === '-' ? '' : dispatchDeadline;
            document.getElementById('editDeliveryDate').value = deliveryDate === '-' ? '' : deliveryDate;
        }
        if (section === 'customer') {

            const customerName = document.getElementById( 'customer-name' ).textContent.trim();
            const customerEmail = document.getElementById( 'customer-email' ).textContent.trim();
            const customerTelephone = document.getElementById( 'customer-telephone' ).textContent.trim();
            const nameParts = customerName.split(/\s+/);
            const firstname = nameParts.shift() || '';
            const lastname = nameParts.join(' ');
            editSectionTitle.textContent = 'Edit Customer Details';

            editSectionBody.innerHTML = `
                <div class="col-md-6 mb-3">
                    <label for="edit_customer_firstname" class="form-label"> First Name </label>
                    <input type="text" class="form-control" name="firstname" id="edit_customer_firstname" value="${escapeHtml(firstname)}" required >
                </div>
                <div class="col-md-6 mb-3">
                    <label for="edit_customer_lastname" class="form-label"> Last Name </label>
                    <input type="text" class="form-control" name="lastname" id="edit_customer_lastname" value="${escapeHtml(lastname)}" >
                </div>
                <div class="mb-3">
                    <label for="edit_customer_email" class="form-label"> Email </label>
                    <input type="email" class="form-control" name="email" id="edit_customer_email" value="${escapeHtml(customerEmail)}">
                </div>

                <div class="mb-3">
                    <label for="edit_customer_telephone" class="form-label" > Telephone </label>
                    <input type="text" class="form-control" name="telephone" id="edit_customer_telephone" value="${escapeHtml(customerTelephone)}">
                </div>
            `;
        }
        if (section === 'payment') {
            editSectionTitle.textContent ='Add Payment Details';

            editSectionBody.innerHTML = `
                <div class="mb-3">
                    <label for="addAmount" class="form-label"> Amount </label>
                    <input type="text" class="form-control" name="amount" id="addAmount" >
                </div>
                <div class="mb-3">
                    <label for="addPaymentMethod" class="form-label"> Payment Method </label>
                    <input type="text" class="form-control" name="payment_method" id="addPaymentMethod">
                </div>
                <div class="mb-3">
                    <label for="addPaymentReference" class="form-label"> Payment Reference </label>
                    <input type="text" class="form-control" name="payment_reference" id="addPaymentReference">
                </div>
                <div class="mb-3">
                    <label for="addPaymentDate" class="form-label"> Payment Date </label>
                    <input type="date" class="form-control" name="payment_date" id="addPaymentDate">
                </div>
                <div class="mb-3">
                    <label for="addPaymentComment" class="form-label"> Payment Comment </label>
                    <input type="text" class="form-control" name="payment_comment" id="addPaymentcomment">
                </div>
            `;
        }
        if (section === 'product') {
            const orderProductId = button.dataset.orderProductId;
            editSectionTitle.textContent = 'Edit Product';
            editSectionBody.innerHTML = `
                <input type="hidden" name="order_product_id" id="editOrderProductId">

                <div class="mb-3">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="name" id="editProductName" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity" id="editProductQuantity" class="form-control" min="1" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Price</label>
                    <input type="number" name="price" id="editProductPrice" class="form-control" min="0" step="0.01" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Vendor</label>
                    <input type="text" name="vendor" id="editProductVendor" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Vendor Price</label>
                    <input type="number" name="vendor_price" id="editProductVendorPrice" class="form-control" min="0" step="0.01">
                </div>
            `;

            document.getElementById('editOrderProductId').value = orderProductId;
            document.getElementById('editProductName').value = document.getElementById('product-name-' + orderProductId).textContent.trim();
            document.getElementById('editProductQuantity').value = document.getElementById('product-quantity-' + orderProductId).textContent.trim();

            let price = document.getElementById('product-price-' + orderProductId).textContent.trim();

            price = price.replace(/[^\d.-]/g, '');
            document.getElementById('editProductPrice').value = price;
            document.getElementById('editProductVendor').value = document.getElementById('product-vendor-' + orderProductId).textContent.trim();
            let vendorPrice = document.getElementById('product-vendor-price-' + orderProductId).textContent.trim();
            vendorPrice = vendorPrice.replace(/[^\d.-]/g, '');

            if (vendorPrice === '-') {
                vendorPrice = '';
            }

            document.getElementById('editProductVendorPrice').value = vendorPrice;
        }
        if (section === 'product-add') {
            // const orderProductId = button.dataset.orderProductId;
            editSectionTitle.textContent = 'Add Product';
            editSectionBody.innerHTML = `
                <input type="hidden" name="product_id" id="addProductId">

                <div class="mb-3">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="name" id="addProductName" class="form-control" required autocomplete="off">
                </div>
                <div id="addProductSearchResults" class="list-group position-absolute" style="z-index: 1056; max-height: 220px; overflow-y: auto;" ></div>

                <div class="mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity" id="addProductQuantity" class="form-control" min="1" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Price</label>
                    <input type="number" name="price" id="addProductPrice" class="form-control" min="0" step="0.01" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Vendor</label>
                    <input type="text" name="vendor" id="addProductVendor" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Vendor Price</label>
                    <input type="number" name="vendor_price" id="addProductVendorPrice" class="form-control" min="0" step="0.01">
                </div>
            `;
            // Initialize autocomplete only after the modal fields exist.
            const productNameInput =  editSectionBody.querySelector('#addProductName');
            const resultsContainer = editSectionBody.querySelector('#addProductSearchResults');
            const priceInput = editSectionBody.querySelector('#addProductPrice');
            const productId = editSectionBody.querySelector('#addProductId');

            if (!productNameInput || !resultsContainer || !priceInput) {
                console.error('Product search elements are missing from the modal.');
                return;
            }

            let searchTimer;
            let activeRequest = 0;
                    
            productNameInput.addEventListener('input', function () {
                const term = this.value.trim();
            
                clearTimeout(searchTimer);
                resultsContainer.innerHTML = '';
            
                if (term.length < 2) {
                    return;
                }
            
                searchTimer = setTimeout(async () => {
                    const requestId = ++activeRequest;
                
                    try {
                        const response = await fetch('<?= base_url('leads/searchProduct'); ?>?term=' + encodeURIComponent(term));
                    
                        if (!response.ok) {
                            throw new Error(`Product search failed: HTTP ${response.status}`);
                        }
                    
                        const data = await response.json();
                    
                        // Adjust to match your existing endpoint's response structure.
                        const products = Array.isArray(data) ? data : (data.products || []);
                    
                        // Ignore stale responses if the user has typed a newer query.
                        if (requestId !== activeRequest) {
                            return;
                        }
                    
                        resultsContainer.innerHTML = '';
                    
                        products.forEach(product => {
                            const item = document.createElement('button');
                        
                            item.type = 'button';
                            item.className = 'list-group-item list-group-item-action';
                            item.textContent = product.name;
                        
                            item.addEventListener('click', function () {
                                productNameInput.value = product.name;
                                productId.value = product.id;
                            
                                if (product.special_price !== undefined &&
                                    product.special_price !== null) {

                                    priceInput.value = product.special_price.replace(/,/g, '');
                                     
                                }
                                resultsContainer.innerHTML = '';
                            });
                            resultsContainer.appendChild(item);
                        });
                    
                    } catch (error) {
                        console.error('Product search error:', error);
                        resultsContainer.innerHTML = '';
                    }
                }, 250);
            });

            // Close suggestions when the user clicks outside the search area.
            document.addEventListener('click', function closeProductResults(event) {
                if (!editSectionBody.contains(event.target)) {
                    resultsContainer.innerHTML = '';
                    document.removeEventListener('click', closeProductResults);
                }
            });

        }
    });

    const orderId = <?= (int) $order['order_id'] ?>;
    const orderUrl = '<?= site_url('orders/update') ?>/' + orderId;
    const currency = '<?= esc($order['currency_code'] ?? '') ?>';

    editSectionForm.addEventListener('submit', handleFormSubmit);

    const editSectionStatusForm = document.getElementById('orderStatusForm');

    if (editSectionStatusForm) {
        editSectionStatusForm.addEventListener('submit', handleFormSubmit);
    }

    /* Common helpers */
    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function setText(id, value) {
        const element = document.getElementById(id);

        if (element) {
            element.textContent = value ?? '-';
        }
    }

    function formatMoney(value) {
        return currency + ' ' + Number(value ?? 0).toFixed(2);
    }

    async function sendOrderUpdate(formData) {
        const response = await fetch(orderUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Unable to update order.');
        }

        return data;
    }

    /* Update order totals and payment summary in one place. */
    function updateOrderAmounts(data) {
        setText('orderGrandTotal', formatMoney(data.order_total));
        setText('paymentReceived', formatMoney(data.payment_received));
        setText('paymentDue', formatMoney(data.payment_due));

        setText('order-total-sub_total', formatMoney(data.subtotal ?? data.order_total) );

        setText('order-total-total', formatMoney(data.order_total));
    }

    /* Create the product row in one place. */
    function createProductRow(product) {
        const id = product.order_product_id;
        const price = product.price;
        const total = product.line_total ?? product.total;

        return `
            <tr id="product-row-${id}">
                <td>
                    <div id="product-name-${id}">
                        <strong>${escapeHtml(product.name)}</strong>
                    </div>
                </td>

                <td id="product-quantity-${id}">
                    ${Number(product.quantity)}
                </td>

                <td>
                    <span id="product-price-${id}">
                        ${formatMoney(price)}
                    </span>
                </td>

                <td>
                    <span id="product-total-${id}">
                        ${formatMoney(total)}
                    </span>
                </td>

                <td>
                    <div id="product-vendor-${id}">
                        ${escapeHtml(product.vendor || '')}
                    </div>
                </td>

                <td>
                    <div id="product-vendor-price-${id}">
                        ${product.vendor_price !== null &&
                          product.vendor_price !== ''
                            ? formatMoney(product.vendor_price)
                            : '-'}
                    </div>
                </td>

                <td>
                    <button type="button" class="btn btn-sm btn-outline-primary edit-section" data-section="product" data-order-product-id="${id}" data-bs-toggle="modal" data-bs-target="#editSectionModal"> 
                        Edit
                    </button>

                    <button type="button" class="btn btn-sm btn-outline-danger remove-product" data-order-product-id="${id}">
                        Remove
                    </button>
                </td>

                <td>
                    <span class="text-muted">No file</span>
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-outline-primary"> Upload PDF </button>
                    </div>
                </td>
            </tr>`;
    }

    /* Update an existing product row. */
    function updateProductRow(data) {
        const id = data.order_product_id;
        const nameElement = document.getElementById('product-name-' + id);

        if (nameElement) {
            nameElement.innerHTML =
                '<strong>' + escapeHtml(data.name) + '</strong>';
        }

        setText('product-quantity-' + id, data.quantity);
        setText('product-price-' + id, formatMoney(data.price));
        setText('product-total-' + id, formatMoney(data.total));
        setText('product-vendor-' + id, data.vendor || '');

        setText('product-vendor-price-' + id, data.vendor_price !== null && data.vendor_price !== '' ? formatMoney(data.vendor_price) : '-' );
    }

    /* Handle all successful AJAX responses. */
    function handleOrderResponse(data) {
        if (data.section === 'crm') {
            setText('crm-order-source', data.order_source || '-');
            setText('crm-dispatch-deadline', data.dispatch_deadline || '-');
            setText('crm-delivery-date', data.delivery_date || '-');

            showSuccessToast('CRM details updated successfully.');
        }

        if (data.section === 'customer') {
            setText('customer-name', `${data.firstname || ''} ${data.lastname || ''}`.trim() || '-' );

            setText('customer-email', data.email || '-');
            setText('customer-telephone', data.telephone || '-');

            showSuccessToast('Customer details updated successfully.');
        }

        if (data.section === 'status') {
            showSuccessToast('Order status updated successfully.');
        }

        if (data.section === 'payment') {
            updateOrderAmounts({ order_total: document.getElementById('orderGrandTotal') ?.textContent?.replace(currency, '').trim(), payment_received: data.payment_received, payment_due: data.payment_due });

            const payment = data.payment;
            const noPaymentsRow = document.getElementById('noPaymentsRow');

            if (noPaymentsRow) {
                noPaymentsRow.remove();
            }

            const paymentList = document.getElementById('paymentList');

            if (paymentList && payment) {
                const row = document.createElement('tr');

                row.innerHTML = `
                    <td>${escapeHtml(payment.payment_date)}</td>
                    <td>${formatMoney(payment.amount)}</td>
                    <td>${escapeHtml(payment.payment_method || '-')}</td>
                    <td>${escapeHtml(payment.payment_reference || '-')}</td>
                    <td>${escapeHtml(payment.comment || '-')}</td>
                    <td>${escapeHtml(payment.created_by_name || '-')}</td>
                `;

                paymentList.prepend(row);
            }

            showSuccessToast(data.message || 'Payment added successfully.');
        }

        if (data.section === 'product') {
            updateProductRow(data);
            updateOrderAmounts(data);

            showSuccessToast('Product updated successfully.');
        }

        /* Product addition response contains data.product. */
        if (data.product) {
            const tbody = document.querySelector('#productsTable tbody');

            if (tbody) {
                tbody.insertAdjacentHTML('beforeend', createProductRow(data.product));
            }

            updateOrderAmounts(data);
            showSuccessToast(data.message || 'Product added successfully.');
        }

        if (data.section === 'product-delete') {
            document.getElementById('product-row-' + data.order_product_id)?.remove();

            updateOrderAmounts(data);
            showSuccessToast(data.message || 'Product removed successfully.');
        }

        /* Close the modal after a successful form submission. */
        const modalElement = document.getElementById('editSectionModal');

        if (modalElement && window.bootstrap) {
            // const modal = bootstrap.Modal.getInstance(modalElement);

            const $modal = $(modalElement);

            if ($modal.hasClass('show')) {
                $modal.modal('hide');
            }
        }
    }

    /* Existing forms use the same submit handler. */
    async function handleFormSubmit(event) {
        event.preventDefault();

        const form = event.currentTarget;
        const saveButton = form === editSectionStatusForm
            ? document.getElementById('updateStatusButton')
            : editSectionSave;

        if (saveButton) {
            saveButton.disabled = true;
            saveButton.textContent = 'Saving...';
        }

        if (editSectionMessage) {
            editSectionMessage.className = 'px-3 text-muted';
            editSectionMessage.textContent = 'Saving...';
        }

        try {
            const data = await sendOrderUpdate(new FormData(form));

            handleOrderResponse(data);

            if (editSectionMessage) {
                editSectionMessage.textContent = '';
            }
        } catch (error) {
            console.error(error);

            if (editSectionMessage) {
                editSectionMessage.className = 'px-3 text-danger';
                editSectionMessage.textContent = error.message;
            } else {
                showSuccessToast(error.message);
            }
        } finally {
            if (saveButton) {
                saveButton.disabled = false;
                saveButton.textContent = form === editSectionStatusForm
                    ? 'Update Status'
                    : 'Save';
            }
        }
    }

    /* Remove products through the same AJAX request helper. */
    document.addEventListener('click', async function(event) {
        const button = event.target.closest('.remove-product');

        if (!button) {
            return;
        }

        if (!confirm('Remove this product and permanently delete its associated files?')) {
            return;
        }

        const orderProductId = button.dataset.orderProductId;
        const formData = new FormData(editSectionForm);

        formData.set('section', 'product-delete');
        formData.set('order_product_id', orderProductId);

        button.disabled = true;

        try {
            const data = await sendOrderUpdate(formData);
            handleOrderResponse(data);
        } catch (error) {
            console.error(error);
            showSuccessToast(error.message || 'Unable to remove product.');
        } finally {
            button.disabled = false;
        }
    });
</script>

<?php endif; ?>