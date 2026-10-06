<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">Orders</h1>
            <div class="text-muted small">
                <?= number_format($total) ?> orders
            </div>
        </div>
        <?php if ($canCreate): ?>
            <a href="<?= site_url('orders/create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i>Create Order</a>
        <?php endif; ?>
    </div>


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

    <!-- Search -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="get" action="<?= site_url('orders') ?>">
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label"> Search Orders </label>
                        <input type="text" name="search" class="form-control" value="<?= esc($search) ?>" placeholder="Order ID, invoice, name, email, mobile...">
                    </div>
                    <div class="col-auto d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>

                    <?php if ($search !== ''): ?>
                        <div class="col-auto d-flex align-items-end">
                            <a href="<?= site_url('orders') ?>" class="btn btn-outline-secondary"> Reset </a>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Date</th>
                        <?php if ($canViewAll): ?>
                            <th>Created By</th>
                        <?php endif; ?>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>
                                    <strong>#<?= (int) $order['order_id'] ?></strong>
                                    <?php if (!empty($order['invoice_no'])): ?>
                                        <div class="small text-muted">
                                            Invoice: <?= esc($order['invoice_no']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= esc(trim($order['firstname'] .' ' .$order['lastname'])) ?>
                                </td>
                                <td>
                                    <div>
                                        <?= esc($order['telephone']) ?>
                                    </div>
                                    <?php if (!empty($order['email'])): ?>
                                        <div class="small text-muted">
                                            <?= esc($order['email']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= esc($order['order_status_name']?? 'Unknown') ?>

                                    </span>

                                </td>


                                <td>

                                    <?= esc(
                                        $order['currency_code']
                                    ) ?>

                                    <?= number_format(
                                        (float) $order['total'],
                                        2
                                    ) ?>

                                </td>


                                <td>

                                    <?= esc(
                                        $order['date_added']
                                    ) ?>

                                </td>


                                <?php if ($canViewAll): ?>

                                    <td>

                                        <?php
                                        $createdBy =
                                            trim(
                                                ($order['crm_created_by_firstname'] ?? '') .
                                                ' ' .
                                                ($order['crm_created_by_lastname'] ?? '')
                                            );
                                        ?>

                                        <?php if ($createdBy !== ''): ?>

                                            <?= esc($createdBy) ?>

                                        <?php else: ?>

                                            <span class="text-muted">
                                                —
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                <?php endif; ?>


                                <td class="text-end">

                                    <a
                                        href="<?= site_url(
                                            'orders/view/' .
                                            (int) $order['order_id']
                                        ) ?>"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        View
                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td
                                colspan="<?= $canViewAll ? 8 : 7 ?>"
                                class="text-center text-muted py-5"
                            >
                                No orders found.
                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>


        <?php if ($total > 0): ?>

            <div class="card-footer">

                <?= $pager->links() ?>

            </div>

        <?php endif; ?>

    </div>

</div>