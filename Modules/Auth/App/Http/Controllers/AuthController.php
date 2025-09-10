<?php

namespace Modules\Auth\App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Modules\Auth\App\Services\AuthService;
use Modules\Auth\App\Http\Requests\LoginRequest;
use Modules\Auth\App\Http\Requests\SendOtpRequest;
use Modules\Auth\App\Http\Requests\VerifyOtpRequest;
use Modules\Auth\App\Http\Requests\ChangePasswordRequest;

class AuthController extends Controller
{
    /**
     * Constructor
     *
     * @param AuthService $authService
     */
    public function __construct(private AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Login
     */
    public function login(LoginRequest $request)
    {
        try {
            $result = $this->authService->login($request->validated());
            if (!$result['status']) {
                return responseErrorMessage($result['message'], 422);
            }
            return responseSuccessData($result);
        } catch (\Exception $e) {
            return responseErrorMessage($e->getMessage(), 422);
        }
    }

    /**
     * Send Otp
     */
    public function sendOtp(SendOtpRequest $request)
    {
        $result = $this->authService->sendOtp($request->validated());
        if ($result) {
            return responseSuccessMessage(__('auth::messages.otp_sent_successfully'));
        }
        return responseErrorMessage(__('auth::messages.failed_to_send_otp'), 422);
    }

    /**
     * Verify Otp
     */
    public function verifyOtp(VerifyOtpRequest $request)
    {
        $result = $this->authService->verifyOtp($request->validated());
        if ($result) {
            return responseSuccessData($result);
        }
        return responseErrorMessage(__('auth::messages.failed_to_verify_otp'), 422);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $result = $this->authService->logout($request->user());
        if ($result['status']) {
            return responseSuccessData($result);
        }
        return responseErrorMessage(__('auth::messages.failed_to_logout'), 422);
    }

    /**
     * Refresh Token
     */
    public function refreshToken(Request $request)
    {
        $result = $this->authService->refreshToken($request->bearerToken());
        if ($result['status']) {
            return responseSuccessData($result);
        }
        return responseErrorMessage(__('auth::messages.failed_to_refresh_token'), 422);
    }

    /**
     * Change Password
     * 
     * @param ChangePasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $result = $this->authService->changePassword($request->validated());
        if ($result['status']) {
            return responseSuccessData($result);
        }
        return responseErrorMessage(__('auth::messages.failed_to_change_password'), 422);
    }
}
