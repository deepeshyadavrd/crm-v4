<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'oc_user';
    protected $primaryKey = 'user_id';
    protected $returnType = 'array';

    /**
     * Verify OpenCart admin user credentials.
     */
    public function verify_opencart_admin_user(string $username, string $password)
    {
        $user = $this->select([
            'user_id',
            'username',
            'password',
            'salt',
            'status',
            'user_group_id',
            'firstname',
            'lastname',
            'email',
            'mobile'
        ])
        ->where('username', $username)
        ->where('status', 1)
        ->where('crm_access', 1)
        ->first();

        if (!$user) {
            return false;
        }

        /*
         * OpenCart password:
         *
         * SHA1(
         *     salt .
         *     SHA1(
         *         salt .
         *         SHA1(password)
         *     )
         * )
         */
        $hashedPassword = sha1(
            $user['salt'] .
            sha1(
                $user['salt'] .
                sha1($password)
            )
        );

        if (!hash_equals($user['password'], $hashedPassword)) {
            return false;
        }

        return $user;
    }
}