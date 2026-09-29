<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

class OrderModel extends Model {
    protected $table = 'oc_order';
    protected $primaryKey = 'order_id';
    protected $returnType = 'array';

    protected $db;

    public function __construct()
    {
        parent::__construct();

        $this->db = \Config\Database::connect();
    }


    /* ORDER LIST */

    public function getAllOrders(
        int $limit,
        int $offset,
        ?string $search = null,
        string $scope = 'all',
        int $userId = 0
    ): array {
        $builder = $this->db->table('oc_order o');

        $builder->select([
            'o.order_id',
            'o.invoice_no',
            'o.date_added',
            'o.total',
            'o.currency_code',
            'o.firstname',
            'o.lastname',
            'o.email',
            'o.telephone',
            'o.order_status_id',
            'os.name AS order_status_name',
            'crm.created_by',
            'u.firstname AS crm_created_by_firstname',
            'u.lastname AS crm_created_by_lastname'
        ]);

        $builder->join(
            'oc_order_status os',
            'os.order_status_id = o.order_status_id',
            'left'
        );

        $builder->join(
            'oc_crm_order crm',
            'crm.order_id = o.order_id',
            'left'
        );

        $builder->join(
            'oc_user u',
            'u.user_id = crm.created_by',
            'left'
        );

        $this->applyOrderScope($builder, $scope, $userId);

        $this->applySearch($builder, $search);

        $builder->orderBy('o.date_added', 'DESC');
        $builder->limit($limit, $offset);

        return $builder->get()->getResultArray();
    }


    public function countAllOrders( ?string $search = null, string $scope = 'all', int $userId = 0 ): int {
        $builder = $this->db->table('oc_order o');

        $builder->join(
            'oc_order_status os',
            'os.order_status_id = o.order_status_id',
            'left'
        );

        $builder->join(
            'oc_crm_order crm',
            'crm.order_id = o.order_id',
            'left'
        );

        $this->applyOrderScope($builder, $scope, $userId);

        $this->applySearch($builder, $search);

        return (int) $builder->countAllResults();
    }
    public function getTotalOrders(array $data = []): int
{
    $builder = $this->db->table('oc_order o');

    $builder->join(
        'oc_order_status os',
        'os.order_status_id = o.order_status_id',
        'left'
    );

    /*
     * CRM order ownership
     */
    $builder->join(
        'oc_crm_order crm',
        'crm.order_id = o.order_id',
        'left'
    );

    /*
     * New CRM order-page scope
     */
    $scope = $data['scope'] ?? 'all';
    $userId = (int) ($data['user_id'] ?? 0);

    $this->applyOrderScope(
        $builder,
        $scope,
        $userId
    );

    /*
     * New CRM order-page search
     */
    if (
        isset($data['search']) &&
        trim((string) $data['search']) !== ''
    ) {
        $this->applySearch(
            $builder,
            trim((string) $data['search'])
        );
    }

    /*
     * Old OpenCart status filter
     */
    if (!empty($data['filter_order_status'])) {

        $statuses = explode(
            ',',
            $data['filter_order_status']
        );

        $statusIds = [];

        foreach ($statuses as $statusId) {

            $statusId = (int) trim($statusId);

            if ($statusId > 0) {
                $statusIds[] = $statusId;
            }
        }

        if (!empty($statusIds)) {
            $builder->whereIn(
                'o.order_status_id',
                $statusIds
            );
        }

    } elseif (
        isset($data['filter_order_status_id']) &&
        $data['filter_order_status_id'] !== ''
    ) {

        $builder->where(
            'o.order_status_id',
            (int) $data['filter_order_status_id']
        );

    } else {

        /*
         * Old behaviour:
         * only count orders having a status
         */
        $builder->where(
            'o.order_status_id >',
            0
        );
    }

    /*
     * Old OpenCart filters
     */
    if (!empty($data['filter_order_id'])) {

        $builder->where(
            'o.order_id',
            (int) $data['filter_order_id']
        );
    }

    if (!empty($data['filter_customer'])) {

        $builder->groupStart();

        $builder->like(
            "CONCAT(o.firstname, ' ', o.lastname)",
            $data['filter_customer'],
            'both',
            null,
            true
        );

        $builder->groupEnd();
    }

    if (!empty($data['filter_date_added'])) {

        $builder->where(
            'DATE(o.date_added)',
            date(
                'Y-m-d',
                strtotime($data['filter_date_added'])
            )
        );
    }

    if (!empty($data['filter_date_modified'])) {

        $builder->where(
            'DATE(o.date_modified)',
            date(
                'Y-m-d',
                strtotime($data['filter_date_modified'])
            )
        );
    }

    if (
        isset($data['filter_total']) &&
        $data['filter_total'] !== ''
    ) {

        $builder->where(
            'o.total',
            (float) $data['filter_total']
        );
    }

    return (int) $builder->countAllResults();
}


    private function applyOrderScope( $builder, string $scope, int $userId ): void {
        if ($scope === 'own') {
            $builder->where('crm.created_by', $userId);
        }
    }


    private function applySearch($builder, ?string $search): void {
        if ($search === null || trim($search) === '') {
            return;
        }

        $search = trim($search);

        $builder->groupStart()
            ->like('o.order_id', $search)
            ->orLike('o.invoice_no', $search)
            ->orLike('o.firstname', $search)
            ->orLike('o.lastname', $search)
            ->orLike('o.email', $search)
            ->orLike('o.telephone', $search)
            ->orLike('os.name', $search)
            ->groupEnd();
    }


    /* ORDER ACCESS */

    public function canAccessOrder( int $orderId, string $scope, int $userId ): bool {
        if ($scope === 'all') {
            return $this->orderExists($orderId);
        }

        if ($scope === 'own') {
            return $this->db
                ->table('oc_crm_order')
                ->where('order_id', $orderId)
                ->where('created_by', $userId)
                ->countAllResults() > 0;
        }

        return false;
    }


    public function orderExists(int $orderId): bool
    {
        return $this->db
            ->table('oc_order')
            ->where('order_id', $orderId)
            ->countAllResults() > 0;
    }


    /* CRM ORDER OWNERSHIP */

    public function getCrmOrderOwner(int $orderId): ?array {
        $owner = $this->db
            ->table('oc_crm_order crm')
            ->select([
                'crm.crm_order_id',
                'crm.order_id',
                'crm.created_by',
                'crm.date_added',
                'u.username',
                'u.firstname',
                'u.lastname'
            ])
            ->join(
                'oc_user u',
                'u.user_id = crm.created_by',
                'left'
            )
            ->where('crm.order_id', $orderId)
            ->get()
            ->getRowArray();

        return $owner ?: null;
    }


    private function createCrmOrderOwnership( int $orderId, int $userId ): bool {
        return $this->db
            ->table('oc_crm_order')
            ->insert([
                'order_id'   => $orderId,
                'created_by' => $userId,
                'date_added' => date('Y-m-d H:i:s')
            ]);
    }


    /* ORDER DETAIL*/

    public function getOrder(int $orderId): ?array {
        $builder = $this->db->table('oc_order o');

        $builder->select([
            'o.*',
            'CONCAT(c.firstname, " ", c.lastname) AS customer',
            'c.firstname AS customer_firstname',
            'c.lastname AS customer_lastname',
            'c.email AS customer_email',
            'c.telephone AS customer_telephone',
            'os.name AS order_status'
        ]);

        $builder->join(
            'oc_customer c',
            'c.customer_id = o.customer_id',
            'left'
        );

        $builder->join(
            'oc_order_status os',
            'os.order_status_id = o.order_status_id',
            'left'
        );

        $builder->where('o.order_id', $orderId);

        $order = $builder->get()->getRowArray();

        if (!$order) {
            return null;
        }

        $order['products'] = $this->getOrderProducts($orderId);
        $order['totals'] = $this->getOrderTotals($orderId);
        $order['ownership'] = $this->getCrmOrderOwner($orderId);

        return $order;
    }

    /* ORDER PRODUCTS */

    public function getOrderProducts(int $orderId): array {
        $builder = $this->db->table('oc_order_product op');

        $builder->select([
            'op.*',
            'p.image'
        ]);

        $builder->join(
            'oc_product p',
            'p.product_id = op.product_id',
            'left'
        );

        $builder->where('op.order_id', $orderId);

        $products = $builder
            ->orderBy('op.order_product_id', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($products as &$product) {
            $product['options'] = $this->getOrderOptions(
                $orderId,
                (int) $product['order_product_id']
            );
        }

        return $products;
    }


    public function getOrderOptions( int $orderId, int $orderProductId ): array {
        return $this->db
            ->table('oc_order_option')
            ->where('order_id', $orderId)
            ->where('order_product_id', $orderProductId)
            ->get()
            ->getResultArray();
    }


    /* ORDER TOTALS */

    public function getOrderTotals(int $orderId): array {
        return $this->db
            ->table('oc_order_total')
            ->where('order_id', $orderId)
            ->orderBy('sort_order', 'ASC')
            ->get()
            ->getResultArray();
    }


    /* ORDER HISTORY */

    public function getOrderHistory(int $orderId): array
    {
        return $this->db
            ->table('oc_order_history oh')
            ->select([
                'oh.*',
                'os.name AS status_name'
            ])
            ->join(
                'oc_order_status os',
                'os.order_status_id = oh.order_status_id',
                'left'
            )
            ->where('oh.order_id', $orderId)
            ->orderBy('oh.date_added', 'DESC')
            ->get()
            ->getResultArray();
    }


    /* CREATE ORDER */
public function createOrder(array $orderData, int $createdBy): int
{
    if ($createdBy <= 0) {
        throw new Exception('Invalid CRM user.');
    }

    $this->db->transStart();

    try {

        $now = date('Y-m-d H:i:s');

        /* Currency */
        $currency = $this->db
            ->table('oc_currency')
            ->where('code', 'INR')
            ->get()
            ->getRowArray();

        if (!$currency) {
            throw new Exception(
                'Default currency INR was not found.'
            );
        }

        /* Request */
        $request = service('request');

        /* Main order */
        $order = [
            'invoice_no'              => 0,
            'invoice_prefix'          => '',
            'store_id'                => 0,
            'store_name'              => 'URBANWOOD FURNITURE PRIVATE LIMITED',
            'store_url'               => 'https://www.urbanwood.in/',

            'customer_id'             => 0,
            'customer_group_id'       => 1,

            'firstname'               => $orderData['firstname'],
            'lastname'                => $orderData['lastname'],
            'email'                   => $orderData['email'],
            'telephone'               => $orderData['telephone'],
            'fax'                     => $orderData['fax'] ?? '',

            'custom_field'            => '[]',

            'payment_firstname'       => $orderData['payment_firstname'],
            'payment_lastname'        => $orderData['payment_lastname'],
            'payment_company'         => $orderData['payment_company'] ?? '',
            'payment_address_1'       => $orderData['payment_address_1'],
            'payment_address_2'       => $orderData['payment_address_2'] ?? '',
            'payment_city'            => $orderData['payment_city'],
            'payment_postcode'        => $orderData['payment_postcode'],
            'payment_country'         => $orderData['payment_country'],
            'payment_country_id'      => $orderData['payment_country_id'],
            'payment_zone'            => $orderData['payment_zone'],
            'payment_zone_id'         => $orderData['payment_zone_id'],
            'payment_method'          => $orderData['payment_method'],
            'payment_code'            => $orderData['payment_code'],
            'payment_address_format'  => '',
            'payment_custom_field'    => '[]',

            'shipping_firstname'      => '',
            'shipping_lastname'       => '',
            'shipping_company'        => '',
            'shipping_address_1'      => '',
            'shipping_address_2'      => '',
            'shipping_city'           => '',
            'shipping_postcode'       => '',
            'shipping_country_id'     => 0,
            'shipping_zone_id'        => 0,
            'shipping_method'         => '',
            'shipping_code'           => '',
            'shipping_address_format' => '',
            'shipping_custom_field'   => '[]',

            'comment'                 => $orderData['comment'] ?? '',

            'total'                   => 0,

            /*
             * OpenCart order status.
             * This is NOT the payment status.
             */
            'order_status_id'         => (int) $orderData['order_status_id'],

            'affiliate_id'            => 0,
            'marketing_id'            => 0,
            'tracking'                => '',

            'language_id'             => 1,

            'currency_id'             => $currency['currency_id'],
            'currency_code'           => $currency['code'],
            'currency_value'          => $currency['value'],

            'ip'                      => $request->getIPAddress(),
            'user_agent'              => $request
                ->getUserAgent()
                ->getAgentString(),
            'accept_language'         => $request
                ->getServer('HTTP_ACCEPT_LANGUAGE'),

            'date_added'              => $now,
            'date_modified'           => $now
        ];

        /* Shipping address */
        if (
            !empty($orderData['shipping_same_as_payment'])
        ) {
            $order['shipping_firstname'] =
                $orderData['payment_firstname'];

            $order['shipping_lastname'] =
                $orderData['payment_lastname'];

            $order['shipping_company'] =
                $orderData['payment_company'] ?? '';

            $order['shipping_address_1'] =
                $orderData['payment_address_1'];

            $order['shipping_address_2'] =
                $orderData['payment_address_2'] ?? '';

            $order['shipping_city'] =
                $orderData['payment_city'];

            $order['shipping_postcode'] =
                $orderData['payment_postcode'];

            $order['shipping_country_id'] =
                $orderData['payment_country_id'];

            $order['shipping_zone_id'] =
                $orderData['payment_zone_id'];

            $order['shipping_method'] = '';
            $order['shipping_code'] = '';

        } else {
            $order['shipping_firstname'] =
                $orderData['shipping_firstname'];

            $order['shipping_lastname'] =
                $orderData['shipping_lastname'];

            $order['shipping_company'] =
                $orderData['shipping_company'] ?? '';

            $order['shipping_address_1'] =
                $orderData['shipping_address_1'];

            $order['shipping_address_2'] =
                $orderData['shipping_address_2'] ?? '';

            $order['shipping_city'] =
                $orderData['shipping_city'];

            $order['shipping_postcode'] =
                $orderData['shipping_postcode'];

            $order['shipping_country_id'] =
                $orderData['shipping_country_id'];

            $order['shipping_zone_id'] =
                $orderData['shipping_zone_id'];

            $order['shipping_method'] =
                $orderData['shipping_method'] ?? '';

            $order['shipping_code'] =
                $orderData['shipping_code'] ?? '';
        }

        /* Insert main order */
        $this->db
            ->table('oc_order')
            ->insert($order);

        $orderId = (int) $this->db->insertID();

        if ($orderId <= 0) {
            throw new Exception(
                'Failed to create order.'
            );
        }

        /* Products */
        $subTotal = 0;
        $taxTotal = 0;

        if (
            empty($orderData['products']) ||
            !is_array($orderData['products'])
        ) {
            throw new Exception(
                'At least one product is required.'
            );
        }

        foreach ($orderData['products'] as $product) {

            $quantity = (int) ($product['quantity'] ?? 0);
            $price = (float) ($product['price'] ?? 0);
            $taxPerUnit = (float) (
                $product['tax_per_unit'] ?? 0
            );

            if ($quantity <= 0) {
                throw new Exception(
                    'Product quantity must be greater than zero.'
                );
            }

            if ($price < 0) {
                throw new Exception(
                    'Product selling price cannot be negative.'
                );
            }

            $productTotal = $quantity * $price;
            $productTax = $quantity * $taxPerUnit;

            $subTotal += $productTotal;
            $taxTotal += $productTax;

            /* OpenCart order product */
            $this->db
                ->table('oc_order_product')
                ->insert([
                    'order_id'   => $orderId,
                    'product_id' => (int) (
                        $product['product_id'] ?? 0
                    ),
                    'name'       => $product['name'],
                    'model'      => $product['model'] ?? '',
                    'quantity'   => $quantity,
                    'price'      => $price,
                    'total'      => $productTotal,
                    'tax'        => $productTax,
                    'reward'     => 0
                ]);

            $orderProductId = (int) $this->db->insertID();

            if ($orderProductId <= 0) {
                throw new Exception(
                    'Failed to create order product.'
                );
            }

            /*
             * CRM product information.
             *
             * Vendor price is the cost per unit charged
             * by the vendor to Urbanwood.
             */
            $vendorPrice = (float) (
                $product['vendor_price'] ?? 0
            );

            if ($vendorPrice < 0) {
                throw new Exception(
                    'Vendor price cannot be negative.'
                );
            }

            $this->db
                ->table('oc_crm_order_product')
                ->insert([
                    'order_id'         => $orderId,
                    'order_product_id' => $orderProductId,
                    'vendor'           => $product['vendor'] ?? '',
                    'vendor_price'     => $vendorPrice
                ]);
        }

        /* Subtotal */
        $this->db
            ->table('oc_order_total')
            ->insert([
                'order_id'   => $orderId,
                'code'       => 'sub_total',
                'title'      => 'Sub-Total',
                'value'      => $subTotal,
                'sort_order' => 1
            ]);

        /* Tax */
        if ($taxTotal > 0) {
            $this->db
                ->table('oc_order_total')
                ->insert([
                    'order_id'   => $orderId,
                    'code'       => 'tax',
                    'title'      => 'Tax',
                    'value'      => $taxTotal,
                    'sort_order' => 4
                ]);
        }

        /* Grand total */
        $shippingCost = 0;

        $grandTotal =
            $subTotal +
            $taxTotal +
            $shippingCost;

        // if (
        //     isset($orderData['total_override']) &&
        //     $orderData['total_override'] !== '' &&
        //     is_numeric($orderData['total_override'])
        // ) {
        //     $grandTotal =
        //         (float) $orderData['total_override'];
        // }

        if ($grandTotal < 0) {
            throw new Exception(
                'Order total cannot be negative.'
            );
        }

        $this->db
            ->table('oc_order_total')
            ->insert([
                'order_id'   => $orderId,
                'code'       => 'total',
                'title'      => 'Total',
                'value'      => $grandTotal,
                'sort_order' => 9
            ]);

        /* Update order total */
        $this->db
            ->table('oc_order')
            ->where('order_id', $orderId)
            ->update([
                'total'         => $grandTotal,
                'date_modified' => $now
            ]);

        /*
         * CRM order information
         */
        $this->db
            ->table('oc_crm_order')
            ->insert([
                'order_id'          => $orderId,
                'created_by'        => $createdBy,
                'date_added'       => $now,
                'order_source'     => $orderData['order_source'] ?? '',
                'dispatch_deadline' => !empty(
                    $orderData['dispatch_deadline']
                )
                    ? $orderData['dispatch_deadline']
                    : null,
                'delivery_date'    => !empty(
                    $orderData['delivery_date']
                )
                    ? $orderData['delivery_date']
                    : null
            ]);

        $crmOrderId = (int) $this->db->insertID();

        if ($crmOrderId <= 0) {
            throw new Exception(
                'Failed to create CRM order.'
            );
        }

        /* CRM ownership */
        if (!$this->createCrmOrderOwnership(
            $orderId,
            $createdBy
        )) {
            throw new Exception(
                'Failed to save CRM order ownership.'
            );
        }

        /*
         * Initial payment / advance.
         *
         * Zero advance is valid. In that case no payment
         * record is created and payment status remains Pending.
         */
        $advance = 0;

        if (
            isset($orderData['advance']) &&
            $orderData['advance'] !== '' &&
            is_numeric($orderData['advance'])
        ) {
            $advance = (float) $orderData['advance'];
        }

        if ($advance < 0) {
            throw new Exception(
                'Advance amount cannot be negative.'
            );
        }

        if ($advance > $grandTotal) {
            throw new Exception(
                'Advance cannot be greater than order total.'
            );
        }

        if ($advance > 0) {

            $paymentDate = $now;

            if (!empty($orderData['payment_date'])) {
                $paymentDate =
                    $orderData['payment_date'];
            }

            $this->db
                ->table('oc_crm_order_payment')
                ->insert([
                    'order_id'          => $orderId,
                    'amount'             => $advance,
                    'payment_method'     =>
                        $orderData['payment_method'] ?? '',
                    'payment_reference'  =>
                        $orderData['payment_reference'] ?? '',
                    'payment_date'       => $paymentDate,
                    'comment'            =>
                        $orderData['payment_comment'] ?? '',
                    'created_by'         => $createdBy,
                    'date_added'        => $now
                ]);

            $paymentId = (int) $this->db->insertID();

            if ($paymentId <= 0) {
                throw new Exception(
                    'Failed to save initial payment.'
                );
            }
        }

        /* Order history */
        $username = session()->get('username');

        if (!$username) {
            $username = 'CRM User';
        }

        $comment =
            'Order registered via CRM by ' .
            $username;

        if (!empty($orderData['comment'])) {
            $comment .=
                '. Internal Comment: ' .
                $orderData['comment'];
        }

        $this->db
            ->table('oc_order_history')
            ->insert([
                'order_id'        => $orderId,
                'order_status_id' => $order['order_status_id'],
                'notify'          => 0,
                'comment'         => $comment,
                'date_added'      => $now
            ]);

        /* Complete transaction */
        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            throw new Exception(
                'Order transaction failed.'
            );
        }

        return $orderId;

    } catch (Exception $e) {

        $this->db->transRollback();

        log_message(
            'error',
            'CRM order creation failed: ' .
            $e->getMessage()
        );

        throw new Exception(
            'Failed to create order: ' .
            $e->getMessage()
        );
    }
}




    /* COUNTRIES */

    public function getCountries(): array
    {
        return $this->db
            ->table('oc_country')
            ->select([
                'country_id',
                'name'
            ])
            ->where('status', 1)
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
    }


    /* ZONES */

    public function getZonesByCountry(int $countryId): array {
        return $this->db
            ->table('oc_zone')
            ->select([
                'zone_id',
                'name'
            ])
            ->where('country_id', $countryId)
            ->where('status', 1)
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
    }


    /* ORDER STATUS */

    public function getOrderStatuses(): array
    {
        return $this->db
            ->table('oc_order_status')
            ->select([
                'order_status_id',
                'name'
            ])
            ->where('language_id', 1)
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
    }


    /* UPDATE ORDER STATUS */

    public function updateOrderStatus(
        int $orderId,
        int $statusId,
        string $comment = ''
    ): bool {
        $this->db->transStart();

        $now = date('Y-m-d H:i:s');

        $this->db
            ->table('oc_order')
            ->where('order_id', $orderId)
            ->update([
                'order_status_id' => $statusId,
                'date_modified'   => $now
            ]);

        $this->db
            ->table('oc_order_history')
            ->insert([
                'order_id'        => $orderId,
                'order_status_id' => $statusId,
                'notify'          => 0,
                'comment'         => $comment,
                'date_added'      => $now
            ]);

        $this->db->transComplete();

        return $this->db->transStatus();
    }
    public function getCrmOrder(int $orderId): ?array
{
    $builder = $this->db->table('oc_crm_order crm');

    $builder->select([
        'crm.crm_order_id',
        'crm.order_id',
        'crm.created_by',
        'crm.date_added',
        'crm.order_source',
        'crm.dispatch_deadline',
        'crm.delivery_date',
        'CONCAT(u.firstname, " ", u.lastname) AS sales_person'
    ]);

    $builder->join(
        'oc_user u',
        'u.user_id = crm.created_by',
        'left'
    );

    $builder->where('crm.order_id', $orderId);

    $result = $builder->get()->getRowArray();

    return $result ?: null;
}
public function getCrmOrderProducts(int $orderId): array
{
    $builder = $this->db->table('oc_order_product op');

    $builder->select([
        'op.order_product_id',
        'op.order_id',
        'op.product_id',
        'op.name',
        'op.model',
        'op.quantity',
        'op.price',
        'op.total',
        'crm.vendor',
        'crm.vendor_price'
    ]);

    $builder->join(
        'oc_crm_order_product crm',
        'crm.order_product_id = op.order_product_id',
        'left'
    );

    $builder->where('op.order_id', $orderId);

    return $builder->get()->getResultArray();
}
public function getOrderPayments(int $orderId): array
{
    $builder = $this->db->table('oc_crm_order_payment p');

    $builder->select([
        'p.payment_id',
        'p.amount',
        'p.payment_method',
        'p.payment_reference',
        'p.payment_date',
        'p.comment',
        'p.created_by',
        'CONCAT(u.firstname, " ", u.lastname) AS created_by_name'
    ]);

    $builder->join(
        'oc_user u',
        'u.user_id = p.created_by',
        'left'
    );

    $builder->where('p.order_id', $orderId);
    $builder->orderBy('p.payment_date', 'ASC');

    return $builder->get()->getResultArray(); 
}
public function getOrderPaidAmount(int $orderId): float
{
    $builder = $this->db->table('oc_crm_order_payment');

    $builder->selectSum('amount');
    $builder->where('order_id', $orderId);

    $result = $builder->get()->getRowArray();

    return (float) ($result['amount'] ?? 0);
}
}