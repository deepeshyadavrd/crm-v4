<?php

namespace App\Controllers;

// Extend the core BaseController provided by the clean CI4 system
use App\Controllers\BaseController;

class WSController extends BaseController
{
    public function __construct()
    {
        // Output buffering initialization
        ob_start();
        
        // In CI4, you don't load the session library manually. 
        // It initializes automatically. Helpers are declared globally:
        helper(['url', 'form']);
    }

    /* Replaces check_isvaliduser() security block */
    public function checkIsValidUser()
    {
        // session() is a global helper function available anywhere in CI4
        if (!session()->get('is_logged_in')) {
            // In CI4, you MUST explicitly use 'return' with redirects
            header('Location: ' . base_url('auth/login'));
            exit(); 
        }
    }

    /* Renders your header view fragment */
    public function website_header($seo_data = '')
    {
        $this->checkIsValidUser();

        $data['user_type'] = session()->get('user_group_id');
        
        // In CI4, view() returns a string instead of auto-printing to the screen
        return view('common/header', $data);
    }

    /* Renders your footer view fragment */
    public function website_footer()
    {
        return view('common/footer');
    }
}