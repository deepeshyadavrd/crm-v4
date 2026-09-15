<?php

namespace App\Models;

use CodeIgniter\Model;

class LeadsModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();

        $this->db = \Config\Database::connect();
    }


    /**
     * Get single lead
     */
    public function getlead($lead_id)
    {
        $builder = $this->db->table('oc_request_callback');

        $builder->select('oc_request_callback.*, oc_custom_furniture_request_images.*');

        $builder->join(
            'oc_custom_furniture_request_images',
            'oc_request_callback.request_callback_id = oc_custom_furniture_request_images.cfr_id',
            'left'
        );

        $builder->where(
            'oc_request_callback.request_callback_id',
            $lead_id
        );

        $leadData = $builder->get()->getRow();

        if (!$leadData) {
            return [];
        }

        $lead = [
            'lead_id'    => $leadData->request_callback_id,
            'name'       => $leadData->name,
            'email'      => $leadData->email,
            'mobile'     => $leadData->mobile,
            'state'      => $leadData->state,
            'message'    => $leadData->message,
            'image'      => $leadData->custom_furniture_image,
            'status'     => $leadData->status,
            'stage'      => $leadData->stage,
            'date'       => $leadData->date_added,
            'remarks'    => $this->getRemark($leadData->request_callback_id),
            'assignedto' => $this->getAssigned($leadData->request_callback_id)
        ];

        return $lead;
    }


    /**
     * Get leads
     */
    public function getLeads($start, $limit, $user_id, $filters = [])
{
    $builder = $this->db->table('oc_request_callback rc');

    $builder->select([
        'rc.request_callback_id',
        'rc.name',
        'rc.email',
        'rc.mobile',
        'rc.state',
        'rc.stage',
        'rc.status',
        'rc.date_added',
        'rc.tracking_data',
        'la.user_id AS assigned_user_id',
        'CONCAT(u.firstname, " ", u.lastname) AS assignedto'
    ]);

    $builder->join(
        'lead_assign la',
        'la.lead_id = rc.request_callback_id',
        'left'
    );

    $builder->join(
        'oc_user u',
        'u.user_id = la.user_id',
        'left'
    );

    $builder->notLike('rc.name', 'test');

    /*
     * Date
     */
    if (
        empty($filters['from_date']) &&
        empty($filters['to_date'])
    ) {
        $builder->where(
            'rc.date_added >',
            date('Y-m-d', strtotime('-30 days'))
        );
    } else {

        if (!empty($filters['from_date'])) {
            $builder->where(
                'rc.date_added >=',
                $filters['from_date'] . ' 00:00:00'
            );
        }

        if (!empty($filters['to_date'])) {
            $builder->where(
                'rc.date_added <=',
                $filters['to_date'] . ' 23:59:59'
            );
        }
    }

    /*
     * User restriction
     */
    if ($user_id != 0) {
        $builder->where('la.user_id', $user_id);
    }

    /*
     * Salesperson filter
     */
    if (!empty($filters['salesperson'])) {
        $builder->where(
            'la.user_id',
            $filters['salesperson']
        );
    }

    /*
     * Status
     */
    if (
        isset($filters['status']) &&
        $filters['status'] !== ''
    ) {
        $builder->where(
            'rc.status',
            $filters['status']
        );
    }

    $builder->orderBy(
        'rc.date_added',
        'DESC'
    );

    /*
     * IMPORTANT:
     * CI4 = limit(number, offset)
     */
    $builder->limit($limit, $start);

    $leads = $builder
        ->get()
        ->getResult();

    /*
     * Tracking data
     */
    foreach ($leads as $lead) {

        $tracking = html_entity_decode(
            $lead->tracking_data ?? '',
            ENT_QUOTES,
            'UTF-8'
        );

        $tracking = json_decode(
            $tracking,
            true
        );

        $lead->lead_source = is_array($tracking)
            ? ($tracking['lead_source'] ?? '')
            : '';

        unset($lead->tracking_data);
    }

    return $leads;
}


    /**
     * Get assigned salesperson
     */
    public function getAssigned($leadid)
    {
        $builder = $this->db->table('lead_assign');

        $builder->select('user_id');

        $builder->where(
            'lead_id',
            $leadid
        );

        $result = $builder->get()->getResult();

        foreach ($result as $value) {

            $userDetails = $this->getUser(
                $value->user_id
            );

            if ($userDetails) {
                return $userDetails->firstname . ' ' . $userDetails->lastname;
            }

            return null;
        }

        return null;
    }


    /**
     * Get OpenCart user
     */
    public function getUser($userid)
    {
        $builder = $this->db->table('oc_user');

        $builder->select([
            'firstname',
            'lastname',
            'user_group_id'
        ]);

        $builder->where(
            'user_id',
            $userid
        );

        return $builder->get()->getRow();
    }


    /**
     * Count leads
     */
    public function get_count($user_id, $filters = [])
    {
        $builder = $this->db->table('oc_request_callback');

        /*
         * Exclude test leads
         */
        // $builder->where(
        //     "oc_request_callback.name NOT LIKE '%test%'",
        //     null,
        //     false
        // );
        $builder->notLike('oc_request_callback.name', 'test');

        /*
         * Date filters
         */
        if (
            empty($filters['from_date']) &&
            empty($filters['to_date'])
        ) {

            $builder->where(
                'oc_request_callback.date_added >',
                date('Y-m-d', strtotime('-30 days'))
            );

        } else {

            if (!empty($filters['from_date'])) {

                $builder->where(
                    'oc_request_callback.date_added >=',
                    $filters['from_date'] . ' 00:00:00'
                );
            }

            if (!empty($filters['to_date'])) {

                $builder->where(
                    'oc_request_callback.date_added <=',
                    $filters['to_date'] . ' 23:59:59'
                );
            }
        }

        /*
         * Assignment
         */
        $builder->join(
            'lead_assign la',
            'la.lead_id = oc_request_callback.request_callback_id',
            'left'
        );

        /*
         * Restrict to salesperson
         */
        if ($user_id != 0) {

            $builder->where(
                'la.user_id',
                $user_id
            );
        }

        /*
         * Salesperson filter
         */
        if (!empty($filters['salesperson'])) {

            $builder->where(
                'la.user_id',
                $filters['salesperson']
            );
        }

        /*
         * Status filter
         */
        if (
            isset($filters['status']) &&
            $filters['status'] !== ''
        ) {

            $builder->where(
                'oc_request_callback.status',
                $filters['status']
            );
        }

        return $builder->countAllResults();
    }


    /**
     * Add remark
     */
    public function addRemark($data)
    {
        $data['date_added'] = date(
            'Y-m-d H:i:s'
        );

        $this->db->table('cf_remark')
            ->insert($data);

        return true;
    }


    /**
     * Add reminder
     */
    public function addReminder($data)
    {
        $this->db->table('lead_reminder')
            ->insert($data);

        return true;
    }


    /**
     * Get today's reminders
     */
    public function getmyreminders($user_id)
    {
        $timestamp = time();

        $date = date(
            'Y-m-d H:i:s',
            strtotime('midnight', $timestamp)
        );

        $date2 = date(
            'Y-m-d H:i:s',
            strtotime('today 23:59:59')
        );

        $builder = $this->db->table('lead_reminder');

        $builder->where(
            'reminder_date >=',
            $date
        );

        $builder->where(
            'reminder_date <=',
            $date2
        );

        if ($user_id != 0) {

            $builder->where(
                'sales_person_id',
                $user_id
            );
        }

        $query = $builder->get();

        if ($query && $query->getNumRows()) {

            return $query->getResult();
        }

        return 'empty';
    }


    /**
     * Get reminders for a lead
     */
    public function getRemindersWithLead($lid)
    {
        $builder = $this->db->table('lead_reminder');

        $builder->where(
            'lead_id',
            $lid
        );

        return $builder->get()->getResult();
    }


    /**
     * Get remarks for a lead
     */
    public function getRemark($lead_id)
    {
        $builder = $this->db->table('cf_remark');

        $builder->where(
            'lead_id',
            $lead_id
        );

        return $builder->get()->getResult();
    }


    /**
     * Get leads with date
     */
    public function getleadswithdate($from, $to, $userid)
    {
        $num = $this->checkuserinleads($userid);

        $builder = $this->db->table(
            'oc_request_callback orc'
        );

        $builder->select(
            'orc.*, la.user_id'
        );

        $builder->join(
            'lead_assign la',
            'la.lead_id = orc.request_callback_id',
            'left'
        );

        /*
         * Same date
         */
        if ($from == $to) {

            $nextdate = date(
                'Y-m-d',
                strtotime('+1 day', strtotime($to))
            );

            $builder->where(
                'orc.date_added >=',
                $to
            );

            $builder->where(
                'orc.date_added <',
                $nextdate
            );

        } else {

            $builder->where(
                'orc.date_added >=',
                $from
            );

            $builder->where(
                'orc.date_added <=',
                $to
            );
        }

        /*
         * If user has leads assigned,
         * restrict results to that user.
         */
        if ($num > 0) {

            $builder->where(
                'la.user_id',
                $userid
            );
        }

        $builder->orderBy(
            'orc.date_added',
            'ASC'
        );

        $query = $builder->get();

        $remarkArray1 = $query->getResult();

        /*
         * Add salesperson name
         */
        foreach ($remarkArray1 as $row) {

            $vidid = $row->user_id;

            $userQuery = $this->db->table('oc_user')
                ->select('firstname')
                ->where('user_id', $vidid)
                ->get();

            $username = $userQuery->getResult();

            $row->user_id = $username;
        }

        /*
         * Add status
         */
        foreach ($remarkArray1 as $row) {

            $vidid = $row->status;

            $statusQuery = $this->db->table('oc_lead_status')
                ->select('status_name')
                ->where('lead_status_id', $vidid)
                ->get();

            $status = $statusQuery->getResult();

            $row->status = $status;
        }

        /*
         * Convert stage
         */
        foreach ($remarkArray1 as $row) {

            $vidid = $row->stage;

            if ($vidid == 0) {

                $stage = 'Cold';

            } elseif ($vidid == 1) {

                $stage = 'Hot';

            } else {

                $stage = 'Closed';
            }

            $row->stage = $stage;
        }

        /*
         * Add remarks
         */
        foreach ($remarkArray1 as $row) {

            $vidid = $row->request_callback_id;

            $remarksQuery = $this->db->table('cf_remark cfr')
                ->select('remark')
                ->where('lead_id', $vidid)
                ->get();

            $remarks = $remarksQuery->getResult();

            $row->remarks = $remarks;
        }

        return $remarkArray1;
    }


    /**
     * Check whether user has assigned leads
     */
    public function checkuserinleads($userid)
    {
        return $this->db
            ->table('lead_assign')
            ->where('user_id', $userid)
            ->countAllResults();
    }


    /**
     * Get all salespeople
     */
    public function getAllSalespeople()
    {
        $builder = $this->db->table('oc_user');

        $builder->select([
            'user_id',
            'firstname',
            'lastname'
        ]);

        $builder->whereIn(
            'user_group_id',
            ['14', '17', '21']
        );

        $builder->where(
            'crm_access',
            1
        );

        return $builder->get()->getResult();
    }


    /**
     * Assign lead to salesperson
     */
    public function assignLeadToSalesperson(
        $leadId,
        $salespersonId
    ) {

        $user = $this->getUser(
            $salespersonId
        );

        if (!$user) {
            return false;
        }

        if (
            $user->user_group_id == 14 ||
            $user->user_group_id == 17 ||
            $user->user_group_id == 21
        ) {

            $data = [
                'lead_id' => $leadId,
                'user_id' => $salespersonId
            ];

            /*
             * Check existing assignment
             */
            $query = $this->db
                ->table('lead_assign')
                ->where($data)
                ->get();

            /*
             * Preserve original behaviour:
             * date_added is added even if assignment already exists.
             */
            $data['date_added'] = date(
                'Y-m-d H:i:s'
            );

            if ($query->getNumRows() === 0) {

                $this->db
                    ->table('lead_assign')
                    ->insert($data);

            } else {

                /*
                 * Original code echoed "already there".
                 * We don't echo from the model in CI4.
                 */
                return true;
            }
        }

        return true;
    }


    /**
     * Remove lead assignment
     */
    public function unassignLead($leadid)
    {
        $this->db
            ->table('lead_assign')
            ->where('lead_id', $leadid)
            ->delete();

        return true;
    }


    /**
     * Get lead statuses
     */
    public function getLeadstatus()
    {
        $builder = $this->db->table(
            'oc_lead_status'
        );

        $builder->where(
            'lead_status_id !=',
            2
        );

        return $builder->get()->getResult();
    }


    /**
     * Count callback requests
     */
    public function getCountRequestcallback()
    {
        return $this->db
            ->table('oc_request_callback')
            ->countAllResults();
    }


    /**
     * Search leads by mobile number
     */
    public function search($mno)
    {
        return $this->db
        ->table('oc_request_callback')
        ->select('request_callback_id, name, status')
        ->like('mobile', $mno)
        ->orderBy('request_callback_id', 'DESC')
        ->limit(10)
        ->get()
        ->getResultArray();
    }


    /**
     * Check Google lead exists
     */
    public function googleLeadExists($googleLeadId)
    {
        return $this->db
            ->table('oc_request_callback')
            ->where(
                'google_lead_id',
                $googleLeadId
            )
            ->countAllResults() > 0;
    }


    /**
     * Add Google lead
     */
    public function addGoogleLead($data)
    {
        $builder = $this->db->table(
            'oc_request_callback'
        );

        $builder->insert($data);

        return $this->db->insertID();
    }


    /**
     * Check Facebook lead exists
     *
     * NOTE:
     * Your original CI3 code checks google_lead_id here.
     * This is intentionally preserved.
     */
    public function facebookLeadExists($leadId)
    {
        return $this->db
            ->table('oc_request_callback')
            ->where(
                'google_lead_id',
                $leadId
            )
            ->countAllResults() > 0;
    }


    /**
     * Add Facebook lead
     */
    public function addFacebookLead($data)
    {
        $builder = $this->db->table(
            'oc_request_callback'
        );

        $builder->insert($data);

        return $this->db->insertID();
    }
}