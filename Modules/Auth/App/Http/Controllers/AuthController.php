<?php

namespace Modules\Auth\App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Modules\Auth\App\Services\AuthService;
use Modules\Auth\App\Http\Requests\LoginRequest;
use Modules\Auth\App\Http\Requests\SendOtpRequest;

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
    public function loginByPassword(LoginRequest $request)
    {
        try {
            $result = $this->authService->loginByPassword($request->validated());
            return responseSuccessData($result, 200);
        } catch (\Exception $e) {
            return responseErrorMessage($e->getMessage(), 422);
        }
    }

    /**
     * Send Otp
     */
    public function sendOtp(SendOtpRequest $request)
    {
        return $this->authService->sendOtp($request->validated());
    }
    
    /**
     * Verify Otp
     */
    public function verifyOtp(VerifyOtpRequest $request)
    {
        return $this->authService->verifyOtp($request->validated());
    }
    
    
}
