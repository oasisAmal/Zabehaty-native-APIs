<?php

namespace Modules\Auth\App\Services;

use App\Enums\Common;
use Illuminate\Support\Facades\DB;
use Modules\Users\App\Models\User;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Socialite\Facades\Socialite;
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

        if ($data['app_version'] > $user->app_version) {
            $user->app_version = $data['app_version'];
            $user->save();
        }

        return [
            'status' => true,
            'message' => __('auth::messages.login_successfully'),
            'data' => $this->loginSanctum($user),
        ];
    }

    /**
     * Social Login
     *
     * @param array $data
     * @return array
     */
    public function socialLogin($data): array
    {
        try {
            // $socialUser = Socialite::driver($data['social_type'])->stateless()->userFromToken($data['social_token']);
            $socialUser = Socialite::driver($data['social_type'])->userFromToken($data['social_token']);

            $user = User::where('social_profile_id', $socialUser->getId())
                ->where('social_type', $data['social_type'])
                ->first();

            if (!$user) {
                $user = User::create([
                    'first_name' => $socialUser->getName() ?? $socialUser->getNickname(),
                    'last_name' => $socialUser->getName() ?? $socialUser->getNickname(),
                    'email' => $data['email'] ?? $socialUser->getEmail(),
                    'social_type' => $data['social_type'],
                    'social_profile_id' => $data['social_profile_id'] ?? $socialUser->getId(),
                    'social_token' => $data['social_token'],
                    'is_guest' => false,
                    'old_id' => 0,
                    'app_version' => $data['app_version'],
                ]);
            }

            return [
                'status' => true,
                'message' => __('auth::messages.social_login_successfully'),
                'data' => $this->loginSanctum($user),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to social login', ['error' => $e->getMessage()]);
            return [
                'status' => false,
                'message' => __('auth::messages.invalid_social_token'),
                'data' => null,
            ];
        }
    }

    /**
     * Register
     *
     * @param array $data
     * @return array
     */
    public function register($data): array
    {
        DB::beginTransaction();
        try {
            // Format mobile number to database, to avoid issues with phone number validation
            $data['mobile'] = format_mobile_number_to_database($data['mobile'], $data['mobile_country_code']);

            // Check if user is already authenticated (guest user with token)
            $currentUser = User::find(auth('api')->id());

            if ($currentUser) {
                if ($currentUser->isGuest()) {
                    // Update existing guest user to registered user
                    $currentUser->first_name = $data['first_name'];
                    $currentUser->last_name = $data['last_name'];
                    $currentUser->mobile = $data['mobile'];
                    $currentUser->email = $data['email'] ?? null;
                    $currentUser->password = md5($data['password']);
                    $currentUser->is_guest = false;
                    $currentUser->is_verified = true;
                    $currentUser->app_version = $data['app_version'];
                    $currentUser->old_id = 0;
                    $currentUser->save();

                    DB::commit();

                    return [
                        'status' => true,
                        'message' => __('auth::messages.guest_registered_successfully'),
                        'data' => $this->loginSanctum($currentUser),
                    ];
                } else {
                    // User is already registered
                    return [
                        'status' => false,
                        'message' => __('auth::messages.user_already_registered'),
                        'data' => null,
                    ];
                }
            } else {
                // Create new registered user
                $user = User::create([
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'mobile' => $data['mobile'],
                    'email' => $data['email'] ?? null,
                    'password' => md5($data['password']),
                    'is_guest' => false,
                    'app_version' => $data['app_version'],
                    'old_id' => 0,
                ]);

                DB::commit();

                return [
                    'status' => true,
                    'message' => __('auth::messages.register_successfully'),
                    'data' => $this->loginSanctum($user),
                ];
            }
        } catch (\Throwable $th) {
            Log::error('Failed to register user', ['error' => $th->getMessage()]);
            DB::rollBack();
            return [
                'status' => false,
                'message' => __('auth::messages.failed_to_register'),
                'data' => null,
            ];
        }
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

        // $user->verification_code = generateRandomNumber(Common::RANDOM_AUTH_CODE_LENGTH);
        $user->verification_code = '0000';
        $user->updated_at = now()->addMinutes(10);
        $user->save();
        $message = __('auth::messages.otp_code', ['code' => $user->verification_code]);

        return true;

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
            'expires_at' => config('session.lifetime'),
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
            'data' => ['token' => $user->createToken('userAuthToken')->plainTextToken, 'expires_at' => config('session.lifetime')],
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

    /**
     * Profile
     *
     * @param User $user
     * @return array
     */
    public function profile($user): array
    {
        return [
            'status' => true,
            'message' => __('auth::messages.profile_get_successfully'),
            'data' => new AuthResource($user),
        ];
    }

    /**
     * Delete Account
     *
     * @param User $user
     * @return array
     */
    public function deleteAccount($user): array
    {
        try {
            $user->tokens()->delete();
            $user->delete();
            return [
                'status' => true,
                'message' => __('auth::messages.account_deleted_successfully'),
                'data' => null,
            ];
        } catch (\Throwable $th) {
            Log::error('Failed to delete account', ['error' => $th->getMessage()]);
            return [
                'status' => false,
                'message' => __('auth::messages.failed_to_delete_account'),
                'data' => null,
            ];
        }
    }

    /**
     * Create Guest User
     *
     * @param array $data
     * @return array
     */
    public function createGuest($data = []): array
    {
        try {
            $guestData = [
                'first_name' => 'Guest',
                'last_name' => 'User',
                'mobile' => null,
                'email' => null,
                'is_guest' => true,
                'app_version' => $data['app_version'],
                'old_id' => 0,
            ];

            $user = User::create($guestData);

            if ($data['device_token'] && $data['device_type']) {
                $user->device_token = $data['device_token'];
                $user->device_type = $data['device_type'];
                $user->device_brand = $data['device_brand'];
            }

            $user->save();

            return [
                'status' => true,
                'message' => __('auth::messages.guest_created_successfully'),
                'data' => $this->loginSanctum($user),
            ];
        } catch (\Throwable $th) {
            Log::error('Failed to create guest user', ['error' => $th->getMessage()]);
            return [
                'status' => false,
                'message' => __('auth::messages.failed_to_create_guest'),
                'data' => null,
            ];
        }
    }
}
