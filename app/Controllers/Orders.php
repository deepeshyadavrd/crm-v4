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


        /*
         * CRM permissions
         *
         * 1, 17, 21
         * Full access
         *
         * 11
         * View all orders
         *
         * 14
         * Own orders + create
         */

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


    /*
     * ORDER LIST
     */

    public function index()
    {
        $search = trim(
            (string) $this->request->getGet('search')
        );

        $page = max(
            1,
            (int) $this->request->getGet('page')
        );

        $perPage = 20;

        $offset = ($page - 1) * $perPage;


        $orders = $this->orderModel->getAllOrders(
            $perPage,
            $offset,
            $search !== '' ? $search : null,
            $this->scope,
            $this->userId
        );


        $total = $this->orderModel->countAllOrders(
            $search !== '' ? $search : null,
            $this->scope,
            $this->userId
        );


        $pager = service('pager');

        $pager->makeLinks(
            $page,
            $perPage,
            $total
        );


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


        /*
 * CI4 view loading
 */
$html = $this->website_header();

$html .= view('orders/index', $data);

$html .= $this->website_footer();

return $html;
    }


    /*
     * ORDER DETAIL
     */

    public function view(int $orderId = 0)
    {
        if ($orderId <= 0) {

            return redirect()
                ->to(site_url('orders'))
                ->with(
                    'error',
                    'Invalid order.'
                );
        }


        /*
         * Check access before loading order.
         */

        if (!$this->orderModel->canAccessOrder(
            $orderId,
            $this->scope,
            $this->userId
        )) {

            return redirect()
                ->to(site_url('orders'))
                ->with(
                    'error',
                    'You do not have access to this order.'
                );
        }


        $order = $this->orderModel->getOrder(
            $orderId
        );


        if (!$order) {

            return redirect()
                ->to(site_url('orders'))
                ->with(
                    'error',
                    'Order not found.'
                );
        }


        $data = [
            'title' => 'Order #' . $orderId,

            'order' => $order,

            /*
             * getOrder() already loads these,
             * so don't query them again.
             */

            'products' => $order['products'] ?? [],
            'totals' => $order['totals'] ?? [],
            'ownership' => $order['ownership'] ?? null,

            'statuses' =>
                $this->orderModel->getOrderStatuses(),

            'history' =>
                $this->orderModel->getOrderHistory(
                    $orderId
                ),

            'canViewAll' => $this->canViewAll,
            'canCreate' => $this->canCreate,
            'canEdit' => $this->canEdit,
            'canDelete' => $this->canDelete
        ];


        /*
 * CI4 view loading
 */
$html = $this->website_header();

$html .= view('orders/view', $data);

$html .= $this->website_footer();

return $html;
    }


    /*
     * UPDATE ORDER STATUS
     */

    public function status(int $orderId = 0)
    {
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


        /*
         * Verify access.
         */

        if (!$this->orderModel->canAccessOrder(
            $orderId,
            $this->scope,
            $this->userId
        )) {

            return redirect()
                ->to(site_url('orders'))
                ->with(
                    'error',
                    'You do not have access to this order.'
                );
        }


        $statusId = (int) $this->request
            ->getPost('order_status_id');

        $comment = trim(
            (string) $this->request->getPost('comment')
        );


        if ($statusId <= 0) {

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Please select an order status.'
                );
        }


        /*
         * Verify status exists.
         */

        $validStatus = false;

        foreach (
            $this->orderModel->getOrderStatuses()
            as $status
        ) {

            if (
                (int) $status['order_status_id'] ===
                $statusId
            ) {
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
}