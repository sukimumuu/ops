<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! $notifiable instanceof User || ! $notification instanceof AppointmentNotification) {
            throw new InvalidArgumentException('WebPushChannel only supports appointment notifications for users.');
        }

        $subscriptions = $notifiable->webPushSubscriptions()->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        $webPush = new WebPush([
            'VAPID' => [
                'subject' => config('services.webpush.subject'),
                'publicKey' => config('services.webpush.public_key'),
                'privateKey' => config('services.webpush.private_key'),
            ],
        ], ['TTL' => 60, 'urgency' => 'high']);

        $payload = json_encode(
            $notification->toWebPush($notifiable),
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );

        foreach ($subscriptions as $pushSubscription) {
            $webPush->queueNotification(
                Subscription::create([
                    'endpoint' => $pushSubscription->endpoint,
                    'publicKey' => $pushSubscription->public_key,
                    'authToken' => $pushSubscription->auth_token,
                    'contentEncoding' => 'aes128gcm',
                ]),
                $payload,
            );
        }

        foreach ($webPush->flush() as $report) {
            $endpointHash = hash('sha256', $report->getEndpoint());
            $subscription = $notifiable->webPushSubscriptions()
                ->where('endpoint_hash', $endpointHash)
                ->first();

            if ($report->isSubscriptionExpired()) {
                $subscription?->delete();

                continue;
            }

            if (! $report->isSuccess()) {
                Log::warning('Browser push notification delivery failed.', [
                    'user_id' => $notifiable->getKey(),
                    'subscription_id' => $subscription?->getKey(),
                    'reason' => $report->getReason(),
                ]);
            }
        }
    }
}
