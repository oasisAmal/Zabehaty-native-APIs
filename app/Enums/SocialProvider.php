<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

class SocialProvider extends Enum
{
    const GOOGLE = 'google';
    const GOOGLE_IOS = 'google_ios';
    const FACEBOOK = 'facebook';
    const APPLE = 'apple';
}
