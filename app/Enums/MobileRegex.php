<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class MobileRegex extends Enum
{
    const ALL_MOBILE_REGEX = [
        'AE' => '^\+9715\d{8}$',
        'SA' => '^\+9665\d{8}$',
        'OM' => '^\+9689\d{7}$',
        'KW' => '^\+9655\d{7}$',
        'QA' => '^\+974[3-6]\d{7}$',
        'BH' => '^\+973[3-3]\d{7}$',
        'USA' => '^\+1\d{10}$',
        'UK' => '^\+447\d{9}$',
        'EG' => '^\+201\d{9}$',
    ];
    
    const AE = '^\+9715\d{8}$';
    const SA = '^\+9665\d{8}$';
    const OM = '^\+9689\d{7}$';
    const KW = '^\+9655\d{7}$';
    const QA = '^\+974[3-6]\d{7}$';
    const BH = '^\+973[3-3]\d{7}$';
    const USA = '^\+1\d{10}$';
    const UK = '^\+447\d{9}$';
    const EG = '^\+201\d{9}$';
}
