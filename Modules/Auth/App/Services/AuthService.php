<?php

namespace Modules\Auth\App\Services;

use Carbon\Carbon;
use App\Enums\Common;
use App\Enums\SocialProvider;
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
     * Check Mobile
     *
     * @param array $data
     * @return array
     */
    public function checkMobile($data): array
    {
        $user = User::where('mobile', $data['mobile'])
            ->orWhere('mobile', format_mobile_number_to_database($data['mobile'], $data['mobile_country_code']))
            ->first();
        if (!$user) {
            return [
                'status' => false,
                'message' => __('auth::messages.mobile_not_found'),
                'data' => null,
            ];
        }
        return [
            'status' => true,
            'message' => __('auth::messages.mobile_found'),
            'data' => null,
        ];
    }
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
            $provider = $data['social_type'];
            if ($data['social_type'] == SocialProvider::GOOGLE_IOS) {
                $provider = 'google';
                // Get credentials based on platform
                $clientId = config("services.google_ios.client_id");
                $clientSecret = config("services.google_ios.client_secret");
                $redirectUri = config("services.google_ios.redirect");

                // Temporarily override Socialite config
                config([
                    "services.google.client_id" => $clientId,
                    "services.google.client_secret" => $clientSecret,
                    "services.google.redirect" => $redirectUri,
                ]);
            }

            $socialUser = Socialite::driver($provider)->stateless()->userFromToken($data['social_token']);

            $user = User::where('social_profile_id', $socialUser->getId())->orWhere('email', $data['email'] ?? $socialUser->getEmail())->first();
            if (!$user) {
                $firstName = $socialUser->getName() ?? $socialUser->getNickname() ?? $data['first_name'] ?? null;
                $lastName = $socialUser->getName() ?? $socialUser->getNickname() ?? $data['last_name'] ?? null;
                $user = User::create([
                    'social_profile_id' => $data['social_profile_id'] ?? $socialUser->getId(),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $data['email'] ?? $socialUser->getEmail(),
                    'social_type' => $provider,
                    'social_token' => $data['social_token'],
                    'is_guest' => false,
                    'old_id' => 0,
                    'app_version' => $data['app_version'],
                ]);
            } else {
                $user->social_profile_id = $data['social_profile_id'] ?? $socialUser->getId();
                $user->email = $data['email'] ?? $socialUser->getEmail();
                $user->social_type = $provider;
                $user->social_token = $data['social_token'];
                $user->app_version = $data['app_version'];
                $user->save();
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
            $data['mobile'] = $data['validate_mobile'];

            // Check if user is already authenticated (guest user with token)
            $currentUser = User::find(auth('api')->id());

            if ($currentUser) {
                if ($currentUser->isGuest()) {
                    // Update existing guest user to registered user
                    $currentUser->first_name = $data['first_name'];
                    $currentUser->last_name = $data['last_name'];
                    $currentUser->mobile = $data['mobile'];
                    $currentUser->country_symbol = $data['mobile_country_code'];
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
                    'country_symbol' => $data['mobile_country_code'],
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
     * @return array|bool
     */
    public function sendOtp($data): array|bool
    {
        $user = User::where('mobile', $data['mobile'])
            ->orWhere('mobile', format_mobile_number_to_database($data['mobile'], $data['mobile_country_code']))
            ->first();
        if (!$user) {
            return [
                'status' => false,
                'status_code' => 422,
                'message' => __('auth::messages.mobile_not_found'),
                'data' => null,
            ];
        }

        // Check if user is blocked using database
        $blockRecord = DB::table('otp_blocks')
            ->where('mobile', $data['mobile'])
            ->where('blocked_until', '>', now())
            ->first();
        
        if ($blockRecord) {
            $minutesRemaining = now()->diffInMinutes($blockRecord->blocked_until, false);
            if ($minutesRemaining > 0) {
                return [
                    'status' => false,
                    'status_code' => 429,
                    'message' => __('auth::messages.otp_blocked', ['minutes' => ceil($minutesRemaining)]),
                    'data' => [
                        'attempts_remaining' => 0,
                        'blocked_until' => $blockRecord->blocked_until
                    ]
                ];
            } else {
                // Block expired, remove it
                DB::table('otp_blocks')->where('mobile', $data['mobile'])->delete();
            }
        }

        // Reset verification attempt counter when sending new OTP
        DB::table('otp_attempts')->where('mobile', $data['mobile'])->delete();

        // $user->verification_code = generateRandomNumber(Common::RANDOM_AUTH_CODE_LENGTH);
        $user->verification_code = '0000';
        $user->updated_at = now()->addMinutes(Common::OTP_EXPIRATION_MINUTES);
        $user->save();
        
        $message = __('auth::messages.otp_code', ['code' => $user->verification_code]);

        // if (!app(SMSService::class)->send($message, $user->mobile, $data['mobile_country_code'])) {
        //     return [
        //         'status' => false,
        //         'status_code' => 422,
        //         'message' => __('auth::messages.failed_to_send_otp'),
        //         'data' => null,
        //     ];
        // }

        return [
            'status' => true,
            'status_code' => 200,
            'message' => __('auth::messages.otp_sent_successfully'),
            'data' => null,
        ];
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
            return [
                'status' => false,
                'status_code' => 422,
                'message' => __('auth::messages.mobile_not_found'),
                'data' => null,
            ];
        }

        // Check if user is blocked using database
        $blockRecord = DB::table('otp_blocks')
            ->where('mobile', $data['mobile'])
            ->where('blocked_until', '>', now())
            ->first();
        
        if ($blockRecord) {
            $minutesRemaining = now()->diffInMinutes($blockRecord->blocked_until, false);
            if ($minutesRemaining > 0) {
                return [
                    'status' => false,
                    'status_code' => 429,
                    'message' => __('auth::messages.otp_blocked', ['minutes' => ceil($minutesRemaining)]),
                    'data' => [
                        'attempts_remaining' => 0,
                        'blocked_until' => $blockRecord->blocked_until
                    ]
                ];
            } else {
                // Block expired, remove it
                DB::table('otp_blocks')->where('mobile', $data['mobile'])->delete();
            }
        }

        // Check OTP validity
        if ($user->verification_code != $data['verification_code']) {
            // Get current attempts from database
            $currentAttempts = DB::table('otp_attempts')
                ->where('mobile', $data['mobile'])
                ->where('created_at', '>', now()->subMinutes(Common::OTP_ATTEMPT_COUNTER_MINUTES))
                ->count();
            
            $attempts = $currentAttempts + 1;
            
            if ($attempts > Common::OTP_ATTEMPT_MAX_ATTEMPTS) {
                // Set 15-minute block in database
                $blockedUntil = now()->addMinutes(Common::OTP_ATTEMPT_BLOCK_MINUTES);
                DB::table('otp_blocks')->updateOrInsert(
                    ['mobile' => $data['mobile']],
                    [
                        'mobile' => $data['mobile'],
                        'blocked_until' => $blockedUntil,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
                
                return [
                    'status' => false,
                    'status_code' => 429,
                    'message' => __('auth::messages.otp_attempts_exceeded', ['minutes' => Common::OTP_ATTEMPT_BLOCK_MINUTES]),
                    'data' => [
                        'attempts_remaining' => 0,
                        'blocked_until' => $blockedUntil->format('Y-m-d H:i:s')
                    ]
                ];
            } else {
                // Store attempt in database
                DB::table('otp_attempts')->insert([
                    'mobile' => $data['mobile'],
                    'attempts' => $attempts,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $remainingAttempts = Common::OTP_ATTEMPT_MAX_ATTEMPTS - $attempts;
                
                return [
                    'status' => false,
                    'status_code' => 422,
                    'message' => __('auth::messages.otp_invalid_attempts_remaining', ['attempts' => $remainingAttempts]),
                    'data' => [
                        'attempts_remaining' => $remainingAttempts,
                        'blocked_until' => null
                    ]
                ];
            }
        }

        // Check if OTP is expired
        if ($user->updated_at < now()->subMinutes(Common::OTP_EXPIRATION_MINUTES)) {
            return [
                'status' => false,
                'status_code' => 422,
                'message' => __('auth::messages.otp_expired', ['minutes' => ceil(now()->diffInMinutes($user->updated_at, false))]),
                'data' => null,
            ];
        }

        // OTP is correct - clear all counters and block status from database
        DB::table('otp_attempts')->where('mobile', $data['mobile'])->delete();
        DB::table('otp_blocks')->where('mobile', $data['mobile'])->delete();

        if ($data['device_token'] && $data['device_type']) {
            $user->device_token = $data['device_token'];
            $user->device_type = $data['device_type'];
            $user->device_brand = $data['device_brand'];
        }

        $user->verification_code = null;
        $user->is_verified = true;
        $user->save();

        if ($data['return_token'] ?? false) {
            return [
                'status' => true,
                'status_code' => 200,
                'message' => __('auth::messages.otp_verified_successfully'),
                'data' => $this->loginSanctum($user),
            ];
        }

        return [
            'status' => true,
            'status_code' => 200,
            'message' => __('auth::messages.otp_verified_successfully'),
            'data' => null,
        ];
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
            'token' => $user->createToken('userAuthToken', ['*'], now()->addMinutes(config('session.lifetime')))->plainTextToken,
            'expires_at' => Carbon::parse($user->tokens()->first()->expires_at)->timestamp,
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
            'data' => ['token' => $user->createToken('userAuthToken', ['*'], now()->addMinutes(config('session.lifetime')))->plainTextToken, 'expires_at' => config('session.lifetime')],
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
        $user = User::whereId(auth('api')->id())->first();
        $user->password = md5($data['new_password']);
        $user->save();

        return [
            'status' => true,
            'message' => __('auth::messages.password_changed_successfully'),
            'data' => null,
        ];
    }

    /**
     * Update Mobile
     *
     * @param array $data
     * @return array
     */
    public function updateMobile($data): array
    {
        $user = User::find(auth('api')->id());
        $user->mobile = $data['validate_mobile'];
        $user->save();

        return [
            'status' => true,
            'message' => __('auth::messages.mobile_updated_successfully'),
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
