<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class CountryTimezones extends Enum
{
    const AE = 'Asia/Dubai';      // United Arab Emirates
    const SA = 'Asia/Riyadh';     // Saudi Arabia
    const OM = 'Asia/Muscat';     // Oman
    const KW = 'Asia/Kuwait';     // Kuwait
    const BH = 'Asia/Bahrain';    // Bahrain

    /**
     * Get timezone for a specific country code
     *
     * @param string $countryCode
     * @return string
     */
    public static function getTimezone(string $countryCode): string
    {
        $countryCode = strtoupper($countryCode);
        
        if (!isset(self::getAllTimezones()[$countryCode])) {
            throw new \InvalidArgumentException("Invalid country code: {$countryCode}");
        }

        return self::getAllTimezones()[$countryCode];
    }

    /**
     * Get all available timezones
     *
     * @return array
     */
    public static function getAllTimezones(): array
    {
        return [
            'AE' => self::AE,
            'SA' => self::SA,
            'OM' => self::OM,
            'KW' => self::KW,
            'BH' => self::BH,
        ];
    }
}
