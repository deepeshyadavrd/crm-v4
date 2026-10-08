<?php

namespace App\Controllers;

use App\Models\OrderModel;
use Exception;

class Orders extends WSController
{
    protected OrderModel $orderModel;

    protected int $userId = 0;
    protected int $userGroupId = 0;

    protected string $scope = 'all';

    protected bool $canViewAll = false;
    protected bool $canCreate = false;
    protected bool $canEdit = false;
    protected bool $canDelete = false;


    public function __construct()
    {
        $this->orderModel = new OrderModel();

        $this->userId = (int) session()->get('user_id');
        $this->userGroupId = (int) session()->get('user_group_id');


        /* CRM permissions * 1, 17, 21 Full access * 11 View all orders * 14 Own orders + create */
        if (in_array($this->userGroupId, [1, 17, 21], true)) {

            $this->scope = 'all';

            $this->canViewAll = true;
            $this->canCreate = true;
            $this->canEdit = true;
            $this->canDelete = true;

        } elseif ($this->userGroupId === 11) {
            $this->scope = 'all';
            $this->canViewAll = true;
        } elseif ($this->userGroupId === 14) {
            $this->scope = 'own';
            $this->canCreate = true;
        }
    }

    /* ORDER LIST */
    public function index() {
        $search = trim((string) $this->request->getGet('search'));

        $page = max(1,(int) $this->request->getGet('page'));

        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $orders = $this->orderModel->getAllOrders($perPage,$offset,$search !== '' ? $search : null,$this->scope,$this->userId);
        $total = $this->orderModel->getTotalOrders(['search' => $search,'scope' => $this->scope,'user_id' => $this->userId]);

        $pager = service('pager');
        $pager->makeLinks($page,$perPage,$total);

        $data = [
            'title' => 'Orders',
            'orders' => $orders,
            'total' => $total,
            'search' => $search,
            'pager' => $pager,
            'canViewAll' => $this->canViewAll,
            'canCreate' => $this->canCreate,
            'canEdit' => $this->canEdit,
            'canDelete' => $this->canDelete,
            'scope' => $this->scope
        ];

        $html = $this->website_header();
        $html .= view('orders/index', $data);
        $html .= $this->website_footer();

        return $html;
    }


    /* ORDER DETAIL */
    public function view(int $orderId = 0) {
        if ($orderId <= 0) {
            return redirect()
                ->to(site_url('orders'))
                ->with(
                    'error',
                    'Invalid order.'
                );
        }

        /* Check access before loading order. */
        if (!$this->orderModel->canAccessOrder($orderId,$this->scope,$this->userId)) {
            return redirect()
                ->to(site_url('orders'))
                ->with(
                    'error',
                    'You do not have access to this order.'
                );
        }

        $order = $this->orderModel->getOrder($orderId);

        if (!$order) {
            return redirect()
                ->to(site_url('orders'))
                ->with(
                    'error',
                    'Order not found.'
                );
        }
        $payments = $order['payments'] ?? [];
        $paymentReceived = 0;
        foreach ($payments as $payment) {
            $paymentReceived +=
            (float) $payment['amount'];
        }
        $orderTotal = (float) $order['total'];
        $paymentDue = $orderTotal - $paymentReceived;

        if ($paymentDue < 0) {
            $paymentDue = 0;
        }
        /* Create CRM product lookup. */
        $crmProducts = [];

        foreach ($order['crm_products'] ?? [] as $crmProduct) {
            $crmProducts[(int) $crmProduct['order_product_id']] = $crmProduct;
        }
        // print_r($order['crm_products']);
        $data = [
            'title' => 'Order #' . $orderId,
            'order' => $order,
            /* getOrder() already loads these, so don't query them again. */
            'products' => $order['products'] ?? [],
            'crmProducts' => $order['crm_products'] ?? [],
            'totals' => $order['totals'] ?? [],
            'ownership' => $order['ownership'] ?? [
                'firstname' => 'website',
                'lastname'  => 'user',
                'date_added' => $order['date_added']
            ],
            'payments' => $payments,
            'paymentReceived' => $paymentReceived,
            'paymentDue' => $paymentDue,
            'statuses' => $this->orderModel->getOrderStatuses(),
            'history' =>
                $this->orderModel->getOrderHistory(
                    $orderId
                ),
            'canViewAll' => $this->canViewAll,
            'canCreate' => $this->canCreate,
            'canEdit' => $this->canEdit,
            'canDelete' => $this->canDelete
        ];

        $html = $this->website_header();
        $html .= view('orders/view', $data);
        $html .= $this->website_footer();
        return $html;
    }

    /* UPDATE ORDER STATUS */
    public function status(int $orderId = 0) {
        if (!$this->canEdit) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'You do not have permission to update orders.'
                );
        }

        if ($orderId <= 0) {
            return redirect()
                ->to(site_url('orders'))
                ->with(
                    'error',
                    'Invalid order.'
                );
        }

        /* Verify access. */
        if (!$this->orderModel->canAccessOrder( $orderId, $this->scope, $this->userId )) {
            return redirect()
                ->to(site_url('orders'))
                ->with(
                    'error',
                    'You do not have access to this order.'
                );
        }

        $statusId = (int) $this->request->getPost('order_status_id');

        $comment = trim((string) $this->request->getPost('comment'));

        if ($statusId <= 0) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Please select an order status.'
                );
        }

        /* Verify status exists. */
        $validStatus = false;
        foreach ($this->orderModel->getOrderStatuses() as $status) {
            if ((int) $status['order_status_id'] === $statusId ) {
                $validStatus = true;
                break;
            }
        }

        if (!$validStatus) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Invalid order status.'
                );
        }

        try {
            $updated = $this->orderModel->updateOrderStatus(
                $orderId,
                $statusId,
                $comment
            );

            if (!$updated) {
                return redirect()
                    ->back()
                    ->with(
                        'error',
                        'Unable to update order status.'
                    );
            }

            return redirect()
                ->to(
                    site_url(
                        'orders/view/' . $orderId
                    )
                )
                ->with(
                    'success',
                    'Order status updated successfully.'
                );

        } catch (Exception $e) {

            log_message(
                'error',
                'CRM order status update failed: ' .
                $e->getMessage()
            );

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Unable to update order status.'
                );
        }
    }

    public function create() {
        log_message('error','CREATE ORDER: method = ' .$this->request->getMethod());
    
        if (!$this->canCreate) {
            return redirect()
                ->to(base_url('orders'))
                ->with('error', 'You do not have permission to create orders.');
        }

        /* GET */
        if ($this->request->getMethod() !== 'POST') {
            log_message(
                'error',
                'CREATE ORDER: rendering form'
            );
            $data = [
                'title' => 'Create Order',
                'orderStatuses' => $this->orderModel->getOrderStatuses(),
                'countries' => $this->orderModel->getCountries(),
                'zones' => $this->orderModel->getZonesByCountry(99),
                'formData' => [
                    'firstname' => '',
                    'lastname' => '',
                    'email' => '',
                    'telephone' => '',

                    'payment_firstname' => '',
                    'payment_lastname' => '',
                    'payment_company' => '',
                    'payment_address_1' => '',
                    'payment_address_2' => '',
                    'payment_city' => '',
                    'payment_postcode' => '',
                    'payment_country' => '',
                    'payment_country_id' => '',
                    'payment_zone' => '',
                    'payment_zone_id' => '',

                    'shipping_same_as_payment' => 1,

                    'shipping_firstname' => '',
                    'shipping_lastname' => '',
                    'shipping_company' => '',
                    'shipping_address_1' => '',
                    'shipping_address_2' => '',
                    'shipping_city' => '',
                    'shipping_postcode' => '',
                    'shipping_country_id' => '',
                    'shipping_zone_id' => '',
                    'shipping_method' => '',
                    'shipping_code' => '',

                    'payment_method' => '',
                    'payment_code' => '',

                    'order_source' => '',
                    'dispatch_deadline' => '',
                    'delivery_date' => '',

                    'order_status_id' => '',
                    'advance' => '',
                    'payment_reference' => '',
                    'payment_date' => '',
                    'payment_comment' => '',

                    'comment' => ''
                ]
            ];

            $html = $this->website_header();
            $html .= view('orders/create_order',$data);
            $html .= $this->website_footer();

            return $html;
        }
        log_message('error','CREATE ORDER: POST processing started');
        /* POST */
        $post = $this->request->getPost();

        /* Basic validation */
        $validation = service('validation');
        $rules = [
            'firstname' => [
                'label' => 'First Name',
                'rules' => 'required|max_length[100]'
            ],

            'email' => [
                'label' => 'Email',
                'rules' => 'permit_empty|valid_email|max_length[150]'
            ],

            'telephone' => [
                'label' => 'Contact',
                'rules' => 'required|max_length[50]'
            ],

            'payment_firstname' => [
                'label' => 'Billing First Name',
                'rules' => 'required|max_length[100]'
            ],

            'payment_address_1' => [
                'label' => 'Billing Address',
                'rules' => 'required|max_length[255]'
            ],

            'payment_city' => [
                'label' => 'Billing City',
                'rules' => 'required|max_length[100]'
            ],

            'payment_postcode' => [
                'label' => 'Billing Postcode',
                'rules' => 'required|max_length[20]'
            ],

            'payment_country_id' => [
                'label' => 'Billing Country',
                'rules' => 'required|is_natural_no_zero'
            ],

            'payment_zone_id' => [
                'label' => 'Billing State',
                'rules' => 'required|is_natural_no_zero'
            ],

            'order_status_id' => [
                'label' => 'Final Status',
                'rules' => 'required|is_natural_no_zero'
            ],

            'products' => [
                'label' => 'Products',
                'rules' => 'required'
            ]
        ];

        if (!$validation->setRules($rules)->run($post)) {
            log_message('error','CREATE ORDER: validation failed');
        
            log_message('error','CREATE ORDER: validation errors = ' . json_encode($validation->getErrors()));
            $data = [
                'title' => 'Create Order',
                'orderStatuses' => $this->orderModel->getOrderStatuses(),
                'countries' => $this->orderModel->getCountries(),
                'zones' => [],
                'formData' => $post,
                'validation' => $validation
            ];

            $html = $this->website_header();
            $html .= view('orders/create_order',$data
            );
            $html .= $this->website_footer();

            return $html;
        }

        /* Products */
        $products = [];

        if (isset($post['products']) && is_array($post['products']) ) {

            foreach ($post['products'] as $product) {

                $productId = (int) ($product['product_id'] ?? 0);
                $name = trim((string) ($product['name'] ?? ''));
                $model = trim((string) ($product['model'] ?? ''));
                $quantity = (int) ($product['quantity'] ?? 0);
                $price = (float) ($product['price'] ?? 0);

                /* Product name is required. */
                if ($name === '') {
                    throw new Exception('Product name is required.');
                }
                if ($quantity <= 0) {
                    throw new Exception('Invalid product quantity.');
                }
                if ($price < 0) {
                    throw new Exception('Invalid product selling price.');
                }

                /* Vendor is optional. */
                $vendor = trim((string) ($product['vendor'] ?? ''));

                /* Vendor price is optional.
                 * NULL means vendor pricing has not been
                 * decided yet. */
                $vendorPrice = null;

                if (isset($product['vendor_price']) && $product['vendor_price'] !== '' && is_numeric($product['vendor_price'])) {

                    $vendorPrice = (float) $product['vendor_price'];

                    if ($vendorPrice < 0) {
                        throw new Exception('Vendor price cannot be negative.');
                    }
                }

                $products[] = [

                    /* Existing OpenCart product = actual ID. Custom product = 0. */
                    'product_id' => $productId,
                    /* This is the actual order-specific product name. */
                    'name' => $name,
                    'model' => $model,
                    'quantity' => $quantity,
                    /*Customer negotiated selling price. */
                    'price' => $price,
                    /*Vendor / manufacturer.*/
                    'vendor' => $vendor !== '' ? $vendor : null,
                    /* Vendor manufacturing cost. */
                    'vendor_price' => $vendorPrice,
                    'tax_per_unit' => (float) (
                        $product['tax_per_unit'] ?? 0
                    )
                ];
            }
        }

        if (empty($products)) {

            return redirect()
                ->back()
                ->withInput()
                ->with( 'error', 'Please add at least one product.' );
        }

        /* Advance */
        $advance = 0;

        if (isset($post['advance']) && $post['advance'] !== '' && is_numeric($post['advance'])) {
            $advance = (float) $post['advance'];
        }

        if ($advance < 0) {

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Advance amount cannot be negative.'
                );
        }

        /* Prepare order data */
        $orderData = [
            /* Customer */
            'firstname' => trim((string) ($post['firstname'] ?? '')),
            'lastname' => trim((string) ($post['lastname'] ?? '')),
            'email' => trim((string) ($post['email'] ?? '')),
            'telephone' => trim((string) ($post['telephone'] ?? '')),
            'fax' => trim((string) ($post['fax'] ?? '')),
            /* Billing */
            'payment_firstname' => trim((string) ($post['payment_firstname'] ?? '')),
            'payment_lastname' => trim((string) ($post['payment_lastname'] ?? '')),
            'payment_company' => trim((string) ($post['payment_company'] ?? '')),
            'payment_address_1' => trim((string) ($post['payment_address_1'] ?? '')),
            'payment_address_2' => trim((string) ($post['payment_address_2'] ?? '')),
            'payment_city' => trim((string) ($post['payment_city'] ?? '')),
            'payment_postcode' => trim((string) ($post['payment_postcode'] ?? '')),
            'payment_country' => trim((string) ($post['payment_country'] ?? '')),
            'payment_country_id' => (int) ($post['payment_country_id'] ?? 0),
            'payment_zone' => trim((string) ($post['payment_zone'] ?? '')),
            'payment_zone_id' => (int) ($post['payment_zone_id'] ?? 0),
            'payment_method' => trim((string) ($post['payment_method'] ?? '')),
            'payment_code' => trim((string) ($post['payment_code'] ?? '')),
            /* Shipping */
            'shipping_same_as_payment' => !empty($post['shipping_same_as_payment']),
            'shipping_firstname' => trim((string) ($post['shipping_firstname'] ?? '')),
            'shipping_lastname' => trim((string) ($post['shipping_lastname'] ?? '')),
            'shipping_company' => trim((string) ($post['shipping_company'] ?? '')),
            'shipping_address_1' => trim((string) ($post['shipping_address_1'] ?? '')),
            'shipping_address_2' => trim((string) ($post['shipping_address_2'] ?? '')),
            'shipping_city' => trim((string) ($post['shipping_city'] ?? '')),
            'shipping_postcode' => trim((string) ($post['shipping_postcode'] ?? '')),
            'shipping_country_id' => (int) ($post['shipping_country_id'] ?? 0),
            'shipping_zone_id' => (int) ($post['shipping_zone_id'] ?? 0),
            'shipping_method' => trim((string) ($post['shipping_method'] ?? '')),
            'shipping_code' => trim((string) ($post['shipping_code'] ?? '')),
            /* CRM */
            'order_source' => trim((string) ($post['order_source'] ?? '')),
            'dispatch_deadline' => !empty($post['dispatch_deadline']) ? $post['dispatch_deadline'] : null,
            'delivery_date' => !empty($post['delivery_date']) ? $post['delivery_date'] : null,

            /* This goes directly to oc_order.order_status_id. */
            'order_status_id' => (int) ( $post['order_status_id'] ?? 0 ),

            /* Payment */
            'advance' => $advance,
            'payment_reference' => trim((string) ($post['payment_reference'] ?? '')),
            'payment_date' => !empty($post['payment_date']) ? $post['payment_date'] : null,
            'payment_comment' => trim((string) ($post['payment_comment'] ?? '')),

            /* Internal comment */
            'comment' => trim((string) ($post['comment'] ?? '')),
            /* Products */
            'products' => $products
        ];

        /* Create order */
        try {

            $orderId = $this->orderModel->createOrder($orderData, $this->userId);

            return redirect()
                ->to(base_url('orders/view/' . $orderId))
                ->with(
                    'success',
                    'Order created successfully.'
                );

        } catch (Exception $e) {

            log_message(
                'error',
                'CRM create order controller error: ' .
                $e->getMessage()
            );

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    public function productUpdate() {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400);
        }

        $orderId = (int) $this->request->getPost('order_id');
        $orderProductId = (int) $this->request->getPost('order_product_id');

        $name = trim((string) $this->request->getPost('name'));
        $quantity = (int) $this->request->getPost('quantity');
        $price = (float) $this->request->getPost('price');

        $vendor = trim((string) $this->request->getPost('vendor'));
        $vendor = $vendor !== '' ? $vendor : null;

        $vendorPriceInput = $this->request->getPost('vendor_price');
        $vendorPrice = ($vendorPriceInput !== '' && $vendorPriceInput !== null)
            ? (float) $vendorPriceInput
            : null;

        if (
            $orderId <= 0 ||
            $orderProductId <= 0 ||
            $name === '' ||
            $quantity < 1 ||
            $price < 0
        ) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please enter valid product details.'
            ]);
        }

        if ($vendorPrice !== null && $vendorPrice < 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Vendor price cannot be negative.'
            ]);
        }

        $updated = $this->orderModel->updateCrmOrderProduct(
            $orderId,
            $orderProductId,
            $name,
            $quantity,
            $price,
            $vendor,
            $vendorPrice
        );

        if (!$updated) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unable to update product.'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data' => [
                'name'         => $name,
                'quantity'     => $quantity,
                'price'        => number_format($price, 2, '.', ''),
                'total'        => number_format($quantity * $price, 2, '.', ''),
                'vendor'       => $vendor ?? '',
                'vendor_price' => $vendorPrice !== null
                    ? number_format($vendorPrice, 2, '.', '')
                    : ''
            ]
        ]);
    }
    public function update(int $orderId = 0) {
        if ($orderId <= 0) {

            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'success' => false,
                    'message' => 'Invalid order.'
                ]);
        }

        /* User must have edit permission. */
        if (!$this->canEdit) {

            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'success' => false,
                    'message' =>
                        'You do not have permission to edit this order.'
                ]);
        }

        /* Check order access before doing anything. */
        if (!$this->orderModel->canAccessOrder( $orderId, $this->scope, $this->userId )) {

            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'success' => false,
                    'message' =>
                        'You do not have access to this order.'
                ]);
        }

        /* Identify which order section is being updated. */
        $section = trim( (string) $this->request->getPost('section') );

        if ($section === '') {

            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'success' => false,
                    'message' =>
                        'Invalid update section.'
                ]);
        }

        /* Dispatch the update to the relevant section. */
        switch ($section) {

            case 'crm':
                return $this->updateCrmSection($orderId);

            case 'customer':
                return $this->updateCustomerSection($orderId);

            case 'status':
                return $this->updateStatusSection($orderId);
            
            case 'payment':
                return $this->insertPayment($orderId);
            case 'product':
                return $this->updateProductSection($orderId);

            case 'payment-address':
                return $this->updatePaymentAddressSection($orderId);

            case 'shipping-address':
                return $this->updateShippingAddressSection($orderId);

            default:

                return $this->response
                    ->setStatusCode(400)
                    ->setJSON([
                        'success' => false,
                        'message' => 'Invalid update section.'
                    ]);
        }
    }
    private function updateCrmSection(int $orderId) {
        $orderSource = trim( (string) $this->request->getPost( 'order_source' ) );
        $dispatchDeadline = $this->request->getPost( 'dispatch_deadline' );
        $deliveryDate = $this->request->getPost( 'delivery_date' );
    
        $updated = $this->orderModel->updateCrmOrderDetails( $orderId, $orderSource, $dispatchDeadline, $deliveryDate );
    
        if (!$updated) {
    
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => 'CRM details could not be updated.'
                ]);
        }
    
        return $this->response
            ->setJSON([
                'success' => true,
                'section' => 'crm',
                'order_source' => $orderSource,
                'dispatch_deadline' => $dispatchDeadline,
                'delivery_date' => $deliveryDate
            ]);
    }
    private function updateCustomerSection( int $orderId ) {
    
        $firstname = trim((string) $this->request->getPost('firstname'));
        $lastname = trim((string) $this->request->getPost('lastname'));
        $email = trim((string) $this->request->getPost('email'));
        $telephone = trim( (string) $this->request->getPost('telephone'));
    
        if ($firstname === '') {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'success' => false,
                    'message' => 'First name is required.'
                ]);
        }
    
        if ( $email !== '' && !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'success' => false,
                    'message' => 'Please enter a valid email address.'
                ]);
        }
    
        $updated =
            $this->orderModel->updateCustomerDetails( $orderId, $firstname, $lastname, $email, $telephone );
    
        if (!$updated) {
    
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => 'Customer details could not be updated.'
                ]);
        }
    
        return $this->response
            ->setJSON([
                'success' => true,
                'section' => 'customer',
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'telephone' => $telephone
            ]);
    }
    private function updateStatusSection( int $orderId ) {
    
        $orderstatusid = trim((string) $this->request->getPost('order_status_id'));
        $comment = trim((string) $this->request->getPost('comment'));
    
        if ($orderstatusid === '') {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'success' => false,
                    'message' => 'Order status id is required.'
                ]);
        }
    
    
        $updated = $this->orderModel->updateStatusDetails($orderId, $orderstatusid, $comment);
    
        if (!$updated) {
    
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => 'order status could not be updated.'
                ]);
        }
    
        return $this->response
            ->setJSON([
                'success' => true,
                'section' => 'status',
                'order_status_id' => $orderstatusid,
                'comment' => $comment
            ]);
    }

    private function insertPayment( int $orderId ) {
    
        $amount = trim((string) $this->request->getPost('amount'));
        $paymentMethod = trim((string) $this->request->getPost('payment_method'));
        $paymentReference = trim((string) $this->request->getPost('payment_reference'));
        $paymentComment = trim((string) $this->request->getPost('payment_comment'));
        $paymentDate = trim((string) $this->request->getPost('payment_date'));
        $createdBy = session()->get('user_id');
    
        if ($amount === '') {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'success' => false,
                    'message' =>
                        'Amount is required.'
                ]);
        }
    
    
        $updated = $this->orderModel->addPayment($orderId, $amount, $paymentMethod, $paymentReference, $paymentComment, $paymentDate, $createdBy);
    
        if (!$updated) {
    
            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' =>
                        'Payment could not be updated.'
                ]);
        }
        // print_r($updated);
        $payment = $this->orderModel->getPayment($updated);
        $orderTotal = (float) $this->orderModel->getOrderTotal($orderId);
        $payments = $this->orderModel->getOrderPayments($orderId);
        $paymentReceived = 0;

        foreach ($payments as $paymentRow) {
            $paymentReceived += (float) $paymentRow['amount'];
        }

        $paymentDue = $orderTotal - $paymentReceived;

        return $this->response
            ->setJSON([
                'success' => true,
                'section' => 'payment',
                'payment'          => $payment,
                'payment_received' => $paymentReceived,
                'payment_due'      => $paymentDue,
                'message'          => 'Payment added successfully.'
            ]);
    }
    public function fileDownload($fileId) {
        $file = $this->orderModel->getCrmOrderProductFile($fileId);

        if (!$file) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $filePath = WRITEPATH . $file['file_path'];

        if (!is_file($filePath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->response
            ->setHeader('Content-Type', $file['file_type'])
            ->setHeader(
                'Content-Disposition',
                'inline; filename="' . $file['file_name'] . '"'
            )
            ->setHeader('Content-Length', (string) filesize($filePath))
            ->setBody(file_get_contents($filePath));
    }
    private function updateProductSection(int $orderId) {
        $orderProductId = (int) $this->request->getPost('order_product_id');
        $name = trim((string) $this->request->getPost('name'));
        $quantity = (int) $this->request->getPost('quantity');
        $price = (float) $this->request->getPost('price');
        $vendor = trim((string) $this->request->getPost('vendor'));
        $vendorPriceInput = $this->request->getPost('vendor_price');
        $vendorPrice = ($vendorPriceInput === '' || $vendorPriceInput === null)
            ? null
            : (float) $vendorPriceInput;


        if ($orderProductId <= 0) {

            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'success' => false,
                    'message' => 'Invalid product.'
                ]);
        }

        if ($name === '') {

            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'success' => false,
                    'message' => 'Product name is required.'
                ]);
        }

        if ($quantity <= 0) {

            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'success' => false,
                    'message' => 'Quantity must be greater than zero.'
                ]);
        }

        if ($price < 0) {

            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'success' => false,
                    'message' => 'Price cannot be negative.'
                ]);
        }

        if ($vendorPrice !== null && $vendorPrice < 0) {

            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'success' => false,
                    'message' => 'Vendor price cannot be negative.'
                ]);
        }


        $updated = $this->orderModel->updateCrmOrderProduct(
            $orderId,
            $orderProductId,
            $name,
            $quantity,
            $price,
            $vendor,
            $vendorPrice
        );


        if (!$updated) {

            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'success' => false,
                    'message' => 'Unable to update product.'
                ]);
        }


        $total = $quantity * $price;


        return $this->response
            ->setJSON([
                'success' => true,
                'section' => 'product',
                'order_product_id' => $orderProductId,
                'name' => $name,
                'quantity' => $quantity,
                'price' => $price,
                'total' => $total,
                'vendor' => $vendor,
                'vendor_price' => $vendorPrice
            ]);
    }
}