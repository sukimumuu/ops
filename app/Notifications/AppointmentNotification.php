<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class AppointmentNotification extends Notification
{
    public function __construct(
        public string $title,
        public string $message,
        public string $url,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (
            config('services.webpush.public_key')
            && config('services.webpush.private_key')
            && config('services.webpush.subject')
        ) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    /**
     * @return array{title: string, message: string, url: string}
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
        ];
    }

    /**
     * @return array{title: string, body: string, url: string}
     */
    public function toWebPush(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->message,
            'url' => $this->url,
        ];
    }
}
