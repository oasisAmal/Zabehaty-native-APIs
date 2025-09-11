<?php

namespace App\Services\Common;

use App\Enums\AppCountries;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class DatabaseConnectionService
{
    /**
     * Set the database connection based on country code
     *
     * @param string $countryCode
     * @return void
     */
    public static function setConnection(string $countryCode): void
    {
        $countryCode = strtolower($countryCode);
        
        if (!in_array(strtoupper($countryCode), AppCountries::getValues())) {
            throw new \InvalidArgumentException("Invalid country code: {$countryCode}");
        }

        // Set the default database connection for the current request
        Config::set('database.default', $countryCode);
        
        // Purge the current connection to force Laravel to use the new connection
        DB::purge($countryCode);
    }

    /**
     * Get the current country-specific database connection
     *
     * @param string|null $countryCode
     * @return \Illuminate\Database\Connection
     */
    public static function getConnection(?string $countryCode = null): \Illuminate\Database\Connection
    {
        if ($countryCode) {
            return DB::connection($countryCode);
        }

        return DB::connection();
    }

    /**
     * Check if a country-specific database connection exists
     *
     * @param string $countryCode
     * @return bool
     */
    public static function connectionExists(string $countryCode): bool
    {
        $countryCode = strtolower($countryCode);
        return Config::has("database.connections.{$countryCode}");
    }

    /**
     * Get all available country database connections
     *
     * @return array
     */
    public static function getAvailableConnections(): array
    {
        $connections = [];
        
        foreach (AppCountries::getValues() as $country) {
            $countryCode = strtolower($country);
            if (self::connectionExists($countryCode)) {
                $connections[] = $countryCode;
            }
        }

        return $connections;
    }

    /**
     * Execute a callback with a specific country database connection
     *
     * @param string $countryCode
     * @param callable $callback
     * @return mixed
     */
    public static function withConnection(string $countryCode, callable $callback)
    {
        $originalConnection = Config::get('database.default');
        
        try {
            self::setConnection($countryCode);
            return $callback();
        } finally {
            // Restore the original connection
            Config::set('database.default', $originalConnection);
            DB::purge($originalConnection);
        }
    }

    /**
     * Get database connection info for a specific country
     *
     * @param string $countryCode
     * @return array
     */
    public static function getConnectionInfo(string $countryCode): array
    {
        $countryCode = strtolower($countryCode);
        
        if (!self::connectionExists($countryCode)) {
            throw new \InvalidArgumentException("Database connection for country {$countryCode} does not exist");
        }

        return Config::get("database.connections.{$countryCode}");
    }
}
