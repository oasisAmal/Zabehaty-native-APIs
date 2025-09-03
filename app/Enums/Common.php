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

    const PASSWORD_REGEX = "/^(?=.*[A-Za-z])(?=.*\d).{8,}$/";
    const EMAIL_REGEX = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
}
