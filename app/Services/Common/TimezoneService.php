<?php

namespace App\Services\Common;

use App\Enums\CountryTimezones;
use Illuminate\Support\Facades\Config;

class TimezoneService
{
    /**
     * Set the application timezone based on country code
     *
     * @param string $countryCode
     * @return void
     */
    public static function setTimezone(string $countryCode): void
    {
        // Convert country code to uppercase
        $countryCode = strtoupper($countryCode);

        // Get the timezone for the country
        $timezone = CountryTimezones::getTimezone($countryCode);

        // Set the application timezone
        Config::set('app.timezone', $timezone);

        // Set PHP's default timezone
        date_default_timezone_set($timezone);
    }

    /**
     * Get the timezone for a specific country
     *
     * @param string $countryCode
     * @return string
     */
    public static function getTimezone(string $countryCode): string
    {
        // Convert country code to uppercase
        $countryCode = strtoupper($countryCode);

        // Get the timezone for the country
        return CountryTimezones::getTimezone($countryCode);
    }

    /**
     * Get the current application timezone
     *
     * @return string
     */
    public static function getCurrentTimezone(): string
    {
        return Config::get('app.timezone', 'UTC');
    }
}
