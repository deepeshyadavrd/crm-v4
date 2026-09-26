<?php

namespace App\Controllers;

use App\Services\Authorization;
use App\Controllers\WSController;
use App\Models\LeadsModel;

class Leads extends WSController {
    protected $leadsModel;
    protected $db;
    protected $authorization;

    public function __construct() {
        helper(['url', 'form']);

        $this->leadsModel = new LeadsModel();
        $this->db = \Config\Database::connect();
        $this->authorization = new Authorization();
    }

    /* Lead listing */

    public function index($page = 1) {
        if (!$this->authorization->can('leads','view')) {
            return $this->response->setStatusCode(403)->setBody('Access Denied');
        }

        $data['salespeople'] = $this->leadsModel->getAllSalespeople();

        /* * Lead scope  * all  = Admin / Sales Managers * own  = Salesperson * none = No lead access */
        $leadScope = $this->authorization->getLeadScope();

        $user_id = 0;
        if ($leadScope === 'own') {
            $user_id = (int) session()->get('user_id');
        }

        $filters = [
            'from_date'   => $this->request->getGet('from_date'),
            'to_date'     => $this->request->getGet('to_date'),
            'status'      => $this->request->getGet('status'),
            'salesperson' => $this->request->getGet('salesperson')
        ];

        /* Pagination */
        $perPage = 15;

        $page = max(1, (int) $page);

        $totalRows = $this->leadsModel->get_count($user_id, $filters);

        $offset = ($page - 1) * $perPage;        

        $data['leads'] = $this->leadsModel->getLeads(
            $offset,
            $perPage,
            $user_id,
            $filters
        );
        $data['links'] = $this->createLeadPagination(
            $page,
            $perPage,
            $totalRows,
            $filters
        );

        $data['getreminder'] = true;

        /*
         * CI4 view loading
         */
        $html = $this->website_header();

        $html .= view('lead_list', $data);

        $html .= $this->website_footer();

        return $html;
    }


    /**
     * Lead details
     */
    public function view($lid) {
        if (!$this->authorization->can('leads', 'view')) {
            return $this->response
                ->setStatusCode(403)
                ->setBody('Access denied.');
        }
        $leadScope = $this->authorization->getLeadScope();

        $userId = 0;

        if ($leadScope === 'own') {
            $userId = (int) session()->get('user_id');
        }

        $data['row'] = $this->leadsModel->getlead( $lid, $userId );

        if (empty($data['row'])) {
            return $this->response
                ->setStatusCode(404)
                ->setBody('Lead not found.');
        }

        $data['sales_person_id'] = session()->get('user_id');
        $data['sales_person_mobile'] = session()->get('mobile');
        $data['sales_person_name'] = session()->get('username');

        // $data['row'] = $this->leadsModel->getlead($lid);

        $data['lead_status'] = $this->leadsModel->getLeadstatus();

        $data['reminders'] = $this->leadsModel->getRemindersWithLead($lid);

        $html = $this->website_header();

        $html .= view('lead_details', $data);

        $html .= $this->website_footer();

        return $html;
    }


    /* Update lead status */
    public function update_lead_status() {
        if (!$this->authorization->can('leads', 'change_status')) {
            return $this->response
                ->setStatusCode(403)
                ->setBody('Access denied.');
        }

        $lead_id = $this->request->getPost('lid');
        $lead_status = $this->request->getPost('opstatus');

        if ($lead_id == '' && $lead_status == '') {
            return $this->response->setBody('error');
        }
        if (!$this->canAccessLead($lead_id)) {
            return $this->response
                ->setStatusCode(403)
                ->setBody('Access denied.');
        }

        $this->db->table('oc_request_callback')
            ->where('request_callback_id', $lead_id)
            ->update([
                'status' => $lead_status
            ]);

        return $this->response->setBody('success');
        
    }


    /* Update lead stage and status */
    public function update_lead_stage() {
        if (!$this->authorization->can('leads', 'change_status')) {
            return $this->response
                ->setStatusCode(403)
                ->setBody('Access denied.');
        }
        $lead_id = $this->request->getPost('lid');
        $lead_stage = $this->request->getPost('lpstage');
        $lead_status = $this->request->getPost('lpstatus');

        if ($lead_id  == '' && $lead_stage == '' && $lead_status == '') {
            return $this->response->setBody('error');
        }

        if (!$this->canAccessLead($lead_id)) {
            return $this->response
                ->setStatusCode(403)
                ->setBody('Access denied.');
        }
        $this->db->table('oc_request_callback')
            ->where('request_callback_id', $lead_id)
            ->update([
                'stage'  => $lead_stage,
                'status' => $lead_status
            ]);

        return $this->response->setBody('success');

    }


    /* Add reminder */
    public function addReminder() {
        if (!$this->authorization->can('leads', 'add_remark')) {
            return $this->response
                ->setStatusCode(403)
                ->setBody('Access denied.');
        }

        date_default_timezone_set('Asia/Kolkata');

        $txtDate = $this->request->getPost('txtDate');
        $leadId = $this->request->getPost('lead_id');

        if ($txtDate == '' || $leadId == '') {
            return $this->response->setBody('error');
        }

        if (!$this->canAccessLead($leadId)) {
            return $this->response
                ->setStatusCode(403)
                ->setBody('Access denied.');
        }

        $time = date(
            'Y-m-d H:i',
            strtotime($txtDate)
        );

        $data = [
            'lead_id'         => $leadId,
            'sales_person_id' => $this->request->getPost('sales_person_id'),
            'title'           => $this->request->getPost('title'),
            'description'     => $this->request->getPost('descript'),
            'reminder_date'   => $time,
            'date_added'      => date('Y-m-d H:i:s')
        ];

        $result = $this->leadsModel->addReminder($data);

        if ($result) {
            return $this->response->setBody('added');
        }

        return $this->response->setBody('error');
    }


    /* Add remark */
    public function addRemark() {
        if (!$this->authorization->can('leads', 'add_remark')) {
            return $this->response
                ->setStatusCode(403)
                ->setBody('Access denied.');
        }

        $leadId = $this->request->getPost('lead_id');

        if ($leadId == '' || !$this->canAccessLead($leadId)) {
            return $this->response
                ->setStatusCode(403)
                ->setBody('Access denied.');
        }

        $data = [
            'lead_id' => $leadId,
            'remark'  => $this->request->getPost('remark')
        ];

        $this->leadsModel->addRemark($data);

        $remarks = $this->leadsModel->getRemark($leadId);

        return $this->response->setBody($remarks);
    }


    /* Assign lead to salesperson */
    public function assign() {
        if (!$this->authorization->can('leads', 'assign')) {
            return $this->response
                ->setStatusCode(403)
                ->setBody('Access denied.');
        }

        $leadIds = $this->request->getPost('lead_id');
        $userid = $this->request->getPost('salesperson_id');

        if (
            empty($leadIds) ||
            empty($userid)
        ) {
            return $this->response->setBody('error');
        }

        if (is_array($leadIds)) {
            $resp = '';

            foreach ($leadIds as $value) {
                if (
                    $this->leadsModel->assignLeadToSalesperson(
                        $value,
                        $userid
                    )
                ) {
                    $resp = 'Lead assigned';
                } else {
                    return $this->response->setBody(
                        'Lead assignment failed'
                    );
                }
            }

            return $this->response->setBody($resp);
        }

        if (
            $this->leadsModel->assignLeadToSalesperson(
                $leadIds,
                $userid
            )
        ) {
            return $this->response->setBody('Lead assigned');
        }

        return $this->response->setBody(
            'Lead assignment failed'
        );
    }


    /* Unassign lead */
    public function unassign() {
        if (!$this->authorization->can('leads', 'unassign')) {
            return $this->response
                ->setStatusCode(403)
                ->setBody('Access denied.');
        }

        $leadIds = $this->request->getPost('lead_id');

        if (empty($leadIds)) {
            return $this->response->setBody('error');
        }

        if (is_array($leadIds)) {
            $resp = '';

            foreach ($leadIds as $value) {
                if ($this->leadsModel->unassignLead($value)) {
                    $resp = 'Lead un-assigned';
                } else {
                    return $this->response->setBody('Lead un-assignment failed');
                }
            }

            return $this->response->setBody($resp);
        }

        if ($this->leadsModel->unassignLead($leadIds)) {
            return $this->response->setBody('Lead un-assigned');
        }

        return $this->response->setBody('Lead un-assignment failed');
    }

    /* Self assign */
    public function selfAssign($leadid, $userid = null) {
        if (!$this->authorization->can('leads', 'assign')) {
            return $this->response
                ->setStatusCode(403)
                ->setBody('Access denied.');
        }

        $userId = (int) session()->get('user_id');

        if (!$this->canAccessLead($leadid)) {
            return $this->response
                ->setStatusCode(403)
                ->setBody('Access denied.');
        }

        if (
            $this->leadsModel->assignLeadToSalesperson(
                $leadid,
                $userId
            )
        ) {
            return $this->response->setBody('Lead assigned');
        }

        return $this->response->setBody(
            'Lead assignment failed'
        );
    }


    /* Get leads by date */
    public function getwithdate() {
        if (!$this->authorization->can('leads', 'view')) {
            return $this->response
                ->setStatusCode(403)
                ->setBody('Access denied.');
        }

        $from = $this->request->getPost('fromdate');
        $to = $this->request->getPost('todate');

        $leadScope = $this->authorization->getLeadScope();

        if ($leadScope === 'own') {
            $userid = (int) session()->get('user_id');
        } elseif ($leadScope === 'all') {
            $userid = 0;
        } else {
            return $this->response
                ->setStatusCode(403)
                ->setBody('Access denied.');
        }

        $leads = $this->leadsModel->getleadswithdate(
            $from,
            $to,
            $userid
        );

        return $this->response->setJSON($leads);
    }


    /**
     * Get current user's reminders
     */
    public function getmyreminder()
    {
        $user_id = session()->get('user_id');

        $data = $this->leadsModel->getmyreminders($user_id);

        return $this->response->setJSON($data);
    }


    /* Mark reminder as seen */
    public function mark_seen($id)
    {
        if (!session()->get('is_logged_in')) {
            return redirect()->to(
                base_url('auth/login')
            );
        }
    
        $userId = (int) session()->get('user_id');
    
        $builder = $this->db
            ->table('lead_reminder')
            ->where('lr_id', $id);
    
        if (
            $this->authorization->getLeadScope() === 'own'
        ) {
            $builder->where(
                'sales_person_id',
                $userId
            );
        }
    
        $builder->update([
            'is_seen' => 1
        ]);
    
        return $this->response->setBody('success');
    }


    /* Search lead */
    public function search() {
        if (!$this->authorization->can('leads', 'view')) {
            return $this->response
                ->setStatusCode(403)
                ->setBody('Access denied.');
        }

        $mno = $this->request->getPost('input_value');

        $data = $this->leadsModel->search($mno);

        if ($this->authorization->getLeadScope() === 'own') {
            $userId = (int) session()->get('user_id');

            $filteredData = [];

            foreach ($data as $lead) {
                if (
                    $this->leadsModel->getlead(
                        $lead['request_callback_id'],
                        $userId
                    )
                ) {
                    $filteredData[] = $lead;
                }
            }

            $data = $filteredData;
        }

        return $this->response->setJSON($data);
    }


    /**
     * Product search
     */
    public function searchProduct()
    {
        $term = trim(
            (string) $this->request->getGet('term')
        );

        $words = explode(' ', $term);

        $builder = $this->db->table('oc_product p');

        $builder->select([
            'p.product_id',
            'pd.name',
            'p.image',
            'p.price AS original_price',
            'ps.price AS special_price'
        ]);

        /*
         * Product description
         */
        $builder->join(
            'oc_product_description pd',
            'pd.product_id = p.product_id',
            'left'
        );

        /*
         * Special price
         */
        $builder->join(
            'oc_product_special ps',
            'ps.product_id = p.product_id',
            'left'
        );

        $builder->where('p.status', 1);

        /*
         * Search words
         */
        $builder->groupStart();

        foreach ($words as $word) {

            if (!empty($word)) {

                $builder->groupStart();

                $builder->like('pd.name', $word);
                $builder->orLike('p.model', $word);

                $builder->groupEnd();
            }
        }

        $builder->groupEnd();

        /*
         * Exclude test/custom products
         */
        $builder->notLike('pd.name', 'test');
        $builder->notLike('pd.name', 'custom');

        $builder->limit(10);

        /*
         * Prioritize exact/full-term name matches.
         *
         * IMPORTANT:
         * CI4's Query Builder can escape normal values.
         * We use escape() here before putting the value into
         * the CASE expression.
         */
        $escapedTerm = $this->db->escape($term);

        $builder->orderBy(
            "CASE
                WHEN pd.name LIKE CONCAT('%', {$escapedTerm}, '%') THEN 1
                ELSE 2
            END",
            'ASC',
            false
        );

        $query = $builder->get()->getResultArray();

        $result = [];

        /*
         * OpenCart website image URL
         */
        $imageBase = 'https://www.urbanwood.in/';

        foreach ($query as $row) {

            $result[] = [
                'id' => $row['product_id'],

                'name' => $row['name'],

                'original_price' => number_format(
                    (float) $row['original_price'],
                    2
                ),

                'special_price' => !empty($row['special_price'])
                    ? number_format(
                        (float) $row['special_price'],
                        2
                    )
                    : null,

                'image' => !empty($row['image'])
                    ? $imageBase . 'image/' . $row['image']
                    : $imageBase . 'image/no_image.png'
            ];
        }

        return $this->response->setJSON($result);
    }

    private function canAccessLead($leadId): bool {
        if (!$this->authorization->can('leads', 'view')) {
            return false;
        }

        $leadScope = $this->authorization->getLeadScope();

        if ($leadScope === 'all') {
            return true;
        }

        if ($leadScope === 'own') {
            $userId = (int) session()->get('user_id');

            return !empty(
                $this->leadsModel->getlead($leadId, $userId)
            );
        }

        return false;
    }
    private function createLeadPagination(
        int $currentPage,
        int $perPage,
        int $totalRows,
        array $filters = []
    ): string {
        $totalPages = (int) ceil($totalRows / $perPage);
    
        if ($totalPages <= 1) {
            return '';
        }
    
        $html = '<ul class="pagination">';
    
        /*
         * Build filter query string
         */
        $query = [];
    
        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '') {
                $query[$key] = $value;
            }
        }
    
        $queryString = !empty($query)
            ? '?' . http_build_query($query)
            : '';
    
        /*
         * Previous
         */
        if ($currentPage > 1) {
    
            $previousPage = $currentPage - 1;
    
            $url = $previousPage == 1
                ? site_url('leads') . $queryString
                : site_url('leads/' . $previousPage) . $queryString;
    
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . esc($url) . '">';
            $html .= '&laquo;';
            $html .= '</a>';
            $html .= '</li>';
        }
    
        /*
         * Page numbers
         */
        for ($i = 1; $i <= $totalPages; $i++) {
    
            $url = $i == 1
                ? site_url('leads') . $queryString
                : site_url('leads/' . $i) . $queryString;
    
            $active = ($i == $currentPage)
                ? ' active'
                : '';
    
            $html .= '<li class="page-item' . $active . '">';
            $html .= '<a class="page-link" href="' . esc($url) . '">';
            $html .= $i;
            $html .= '</a>';
            $html .= '</li>';
        }
    
        /*
         * Next
         */
        if ($currentPage < $totalPages) {
    
            $nextPage = $currentPage + 1;
    
            $url = site_url('leads/' . $nextPage) . $queryString;
    
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . esc($url) . '">';
            $html .= '&raquo;';
            $html .= '</a>';
            $html .= '</li>';
        }
    
        $html .= '</ul>';
    
        return $html;
    }
}
