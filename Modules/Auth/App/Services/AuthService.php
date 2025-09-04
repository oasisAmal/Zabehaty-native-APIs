<?php

namespace Modules\Auth\App\Services;

use App\Enums\Common;
use Modules\Users\App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Services\Integrations\SMS\SMSService;

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

    /**
     * Send Otp
     *
     * @param array $data
     * @return array
     */
    public function sendOtp($data)
    {
        $user = User::where('mobile', $data['mobile'])
        ->orWhere('mobile', format_mobile_number_to_database($data['mobile'], $data['mobile_country_code']))
        ->first();
        if (!$user) {
            return false;
        }

        $user->verification_code = generateRandomNumber(Common::RANDOM_AUTH_CODE_LENGTH);
        // $user->otp_code_expire_at = now()->addMinutes(30);
        $user->save();
        $message = __('auth::messages.otp_code', ['code' => $user->verification_code]);

        return app(SMSService::class)->send($message, $user->mobile, $data['mobile_country_code']);
    }

    /**
     * Verify Otp
     *
     * @param array $data
     * @return array
     */
    public function verifyOtp($data)
    {
        return $data;
    }
}
