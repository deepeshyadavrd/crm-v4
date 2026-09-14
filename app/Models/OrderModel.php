<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

class OrderModel extends Model
{
    protected $table = 'oc_order';
    protected $primaryKey = 'order_id';
    protected $returnType = 'array';

    protected $db;

    public function __construct()
    {
        parent::__construct();

        $this->db = \Config\Database::connect();
    }


    /* =========================================================
     * SEARCH / PAGINATION
     * ========================================================= */

    private function _apply_order_search_filter($builder, $search_query)
    {
        if (!empty($search_query)) {
            $builder->groupStart()
                ->like('o.order_id', $search_query)
                ->orLike('o.invoice_no', $search_query)
                ->orLike('o.firstname', $search_query)
                ->orLike('o.lastname', $search_query)
                ->orLike('os.name', $search_query)
                ->groupEnd();
        }

        return $builder;
    }


    public function get_all_orders($limit, $offset, $search_query = null)
    {
        $builder = $this->db->table('oc_order o');

        $builder->select(
            'o.order_id,
             o.invoice_no,
             o.date_added,
             o.total,
             o.currency_code,
             o.firstname,
             o.lastname,
             os.name AS order_status_name,
             o.order_status_id AS status_id'
        );

        $builder->join(
            'oc_order_status os',
            'os.order_status_id = o.order_status_id',
            'left'
        );

        $this->_apply_order_search_filter($builder, $search_query);

        $builder->orderBy('o.date_added', 'DESC');
        $builder->limit($limit, $offset);

        return $builder->get()->getResultArray();
    }


    public function count_all_orders($search_query = null)
    {
        $builder = $this->db->table('oc_order o');

        $builder->join(
            'oc_order_status os',
            'os.order_status_id = o.order_status_id',
            'left'
        );

        $this->_apply_order_search_filter($builder, $search_query);

        return $builder->countAllResults();
    }


    /* =========================================================
     * FULL ORDER DETAILS
     * ========================================================= */

    public function get_full_order_details($order_id)
    {
        $builder = $this->db->table('oc_order o');

        $builder->select(
            'o.*,
             c.firstname AS customer_firstname,
             c.lastname AS customer_lastname,
             c.email AS customer_email,
             c.telephone AS customer_telephone,
             os.name AS order_status_name'
        );

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

        $builder->where('o.order_id', $order_id);

        $order = $builder->get()->getRowArray();

        if (!$order) {
            return false;
        }

        /* Products */

        $productBuilder = $this->db->table('oc_order_product');

        $order['products'] = $productBuilder
            ->where('order_id', $order_id)
            ->get()
            ->getResultArray();


        /* Totals */

        $totalBuilder = $this->db->table('oc_order_total');

        $order['totals'] = $totalBuilder
            ->where('order_id', $order_id)
            ->orderBy('sort_order', 'ASC')
            ->get()
            ->getResultArray();

        return $order;
    }


    /* =========================================================
     * CREATE OPENCART ORDER
     * ========================================================= */

    public function create_opencart_order($order_data)
    {
        $this->db->transStart();

        try {

            /* -------------------------------------------------
             * Currency
             * ------------------------------------------------- */

            $currency = $this->db->table('oc_currency')
                ->where('code', 'INR')
                ->get()
                ->getRowArray();

            if (!$currency) {
                throw new Exception(
                    "Default currency 'INR' not found in OpenCart."
                );
            }


            /* -------------------------------------------------
             * Request information
             * ------------------------------------------------- */

            $request = service('request');

            $now = date('Y-m-d H:i:s');


            /* -------------------------------------------------
             * Main order data
             * ------------------------------------------------- */

            $data_oc_order = [
                'invoice_no'             => 0,
                'invoice_prefix'         => '',
                'store_id'               => 0,
                'store_name'             => 'URBANWOOD FURNITURE PRIVATE LIMITED',
                'store_url'              => 'https://www.urbanwood.in/',
                'customer_id'            => 0,
                'customer_group_id'      => 1,

                'firstname'              => $order_data['firstname'],
                'lastname'               => $order_data['lastname'],
                'email'                  => $order_data['email'],
                'telephone'              => $order_data['telephone'],
                'fax'                    => $order_data['fax'] ?? '',

                'custom_field'           => '[]',

                'payment_firstname'      => $order_data['payment_firstname'],
                'payment_lastname'       => $order_data['payment_lastname'],
                'payment_company'        => $order_data['payment_company'] ?? '',
                'payment_address_1'      => $order_data['payment_address_1'],
                'payment_address_2'      => $order_data['payment_address_2'] ?? '',
                'payment_city'           => $order_data['payment_city'],
                'payment_postcode'       => $order_data['payment_postcode'],
                'payment_country'       => $order_data['payment_country'],
                'payment_country_id'     => $order_data['payment_country_id'],
                'payment_zone'           => $order_data['payment_zone'],
                'payment_zone_id'        => $order_data['payment_zone_id'],
                'payment_method'         => $order_data['payment_method'],
                'payment_code'           => $order_data['payment_code'],
                'payment_address_format' => '',
                'payment_custom_field'   => '[]',

                'shipping_firstname'     => '',
                'shipping_lastname'      => '',
                'shipping_company'       => '',
                'shipping_address_1'     => '',
                'shipping_address_2'     => '',
                'shipping_city'          => '',
                'shipping_postcode'      => '',
                'shipping_country_id'    => 0,
                'shipping_zone_id'       => 0,
                'shipping_method'        => '',
                'shipping_code'          => '',
                'shipping_address_format' => '',
                'shipping_custom_field'  => '[]',

                'comment'                => $order_data['comment'] ?? '',
                'total'                  => 0.00,

                'order_status_id'        => $order_data['order_status_id'],

                'affiliate_id'           => 0,
                'marketing_id'           => 0,
                'tracking'               => '',

                'language_id'            => 1,

                'currency_id'            => $currency['currency_id'],
                'currency_code'          => $currency['code'],
                'currency_value'         => $currency['value'],

                'ip'                     => $request->getIPAddress(),
                'user_agent'             => $request->getUserAgent()->getAgentString(),
                'accept_language'        => $request->getServer('HTTP_ACCEPT_LANGUAGE'),

                'date_added'             => $now,
                'date_modified'          => $now
            ];


            /* -------------------------------------------------
             * Shipping address
             * ------------------------------------------------- */

            if (
                isset($order_data['shipping_same_as_payment']) &&
                $order_data['shipping_same_as_payment'] === 'on'
            ) {

                $data_oc_order['shipping_firstname'] =
                    $order_data['payment_firstname'];

                $data_oc_order['shipping_lastname'] =
                    $order_data['payment_lastname'];

                $data_oc_order['shipping_company'] =
                    $order_data['payment_company'] ?? '';

                $data_oc_order['shipping_address_1'] =
                    $order_data['payment_address_1'];

                $data_oc_order['shipping_address_2'] =
                    $order_data['payment_address_2'] ?? '';

                $data_oc_order['shipping_city'] =
                    $order_data['payment_city'];

                $data_oc_order['shipping_postcode'] =
                    $order_data['payment_postcode'];

                $data_oc_order['shipping_country_id'] =
                    $order_data['payment_country_id'];

                $data_oc_order['shipping_zone_id'] =
                    $order_data['payment_zone_id'];

                $data_oc_order['shipping_method'] = '';
                $data_oc_order['shipping_code'] = '';

            } else {

                $data_oc_order['shipping_firstname'] =
                    $order_data['shipping_firstname'];

                $data_oc_order['shipping_lastname'] =
                    $order_data['shipping_lastname'];

                $data_oc_order['shipping_company'] =
                    $order_data['shipping_company'] ?? '';

                $data_oc_order['shipping_address_1'] =
                    $order_data['shipping_address_1'];

                $data_oc_order['shipping_address_2'] =
                    $order_data['shipping_address_2'] ?? '';

                $data_oc_order['shipping_city'] =
                    $order_data['shipping_city'];

                $data_oc_order['shipping_postcode'] =
                    $order_data['shipping_postcode'];

                $data_oc_order['shipping_country_id'] =
                    $order_data['shipping_country_id'];

                $data_oc_order['shipping_zone_id'] =
                    $order_data['shipping_zone_id'];

                $data_oc_order['shipping_method'] =
                    $order_data['shipping_method'];

                $data_oc_order['shipping_code'] =
                    $order_data['shipping_code'];
            }


            /* -------------------------------------------------
             * Insert order
             * ------------------------------------------------- */

            $this->db->table('oc_order')->insert($data_oc_order);

            $order_id = $this->db->insertID();

            if (!$order_id) {
                throw new Exception(
                    'Failed to insert main order data.'
                );
            }


            /* -------------------------------------------------
             * Products
             * ------------------------------------------------- */

            $sub_total = 0;
            $total_tax = 0;

            foreach ($order_data['products'] as $product) {

                $product_quantity =
                    (int) $product['quantity'];

                $product_price =
                    (float) $product['price'];

                $product_tax_per_unit =
                    (float) ($product['tax_per_unit'] ?? 0);

                $product_total_price =
                    $product_quantity * $product_price;

                $product_total_tax =
                    $product_quantity * $product_tax_per_unit;

                $sub_total += $product_total_price;
                $total_tax += $product_total_tax;


                $data_oc_order_product = [
                    'order_id'   => $order_id,
                    'product_id' => (int) ($product['product_id'] ?? 0),
                    'name'       => $product['name'],
                    'model'      => $product['model'] ?? '',
                    'quantity'   => $product_quantity,
                    'price'      => $product_price,
                    'total'      => $product_total_price,
                    'tax'        => $product_total_tax,
                    'reward'     => 0
                ];

                $this->db
                    ->table('oc_order_product')
                    ->insert($data_oc_order_product);

                if ($this->db->affectedRows() === 0) {
                    throw new Exception(
                        'Failed to insert product: ' .
                        $product['name']
                    );
                }
            }


            /* -------------------------------------------------
             * Sub-total
             * ------------------------------------------------- */

            $this->db->table('oc_order_total')->insert([
                'order_id'   => $order_id,
                'code'       => 'sub_total',
                'title'      => 'Sub-Total',
                'value'      => $sub_total,
                'sort_order' => 1
            ]);


            /* -------------------------------------------------
             * Shipping
             * ------------------------------------------------- */

            $shipping_cost = 0;

            if (!empty($data_oc_order['shipping_method'])) {

                if (
                    $data_oc_order['shipping_code'] === 'flat.flat'
                ) {
                    $shipping_cost = 10.00;
                }

                $this->db->table('oc_order_total')->insert([
                    'order_id'   => $order_id,
                    'code'       => 'shipping',
                    'title'      => $data_oc_order['shipping_method'],
                    'value'      => $shipping_cost,
                    'sort_order' => 3
                ]);
            }


            /* -------------------------------------------------
             * Tax
             * ------------------------------------------------- */

            if ($total_tax > 0) {

                $this->db->table('oc_order_total')->insert([
                    'order_id'   => $order_id,
                    'code'       => 'tax',
                    'title'      => 'Tax',
                    'value'      => $total_tax,
                    'sort_order' => 4
                ]);
            }


            /* -------------------------------------------------
             * Grand total
             * ------------------------------------------------- */

            $grand_total =
                $sub_total +
                $shipping_cost +
                $total_tax;

            if (
                isset($order_data['total_override']) &&
                is_numeric($order_data['total_override']) &&
                $order_data['total_override'] !== ''
            ) {
                $grand_total =
                    (float) $order_data['total_override'];
            }


            $this->db->table('oc_order_total')->insert([
                'order_id'   => $order_id,
                'code'       => 'total',
                'title'      => 'Total',
                'value'      => $grand_total,
                'sort_order' => 9
            ]);


            /* -------------------------------------------------
             * Update main order total
             * ------------------------------------------------- */

            $this->db
                ->table('oc_order')
                ->where('order_id', $order_id)
                ->update([
                    'total'        => $grand_total,
                    'date_modified' => $now
                ]);


            /* -------------------------------------------------
             * Order history
             * ------------------------------------------------- */

            $crm_username =
                session()->get('username');

            if (empty($crm_username)) {
                $crm_username = 'Unknown CRM User';
            }

            $history_comment =
                'Order registered via CRM by ' .
                $crm_username;

            if (!empty($order_data['comment'])) {

                $history_comment .=
                    '. Internal Comment: ' .
                    $order_data['comment'] .
                    ' - ' .
                    $crm_username;
            }


            $this->db->table('oc_order_history')->insert([
                'order_id'        => $order_id,
                'order_status_id' => $data_oc_order['order_status_id'],
                'notify'          => 0,
                'comment'         => $history_comment,
                'date_added'      => $now
            ]);


            /* -------------------------------------------------
             * Complete transaction
             * ------------------------------------------------- */

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new Exception(
                    'Database transaction failed.'
                );
            }

            return $order_id;

        } catch (Exception $e) {

            $this->db->transRollback();

            log_message(
                'error',
                'OpenCart order creation failed: ' .
                $e->getMessage()
            );

            throw new Exception(
                'Failed to create order: ' .
                $e->getMessage()
            );
        }
    }


    /* =========================================================
     * COUNTRIES / ZONES
     * ========================================================= */

    public function get_countries()
    {
        return $this->db
            ->table('oc_country')
            ->select('country_id, name')
            ->where('status', 1)
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
    }


    public function get_zones_by_country($country_id)
    {
        return $this->db
            ->table('oc_zone')
            ->select('zone_id, name')
            ->where('country_id', $country_id)
            ->where('status', 1)
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
    }


    public function get_country_name_by_id($country_id)
    {
        $row = $this->db
            ->table('oc_country')
            ->select('name')
            ->where('country_id', $country_id)
            ->get()
            ->getRowArray();

        return $row['name'] ?? '';
    }


    public function get_zone_name_by_id($zone_id)
    {
        $row = $this->db
            ->table('oc_zone')
            ->select('name')
            ->where('zone_id', $zone_id)
            ->get()
            ->getRowArray();

        return $row['name'] ?? '';
    }


    /* =========================================================
     * GET SINGLE ORDER
     * ========================================================= */

    public function getOrder($order_id)
    {
        $builder = $this->db->table('oc_order o');

        $builder->select(
            'o.*,
             CONCAT(c.firstname, " ", c.lastname) AS customer,
             os.name AS order_status'
        );

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

        $builder->where('o.order_id', (int) $order_id);

        $orderData = $builder->get()->getRowArray();

        if (!$orderData) {
            return false;
        }


        /* Payment country */

        $country = $this->db
            ->table('oc_country')
            ->where(
                'country_id',
                (int) $orderData['payment_country_id']
            )
            ->get()
            ->getRowArray();

        $payment_iso_code_2 =
            $country['iso_code_2'] ?? '';

        $payment_iso_code_3 =
            $country['iso_code_3'] ?? '';


        /* Payment zone */

        $zone = $this->db
            ->table('oc_zone')
            ->where(
                'zone_id',
                (int) $orderData['payment_zone_id']
            )
            ->get()
            ->getRowArray();

        $payment_zone_code =
            $zone['code'] ?? '';


        /* Shipping country */

        $country = $this->db
            ->table('oc_country')
            ->where(
                'country_id',
                (int) $orderData['shipping_country_id']
            )
            ->get()
            ->getRowArray();

        $shipping_iso_code_2 =
            $country['iso_code_2'] ?? '';

        $shipping_iso_code_3 =
            $country['iso_code_3'] ?? '';


        /* Shipping zone */

        $zone = $this->db
            ->table('oc_zone')
            ->where(
                'zone_id',
                (int) $orderData['shipping_zone_id']
            )
            ->get()
            ->getRowArray();

        $shipping_zone_code =
            $zone['code'] ?? '';


        return [
            'order_id'                => $orderData['order_id'],
            'invoice_no'              => $orderData['invoice_no'],
            'invoice_prefix'          => $orderData['invoice_prefix'],
            'store_id'                => $orderData['store_id'],
            'store_name'              => $orderData['store_name'],
            'store_url'               => $orderData['store_url'],
            'customer_id'             => $orderData['customer_id'],
            'customer'                => $orderData['customer'],
            'customer_group_id'       => $orderData['customer_group_id'],

            'firstname'               => $orderData['firstname'],
            'lastname'                => $orderData['lastname'],
            'email'                   => $orderData['email'],
            'telephone'               => $orderData['telephone'],

            'custom_field'            => json_decode(
                $orderData['custom_field'] ?? '[]',
                true
            ),

            'payment_firstname'       => $orderData['payment_firstname'],
            'payment_lastname'        => $orderData['payment_lastname'],
            'payment_company'         => $orderData['payment_company'],
            'payment_address_1'       => $orderData['payment_address_1'],
            'payment_address_2'       => $orderData['payment_address_2'],
            'payment_postcode'        => $orderData['payment_postcode'],
            'payment_city'            => $orderData['payment_city'],
            'payment_zone_id'         => $orderData['payment_zone_id'],
            'payment_zone'            => $orderData['payment_zone'],
            'payment_zone_code'       => $payment_zone_code,
            'payment_country_id'      => $orderData['payment_country_id'],
            'payment_country'         => $orderData['payment_country'],
            'payment_iso_code_2'      => $payment_iso_code_2,
            'payment_iso_code_3'      => $payment_iso_code_3,
            'payment_address_format'  => $orderData['payment_address_format'],

            'payment_custom_field'    => json_decode(
                $orderData['payment_custom_field'] ?? '[]',
                true
            ),

            'payment_method'          => $orderData['payment_method'],
            'payment_code'            => $orderData['payment_code'],

            'shipping_firstname'      => $orderData['shipping_firstname'],
            'shipping_lastname'       => $orderData['shipping_lastname'],
            'shipping_company'       => $orderData['shipping_company'],
            'shipping_address_1'      => $orderData['shipping_address_1'],
            'shipping_address_2'      => $orderData['shipping_address_2'],
            'shipping_postcode'       => $orderData['shipping_postcode'],
            'shipping_city'           => $orderData['shipping_city'],
            'shipping_zone_id'        => $orderData['shipping_zone_id'],
            'shipping_zone'           => $orderData['shipping_zone'],
            'shipping_zone_code'      => $shipping_zone_code,
            'shipping_country_id'     => $orderData['shipping_country_id'],
            'shipping_country'        => $orderData['shipping_country'],
            'shipping_iso_code_2'     => $shipping_iso_code_2,
            'shipping_iso_code_3'     => $shipping_iso_code_3,
            'shipping_address_format' => $orderData['shipping_address_format'],

            'shipping_custom_field'   => json_decode(
                $orderData['shipping_custom_field'] ?? '[]',
                true
            ),

            'shipping_method'         => $orderData['shipping_method'],
            'shipping_code'           => $orderData['shipping_code'],

            'comment'                 => $orderData['comment'],
            'total'                   => $orderData['total'],
            'reward'                  => 0,

            'order_status_id'         => $orderData['order_status_id'],
            'order_status'            => $orderData['order_status'],

            'affiliate_id'            => $orderData['affiliate_id'],
            'commission'              => $orderData['commission'],
            'language_id'             => $orderData['language_id'],
            'currency_id'             => $orderData['currency_id'],
            'currency_code'           => $orderData['currency_code'],
            'currency_value'          => $orderData['currency_value'],
            'ip'                      => $orderData['ip'],
            'forwarded_ip'            => $orderData['forwarded_ip'],
            'user_agent'              => $orderData['user_agent'],
            'accept_language'         => $orderData['accept_language'],
            'date_added'              => $orderData['date_added'],
            'date_modified'           => $orderData['date_modified'],

            'products'                =>
                $this->getOrderProducts($order_id)
        ];
    }


    /* =========================================================
     * ORDERS
     * ========================================================= */

    public function getOrders($data = [], $search_query = null)
    {
        $builder = $this->db->table('oc_order o');

        $builder->select(
            'o.*,
             os.name AS order_status'
        );

        $builder->join(
            'oc_order_status os',
            'o.order_status_id = os.order_status_id',
            'left'
        );

        $builder->where('o.order_status_id >', 1);
        $builder->orderBy('o.date_added', 'DESC');

        $orders = $builder->get()->getResultArray();

        if (empty($orders)) {
            return [];
        }

        foreach ($orders as &$order) {
            $order['products'] =
                $this->getOrderProductsOnly($order['order_id']);
        }

        return $orders;
    }


    public function getOrderProductsOnly($order_id)
    {
        $builder = $this->db->table('oc_order_product op');

        $builder->select(
            'op.*,
             ops.name AS status_name,
             p.image'
        );

        $builder->join(
            'oc_product p',
            'p.product_id = op.product_id',
            'left'
        );

        $builder->join(
            'order_product_status ops',
            'op.status = ops.status',
            'left'
        );

        $builder->where(
            'op.order_id',
            (int) $order_id
        );

        return $builder->get()->getResultArray();
    }


    public function getOrderProducts($order_id)
    {
        $builder = $this->db->table('oc_order_product op');

        $builder->select(
            'op.*,
             p.image'
        );

        $builder->join(
            'oc_product p',
            'p.product_id = op.product_id',
            'left'
        );

        $builder->where(
            'op.order_id',
            (int) $order_id
        );

        $products =
            $builder->get()->getResultArray();

        if (empty($products)) {
            return [];
        }

        foreach ($products as &$product) {
            $product['history'] =
                $this->getProductHistory(
                    $product['order_product_id']
                );
        }

        return $products;
    }


    public function getProductHistory($order_product_id)
    {
        return $this->db
            ->table('oc_order_product_history')
            ->where(
                'order_product_id',
                (int) $order_product_id
            )
            ->get()
            ->getResultArray();
    }


    public function getOrderOptions($order_id, $order_product_id)
    {
        return $this->db
            ->table('oc_order_option')
            ->where('order_id', (int) $order_id)
            ->where(
                'order_product_id',
                (int) $order_product_id
            )
            ->get()
            ->getResultArray();
    }


    public function getOrderVouchers($order_id)
    {
        return $this->db
            ->table('oc_order_voucher')
            ->where(
                'order_id',
                (int) $order_id
            )
            ->get()
            ->getResultArray();
    }


    public function getOrderVoucherByVoucherId($voucher_id)
    {
        return $this->db
            ->table('oc_order_voucher')
            ->where(
                'voucher_id',
                (int) $voucher_id
            )
            ->get()
            ->getRowArray();
    }


    public function getOrderTotals($order_id)
    {
        return $this->db
            ->table('oc_order_total')
            ->where(
                'order_id',
                (int) $order_id
            )
            ->orderBy('sort_order', 'ASC')
            ->get()
            ->getResultArray();
    }


    /* =========================================================
     * ORDER COUNTS
     * ========================================================= */

    public function getTotalOrders($data = [])
    {
        $builder = $this->db->table('oc_order');

        if (!empty($data['filter_order_status'])) {

            $statuses =
                explode(',', $data['filter_order_status']);

            $builder->whereIn(
                'order_status_id',
                $statuses
            );

        } elseif (
            isset($data['filter_order_status_id']) &&
            $data['filter_order_status_id'] !== ''
        ) {

            $builder->where(
                'order_status_id',
                $data['filter_order_status_id']
            );

        } else {

            $builder->where(
                'order_status_id >',
                0
            );
        }


        if (!empty($data['filter_order_id'])) {
            $builder->where(
                'order_id',
                $data['filter_order_id']
            );
        }


        if (!empty($data['filter_customer'])) {

            $builder->like(
                "CONCAT(firstname, ' ', lastname)",
                $data['filter_customer'],
                'both',
                false
            );
        }


        if (!empty($data['filter_date_added'])) {

            $builder->where(
                'DATE(date_added)',
                $data['filter_date_added'],
                false
            );
        }


        if (!empty($data['filter_date_modified'])) {

            $builder->where(
                'DATE(date_modified)',
                $data['filter_date_modified'],
                false
            );
        }


        if (
            isset($data['filter_total']) &&
            $data['filter_total'] !== ''
        ) {

            $builder->where(
                'total',
                (float) $data['filter_total']
            );
        }


        return $builder->countAllResults();
    }


    public function getTotalOrdersByStoreId($store_id)
    {
        return $this->db
            ->table('oc_order')
            ->where('store_id', (int) $store_id)
            ->countAllResults();
    }


    public function getTotalOrdersByOrderStatusId($order_status_id)
    {
        return $this->db
            ->table('oc_order')
            ->where(
                'order_status_id',
                (int) $order_status_id
            )
            ->where('order_status_id >', 0)
            ->countAllResults();
    }


    public function getTotalOrdersByProcessingStatus()
    {
        /*
         * Your CI3 code used:
         * $this->config->get('config_processing_status')
         *
         * We can migrate this once your CI3 config values
         * are identified.
         */

        $statuses = config('OpenCart')->processingStatus ?? [];

        if (empty($statuses)) {
            return 0;
        }

        return $this->db
            ->table('oc_order')
            ->whereIn('order_status_id', $statuses)
            ->countAllResults();
    }


    public function getTotalOrdersByCompleteStatus()
    {
        $statuses = config('OpenCart')->completeStatus ?? [];

        if (empty($statuses)) {
            return 0;
        }

        return $this->db
            ->table('oc_order')
            ->whereIn('order_status_id', $statuses)
            ->countAllResults();
    }


    public function getTotalOrdersByLanguageId($language_id)
    {
        return $this->db
            ->table('oc_order')
            ->where(
                'language_id',
                (int) $language_id
            )
            ->where('order_status_id >', 0)
            ->countAllResults();
    }


    public function getTotalOrdersByCurrencyId($currency_id)
    {
        return $this->db
            ->table('oc_order')
            ->where(
                'currency_id',
                (int) $currency_id
            )
            ->where('order_status_id >', 0)
            ->countAllResults();
    }


    /* =========================================================
     * TOTAL SALES
     * ========================================================= */

    public function getTotalSales($data = [])
    {
        $builder = $this->db->table('oc_order');

        $builder->selectSum('total');


        if (!empty($data['filter_order_status'])) {

            $statuses =
                explode(',', $data['filter_order_status']);

            $builder->whereIn(
                'order_status_id',
                $statuses
            );

        } elseif (
            isset($data['filter_order_status_id']) &&
            $data['filter_order_status_id'] !== ''
        ) {

            $builder->where(
                'order_status_id',
                $data['filter_order_status_id']
            );

        } else {

            $builder->where(
                'order_status_id >',
                0
            );
        }


        if (!empty($data['filter_order_id'])) {
            $builder->where(
                'order_id',
                $data['filter_order_id']
            );
        }


        if (!empty($data['filter_customer'])) {

            $builder->like(
                "CONCAT(firstname, ' ', lastname)",
                $data['filter_customer'],
                'both',
                false
            );
        }


        if (!empty($data['filter_date_added'])) {

            $builder->where(
                'DATE(date_added)',
                $data['filter_date_added'],
                false
            );
        }


        if (!empty($data['filter_date_modified'])) {

            $builder->where(
                'DATE(date_modified)',
                $data['filter_date_modified'],
                false
            );
        }


        if (
            isset($data['filter_total']) &&
            $data['filter_total'] !== ''
        ) {

            $builder->where(
                'total',
                (float) $data['filter_total']
            );
        }


        $row = $builder->get()->getRowArray();

        return $row['total'] ?? 0;
    }


    /* =========================================================
     * INVOICE
     * ========================================================= */

    public function createInvoiceNo($order_id)
    {
        $order_info =
            $this->getOrder($order_id);

        if (
            $order_info &&
            empty($order_info['invoice_no'])
        ) {

            $row = $this->db
                ->table('oc_order')
                ->selectMax('invoice_no')
                ->where(
                    'invoice_prefix',
                    $order_info['invoice_prefix']
                )
                ->get()
                ->getRowArray();

            $invoice_no =
                !empty($row['invoice_no'])
                    ? ((int) $row['invoice_no'] + 1)
                    : 1;


            $this->db
                ->table('oc_order')
                ->where(
                    'order_id',
                    (int) $order_id
                )
                ->update([
                    'invoice_no' => $invoice_no,
                    'invoice_prefix' =>
                        $order_info['invoice_prefix']
                ]);

            return
                $order_info['invoice_prefix'] .
                $invoice_no;
        }

        return null;
    }


    /* =========================================================
     * ORDER HISTORY
     * ========================================================= */

    public function getOrderHistories(
        $order_id,
        $start = 0,
        $limit = 10
    ) {

        if ($start < 0) {
            $start = 0;
        }

        if ($limit < 1) {
            $limit = 10;
        }


        $config = config('OpenCart');

        $language_id =
            $config->languageId ?? 1;


        $builder =
            $this->db->table('oc_order_history oh');

        $builder->select(
            'oh.date_added,
             os.name AS status,
             oh.comment,
             oh.notify'
        );

        $builder->join(
            'oc_order_status os',
            'oh.order_status_id = os.order_status_id',
            'left'
        );

        $builder->where(
            'oh.order_id',
            (int) $order_id
        );

        $builder->where(
            'os.language_id',
            $language_id
        );

        $builder->orderBy(
            'oh.date_added',
            'DESC'
        );

        $builder->limit(
            $limit,
            $start
        );

        return $builder
            ->get()
            ->getResultArray();
    }


    public function getTotalOrderHistories($order_id)
    {
        return $this->db
            ->table('oc_order_history')
            ->where(
                'order_id',
                (int) $order_id
            )
            ->countAllResults();
    }


    public function getTotalOrderHistoriesByOrderStatusId(
        $order_status_id
    ) {

        return $this->db
            ->table('oc_order_history')
            ->where(
                'order_status_id',
                (int) $order_status_id
            )
            ->countAllResults();
    }


    /* =========================================================
     * EMAILS BY PRODUCTS
     * ========================================================= */

    public function getEmailsByProductsOrdered(
        $products,
        $start,
        $end
    ) {

        if (empty($products)) {
            return [];
        }

        $builder =
            $this->db->table('oc_order o');

        $builder->distinct();

        $builder->select('o.email');

        $builder->join(
            'oc_order_product op',
            'o.order_id = op.order_id',
            'left'
        );

        $builder->whereIn(
            'op.product_id',
            $products
        );

        $builder->where(
            'o.order_status_id !=',
            0
        );

        $builder->limit(
            $end,
            $start
        );

        return $builder
            ->get()
            ->getResultArray();
    }


    public function getTotalEmailsByProductsOrdered($products)
    {
        if (empty($products)) {
            return 0;
        }

        $builder =
            $this->db->table('oc_order o');

        $builder->select(
            'COUNT(DISTINCT o.email) AS total',
            false
        );

        $builder->join(
            'oc_order_product op',
            'o.order_id = op.order_id',
            'left'
        );

        $builder->whereIn(
            'op.product_id',
            $products
        );

        $builder->where(
            'o.order_status_id !=',
            0
        );

        $row =
            $builder->get()->getRowArray();

        return (int) ($row['total'] ?? 0);
    }


    /* =========================================================
     * ORDER STATUS
     * ========================================================= */

    public function get_all_statuses()
    {
        return $this->db
            ->table('oc_order_status')
            ->get()
            ->getResultArray();
    }


    public function get_order_statuses()
    {
        return $this->db
            ->table('oc_order_status')
            ->select(
                'order_status_id, name'
            )
            ->where(
                'language_id',
                1
            )
            ->get()
            ->getResultArray();
    }


    /* =========================================================
     * UPDATE ORDER STATUS
     * ========================================================= */

    public function update_order_status1(
        $order_id,
        $status_id
    ) {

        $this->db->transStart();

        try {

            $this->db
                ->table('oc_order')
                ->where(
                    'order_id',
                    (int) $order_id
                )
                ->update([
                    'order_status_id' => (int) $status_id,
                    'date_modified'   =>
                        date('Y-m-d H:i:s')
                ]);


            $this->db
                ->table('oc_order_history')
                ->insert([
                    'order_id'        => (int) $order_id,
                    'order_status_id' => (int) $status_id,
                    'notify'          => 0,
                    'comment'         =>
                        'Status updated via CRM list view.',
                    'date_added'      =>
                        date('Y-m-d H:i:s')
                ]);


            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return false;
            }

            return true;

        } catch (Exception $e) {

            $this->db->transRollback();

            log_message(
                'error',
                'Failed to update order status: ' .
                $e->getMessage()
            );

            return false;
        }
    }
}