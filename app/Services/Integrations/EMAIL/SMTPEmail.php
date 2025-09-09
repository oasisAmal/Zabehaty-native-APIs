<?php

namespace App\Services\Integrations\EMAIL;

use App\Interfaces\EmailInterface;
use Illuminate\Support\Facades\Log;
use Mail;
use Config;

class SMTPEmail implements EmailInterface
{
    public $provider;

    public function __construct()
    {
        Config::set('mail.mailers.smtp.host', config('integrations-credentials.smtp_email.mail_host'));
        Config::set('mail.mailers.smtp.port', config('integrations-credentials.smtp_email.mail_port'));
        Config::set('mail.mailers.smtp.encryption', config('integrations-credentials.smtp_email.encryption'));
        Config::set('mail.mailers.smtp.username', config('integrations-credentials.smtp_email.mail_username'));
        Config::set('mail.mailers.smtp.password', config('integrations-credentials.smtp_email.password'));
    }

    /**
     * Send Email
     * @param string $email
     * @param string $mailData
     * @return boolean
     */
    public function send($email, $mailData)
    {
        try {
            Mail::to($email)->send($mailData);
            return false;
        } catch (\Exception $e) {
            Log::error('Send EMAIL:' . json_encode($e->getMessage()));
            return false;
        }
    }

}