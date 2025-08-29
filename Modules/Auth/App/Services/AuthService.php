<?php

namespace Modules\Auth\App\Services;

use Modules\Users\App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Login by password
     *
     * @param array $data
     * @return array
     */
    public function loginByPassword($data)
    {
        $user = User::where('phone', $data['phone'])->first();
        if (!$user) {
            throw new \Exception('User not found');
        }

        if (!Hash::check($data['password'], $user->password)) {
            throw new \Exception('Invalid password');
        }
        return $user;
    }
}