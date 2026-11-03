<?php

use App\Models\Appointment;
use App\Models\DpPaymentAttempt;
use App\Models\PaymentWebhookEvent;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

function createDpPaymentScenario(string $price = '1234565.00'): array
{
    $seller = User::factory()->create(['name' => 'Penjual Mihom']);
    $buyer = User::factory()->create(['name' => 'Pembeli Mihom']);
    $property = Property::query()->create([
        'uuid' => (string) Str::uuid(),
        'seller_id' => $seller->id,
        'property_type' => 'bangunan',
        'type' => 'rumah',
        'category' => 'sale',
        'status' => 'reserved',
        'title' => 'Rumah Pembayaran DP',
        'description' => 'Properti untuk pengujian pembayaran DP.',
        'price' => $price,
        'negotiable' => false,
        'province' => 'Jawa Barat',
        'city' => 'Bandung',
        'district' => 'Coblong',
        'postal_code' => '40132',
        'address' => 'Jl. Merdeka No. 1',
        'land_area_sqm' => 100,
        'building_area_sqm' => 80,
        'certificate_type' => 'SHM',
    ]);
    $appointment = Appointment::query()->create([
        'property_id' => $property->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'scheduled_at' => now()->subDay(),
        'status' => Appointment::STATUS_COMPLETED,
        'buyer_intent' => Appointment::BUYER_INTENT_DEAL,
        'ppat_meeting_date' => now()->addWeek()->toDateString(),
        'ppat_meeting_status' => Appointment::PPAT_MEETING_STATUS_CONFIRMED,
        'completed_at' => now(),
    ]);
    $transaction = Transaction::query()->create([
        'uuid' => (string) Str::uuid(),
        'property_id' => $property->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'booking_fee_amount' => 123457,
        'total_property_amount' => $price,
        'state' => 'draft',
    ]);

    return compact('seller', 'buyer', 'property', 'appointment', 'transaction');
}

function grantDpPaymentPermission(User $user, string $permissionName = 'transactions.initiate_dp'): void
{
    $permission = Permission::firstOrCreate([
        'name' => $permissionName,
        'guard_name' => 'web',
    ]);

    $user->givePermissionTo($permission);
}

function configureXenditForTest(): void
{
    config([
        'services.xendit.environment' => 'sandbox',
        'services.xendit.sandbox_secret_key' => 'xnd_development_test_key',
        'services.xendit.sandbox_webhook_token' => 'sandbox-webhook-token',
        'services.xendit.virtual_account_channel_code' => 'BNI',
        'services.xendit.virtual_account_expiry_hours' => 24,
        'services.mihom.dp_percentage' => 10,
    ]);
}

function paymentRequestResponse(DpPaymentAttempt $attempt, string $status = 'PENDING', mixed $amount = null): array
{
    return [
        'id' => $attempt->xendit_payment_request_id,
        'reference_id' => $attempt->reference_id,
        'amount' => $amount ?? (int) $attempt->amount,
        'currency' => $attempt->currency,
        'status' => $status,
    ];
}

it('creates one IDR Virtual Account request and ignores browser-supplied payment values', function () {
    configureXenditForTest();
    Http::preventStrayRequests();

    $scenario = createDpPaymentScenario();
    grantDpPaymentPermission($scenario['buyer']);

    $expectedReference = null;
    Http::fake([
        'api.xendit.co/payment_requests' => function (Request $request) use (&$expectedReference) {
            $expectedReference = $request['reference_id'];

            return Http::response([
                'id' => 'pr-mihom-001',
                'reference_id' => $expectedReference,
                'status' => 'PENDING',
                'payment_method' => [
                    'virtual_account' => [
                        'channel_properties' => ['account_number' => '8800123456789'],
                    ],
                ],
            ], 201);
        },
    ]);

    $this->actingAs($scenario['buyer'])
        ->post(route('buyer.dp-payments.store', ['transaction' => $scenario['transaction']->uuid]), [
            'amount' => 1,
            'currency' => 'USD',
            'status' => 'SUCCEEDED',
        ])
        ->assertRedirect(route('buyer.bought-properties.show', ['property' => $scenario['property']->uuid]));

    $attempt = DpPaymentAttempt::query()->firstOrFail();

    expect((float) $attempt->amount)->toBe(123457.0)
        ->and($attempt->currency)->toBe('IDR')
        ->and($attempt->status)->toBe(DpPaymentAttempt::STATUS_PENDING)
        ->and($attempt->reference_id)->toBe($expectedReference)
        ->and($attempt->virtual_account_number)->toBe('8800123456789')
        ->and($scenario['transaction']->fresh()->state)->toBe('draft')
        ->and($scenario['transaction']->ledgerEntries()->count())->toBe(0);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && str_ends_with($request->url(), '/payment_requests')
        && $request['amount'] === 123457
        && $request['currency'] === 'IDR'
        && $request['payment_method']['type'] === 'VIRTUAL_ACCOUNT'
        && $request['payment_method']['virtual_account']['channel_code'] === 'BNI'
        && $request->hasHeader('idempotency-key'));
});

it('reuses an active payment request and shows the VA instructions to its buyer', function () {
    configureXenditForTest();
    Http::preventStrayRequests();

    $scenario = createDpPaymentScenario();
    grantDpPaymentPermission($scenario['buyer']);
    Http::fake([
        'api.xendit.co/payment_requests' => function (Request $request) {
            return Http::response([
                'id' => 'pr-mihom-002',
                'reference_id' => $request['reference_id'],
                'status' => 'PENDING',
                'payment_method' => [
                    'virtual_account' => [
                        'channel_properties' => ['account_number' => '8800987654321'],
                    ],
                ],
            ], 201);
        },
    ]);

    $paymentRoute = route('buyer.dp-payments.store', ['transaction' => $scenario['transaction']->uuid]);
    $this->actingAs($scenario['buyer'])->post($paymentRoute)->assertRedirect();
    $this->post($paymentRoute)->assertRedirect();

    expect(DpPaymentAttempt::query()->count())->toBe(1);

    Http::assertSentCount(1);

    $this->get(route('buyer.bought-properties.show', ['property' => $scenario['property']->uuid]))
        ->assertSeeText('8800987654321')
        ->assertSeeText('Transfer tepat')
        ->assertSeeText('Status diperbarui setelah pembayaran dikonfirmasi oleh Xendit.');
});

it('hides another buyer transaction and blocks users without the DP permission', function () {
    configureXenditForTest();

    $scenario = createDpPaymentScenario();
    $otherBuyer = User::factory()->create();
    grantDpPaymentPermission($otherBuyer);
    $route = route('buyer.dp-payments.store', ['transaction' => $scenario['transaction']->uuid]);

    $this->actingAs($otherBuyer)->post($route)->assertNotFound();

    $unprivilegedUser = User::factory()->create();
    $this->actingAs($unprivilegedUser)->post($route)->assertForbidden();

    expect(DpPaymentAttempt::query()->count())->toBe(0);
});

it('blocks DP payment until the PPAT meeting is approved', function () {
    configureXenditForTest();
    Http::preventStrayRequests();

    $scenario = createDpPaymentScenario();
    $scenario['appointment']->update([
        'ppat_meeting_status' => Appointment::PPAT_MEETING_STATUS_REQUESTED,
    ]);
    grantDpPaymentPermission($scenario['buyer']);

    $this->actingAs($scenario['buyer'])
        ->from(route('buyer.bought-properties.show', ['property' => $scenario['property']->uuid]))
        ->post(route('buyer.dp-payments.store', ['transaction' => $scenario['transaction']->uuid]))
        ->assertRedirect(route('buyer.bought-properties.show', ['property' => $scenario['property']->uuid]))
        ->assertSessionHasErrors([
            'dp_payment' => 'Pembayaran DP hanya dapat dilakukan setelah jadwal temu PPAT disetujui.',
        ]);

    expect(DpPaymentAttempt::query()->count())->toBe(0);

    Http::assertNothingSent();
});

it('creates a draft transaction with the configured DP when a sale survey becomes a deal', function () {
    configureXenditForTest();
    Notification::fake();

    $seller = User::factory()->create();
    $buyer = User::factory()->create();
    grantDpPaymentPermission($seller, 'appointments.confirm_or_reject');

    $property = Property::query()->create([
        'uuid' => (string) Str::uuid(),
        'seller_id' => $seller->id,
        'property_type' => 'bangunan',
        'type' => 'rumah',
        'category' => 'sale',
        'status' => 'published',
        'title' => 'Rumah Deal Survei',
        'description' => 'Properti untuk menguji pembuatan transaksi.',
        'price' => '1234565.00',
        'negotiable' => false,
        'province' => 'Jawa Barat',
        'city' => 'Bandung',
        'district' => 'Coblong',
        'postal_code' => '40132',
        'address' => 'Jl. Merdeka No. 2',
        'land_area_sqm' => 100,
        'building_area_sqm' => 80,
        'certificate_type' => 'SHM',
    ]);
    $appointment = Appointment::query()->create([
        'property_id' => $property->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'scheduled_at' => now()->subDay(),
        'status' => Appointment::STATUS_CONFIRMED,
    ]);

    $this->actingAs($seller)
        ->patch(route('seller.my-properties.surveys.update', [
            'property' => $property->uuid,
            'appointment' => $appointment->id,
        ]), [
            'status' => Appointment::STATUS_COMPLETED,
            'buyer_intent' => Appointment::BUYER_INTENT_DEAL,
        ])
        ->assertRedirect();

    $transaction = Transaction::query()->firstOrFail();

    expect((float) $transaction->booking_fee_amount)->toBe(123457.0)
        ->and((float) $transaction->total_property_amount)->toBe(1234565.0)
        ->and($transaction->state)->toBe('draft')
        ->and($property->fresh()->status)->toBe('reserved');

    $this->assertDatabaseHas('transaction_logs', [
        'transaction_id' => $transaction->id,
        'actor_id' => $seller->id,
        'from_state' => 'none',
        'to_state' => 'draft',
    ]);
});

it('rejects webhook requests without the configured callback token', function () {
    configureXenditForTest();

    $this->postJson(route('webhooks.xendit'), [
        'event' => 'payment_request.succeeded',
        'data' => ['id' => 'pr-unknown'],
    ], ['x-callback-token' => 'wrong-token'])
        ->assertUnauthorized();

    expect(PaymentWebhookEvent::query()->count())->toBe(0);
});

it('verifies a successful webhook with Xendit and reserves the DP exactly once', function () {
    configureXenditForTest();
    Http::preventStrayRequests();

    $scenario = createDpPaymentScenario();
    $attempt = $scenario['transaction']->dpPaymentAttempts()->create([
        'uuid' => (string) Str::uuid(),
        'reference_id' => 'mihom-dp-confirmed',
        'xendit_payment_request_id' => 'pr-mihom-confirmed',
        'channel_code' => 'BNI',
        'virtual_account_number' => '8800112233445',
        'amount' => 123457,
        'currency' => 'IDR',
        'status' => DpPaymentAttempt::STATUS_PENDING,
        'request_payload' => ['amount' => 123457],
        'expires_at' => now()->addDay(),
    ]);
    $verificationResponse = paymentRequestResponse($attempt, 'SUCCEEDED');
    Http::fake([
        'api.xendit.co/payment_requests/pr-mihom-confirmed' => Http::response($verificationResponse),
    ]);
    $webhookPayload = [
        'event' => 'payment_request.succeeded',
        'data' => ['payment_request_id' => 'pr-mihom-confirmed'],
    ];

    $this->postJson(route('webhooks.xendit'), $webhookPayload, [
        'x-callback-token' => 'sandbox-webhook-token',
        'webhook-id' => 'evt-mihom-confirmed',
    ])->assertOk();
    $this->postJson(route('webhooks.xendit'), $webhookPayload, [
        'x-callback-token' => 'sandbox-webhook-token',
        'webhook-id' => 'evt-mihom-confirmed',
    ])->assertOk();

    expect($attempt->fresh()->status)->toBe(DpPaymentAttempt::STATUS_PAID);

    $this->assertDatabaseHas('transactions', [
        'id' => $scenario['transaction']->id,
        'state' => 'dp_paid',
        'payment_gateway_ref' => 'pr-mihom-confirmed',
        'payment_method' => 'xendit_virtual_account',
        'settled_at' => null,
    ]);
    $this->assertDatabaseHas('transaction_ledger_entries', [
        'transaction_id' => $scenario['transaction']->id,
        'dp_payment_attempt_id' => $attempt->id,
        'amount' => '123457.00',
        'currency' => 'IDR',
        'status' => 'reserved',
    ]);
    $this->assertDatabaseHas('transaction_logs', [
        'transaction_id' => $scenario['transaction']->id,
        'actor_id' => null,
        'from_state' => 'draft',
        'to_state' => 'dp_paid',
    ]);
    $this->assertDatabaseCount('transaction_ledger_entries', 1);
    $this->assertDatabaseCount('payment_webhook_events', 1);
    expect(PaymentWebhookEvent::query()->firstOrFail()->raw_payload)->toContain('"payment_request_id":"pr-mihom-confirmed"');

    Http::assertSentCount(1);
    Http::assertSent(fn (Request $request): bool => $request->method() === 'GET'
        && str_ends_with($request->url(), '/payment_requests/pr-mihom-confirmed'));
});

it('does not reserve funds when the provider amount does not match the stored DP', function () {
    configureXenditForTest();
    Http::preventStrayRequests();

    $scenario = createDpPaymentScenario();
    $attempt = $scenario['transaction']->dpPaymentAttempts()->create([
        'uuid' => (string) Str::uuid(),
        'reference_id' => 'mihom-dp-mismatch',
        'xendit_payment_request_id' => 'pr-mihom-mismatch',
        'channel_code' => 'BNI',
        'amount' => 123457,
        'currency' => 'IDR',
        'status' => DpPaymentAttempt::STATUS_PENDING,
        'request_payload' => ['amount' => 123457],
        'expires_at' => now()->addDay(),
    ]);
    Http::fake([
        'api.xendit.co/payment_requests/pr-mihom-mismatch' => Http::response(
            paymentRequestResponse($attempt, 'SUCCEEDED', 123456),
        ),
    ]);

    $this->postJson(route('webhooks.xendit'), [
        'event' => 'payment_request.succeeded',
        'data' => ['id' => 'pr-mihom-mismatch'],
    ], [
        'x-callback-token' => 'sandbox-webhook-token',
        'webhook-id' => 'evt-mihom-mismatch',
    ])->assertUnprocessable();

    expect($attempt->fresh()->status)->toBe(DpPaymentAttempt::STATUS_PENDING)
        ->and($scenario['transaction']->fresh()->state)->toBe('draft')
        ->and($scenario['transaction']->ledgerEntries()->count())->toBe(0)
        ->and(PaymentWebhookEvent::query()->firstOrFail()->processing_status)->toBe('failed');
});

it('creates a new uniquely referenced attempt after the previous VA expires', function () {
    configureXenditForTest();
    Http::preventStrayRequests();

    $scenario = createDpPaymentScenario();
    grantDpPaymentPermission($scenario['buyer']);
    $scenario['transaction']->dpPaymentAttempts()->create([
        'uuid' => (string) Str::uuid(),
        'reference_id' => 'mihom-dp-expired',
        'xendit_payment_request_id' => 'pr-mihom-expired',
        'channel_code' => 'BNI',
        'amount' => 123457,
        'currency' => 'IDR',
        'status' => DpPaymentAttempt::STATUS_EXPIRED,
        'request_payload' => ['amount' => 123457],
        'expires_at' => now()->subMinute(),
    ]);
    Http::fake([
        'api.xendit.co/payment_requests' => function (Request $request) {
            return Http::response([
                'id' => 'pr-mihom-retry',
                'reference_id' => $request['reference_id'],
                'status' => 'PENDING',
                'payment_method' => [
                    'virtual_account' => [
                        'channel_properties' => ['account_number' => '8800000012345'],
                    ],
                ],
            ], 201);
        },
    ]);

    $this->actingAs($scenario['buyer'])
        ->post(route('buyer.dp-payments.store', ['transaction' => $scenario['transaction']->uuid]))
        ->assertRedirect();

    $attempts = $scenario['transaction']->dpPaymentAttempts()->orderBy('id')->get();

    expect($attempts)->toHaveCount(2)
        ->and($attempts[0]->reference_id)->not->toBe($attempts[1]->reference_id)
        ->and($attempts[1]->status)->toBe(DpPaymentAttempt::STATUS_PENDING);
});

it('maps non-successful Xendit statuses without advancing the transaction', function (string $providerStatus, string $attemptStatus) {
    configureXenditForTest();
    Http::preventStrayRequests();

    $scenario = createDpPaymentScenario();
    $attempt = $scenario['transaction']->dpPaymentAttempts()->create([
        'uuid' => (string) Str::uuid(),
        'reference_id' => 'mihom-dp-'.$providerStatus,
        'xendit_payment_request_id' => 'pr-mihom-'.$providerStatus,
        'channel_code' => 'BNI',
        'amount' => 123457,
        'currency' => 'IDR',
        'status' => DpPaymentAttempt::STATUS_PENDING,
        'request_payload' => ['amount' => 123457],
        'expires_at' => now()->addDay(),
    ]);
    Http::fake([
        'api.xendit.co/payment_requests/*' => Http::response(paymentRequestResponse($attempt, $providerStatus)),
    ]);

    $this->postJson(route('webhooks.xendit'), [
        'event' => 'payment_request.updated',
        'data' => ['id' => $attempt->xendit_payment_request_id],
    ], [
        'x-callback-token' => 'sandbox-webhook-token',
        'webhook-id' => 'evt-mihom-'.$providerStatus,
    ])->assertOk();

    expect($attempt->fresh()->status)->toBe($attemptStatus)
        ->and($scenario['transaction']->fresh()->state)->toBe('draft')
        ->and($scenario['transaction']->ledgerEntries()->count())->toBe(0);
})->with([
    'pending remains pending' => ['PENDING', DpPaymentAttempt::STATUS_PENDING],
    'expired is recorded' => ['EXPIRED', DpPaymentAttempt::STATUS_EXPIRED],
    'failed is recorded' => ['FAILED', DpPaymentAttempt::STATUS_FAILED],
    'canceled is recorded' => ['CANCELED', DpPaymentAttempt::STATUS_CANCELED],
]);
