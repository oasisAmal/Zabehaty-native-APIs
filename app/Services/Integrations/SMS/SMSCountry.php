<?php

namespace App\Services\Integrations\SMS;

use App\Interfaces\SMSInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SMSCountry implements SMSInterface
{
    public $url;
    public $auth_key;
    public $auth_token;
    public $source;
    public $provider;

    public function __construct()
    {
        $this->url = config('integrations-credentials.smscountry.url');
        $this->auth_key = config('integrations-credentials.smscountry.auth_key');
        $this->auth_token = config('integrations-credentials.smscountry.auth_token');
        $this->source = config('integrations-credentials.smscountry.source');
        $this->provider = Http::withHeaders([
            'Authorization' => 'Basic '.$this->getAuthorization(),
            'Content-Type' => 'application/json'
        ]);
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
            $data = [
                "Text" => $message,
                "Number" => format_mobile_number_without_plus($number, $country_code),
                "SenderId" => $this->source,
                "DRNotifyUrl" => "https://dev.thahabi.net/notifyurl",
                "DRNotifyHttpMethod" => "POST",
                "Tool"  => "API"
            ];
            $response = $this->provider->post($this->url . "/{$this->auth_key}/SMSes/", $data);
            if ($response->ok()) {
                return true;
            }
            Log::error('Failed Send smscountry:', ['response' => $response->json()]);
            return false;
        } catch (\Exception $e) {
            Log::error('Exception Send smscountry:', ['exception' => $e->getMessage()]);
            return false;
        }
    }

    private function getAuthorization()
    {
        return base64_encode($this->auth_key . ':' . $this->auth_token);
    }
}
