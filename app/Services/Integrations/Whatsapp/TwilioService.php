<?php

namespace App\Services\Integrations\Whatsapp;

use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;
use App\Interfaces\WhatsappInterface;

class TwilioService implements WhatsappInterface
{
    public $twilio;
    public $whatsapp_from_number;

    /**
     * Create a new event instance.
     * @return void
     */
    public function __construct()
    {
        if (env('APP_NAME', '') == "HALAL APP") {
            $this->twilio = new Client(config('integrations-credentials.twilio.account_id.halal_app'), config('integrations-credentials.twilio.token.halal_app'));
            $this->whatsapp_from_number = config('integrations-credentials.twilio.whatsapp_number.halal_app');
        } else {
            $this->twilio = new Client(config('integrations-credentials.twilio.account_id.zabehaty_app'), config('integrations-credentials.twilio.token.zabehaty_app'));
            $this->whatsapp_from_number = config('integrations-credentials.twilio.whatsapp_number.zabehaty_app');
        }
    }

    /**
     * Send WhatsApp Message
     * @param string $number
     * @param string $countryCode
     * @param string $body
     * @param string $templateSid
     * @param array $templateVariables
     * @return boolean
     */
    public function send($number, $countryCode = null, $body = null, $templateSid = null, $templateVariables = [])
    {
        try {
            if ($templateSid) {
                // Send using template
                $status = $this->twilio->messages->create("whatsapp:$number", [
                    'from' => "whatsapp:$this->whatsapp_from_number",
                    'contentSid' => $templateSid,
                    'contentVariables' => '{"1":"' . $templateVariables['1'] . '"}',
                ]);
            } else {
                // Send freeform message (will only work within 24-hour window)
                $status = $this->twilio->messages->create("whatsapp:$number", [
                    'from' => "whatsapp:$this->whatsapp_from_number",
                    'body' => $body
                ]);
            }
            $response = $status;
            Log::info('Send Whatsapp Status: ' . json_encode($status));
            return $response;
        } catch (\Throwable $th) {
            Log::error('Throwable Send Whatsapp: ' . $th->getMessage());
            $response = $th->getMessage();
            return $response;
        }
    }
}
