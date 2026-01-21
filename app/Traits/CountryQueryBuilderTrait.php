<?php

namespace App\Traits;

use App\Services\Common\DatabaseConnectionService;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;

trait CountryQueryBuilderTrait
{
    protected function getCountryConnectionName(): string
    {
        $countryCode = strtolower((string) request()->get('app_country_code'));

        if ($countryCode !== '' && DatabaseConnectionService::connectionExists($countryCode)) {
            return $countryCode;
        }

        return (string) config('database.default');
    }

    protected function getCountryConnection(): ConnectionInterface
    {
        return DatabaseConnectionService::getConnection($this->getCountryConnectionName());
    }

    protected function table(string $table): Builder
    {
        return $this->getCountryConnection()->table($table);
    }
}
