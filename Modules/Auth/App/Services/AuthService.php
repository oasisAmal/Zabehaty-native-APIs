<?php

namespace Modules\Auth\App\Services;

use App\Enums\Common;
use Modules\Users\App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;
use App\Services\Integrations\SMS\SMSService;
use Modules\Auth\App\Transformers\AuthResource;

class AuthService
{
    /**
     * Login by password
     *
     * @param array $data
     * @return array
     */
    public function login($data): array
    {
        $user = User::where('mobile', $data['mobile'])
            ->orWhere('mobile', format_mobile_number_to_database($data['mobile'], $data['mobile_country_code']))
            ->first();
        if (!$user) {
            return [
                'status' => false,
                'message' => __('auth::messages.password_or_mobile_incorrect'),
                'data' => null,
            ];
        }

        if (md5($data['password']) != $user->password) {
            return [
                'status' => false,
                'message' => __('auth::messages.password_or_mobile_incorrect'),
                'data' => null,
            ];
        }

        return [
            'status' => true,
            'message' => __('auth::messages.login_successfully'),
            'data' => $this->loginSanctum($user),
        ];
    }

    /**
     * Send Otp
     *
     * @param array $data
     * @return bool
     */
    public function sendOtp($data): bool
    {
        $user = User::where('mobile', $data['mobile'])
            ->orWhere('mobile', format_mobile_number_to_database($data['mobile'], $data['mobile_country_code']))
            ->first();
        if (!$user) {
            return false;
        }

        $user->verification_code = generateRandomNumber(Common::RANDOM_AUTH_CODE_LENGTH);
        $user->updated_at = now()->addMinutes(10);
        $user->save();
        $message = __('auth::messages.otp_code', ['code' => $user->verification_code]);

        return app(SMSService::class)->send($message, $user->mobile, $data['mobile_country_code']);
    }

    /**
     * Verify Otp
     *
     * @param array $data
     * @return array|bool
     */
    public function verifyOtp($data): array|bool
    {
        $user = User::where('mobile', $data['mobile'])
            ->orWhere('mobile', format_mobile_number_to_database($data['mobile'], $data['mobile_country_code']))
            ->first();
        if (!$user) {
            return false;
        }
        if ($user->verification_code != $data['verification_code']) {
            return false;
        }
        if ($user->updated_at < now()->subMinutes(10)) {
            return false;
        }
        if ($data['device_token'] && $data['device_type']) {
            $user->device_token = $data['device_token'];
            $user->device_type = $data['device_type'];
            $user->device_brand = $data['device_brand'];
        }

        $user->verification_code = null;
        $user->is_verified = true;
        $user->save();

        if ($data['return_token'] ?? false) {
            return $this->loginSanctum($user);
        }

        return true;
    }

    /**
     * Login Sanctum
     *
     * @param User $user
     * @return array
     */
    public function loginSanctum($user): array
    {
        $user->tokens()->delete();
        return [
            'token' => $user->createToken('userAuthToken')->plainTextToken,
            'profile' => new AuthResource($user),
        ];
    }

    /**
     * Logout
     *
     * @param User $user
     * @return array
     */
    public function logout($user): array
    {
        $user->tokens()->delete();
        return [
            'status' => true,
            'message' => __('auth::messages.logout_successfully'),
            'data' => null,
        ];
    }

    /**
     * Refresh Token
     *
     * @param string $bearerToken
     * @return array
     */
    public function refreshToken($bearerToken): array
    {
        $currentToken = PersonalAccessToken::findToken($bearerToken);
        if (!$bearerToken || !$currentToken) {
            return [
                'status' => false,
                'message' => __('auth::messages.invalid_bearer_token'),
                'data' => null,
            ];
        }
        $user = User::whereId($currentToken->tokenable_id)->first();
        if (!$user) {
            return [
                'status' => false,
                'message' => __('auth::messages.user_not_found'),
                'data' => null,
            ];
        }
        $currentToken->delete();
        return [
            'status' => true,
            'message' => __('auth::messages.refresh_token_successfully'),
            'data' => ['token' => $user->createToken('userAuthToken')->plainTextToken],
        ];
    }

    /**
     * Change Password
     *
     * @param array $data
     * @return array
     */
    public function changePassword($data): array
    {
        $user = User::where('mobile', $data['mobile'])
            ->orWhere('mobile', format_mobile_number_to_database($data['mobile'], $data['mobile_country_code']))
            ->first();
        if (!$user) {
            return [
                'status' => false,
                'message' => __('auth::messages.user_not_found'),
                'data' => null,
            ];
        }
        $user->password = md5($data['new_password']);
        $user->save();
        return [
            'status' => true,
            'message' => __('auth::messages.password_changed_successfully'),
            'data' => null,
        ];
    }
}
