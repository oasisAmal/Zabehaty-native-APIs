<?php

namespace App\Services\Integrations\SMS;

use App\Enums\AppCountries;
use App\Enums\IntegrationType;
use App\Interfaces\SMSInterface;
use App\Services\Integrations\SMS\DreamsSms;
use App\Services\Integrations\SMS\SMSGlobal;
use App\Services\Integrations\SMS\SMSCountry;

class SMSService implements SMSInterface
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
        if (request()->header('App-Country') == AppCountries::SA) {
            $this->provider = new DreamsSms();
        } else {
            switch (config('integrations-credentials.default_sms_service')) {
                case IntegrationType::SMS_GLOBAL:
                    $this->provider = new SMSGlobal();
                    return true;
                    break;

                case IntegrationType::SMS_COUNTRY:
                    $this->provider = new SMSCountry();
                    return true;
                    break;

                default:
                    $this->provider = new SMSCountry();
                    return true;
                    break;
            }
        }
        return false;
    }

    /**
     * Send SMS
     * @param string $message
     * @param string $number
     * @param string $country_code
     * @return mixed
     */
    public function send($message, $number, $country_code = null)
    {
        if (!$this->checkCredential) {
            return false;
        }
        return $this->provider->send($message, $number, $country_code);
    }
}
