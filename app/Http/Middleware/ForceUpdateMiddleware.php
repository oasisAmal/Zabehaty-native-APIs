<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceUpdateMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $appVersion = $request->header('App-Version');
        $appPlatform = $request->header('App-Platform');
        $countryCode = $request->header('App-Country');

        if (!$appVersion || !$appPlatform || !$countryCode) {
            return responseErrorMessage('The App-Version, App-Platform, and App-Country headers are required', 400);
        }

        // Define latest available versions per platform
        $latestVersions = [
            'ios' => '2.0.0',
            'android' => '2.0.0',
        ];

        // Define app store URLs
        if ($appPlatform == 'IOS') {
            $updateUrl = 'https://apps.apple.com/app/zabehaty/id123456789';
        } elseif ($appPlatform == 'Android') {
            $updateUrl = 'https://play.google.com/store/apps/details?id=com.zabehaty.app';
        } else {
            return responseErrorMessage('The App-Platform is not supported', 400);
        }

        $platform = strtolower($appPlatform);
        $currentVersion = $appVersion ?? '0.0.0';
        $latestVersion = $latestVersions[$platform] ?? '1.0.0';

        // Check if force update is required
        $forceUpdate = version_compare($currentVersion, $latestVersion, '<');

        if ($forceUpdate) {
            return responseErrorData([
                'latest_version' => $latestVersion,
                'force_update' => true,
                'update_url' => $updateUrl,
                'current_version' => $currentVersion,
            ], __('messages.force_update_required'), 426);
        }

        return $next($request);
    }
}
