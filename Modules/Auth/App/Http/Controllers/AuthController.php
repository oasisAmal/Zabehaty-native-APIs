<?php

namespace Modules\Auth\App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Modules\Auth\App\Services\AuthService;
use Modules\Auth\App\Http\Requests\LoginRequest;
use Modules\Auth\App\Http\Requests\SendOtpRequest;
use Modules\Auth\App\Http\Requests\VerifyOtpRequest;

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
}
