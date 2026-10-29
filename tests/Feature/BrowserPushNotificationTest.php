<?php

use App\Models\User;
use App\Models\WebPushSubscription;
use App\Notifications\AppointmentNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'webpush.vapid.public_key' => 'public-key',
        'webpush.vapid.private_key' => 'private-key',
        'webpush.vapid.subject' => 'mailto:test@example.com',
    ]);
});

it('uses the Laravel WebPush channel and produces the service worker payload', function () {
    $user = new User;
    $notification = new AppointmentNotification('Jadwal survei disetujui', 'Jadwal telah disetujui.', '/dashboard');

    expect($notification->via($user))->toContain(WebPushChannel::class);

    $message = $notification->toWebPush($user, $notification);

    expect($message)->toBeInstanceOf(WebPushMessage::class)
        ->and($message->toArray())->toBe([
            'title' => 'Jadwal survei disetujui',
            'body' => 'Jadwal telah disetujui.',
            'data' => ['url' => '/dashboard'],
        ]);
});

it('keeps browser push disabled when the VAPID private key is missing', function () {
    config(['webpush.vapid.private_key' => null]);

    $notification = new AppointmentNotification('Jadwal survei disetujui', 'Jadwal telah disetujui.', '/dashboard');

    expect($notification->via(new User))->toBe(['database']);
});

it('allows browser push when VAPID keys are set and the package subject fallback is used', function () {
    config(['webpush.vapid.subject' => null]);

    $notification = new AppointmentNotification('Jadwal survei disetujui', 'Jadwal telah disetujui.', '/dashboard');

    expect($notification->via(new User))->toContain(WebPushChannel::class);
});

it('provides the browser with the public VAPID key when the subject uses its fallback', function () {
    config(['webpush.vapid.subject' => null]);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('detail-notif'))
        ->assertOk()
        ->assertSee('data-vapid-public-key="public-key"', false);
});

it('requests browser notification permission automatically on the dashboard only', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']));

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('data-auto-request-permission="true"', false);

    $this->get(route('detail-notif'))
        ->assertOk()
        ->assertSee('data-auto-request-permission="false"', false);
});

it('stores a browser push subscription for the signed-in user', function () {
    config(['webpush.vapid.subject' => null]);

    $user = User::factory()->create();
    $endpoint = 'https://fcm.googleapis.com/fcm/send/test-subscription';

    $this->actingAs($user)
        ->postJson(route('notifications.subscriptions.store'), [
            'endpoint' => $endpoint,
            'keys' => [
                'p256dh' => str_repeat('A', 87),
                'auth' => str_repeat('A', 22),
            ],
        ])
        ->assertCreated()
        ->assertJsonPath('subscribed', true);

    $subscription = $user->webPushSubscriptions()->firstOrFail();
    expect($subscription->endpoint)->toBe($endpoint)
        ->and($subscription->endpoint_hash)->toBe(hash('sha256', $endpoint))
        ->and($subscription->public_key)->toBe(str_repeat('A', 87))
        ->and($subscription->auth_token)->toBe(str_repeat('A', 22));

    expect($user->routeNotificationFor('WebPush'))->toHaveCount(1)
        ->and($user->routeNotificationFor('WebPush')->first())->toBeInstanceOf(WebPushSubscription::class);
});

it('does not accept push endpoints outside supported browser push services', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('notifications.subscriptions.store'), [
            'endpoint' => 'https://127.0.0.1/internal',
            'keys' => [
                'p256dh' => str_repeat('A', 87),
                'auth' => str_repeat('A', 22),
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('endpoint');

    expect($user->webPushSubscriptions()->count())->toBe(0);
});

it('only deletes a browser push subscription owned by the signed-in user', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $endpoint = 'https://fcm.googleapis.com/fcm/send/owner-subscription';
    $subscription = $owner->webPushSubscriptions()->create([
        'endpoint_hash' => hash('sha256', $endpoint),
        'endpoint' => $endpoint,
        'public_key' => str_repeat('A', 87),
        'auth_token' => str_repeat('A', 22),
    ]);

    $this->actingAs($otherUser)
        ->deleteJson(route('notifications.subscriptions.destroy', $subscription->endpoint_hash))
        ->assertOk()
        ->assertJsonPath('subscribed', false);

    expect($subscription->fresh())->not->toBeNull();

    $this->actingAs($owner)
        ->deleteJson(route('notifications.subscriptions.destroy', $subscription->endpoint_hash))
        ->assertOk()
        ->assertJsonPath('subscribed', false);

    expect($subscription->fresh())->toBeNull();
});
