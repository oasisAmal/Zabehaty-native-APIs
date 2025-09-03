<?php

namespace App\Interfaces;

interface SMSInterface
{
    /**
     * Send SMS
     * @param string $message
     * @param string $number
     * @param string $country_code
     * @return boolean
     */
    public function send($message, $number, $country_code);
}
