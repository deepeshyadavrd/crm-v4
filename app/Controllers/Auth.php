<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    protected UserModel $userModel;

    public function __construct() {
        helper(['url', 'form']);
        $this->userModel = new UserModel();
    }

    public function login() {
        if (session()->get('is_logged_in')) {
            return redirect()->to(base_url('/'));
        }

        $data['title'] = 'CRM Login';

        return view('login_form', $data);
    }

    public function process_login() {

        $username = trim((string) $this->request->getPost('username'));
        $password = (string) $this->request->getPost('password');

        if($username === '' || $password === ''){
            session()->setFlashdata(
                'error',
                'Username and password required'
            );
            return redirect()->to(base_url('auth/login'));
        }
        $user = $this->userModel->verify_opencart_admin_user($username, $password);

        if (!$user) {
            
            session()->setFlashData(
                'error',
                'Invalid username or password or inactive user.'
            );
            
            return redirect()->to(base_url('auth/login'));
        }
        
        session()->regenerate(true);

        session()->set([
            'user_id'       => $user['user_id'],
            'username'      => $user['username'],
            'user_group_id' => $user['user_group_id'],
            'firstname'     => $user['firstname'],
            'lastname'      => $user['lastname'],
            'email'         => $user['email'],
            'is_logged_in'  => true,
        ]);
            
            return redirect()->to(base_url('/'));
    }

    public function logout() {
        session()->destroy();
        
        return redirect()->to(base_url('auth/login'));
    }
}