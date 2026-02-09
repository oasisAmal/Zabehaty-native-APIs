<?php

namespace Modules\Users\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Users\App\Services\UserService;

class UserCountController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return responseSuccessData([
            'favorites_count' => $this->userService->getFavoritesCount(auth('api')->id()),
            'cart_count' => 0,
            'notifications_count' => 0,
        ]);
    }
}
