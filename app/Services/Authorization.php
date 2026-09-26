<?php

namespace App\Services;

class Authorization
{
    protected int $userGroupId;

    public function __construct()
    {
        $this->userGroupId = (int) session()->get('user_group_id');
    }

    public function can(string $module, string $action): bool
    {
        if (!session()->get('is_logged_in')) {
            return false;
        }

        switch ($module) {
            case 'dashboard':
                return $this->canDashboard($action);

            case 'leads':
                return $this->canLeads($action);

            case 'orders':
                return $this->canOrders($action);

            case 'inventory':
                return $this->canInventory($action);

            case 'reports':
                return $this->canReports($action);

            default:
                return false;
        }
    }

    protected function canDashboard(string $action): bool
    {
        return in_array(
            $this->userGroupId,
            [1, 11, 14, 17, 21],
            true
        );
    }

    protected function canLeads(string $action): bool
    {
        switch ($action) {

            case 'view':
                return in_array(
                    $this->userGroupId,
                    [1, 14, 17, 21],
                    true
                );

            case 'assign':
            case 'unassign':
                return in_array(
                    $this->userGroupId,
                    [1, 17, 21],
                    true
                );

            case 'change_status':
            case 'add_remark':
                return in_array(
                    $this->userGroupId,
                    [1, 14, 17, 21],
                    true
                );

            default:
                return false;
        }
    }

    protected function canOrders(string $action): bool
    {
        switch ($action) {

            case 'view':
                return in_array(
                    $this->userGroupId,
                    [1, 11, 14, 17, 21],
                    true
                );

            case 'create':
                return in_array(
                    $this->userGroupId,
                    [1, 14, 17, 21],
                    true
                );

            case 'edit':
            case 'delete':
                return in_array(
                    $this->userGroupId,
                    [1, 17, 21],
                    true
                );

            default:
                return false;
        }
    }

    protected function canInventory(string $action): bool
    {
        switch ($action) {

            case 'view':
                return in_array(
                    $this->userGroupId,
                    [1, 17, 21],
                    true
                );

            case 'edit':
            case 'delete':
                return $this->userGroupId === 1;

            default:
                return false;
        }
    }

    protected function canReports(string $action): bool
    {
        return in_array(
            $this->userGroupId,
            [1, 17, 21],
            true
        );
    }

    public function getLeadScope(): string
    {
        if (in_array($this->userGroupId, [1, 17, 21], true)) {
            return 'all';
        }

        if ($this->userGroupId === 14) {
            return 'own';
        }

        return 'none';
    }

    public function getOrderScope(): string
    {
        if (in_array($this->userGroupId, [1, 11, 17, 21], true)) {
            return 'all';
        }

        if ($this->userGroupId === 14) {
            return 'own';
        }

        return 'none';
    }

    public function getUserGroupId(): int
    {
        return $this->userGroupId;
    }
}