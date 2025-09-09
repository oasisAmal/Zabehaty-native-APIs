<?php

namespace App\Services\Integrations\Whatsapp;

use App\Enums\IntegrationType;
use App\Interfaces\WhatsappInterface;
use App\Services\Integrations\Whatsapp\TwilioService;

class WhatsappService implements WhatsappInterface
{
    public $checkCredential;
    public $provider;

    /**
     * Create a new event instance.
     * @return void
     */
    public function __construct()
    {
        $this->checkCredential = $this->getDefaultIntegration();
    }

    /**
     * Get Default Integration
     * @return boolean
     */
    public function getDefaultIntegration()
    {
        switch (config('integrations-credentials.default_whatsapp_service')) {
            case IntegrationType::TWILIO_WHATSAPP:
                $this->provider = new TwilioService();
                return true;
                break;

            default:
                $this->provider = new TwilioService();
                return true;
                break;
        }
        return false;
    }

    /**
     * Send Whatsapp
     * @param string $number
     * @param string $countryCode
     * @param string $body
     * @param string $templateSid
     * @param array $templateVariables
     * @return mixed
     */
    public function send($number, $countryCode = null, $body = null, $templateSid = null, $templateVariables = [])
    {
        if (!$this->checkCredential) {
            return false;
        }
        return $this->provider->send($number, $countryCode, $body, $templateSid, $templateVariables);
    }
}
