<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class Common extends Enum
{
    const DATE_FORMAT_12 = 'd-m-Y h:i A';
    const DATE_FORMAT_24 = 'd-m-Y H:i';
    const DATE_FORMAT = 'd-m-Y';
    const DATE_FORMAT_24_TO_SAVE_DATABASE = 'Y-m-d H:i:s';
    const DATE_FORMAT_TO_SAVE_DATABASE = 'Y-m-d';
    const TIME_FORMAT_12 = 'h:i A';

    const RANDOM_AUTH_CODE_LENGTH = 4;

    const PASSWORD_REGEX = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[@$!%*?&])[A-Za-z\\d@$!%*?&]{8,}$/"; // Minimum 8 characters, at least one uppercase letter, one lowercase letter, one number, and one special character
    const EMAIL_REGEX = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";

    const OTP_ATTEMPT_BLOCK_MINUTES = 15; // 15 minutes
    const OTP_ATTEMPT_COUNTER_MINUTES = 5; // 5 minutes
    const OTP_ATTEMPT_MAX_ATTEMPTS = 3; // 3 attempts
    const OTP_EXPIRATION_MINUTES = 10; // 10 minutes
}
