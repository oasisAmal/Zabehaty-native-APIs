<?php

namespace App\Services\Integrations\SMS;

use SMSGlobal\Credentials;
use SMSGlobal\Resource\Sms;
use App\Interfaces\SMSInterface;
use Illuminate\Support\Facades\Log;

class SMSGlobal implements SMSInterface
{
    public $url;
    public $user_name;
    public $password;
    public $source;
    public $provider;

    public function __construct()
    {
        $this->url = config('integrations-credentials.sms_global.url');
        $this->user_name = config('integrations-credentials.sms_global.user_name');
        $this->password = config('integrations-credentials.sms_global.password');
        $this->source = config('integrations-credentials.sms_global.source');
        Credentials::set(config('integrations-credentials.sms_global.api_key'), config('integrations-credentials.sms_global.api_secert'));
    }

    /**
     * Send SMS
     * @param string $message
     * @param string $number
     * @param string $country_code
     * @return boolean
     */
    public function send($message, $number, $country_code = null)
    {
        try {
            $sms = new Sms();
            // $response = $sms->sendToMultiple($this->handlePhoneNumber($number), $message);
            $response = $sms->sendToOne(format_mobile_number_without_plus($number, $country_code), $message, $this->source);
            if (isset($response['messages'][0]['status']) && $response['messages'][0]['status'] == 'sent') {
                return true;
            }
            Log::error('Failed Send SMS:', ['response' => $response]);
            return false;
        } catch (\Exception $e) {
            Log::error('Exception Send SMS:', ['exception' => $e->getMessage()]);
            return false;
        }
    }
}
