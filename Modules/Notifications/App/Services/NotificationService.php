<?php

namespace Modules\Notifications\App\Services;

use Illuminate\Support\Facades\Log;
use Modules\Notifications\App\Services\FCMService;
use Modules\Notifications\Notifications\StoreNotification;

class NotificationService
{
    /**
     * Send And Save Notification To Firebase
     * @param object $notifiable
     * @param string $title
     * @param string $body
     * @param array $data
     * @param string $image_url
     *
     * @return void
     */
    public function send($notifiable, $title, $body, $data = null, $image_url = null)
    {
        $notifiable->notify(new StoreNotification($title, $body, $data));
        return $this->sendToFirebase($notifiable, $title, $body, $data, $image_url);
    }

        /**
     * Send Notification To Firebase
     * @param object $notifiable
     * @param string $title
     * @param string $body
     * @param array $data
     * @param string $image_url
     *
     * @return void
     */
    private function sendToFirebase($notifiable, $title, $body, $data = null, $image_url = null)
    {
        $fcm = new FCMService();
        $fcm->token($notifiable->device_token);
        $fcm->title($title);
        $fcm->body($body);
        $fcm->image($image_url);
        $fcm->data($data);
        $fcm->send();
        $response = $fcm->response();

        // FCM response
        Log::error('FCM Response: ' . json_encode($response));
        return [
            'success_number' => 0,
            'failure_number' => 0
        ];
    }
}