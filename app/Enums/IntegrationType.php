<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class IntegrationType extends Enum
{
    /**
     * SMS
     */
    const SMS_GLOBAL = 'sms_global';
    const SMS_COUNTRY = 'smscountry';
    const DREAMS_SMS = 'dreams_sms';

    /**
     * Email
     */
    const SMTP_EMAIL = 'smtp_email';

    /**
     * Whatsapp
     */
    const TWILIO_WHATSAPP = 'twilio_whatsapp';
}
