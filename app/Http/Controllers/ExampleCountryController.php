<?php

namespace App\Http\Controllers;

use App\Helpers\DatabaseHelpers;
use App\Models\User;
use App\Services\Common\DatabaseConnectionService;
use Illuminate\Http\Request;

class ExampleCountryController extends Controller
{
    /**
     * Get users for current country
     */
    public function getUsers()
    {
        // This will automatically use the current country's database
        // based on the App-Country header
        $users = User::all();
        
        return response()->json([
            'success' => true,
            'data' => $users,
            'country' => DatabaseHelpers::getCurrentCountryCode()
        ]);
    }

    /**
     * Get users for specific country
     */
    public function getUsersForCountry(Request $request, string $countryCode)
    {
        $users = User::forCountry($countryCode)->get();
        
        return response()->json([
            'success' => true,
            'data' => $users,
            'country' => strtoupper($countryCode)
        ]);
    }

    /**
     * Create user for current country
     */
    public function createUser(Request $request)
    {
        $user = User::createForCountry($request->all(), DatabaseHelpers::getCurrentCountryCode());
        
        return response()->json([
            'success' => true,
            'data' => $user,
            'country' => DatabaseHelpers::getCurrentCountryCode()
        ]);
    }

    /**
     * Get database connection status for all countries
     */
    public function getDatabaseStatus()
    {
        $status = DatabaseHelpers::getConnectionsStatus();
        
        return response()->json([
            'success' => true,
            'data' => $status
        ]);
    }

    /**
     * Execute operation for specific country
     */
    public function executeForCountry(Request $request, string $countryCode)
    {
        $result = DatabaseHelpers::forCountry($countryCode, function () {
            return [
                'user_count' => User::count(),
                'connection_name' => DatabaseHelpers::getConnection()->getName(),
                'database_name' => DatabaseHelpers::getConnection()->getDatabaseName()
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $result,
            'country' => strtoupper($countryCode)
        ]);
    }

    /**
     * Get current country information
     */
    public function getCurrentCountryInfo()
    {
        $countryCode = DatabaseHelpers::getCurrentCountryCode();
        
        if (!$countryCode) {
            return response()->json([
                'success' => false,
                'message' => 'No country context found'
            ], 400);
        }

        $connectionInfo = DatabaseConnectionService::getConnectionInfo($countryCode);
        
        return response()->json([
            'success' => true,
            'data' => [
                'country_code' => strtoupper($countryCode),
                'database' => $connectionInfo['database'],
                'host' => $connectionInfo['host'],
                'port' => $connectionInfo['port'],
                'connection_name' => DatabaseHelpers::getConnection()->getName()
            ]
        ]);
    }
}
