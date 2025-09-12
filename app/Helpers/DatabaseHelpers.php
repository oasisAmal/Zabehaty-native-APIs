<?php

namespace App\Helpers;

use App\Services\Common\DatabaseConnectionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseHelpers
{
    /**
     * Execute a database operation for a specific country
     *
     * @param string $countryCode
     * @param callable $callback
     * @return mixed
     */
    public static function forCountry(string $countryCode, callable $callback)
    {
        return DatabaseConnectionService::withConnection($countryCode, $callback);
    }

    /**
     * Get a model instance for a specific country
     *
     * @param string $modelClass
     * @param string $countryCode
     * @return Model
     */
    public static function modelForCountry(string $modelClass, string $countryCode): Model
    {
        $model = new $modelClass;
        $model->setConnection($countryCode);
        return $model;
    }

    /**
     * Run a query for a specific country
     *
     * @param string $countryCode
     * @param string $query
     * @param array $bindings
     * @return mixed
     */
    public static function queryForCountry(string $countryCode, string $query, array $bindings = [])
    {
        return self::forCountry($countryCode, function () use ($query, $bindings) {
            return DB::select($query, $bindings);
        });
    }

    /**
     * Get database connection status for all countries
     *
     * @return array
     */
    public static function getConnectionsStatus(): array
    {
        $status = [];
        $connections = DatabaseConnectionService::getAvailableConnections();

        foreach ($connections as $connection) {
            try {
                $connectionInfo = DatabaseConnectionService::getConnectionInfo($connection);
                $db = DatabaseConnectionService::getConnection($connection);
                $db->getPdo(); // Test connection
                
                $status[$connection] = [
                    'status' => 'connected',
                    'database' => $connectionInfo['database'],
                    'host' => $connectionInfo['host'],
                    'port' => $connectionInfo['port']
                ];
            } catch (\Exception $e) {
                $status[$connection] = [
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];
            }
        }

        return $status;
    }

    /**
     * Migrate database for a specific country
     *
     * @param string $countryCode
     * @param string $path
     * @return bool
     */
    public static function migrateForCountry(string $countryCode, string $path = 'database/migrations'): bool
    {
        try {
            self::forCountry($countryCode, function () use ($path) {
                Artisan::call('migrate', [
                    '--path' => $path,
                    '--force' => true
                ]);
            });
            return true;
        } catch (\Exception $e) {
            Log::error("Migration failed for country {$countryCode}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Seed database for a specific country
     *
     * @param string $countryCode
     * @param string $seederClass
     * @return bool
     */
    public static function seedForCountry(string $countryCode, string $seederClass): bool
    {
        try {
            self::forCountry($countryCode, function () use ($seederClass) {
                Artisan::call('db:seed', [
                    '--class' => $seederClass,
                    '--force' => true
                ]);
            });
            return true;
        } catch (\Exception $e) {
            Log::error("Seeding failed for country {$countryCode}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get current country code from request
     *
     * @return string|null
     */
    public static function getCurrentCountryCode(): ?string
    {
        return request()->get('app_country_code');
    }

    /**
     * Check if current request has country context
     *
     * @return bool
     */
    public static function hasCountryContext(): bool
    {
        return !is_null(self::getCurrentCountryCode());
    }

    /**
     * Get current database connection
     *
     * @return \Illuminate\Database\Connection
     */
    public static function getConnection(): \Illuminate\Database\Connection
    {
        return DB::connection();
    }
}
