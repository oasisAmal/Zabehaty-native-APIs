<?php

namespace Modules\Auth\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Modules\Auth\App\Services\AuthService;
use Modules\Auth\App\Http\Requests\LoginRequest;

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
}
