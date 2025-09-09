<?php

namespace App\Services\Integrations\EMAIL;

use App\Enums\IntegrationType;
use App\Interfaces\EmailInterface;
use App\Services\Integrations\EMAIL\SMTPEmail;

class EMAILService implements EmailInterface
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
        switch (env('DEFAULT_EMAIL_SERVICE', 'smtp_email')) {
            case IntegrationType::SMTP_EMAIL:
                $this->provider = new SMTPEmail();
                return true;
                break;
        }
        return false;
    }

    /**
     * Check Credentials
     * @return boolean
     */
    public function checkCredential()
    {
        return $this->checkCredential;
    }

    /**
     * Send Email
     * @param string $email
     * @param string $mailData
     * @return mixed
     */
    public function send($email, $mailData)
    {
        if (!$this->checkCredential()) {
            return false;
        }
        return $this->provider->send($email, $mailData);
    }
}