<?php

namespace App\Interfaces;

interface EmailInterface
{
    /**
     * Send Email
     * @param string $email
     * @param string $mailDataa
     * @return boolean
     */
    public function send($email, $mailDataa);
}
