<div class="container-fluid">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <h4 class="mb-1">
                Order #<?= (int) $order['order_id'] ?>
            </h4>

            <div class="text-muted small">
                <?= esc($order['date_added']) ?>
            </div>
        </div>

        <a
            href="<?= site_url('orders') ?>"
            class="btn btn-outline-secondary"
        >
            Back to Orders
        </a>

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

                <div class="card-header">
                    <strong>Customer Details</strong>
                </div>

                <div class="card-body">

                    <div class="mb-2">
                        <strong>Name:</strong>
                        <?= esc(($order['firstname'])  . ' ' . ($order['lastname']) ?? '-') ?>
                    </div>

                    <div class="mb-2">
                        <strong>Email:</strong>
                        <?= esc($order['email'] ?? '-') ?>
                    </div>

                    <div>
                        <strong>Telephone:</strong>
                        <?= esc($order['telephone'] ?? '-') ?>
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

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary"
                        >
                            Edit
                        </button>

                    <?php endif; ?>

                </div>

                <div class="card-body">

                    <?php if ($ownership): ?>

                        <div class="mb-2">
                            <strong>Created By:</strong>
                            <?= esc(
                                trim(
                                    ($ownership['firstname'] ?? '') .
                                    ' ' .
                                    ($ownership['lastname'] ?? '')
                                )
                            ) ?>
                        </div>

                        <div class="mb-2">
                            <strong>CRM Date:</strong>
                            <?= esc(
                                $ownership['date_added'] ?? '-'
                            ) ?>
                        </div>

                        <div class="mb-2">
                            <strong>Order Source:</strong>
                            <?= esc(
                                $ownership['order_source'] ?? '-'
                            ) ?>
                        </div>

                        <div class="mb-2">
                            <strong>Dispatch Deadline:</strong>
                            <?= esc(
                                $ownership['dispatch_deadline'] ?? '-'
                            ) ?>
                        </div>

                        <div>
                            <strong>Delivery Date:</strong>
                            <?= esc(
                                $ownership['delivery_date'] ?? '-'
                            ) ?>
                        </div>

                    <?php else: ?>

                        <span class="text-muted">
                            No CRM record found.
                        </span>

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

                <form
                    method="post"
                    action="<?= site_url(
                        'orders/status/' .
                        (int) $order['order_id']
                    ) ?>"
                >

                    <?= csrf_field() ?>

                    <div class="row g-2 align-items-end">

                        <div class="col-md-4">

                            <label class="form-label">
                                Status
                            </label>

                            <select
                                name="order_status_id"
                                class="form-select"
                            >

                                <?php foreach ($statuses as $status): ?>

                                    <option
                                        value="<?= (int) $status['order_status_id'] ?>"
                                        <?= (
                                            (int) $status['order_status_id'] ===
                                            (int) $order['order_status_id']
                                        )
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >
                                        <?= esc($status['name']) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <div class="col-md-6">

                            <label class="form-label">
                                Comment
                            </label>

                            <input
                                type="text"
                                name="comment"
                                class="form-control"
                                placeholder="Optional comment"
                            >

                        </div>


                        <div class="col-md-2">

                            <button
                                type="submit"
                                class="btn btn-primary w-100"
                            >
                                Update Status
                            </button>

                        </div>

                    </div>

                </form>

            <?php else: ?>

                <span class="badge bg-secondary">
                    <?= esc(
                        $order['order_status'] ?? 'Unknown'
                    ) ?>
                </span>

            <?php endif; ?>

        </div>

    </div>


    <!-- Payment Summary -->
    <div class="card mb-3">

        <div class="card-header d-flex justify-content-between align-items-center">

            <strong>Payment</strong>

            <?php if ($canEdit): ?>

                <button
                    type="button"
                    class="btn btn-sm btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#addPaymentModal"
                >
                    Add Payment
                </button>

            <?php endif; ?>

        </div>


        <div class="card-body">

            <div class="row g-3 mb-3">

                <div class="col-md-4">

                    <div class="border rounded p-3">

                        <div class="text-muted small">
                            Order Total
                        </div>

                        <div class="fs-5 fw-semibold">
                            <?= esc(
                                $order['currency_code'] ?? ''
                            ) ?>
                            <?= number_format(
                                (float) $order['total'],
                                2
                            ) ?>
                        </div>

                    </div>

                </div>


                <div class="col-md-4">

                    <div class="border rounded p-3">

                        <div class="text-muted small">
                            Received
                        </div>

                        <div class="fs-5 fw-semibold text-success">
                            <?= esc(
                                $order['currency_code'] ?? ''
                            ) ?>
                            <?= number_format(
                                $paymentReceived,
                                2
                            ) ?>
                        </div>

                    </div>

                </div>


                <div class="col-md-4">

                    <div class="border rounded p-3">

                        <div class="text-muted small">
                            Due
                        </div>

                        <div class="fs-5 fw-semibold text-danger">
                            <?= esc(
                                $order['currency_code'] ?? ''
                            ) ?>
                            <?= number_format(
                                $paymentDue,
                                2
                            ) ?>
                        </div>

                    </div>

                </div>

            </div>


            <?php if ($payments): ?>

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

                        <tbody>

                            <?php foreach ($payments as $payment): ?>

                                <tr>

                                    <td>
                                        <?= esc(
                                            $payment['payment_date']
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $order['currency_code'] ?? ''
                                        ) ?>
                                        <?= number_format(
                                            (float) $payment['amount'],
                                            2
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $payment['payment_method'] ?? '-'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $payment['payment_reference'] ?? '-'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $payment['comment'] ?? '-'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $payment['created_by_name'] ?? '-'
                                        ) ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="text-muted">
                    No CRM payments recorded.
                </div>

            <?php endif; ?>

        </div>

    </div>


    <!-- Products -->
    <div class="card mb-3">

        <div class="card-header">
            <strong>Products</strong>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-bordered mb-0 align-middle">

                    <thead>

                        <tr>

                            <th>Product</th>
                            <th>Model</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Vendor</th>
                            <th>Vendor Price</th>
                            <th>Production PDF</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php foreach ($products as $product): ?>

                            <?php
                            $orderProductId =
                                (int) $product['order_product_id'];

                            $crmProduct =
                                $crmProducts[$orderProductId]
                                ?? [];
                            ?>

                            <tr>

                                <td>

                                    <strong>
                                        <?= esc(
                                            $product['name']
                                        ) ?>
                                    </strong>


                                    <?php if (!empty($product['options'])): ?>

                                        <div class="small text-muted mt-1">

                                            <?php foreach (
                                                $product['options']
                                                as $option
                                            ): ?>

                                                <div>
                                                    <?= esc(
                                                        $option['name']
                                                    ) ?>:
                                                    <?= esc(
                                                        $option['value']
                                                    ) ?>
                                                </div>

                                            <?php endforeach; ?>

                                        </div>

                                    <?php endif; ?>

                                </td>


                                <td>
                                    <?= esc(
                                        $product['model']
                                    ) ?>
                                </td>


                                <td>
                                    <?= (int) $product['quantity'] ?>
                                </td>


                                <td>
                                    <?= esc(
                                        $order['currency_code'] ?? ''
                                    ) ?>
                                    <?= number_format(
                                        (float) $product['price'],
                                        2
                                    ) ?>
                                </td>


                                <td>
                                    <?= esc(
                                        $order['currency_code'] ?? ''
                                    ) ?>
                                    <?= number_format(
                                        (float) $product['total'],
                                        2
                                    ) ?>
                                </td>


                                <td>
                                    <div id="vendor-<?= $orderProductId ?>" >
                                        <?php if ($canEdit): ?>
                                            <input type="text" name="vendor" value="<?= esc( $crmProduct['vendor'] ?? '' ) ?>" class="form-control form-control-sm mb-2" form="product-form-<?= $orderProductId ?>" placeholder="Vendor">
                                        <?php else: ?>
                                            <?= esc( $crmProduct['vendor'] ?? '-' ) ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div id="vendor-price-<?= $orderProductId ?>" >
                                        <?php if ($canEdit): ?>
                                    
                                            <input type="number" name="vendor_price" value="<?= esc( $crmProduct['vendor_price'] ?? '' ) ?>" class="form-control form-control-sm" form="product-form-<?= $orderProductId ?>" min="0" step="0.01" placeholder="Vendor price">
                                                
                                        <?php else: ?>
                                            <?= esc( $order['currency_code'] ?? '' ) ?>
                                            <?= number_format((float) ( $crmProduct['vendor_price'] ?? 0 ), 2 ) ?>
                                        <?php endif; ?>
                                    </div>
                                            
                                    <?php if ($canEdit): ?>
                                        <form id="product-form-<?= $orderProductId ?>" class="product-update-form mt-2" data-order-id="<?= (int) $order['order_id'] ?>" data-order-product-id="<?= $orderProductId ?>" >
                                    
                                            <?= csrf_field() ?>
                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-primary"
                                            >
                                                Save
                                            </button>
                                    
                                            <span
                                                class="small ms-2 product-update-message"
                                            ></span>
                                    
                                        </form>
                                    
                                    <?php endif; ?>
                                    
                                </td>
                                <td>

                                    <?php
                                    $files =
                                        $crmProduct['files']
                                        ?? [];
                                    ?>

                                    <?php if ($files): ?>

                                        <?php foreach (
                                            $files
                                            as $file
                                        ): ?>

                                            <div class="mb-1">

                                                <a
                                                    href="<?= site_url(
                                                        'orders/file/download/' .
                                                        (int)
                                                        $file[
                                                            'crm_order_product_file_id'
                                                        ]
                                                    ) ?>"
                                                    class="text-decoration-none"
                                                >
                                                    <?= esc(
                                                        $file['file_name']
                                                    ) ?>
                                                </a>

                                            </div>

                                        <?php endforeach; ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            No file
                                        </span>

                                    <?php endif; ?>


                                    <?php if ($canEdit): ?>

                                        <div class="mt-2">

                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary"
                                            >
                                                Upload PDF
                                            </button>

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
                <?= esc(
                    $order['payment_firstname'] ?? ''
                ) ?>
                <?= esc(
                    $order['payment_lastname'] ?? ''
                ) ?>
            </div>

            <?php if (!empty($order['payment_company'])): ?>

                <div>
                    <?= esc(
                        $order['payment_company']
                    ) ?>
                </div>

            <?php endif; ?>

            <div>
                <?= esc(
                    $order['payment_address_1'] ?? ''
                ) ?>
            </div>

            <?php if (!empty($order['payment_address_2'])): ?>

                <div>
                    <?= esc(
                        $order['payment_address_2']
                    ) ?>
                </div>

            <?php endif; ?>

            <div>
                <?= esc(
                    $order['payment_city'] ?? ''
                ) ?>

                <?php if (!empty($order['payment_postcode'])): ?>

                    -
                    <?= esc(
                        $order['payment_postcode']
                    ) ?>

                <?php endif; ?>

            </div>

            <div>
                <?= esc(
                    $order['payment_zone'] ?? ''
                ) ?>

                <?php if (!empty($order['payment_country'])): ?>

                    , <?= esc(
                        $order['payment_country']
                    ) ?>

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
                <?= esc(
                    $order['shipping_firstname'] ?? ''
                ) ?>
                <?= esc(
                    $order['shipping_lastname'] ?? ''
                ) ?>
            </div>

            <?php if (!empty($order['shipping_company'])): ?>

                <div>
                    <?= esc(
                        $order['shipping_company']
                    ) ?>
                </div>

            <?php endif; ?>

            <div>
                <?= esc(
                    $order['shipping_address_1'] ?? ''
                ) ?>
            </div>

            <?php if (!empty($order['shipping_address_2'])): ?>

                <div>
                    <?= esc(
                        $order['shipping_address_2']
                    ) ?>
                </div>

            <?php endif; ?>

            <div>
                <?= esc(
                    $order['shipping_city'] ?? ''
                ) ?>

                <?php if (!empty($order['shipping_postcode'])): ?>

                    -
                    <?= esc(
                        $order['shipping_postcode']
                    ) ?>

                <?php endif; ?>

            </div>

            <div>
                <?= esc(
                    $order['shipping_zone'] ?? ''
                ) ?>

                <?php if (!empty($order['shipping_country'])): ?>

                    , <?= esc(
                        $order['shipping_country']
                    ) ?>

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
                                    <?= esc(
                                        $total['title']
                                    ) ?>
                                </td>

                                <td class="text-end">

                                    <?= esc(
                                        $order['currency_code'] ?? ''
                                    ) ?>

                                    <?= number_format(
                                        (float) $total['value'],
                                        2
                                    ) ?>

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
                                        <?= esc(
                                            $item['date_added']
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $item['status']
                                            ?? '-'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $item['comment']
                                            ?? '-'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $item['username']
                                            ?? '-'
                                        ) ?>
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


<!-- Add Payment Modal -->
<?php if ($canEdit): ?>

    <div
        class="modal fade"
        id="addPaymentModal"
        tabindex="-1"
        aria-hidden="true"
    >

        <div class="modal-dialog">

            <div class="modal-content">

                <form
                    method="post"
                    action="<?= site_url(
                        'orders/payment/add/' .
                        (int) $order['order_id']
                    ) ?>"
                >

                    <?= csrf_field() ?>


                    <div class="modal-header">

                        <h5 class="modal-title">
                            Add Payment
                        </h5>

                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                        ></button>

                    </div>


                    <div class="modal-body">

                        <div class="mb-3">

                            <label class="form-label">
                                Amount
                            </label>

                            <input
                                type="number"
                                name="amount"
                                class="form-control"
                                min="0.01"
                                step="0.01"
                                required
                            >

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Payment Method
                            </label>

                            <input
                                type="text"
                                name="payment_method"
                                class="form-control"
                            >

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Reference
                            </label>

                            <input
                                type="text"
                                name="payment_reference"
                                class="form-control"
                            >

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Payment Date
                            </label>

                            <input
                                type="datetime-local"
                                name="payment_date"
                                class="form-control"
                            >

                        </div>


                        <div class="mb-0">

                            <label class="form-label">
                                Comment
                            </label>

                            <textarea
                                name="comment"
                                class="form-control"
                                rows="3"
                            ></textarea>

                        </div>

                    </div>


                    <div class="modal-footer">

                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Save Payment
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

<?php endif; ?>
<?php if ($canEdit): ?>

<script>

document.querySelectorAll(
    '.product-update-form'
).forEach(function(form) {

    form.addEventListener(
        'submit',
        function(event) {

            event.preventDefault();


            const orderId =
                form.dataset.orderId;

            const orderProductId =
                form.dataset.orderProductId;

            const message =
                form.querySelector(
                    '.product-update-message'
                );

            const formData =
                new FormData(form);


            const vendorInput =
                document.querySelector(
                    '#vendor-' +
                    orderProductId +
                    ' input[name="vendor"]'
                );

            const vendorPriceInput =
                document.querySelector(
                    '#vendor-price-' +
                    orderProductId +
                    ' input[name="vendor_price"]'
                );


            const button =
                form.querySelector(
                    'button[type="submit"]'
                );


            button.disabled = true;

            message.textContent =
                'Saving...';


            fetch(
                '<?= site_url(
                    'orders/product/update'
                ) ?>/' +
                orderId +
                '/' +
                orderProductId,
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

                    message
                        .classList
                        .add('text-danger');

                    message.textContent =
                        data.message;

                    return;
                }


                /*
                 * Update only this product's
                 * displayed values.
                 */
                vendorInput.value =
                    data.vendor;

                vendorPriceInput.value =
                    data.vendor_price;


                message
                    .classList
                    .remove('text-danger');

                message
                    .classList
                    .add('text-success');

                message.textContent =
                    'Saved';


                setTimeout(
                    function() {

                        message.textContent =
                            '';

                    },
                    2000
                );

            })
            .catch(function(error) {

                console.error(error);

                message
                    .classList
                    .remove('text-success');

                message
                    .classList
                    .add('text-danger');

                message.textContent =
                    'Something went wrong.';

            })
            .finally(function() {

                button.disabled = false;

            });

        }
    );

});

</script>

<?php endif; ?>