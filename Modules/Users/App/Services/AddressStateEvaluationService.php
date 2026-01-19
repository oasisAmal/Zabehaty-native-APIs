<?php

namespace Modules\Users\App\Services;

use Modules\Users\App\Models\User;
use Modules\Users\App\Models\UserAddress;
use Illuminate\Support\Facades\Cache;

class AddressStateEvaluationService
{
    /**
     * Evaluate address state and return state information if invalid
     *
     * @param User $user
     * @return array|null Returns ['code' => int, 'action' => string, 'message' => string, 'reason' => string] or null if valid
     */
    public function evaluateAddressState(User $user): ?array
    {
        // Check if user has no active addresses
        if ($this->hasNoActiveAddresses($user)) {
            return [
                'code' => 452,
                'action' => 'CREATE_ADDRESS',
                'message' => __('users::messages.address_state_no_active'),
                'reason' => 'no_active_addresses',
            ];
        }

        // Check if user has no default address but has active addresses
        if ($this->hasNoDefaultAddress($user)) {
            return [
                'code' => 453,
                'action' => 'SELECT_ADDRESS',
                'message' => __('users::messages.address_state_no_default'),
                'reason' => 'no_default_address',
            ];
        }

        // Check if default address changed or location changed
        $defaultAddress = $user->defaultAddress;
        if ($defaultAddress) {
            $changeResult = $this->checkAddressStateChange($user, $defaultAddress);
            if ($changeResult) {
                return $changeResult;
            }
        }

        // Create cache on first request if it doesn't exist (for future comparisons)
        $cacheKey = $this->getCacheKey($user);
        if (!Cache::has($cacheKey)) {
            $this->updateAddressStateCache($user);
        }

        return null; // State is valid
    }

    /**
     * Check if user has no default address but has active addresses
     *
     * @param User $user
     * @return bool
     */
    public function hasNoDefaultAddress(User $user): bool
    {
        $defaultAddress = $user->defaultAddress;
        if (!$defaultAddress) {
            // Check if user has any active addresses
            return UserAddress::where('user_id', $user->id)
                ->active()
                ->exists();
        }

        return false;
    }

    /**
     * Check if user has no active addresses
     *
     * @param User $user
     * @return bool
     */
    public function hasNoActiveAddresses(User $user): bool
    {
        return !UserAddress::where('user_id', $user->id)
            ->active()
            ->exists();
    }

    /**
     * Check if default address changed or location changed by comparing with cache
     *
     * @param User $user
     * @param UserAddress $defaultAddress
     * @return array|null
     */
    protected function checkAddressStateChange(User $user, UserAddress $defaultAddress): ?array
    {
        $cacheKey = $this->getCacheKey($user);
        $cachedState = Cache::get($cacheKey);
        
        $currentState = [
            'default_address_id' => $defaultAddress->id,
            'emirate_id' => $defaultAddress->emirate_id,
            'region_id' => $defaultAddress->region_id,
        ];

        // If no cache exists, create it and proceed (first request scenario)
        if (!$cachedState) {
            return null;
        }

        // Check if default address changed
        if ($cachedState['default_address_id'] !== $currentState['default_address_id']) {
            // Update cache immediately to prevent infinite loop on next request
            $this->updateAddressStateCache($user);
            return [
                'code' => 454,
                'action' => 'RELOAD_HOME',
                'message' => __('users::messages.address_state_default_changed'),
                'reason' => 'default_changed',
            ];
        }

        // Check if location changed
        if ($cachedState['emirate_id'] !== $currentState['emirate_id'] || 
            $cachedState['region_id'] !== $currentState['region_id']) {
            // Update cache immediately to prevent infinite loop on next request
            $this->updateAddressStateCache($user);
            return [
                'code' => 454,
                'action' => 'RELOAD_HOME',
                'message' => __('users::messages.address_state_location_changed'),
                'reason' => 'location_changed',
            ];
        }

        return null; // No change detected
    }

    /**
     * Get cached address state
     *
     * @param User $user
     * @return array|null
     */
    public function getCachedAddressState(User $user): ?array
    {
        return Cache::get($this->getCacheKey($user));
    }

    /**
     * Update cache with current address state
     *
     * @param User $user
     * @return void
     */
    public function updateAddressStateCache(User $user): void
    {
        $cacheKey = $this->getCacheKey($user);
        $defaultAddress = $user->defaultAddress;

        $state = [
            'default_address_id' => $defaultAddress?->id,
            'emirate_id' => $defaultAddress?->emirate_id,
            'region_id' => $defaultAddress?->region_id,
        ];

        Cache::put($cacheKey, $state, now()->addHours(24));
    }

    /**
     * Get cache key for user
     *
     * @param User $user
     * @return string
     */
    protected function getCacheKey(User $user): string
    {
        return "address_state_{$user->id}";
    }
}
