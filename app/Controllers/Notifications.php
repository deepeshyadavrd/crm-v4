<?php

namespace App\Controllers;

use App\Controllers\WSController;

class Notifications extends WSController
{
    protected $db;

    public function __construct()
    {
        helper(['url']);

        $this->db = \Config\Database::connect();
    }

    public function unread_notifications()
    {
        $userGroupId = session()->get('user_group_id');

        // Only managers
        $allowedGroups = [1, 17, 21];

        if (!in_array($userGroupId, $allowedGroups)) {
            return $this->response->setJSON([]);
        }

        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $builder = $this->db->table('oc_request_callback rc');

        $builder->select([
            'rc.request_callback_id',
            'rc.name',
            'rc.mobile',
            'rc.status',
            'rc.date_added'
        ]);

        $builder->where('rc.status', 1);
        $builder->where('rc.date_added >=', $yesterday . ' 00:00:00');

        // Only unassigned leads
        $builder->where(
            'NOT EXISTS (
                SELECT 1
                FROM lead_assign la
                WHERE la.lead_id = rc.request_callback_id
            )',
            null,
            false
        );

        $builder->orderBy('rc.request_callback_id', 'DESC');

        $data = $builder->get()->getResultArray();

        return $this->response->setJSON($data);
    }
}