<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Telescope::night();

        $this->hideSensitiveRequestDetails();

        $isLocal = $this->app->environment('local');

        // Add custom tags for Auth and route URI only
        Telescope::tag(function (IncomingEntry $entry) {
            $tags = [];

            if ($entry->type === 'request') {
                $uri = $entry->content['uri'] ?? '';
                $headers = $entry->content['headers'] ?? [];

                // Tag by authentication status
                $authHeader = $headers['authorization'] ?? $headers['Authorization'] ?? null;
                if ($authHeader) {
                    $tags[] = 'Auth:authenticated';
                } else {
                    $tags[] = 'Auth:guest';
                }

                // Tag by route URI (remove leading slash and query parameters)
                if ($uri) {
                    $cleanUri = ltrim($uri, '/');
                    // Remove query parameters (everything after ?)
                    $cleanUri = strtok($cleanUri, '?');
                    $tags[] = 'uri:' . $cleanUri;
                }
            }

            return $tags;
        });

        Telescope::filter(function (IncomingEntry $entry) use ($isLocal) {
            return $isLocal ||
                $entry->isReportableException() ||
                $entry->isFailedRequest() ||
                $entry->isFailedJob() ||
                $entry->isScheduledTask() ||
                $entry->hasMonitoredTag();
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', function ($user) {
            return in_array($user->email, [
                //
            ]);
        });
    }
}
