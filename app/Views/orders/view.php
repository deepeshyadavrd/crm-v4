<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">
                Order #<?= (int) $order['order_id'] ?>
            </h1>
            <div class="text-muted small">
                <?= esc($order['date_added']) ?>
            </div>
        </div>

        <a href="<?= site_url('orders') ?>" class="btn btn-outline-secondary">
            Back to Orders
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= esc(
                session()->getFlashdata('success')
            ) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= esc(
                session()->getFlashdata('error')
            ) ?>
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <!-- Customer -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <strong>Customer Details</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="text-muted small">
                            Name
                        </div>
                        <div>
                            <?= esc(
                                trim(
                                    $order['firstname'] .
                                    ' ' .
                                    $order['lastname']
                                )
                            ) ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small">
                            Email
                        </div>
                        <div>
                            <?= esc($order['email']) ?>
                        </div>
                    </div>
                    <div>
                        <div class="text-muted small">
                            Telephone
                        </div>
                        <div>
                            <?= esc($order['telephone']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CRM Ownership -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <strong>CRM Ownership</strong>
                </div>
                <div class="card-body">
                    <?php if (!empty($ownership)): ?>
                        <div class="mb-2">
                            <div class="text-muted small">
                                Created By
                            </div>
                            <div>
                                <?= esc(
                                    trim(
                                        ($ownership['firstname'] ?? '') .
                                        ' ' .
                                        ($ownership['lastname'] ?? '')
                                    )
                                ) ?>
                            </div>
                        </div>
                        <div>
                            <div class="text-muted small">
                                CRM Date
                            </div>
                            <div>
                                <?= esc(
                                    $ownership['date_added']
                                ) ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <span class="text-muted">
                            No CRM ownership record.
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Status -->
        <div class="col-12">
            <div class="card">
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
                                        required
                                    >
                                        <?php foreach (
                                            $statuses
                                            as $status
                                        ): ?>
                                            <option
                                                value="<?= (int) $status['order_status_id'] ?>"
                                                <?= (
                                                    (int) $order['order_status_id'] ===
                                                    (int) $status['order_status_id']
                                                ) ? 'selected' : '' ?>
                                            >
                                                <?= esc(
                                                    $status['name']
                                                ) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-5">
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
                                <div class="col-auto">
                                    <button
                                        type="submit"
                                        class="btn btn-primary"
                                    >
                                        Update Status
                                    </button>
                                </div>
                            </div>
                        </form>
                    <?php else: ?>
                        <span class="badge bg-secondary">
                            <?= esc(
                                $order['order_status']
                                ?? 'Unknown'
                            ) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Addresses -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <strong>Payment Address</strong>
                </div>
                <div class="card-body">
                    <?= esc(
                        trim(
                            $order['payment_firstname'] .
                            ' ' .
                            $order['payment_lastname']
                        )
                    ) ?>
                    <br>
                    <?= esc(
                        $order['payment_address_1']
                    ) ?>
                    <?php if (
                        !empty($order['payment_address_2'])
                    ): ?>
                        <br>
                        <?= esc(
                            $order['payment_address_2']
                        ) ?>
                    <?php endif; ?>
                    <br>
                    <?= esc(
                        $order['payment_city']
                    ) ?>
                    -
                    <?= esc(
                        $order['payment_postcode']
                    ) ?>
                    <br>
                    <?= esc(
                        $order['payment_zone']
                    ) ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <strong>Shipping Address</strong>
                </div>
                <div class="card-body">
                    <?php if (
                        !empty($order['shipping_address_1'])
                    ): ?>
                        <?= esc(
                            trim(
                                $order['shipping_firstname'] .
                                ' ' .
                                $order['shipping_lastname']
                            )
                        ) ?>
                        <br>
                        <?= esc(
                            $order['shipping_address_1']
                        ) ?>
                        <?php if (
                            !empty($order['shipping_address_2'])
                        ): ?>
                            <br>
                            <?= esc(
                                $order['shipping_address_2']
                            ) ?>
                        <?php endif; ?>
                        <br>
                        <?= esc(
                            $order['shipping_city']
                        ) ?>
                        -
                        <?= esc(
                            $order['shipping_postcode']
                        ) ?>
                    <?php else: ?>
                        <span class="text-muted">
                            No shipping address.
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Products -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <strong>Products</strong>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Model</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (!empty($products)): ?>
                                <?php foreach (
                                    $products
                                    as $product
                                ): ?>
                                    <tr>
                                        <td>
                                            <?= esc(
                                                $product['name']
                                            ) ?>
                                            <?php if (
                                                !empty(
                                                    $product['options']
                                                )
                                            ): ?>
                                                <?php foreach (
                                                    $product['options']
                                                    as $option
                                                ): ?>
                                                    <div class="small text-muted">
                                                        <?= esc(
                                                            $option['name']
                                                        ) ?>:
                                                        <?= esc(
                                                            $option['value']
                                                        ) ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= esc(
                                                $product['model']
                                            ) ?>
                                        </td>
                                        <td class="text-center">
                                            <?= (int) $product['quantity'] ?>
                                        </td>
                                        <td class="text-end">
                                            <?= number_format(
                                                (float) $product['price'],
                                                2
                                            ) ?>
                                        </td>
                                        <td class="text-end">
                                            <?= number_format(
                                                (float) $product['total'],
                                                2
                                            ) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td
                                        colspan="5"
                                        class="text-center text-muted py-4"
                                    >
                                        No products found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Totals -->
        <div class="col-lg-6 ms-auto">
            <div class="card">
                <div class="card-header">
                    <strong>Order Total</strong>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <tbody>
                            <?php foreach (
                                $totals
                                as $total
                            ): ?>
                                <tr>
                                    <td>
                                        <?= esc(
                                            $total['title']
                                        ) ?>
                                    </td>
                                    <td class="text-end">
                                        <?= esc(
                                            $order['currency_code']
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
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <strong>Order History</strong>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Comment</th>
                                <th>Notified</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($history)): ?>
                                <?php foreach (
                                    $history
                                    as $item
                                ): ?>
                                    <tr>
                                        <td>
                                            <?= esc(
                                                $item['date_added']
                                            ) ?>
                                        </td>
                                        <td>
                                            <?= esc(
                                                $item['status_name']
                                                ?? ''
                                            ) ?>
                                        </td>
                                        <td>
                                            <?= nl2br(
                                                esc(
                                                    $item['comment']
                                                )
                                            ) ?>
                                        </td>
                                        <td>
                                            <?php if (
                                                !empty(
                                                    $item['notify']
                                                )
                                            ): ?>
                                                Yes
                                            <?php else: ?>
                                                No
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td
                                        colspan="4"
                                        class="text-center text-muted py-4"
                                    >
                                        No order history found.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>