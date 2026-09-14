<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function __construct()
    {
        // Load URL and Form helpers globally for redirects and input scrubbing
        helper(['url', 'form']);
    }

    public function login()
    {
        // 1. If already logged in, redirect to dashboard using modern session check
        if (session()->get('is_logged_in')) {
            return redirect()->to('dashboard');
        }

        $data['title'] = 'CRM Login';
        
        // 2. Return the view directly
        return view('login_form', $data);
    }

    public function process_login()
    {
        // 3. Collect post data using the Request object
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $userModel = new UserModel();
        $user = $userModel->verify_opencart_admin_user($username, $password);

        if ($user) {
            // 4. Set session data using the global session helper array function
            $sessionData = [
                'user_id'       => $user['user_id'],
                'username'      => $user['username'],
                'user_group_id' => $user['user_group_id'],
                'is_logged_in'  => true
            ];
            session()->set($sessionData);
            
            return redirect()->to('dashboard');
        } else {
            // 5. Flashdata syntax update for temporary error notices
            session()->setFlashdata('error', 'Invalid username or password or inactive user.');
            
            return redirect()->to('auth/login');
        }
    }

    public function logout()
    {
        // 6. Destroy session completely and clear browser authentication cookie footprints
        session()->destroy();
        
        return redirect()->to('auth/login');
    }
}