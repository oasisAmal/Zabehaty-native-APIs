<?php

namespace Modules\Notifications\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StoreNotification extends Notification
{
    use Queueable;

    public $title;
    public $body;
    public $data;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($title, $body, $data = null)
    {
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data,
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
