<?php

namespace App\Controllers;

use App\Controllers\WSController; 
use App\Models\OrderModel;

class Home extends WSController
{
    public function __construct()
    {
        // 1. In CI4, helpers, sessions, and libraries are loaded differently.
        // We typically initialize them globally in BaseController or use global functions.
        helper(['url', 'form']);
    }
    public function index()
    {
        // Authentication check (uncommented and updated to modern CI4 syntax)
        if (!session()->get('is_logged_in')) {
            return redirect()->to('auth/login');
        }

        // 2. Instantiate the modern CI4 Model
        $orderModel = new OrderModel();

        // 3. Analytics processing remains standard PHP math logic
        $today = $orderModel->getTotalOrders(['filter_date_added' => date('Y-m-d', strtotime('-1 day'))]);
        $yesterday = $orderModel->getTotalOrders(['filter_date_added' => date('Y-m-d', strtotime('-2 day'))]);

        $difference = $today - $yesterday;
        $data['percentage'] = ($difference && $today) ? round(($difference / $today) * 100) : 0;

        $order_total = $orderModel->getTotalOrders();

        // 4. Compact conditional numeric sizing matrix
        if ($order_total > 1000000000000) {
            $data['total'] = round($order_total / 1000000000000, 1) . 'T';
        } elseif ($order_total > 1000000000) {
            $data['total'] = round($order_total / 1000000000, 1) . 'B';
        } elseif ($order_total > 1000000) {
            $data['total'] = round($order_total / 1000000, 1) . 'M';
        } elseif ($order_total > 1000) {
            $data['total'] = round($order_total / 1000, 1) . 'K';
        } else {
            $data['total'] = $order_total;
        }

        // 5. Views in CI4 use return statements. 
        // We can cleanly stack them, or combine your headers into a single template.
        $html = $this->website_header();
        $html .= view('home', $data);
        $html .= $this->website_footer();

        return $html;
    }
}
