<?php

namespace App\Interfaces;

interface WhatsappInterface
{
    /**
     * Send WhatsApp Message
     * @param string $number
     * @param string $countryCode
     * @param string $body
     * @param string $templateSid
     * @param array $contentVariables
     * @return boolean
     */
    public function send($number, $countryCode = null, $body = null, $templateSid = null, $templateVariables = []);
}
