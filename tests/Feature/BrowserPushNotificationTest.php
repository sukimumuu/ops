<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'services.webpush.public_key' => 'public-key',
        'services.webpush.private_key' => 'private-key',
        'services.webpush.subject' => 'mailto:test@example.com',
    ]);
});

it('stores a browser push subscription for the signed-in user', function () {
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
