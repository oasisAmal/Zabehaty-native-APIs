<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Modules\Users\App\Services\AddressStateEvaluationService;

class AddressStateMiddleware
{
    protected AddressStateEvaluationService $addressStateService;

    public function __construct(AddressStateEvaluationService $addressStateService)
    {
        $this->addressStateService = $addressStateService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip validation for excluded routes
        if ($this->shouldSkipValidation($request)) {
            return $next($request);
        }

        /** @var \Modules\Users\App\Models\User|null $user */
        $user = auth('api')->user();

        // Skip validation for guest users
        if (!$user || $user->isGuest()) {
            return $next($request);
        }

        // Evaluate address state
        $stateResult = $this->addressStateService->evaluateAddressState($user);

        // If state is invalid, return appropriate response
        if ($stateResult) {
            return responseErrorData(
                [
                    'action' => $stateResult['action'],
                ],
                $stateResult['message'],
                $stateResult['code']
            );
        }

        return $next($request);
    }

    /**
     * Check if validation should be skipped for this route
     *
     * @param Request $request
     * @return bool
     */
    protected function shouldSkipValidation(Request $request): bool
    {
        $path = $request->path();

        // Remove 'api/' prefix if present for matching
        $path = preg_replace('/^api\//', '', $path);

        // Skip address management routes
        if (str_starts_with($path, 'addresses/')) {
            return true;
        }

        // Skip auth routes
        if (str_starts_with($path, 'auth/')) {
            return true;
        }

        // Skip public app routes
        if (str_starts_with($path, 'app/')) {
            return true;
        }

        // Skip home page routes
        // if (str_starts_with($path, 'home-page/')) {
        //     return true;
        // }

        return false;
    }
}
