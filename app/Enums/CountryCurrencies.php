<?php

namespace App\Enums;

use BenSampo\Enum\Enum;
use App\Helpers\DatabaseHelpers;

final class CountryCurrencies extends Enum
{
    const AE = 'AED';      // United Arab Emirates
    const SA = 'SAR';      // Saudi Arabia
    const OM = 'OMR';      // Oman
    const KW = 'KWD';      // Kuwait
    const BH = 'BHD';      // Bahrain

    /**
     * Get currency for a specific country code
     *
     * @return string|null
     */
    public static function getCurrency(): ?string
    {
        $countryCode = DatabaseHelpers::getCurrentCountryCode();
        $countryCode = strtoupper($countryCode);
        
        if (!isset(self::getAllCurrencies()[$countryCode])) {
            return null;
        }

        return self::getAllCurrencies()[$countryCode];
    }

    /**
     * Get all available currencies
     *
     * @return array
     */
    public static function getAllCurrencies(): array
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

