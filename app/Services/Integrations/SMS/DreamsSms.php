<?php

namespace App\Services\Integrations\SMS;
use App\Interfaces\SMSInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class DreamsSms implements SMSInterface
{
    public $url;
    public $user;
    public $secret_key;
    public $sender;

    public function __construct()
    {
        $this->url = config('integrations-credentials.dreams_sms.url');
        $this->user = config('integrations-credentials.dreams_sms.user');
        $this->secret_key = config('integrations-credentials.dreams_sms.secret_key');
        $this->sender = config('integrations-credentials.dreams_sms.sender');
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

            $response = Http::asForm()->post($this->url, [
                'user' => $this->user,
                'secret_key' => $this->secret_key,
                'to' => '966' . $number,
                'message' => $message,
                'sender' => $this->sender,
            ]);

            $result = $response->body();

            if (trim($result) === 'Success') {
                return true;
            } else {
                Log::error('Failed Send SMS Saudi Arabia:' . $result);
                return false;
            }

        } catch (\Exception $e) {
            Log::error('Failed Send SMS Saudi Arabia:' . json_encode($e->getMessage()));
            return false;
        }
    }
}