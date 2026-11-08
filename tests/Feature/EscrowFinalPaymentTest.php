<?php

use App\Models\Appointment;
use App\Models\DpPaymentAttempt;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\TransactionLedgerEntry;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);
});

function createEscrowFinalPaymentScenario(
    string $price = '1000000000.00',
    string $dpAmount = '200000000.00',
    string $state = 'bpn_cleared',
): array {
    $seller = User::factory()->create(['name' => 'Penjual Mihom']);
    $seller->assignRole('User');

    $buyer = User::factory()->create(['name' => 'Pembeli Mihom']);
    $buyer->assignRole('User');

    $ppat = User::factory()->create(['name' => 'PPAT Notaris']);
    $ppat->assignRole('User');

    $property = Property::query()->create([
        'uuid' => (string) Str::uuid(),
        'seller_id' => $seller->id,
        'assigned_ppat_id' => $ppat->id,
        'property_type' => 'bangunan',
        'type' => 'rumah',
        'category' => 'sale',
        'status' => 'reserved',
        'title' => 'Rumah Mewah Escrow',
        'description' => 'Properti pengujian pelunasan escrow.',
        'price' => $price,
        'negotiable' => false,
        'province' => 'Jawa Barat',
        'city' => 'Bandung',
        'district' => 'Coblong',
        'postal_code' => '40132',
        'address' => 'Jl. Dago No. 100',
        'land_area_sqm' => 150,
        'building_area_sqm' => 120,
        'certificate_type' => 'SHM',
    ]);

    $appointment = Appointment::query()->create([
        'property_id' => $property->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'scheduled_at' => now()->subDays(5),
        'status' => Appointment::STATUS_COMPLETED,
        'buyer_intent' => Appointment::BUYER_INTENT_DEAL,
        'ppat_meeting_date' => now()->subDays(3)->toDateString(),
        'ppat_meeting_status' => Appointment::PPAT_MEETING_STATUS_CONFIRMED,
        'completed_at' => now()->subDays(5),
    ]);

    $transaction = Transaction::query()->create([
        'uuid' => (string) Str::uuid(),
        'property_id' => $property->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'booking_fee_amount' => 5000000,
        'total_property_amount' => $price,
        'state' => $state,
    ]);

    // Create confirmed DP attempt and ledger entry
    $dpAttempt = DpPaymentAttempt::query()->create([
        'uuid' => (string) Str::uuid(),
        'transaction_id' => $transaction->id,
        'payment_type' => DpPaymentAttempt::TYPE_DP,
        'reference_id' => 'mihom-dp-test-'.Str::uuid(),
        'xendit_payment_request_id' => 'pr-dp-confirmed-'.Str::uuid(),
        'channel_code' => 'BNI',
        'virtual_account_number' => '880011112222',
        'amount' => $dpAmount,
        'currency' => 'IDR',
        'status' => DpPaymentAttempt::STATUS_PAID,
        'request_payload' => [],
        'expires_at' => now()->addDay(),
        'paid_at' => now()->subDays(2),
    ]);

    TransactionLedgerEntry::query()->create([
        'transaction_id' => $transaction->id,
        'dp_payment_attempt_id' => $dpAttempt->id,
        'type' => TransactionLedgerEntry::TYPE_DP,
        'amount' => $dpAmount,
        'currency' => 'IDR',
        'status' => TransactionLedgerEntry::STATUS_RESERVED,
        'reserved_at' => now()->subDays(2),
    ]);

    return compact('seller', 'buyer', 'ppat', 'property', 'appointment', 'transaction', 'dpAttempt');
}

function configureXenditForEscrowTest(): void
{
    config([
        'services.xendit.environment' => 'sandbox',
        'services.xendit.sandbox_secret_key' => 'xnd_development_test_key',
        'services.xendit.sandbox_webhook_token' => 'sandbox-webhook-token',
        'services.xendit.virtual_account_channel_code' => 'BNI',
        'services.xendit.virtual_account_expiry_hours' => 24,
    ]);
}

it('calculates the outstanding escrow balance automatically based on total price minus confirmed DP', function () {
    $scenario = createEscrowFinalPaymentScenario('1000000000.00', '200000000.00');
    $transaction = $scenario['transaction'];

    expect($transaction->confirmedDpAmount())->toBe(200000000.0)
        ->and($transaction->remainingEscrowAmount())->toBe(800000000);
});

it('creates a new IDR Virtual Account for the remaining balance in Stage 4', function () {
    configureXenditForEscrowTest();
    Http::preventStrayRequests();

    $scenario = createEscrowFinalPaymentScenario('500000000.00', '100000000.00', 'bpn_cleared');
    $buyer = $scenario['buyer'];
    $transaction = $scenario['transaction'];

    $expectedReference = null;
    Http::fake([
        'api.xendit.co/v3/payment_requests' => function (Request $request) use (&$expectedReference) {
            $expectedReference = $request['reference_id'];

            return Http::response([
                'id' => 'pr-escrow-001',
                'reference_id' => $expectedReference,
                'status' => 'PENDING',
                'payment_method' => [
                    'virtual_account' => [
                        'account_number' => '880099998888',
                    ],
                ],
            ], 201);
        },
    ]);

    $this->actingAs($buyer)
        ->post(route('buyer.escrow-payments.store', ['transaction' => $transaction->uuid]))
        ->assertRedirect(route('buyer.bought-properties.show', ['property' => $scenario['property']->uuid]))
        ->assertSessionHas('status');

    $attempt = DpPaymentAttempt::query()
        ->where('payment_type', DpPaymentAttempt::TYPE_FINAL)
        ->firstOrFail();

    expect((int) $attempt->amount)->toBe(400000000)
        ->and($attempt->currency)->toBe('IDR')
        ->and($attempt->status)->toBe(DpPaymentAttempt::STATUS_PENDING)
        ->and($attempt->reference_id)->toBe($expectedReference)
        ->and($attempt->virtual_account_number)->toBe('880099998888')
        ->and($transaction->fresh()->state)->toBe('bpn_cleared');

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && str_ends_with($request->url(), '/v3/payment_requests')
        && $request['request_amount'] === 400000000
        && $request['currency'] === 'IDR'
        && $request['type'] === 'PAY'
        && $request['channel_code'] === 'BNI'
        && $request->hasHeader('idempotency-key')
        && $request->hasHeader('api-version', '2024-11-11'));
});

it('prevents duplicate VA generation by returning existing pending attempt', function () {
    configureXenditForEscrowTest();
    Http::preventStrayRequests();

    $scenario = createEscrowFinalPaymentScenario('500000000.00', '100000000.00', 'bpn_cleared');
    $transaction = $scenario['transaction'];

    // Create an existing pending final payment attempt
    $existingAttempt = DpPaymentAttempt::query()->create([
        'uuid' => (string) Str::uuid(),
        'transaction_id' => $transaction->id,
        'payment_type' => DpPaymentAttempt::TYPE_FINAL,
        'reference_id' => 'mihom-escrow-existing',
        'xendit_payment_request_id' => 'pr-escrow-existing',
        'channel_code' => 'BNI',
        'virtual_account_number' => '880055554444',
        'amount' => 400000000,
        'currency' => 'IDR',
        'status' => DpPaymentAttempt::STATUS_PENDING,
        'request_payload' => [],
        'expires_at' => now()->addDay(),
    ]);

    // Requesting payment again should NOT call Xendit and reuse the existing attempt
    $this->actingAs($scenario['buyer'])
        ->post(route('buyer.escrow-payments.store', ['transaction' => $transaction->uuid]))
        ->assertRedirect(route('buyer.bought-properties.show', ['property' => $scenario['property']->uuid]));

    expect(DpPaymentAttempt::query()->where('payment_type', DpPaymentAttempt::TYPE_FINAL)->count())->toBe(1);
    Http::assertNothingSent();
});

it('blocks Stage 4 payment if BPN check is not cleared or DP is not completed', function () {
    configureXenditForEscrowTest();

    // Case 1: Transaction still in dp_paid (Stage 3 not cleared)
    $scenario1 = createEscrowFinalPaymentScenario('500000000.00', '100000000.00', 'dp_paid');
    $this->actingAs($scenario1['buyer'])
        ->post(route('buyer.escrow-payments.store', ['transaction' => $scenario1['transaction']->uuid]))
        ->assertSessionHasErrors(['escrow_payment']);

    // Case 2: Transaction already completed escrow payment (held_in_escrow)
    $scenario2 = createEscrowFinalPaymentScenario('500000000.00', '100000000.00', 'held_in_escrow');
    $this->actingAs($scenario2['buyer'])
        ->post(route('buyer.escrow-payments.store', ['transaction' => $scenario2['transaction']->uuid]))
        ->assertSessionHasErrors(['escrow_payment']);
});

it('verifies and processes final escrow payment webhook, updating state to held_in_escrow', function () {
    configureXenditForEscrowTest();
    Http::preventStrayRequests();

    $scenario = createEscrowFinalPaymentScenario('500000000.00', '100000000.00', 'bpn_cleared');
    $transaction = $scenario['transaction'];

    $finalAttempt = DpPaymentAttempt::query()->create([
        'uuid' => (string) Str::uuid(),
        'transaction_id' => $transaction->id,
        'payment_type' => DpPaymentAttempt::TYPE_FINAL,
        'reference_id' => 'mihom-escrow-webhook-test',
        'xendit_payment_request_id' => 'pr-escrow-webhook-confirmed',
        'channel_code' => 'BNI',
        'virtual_account_number' => '880012341234',
        'amount' => 400000000,
        'currency' => 'IDR',
        'status' => DpPaymentAttempt::STATUS_PENDING,
        'request_payload' => [],
        'expires_at' => now()->addDay(),
    ]);

    Http::fake([
        'api.xendit.co/v3/payment_requests/pr-escrow-webhook-confirmed' => Http::response([
            'id' => 'pr-escrow-webhook-confirmed',
            'reference_id' => 'mihom-escrow-webhook-test',
            'request_amount' => 400000000,
            'currency' => 'IDR',
            'status' => 'SUCCEEDED',
        ]),
    ]);

    $webhookPayload = [
        'event' => 'payment_request.succeeded',
        'data' => [
            'id' => 'pr-escrow-webhook-confirmed',
            'payment_request_id' => 'pr-escrow-webhook-confirmed',
            'status' => 'SUCCEEDED',
        ],
    ];

    $this->postJson(route('webhooks.xendit'), $webhookPayload, [
        'x-callback-token' => 'sandbox-webhook-token',
        'webhook-id' => 'wh-escrow-final-001',
    ])->assertOk();

    expect($finalAttempt->fresh()->status)->toBe(DpPaymentAttempt::STATUS_PAID)
        ->and($finalAttempt->fresh()->paid_at)->not->toBeNull()
        ->and($transaction->fresh()->state)->toBe('held_in_escrow')
        ->and($transaction->fresh()->settled_at)->not->toBeNull()
        ->and($transaction->fresh()->hasCompletedEscrowPayment())->toBeTrue()
        ->and($transaction->fresh()->ledgerEntries()->where('type', TransactionLedgerEntry::TYPE_FINAL)->count())->toBe(1);

    // Duplicate webhook should be ignored idempotently
    $this->postJson(route('webhooks.xendit'), $webhookPayload, [
        'x-callback-token' => 'sandbox-webhook-token',
        'webhook-id' => 'wh-escrow-final-001',
    ])->assertOk();

    expect($transaction->logs()->where('to_state', 'held_in_escrow')->count())->toBe(1);
});

it('synchronizes Stage 4 status across buyer, seller, and PPAT views', function () {
    // 1. When in bpn_cleared (ready for payment)
    $scenario = createEscrowFinalPaymentScenario('1000000000.00', '200000000.00', 'bpn_cleared');
    $buyer = $scenario['buyer'];
    $seller = $scenario['seller'];
    $ppat = $scenario['ppat'];
    $property = $scenario['property'];

    // Grant PPAT permissions
    foreach (['appointments.view_all', 'transactions.view_all', 'transactions.mark_bpn_cleared'] as $perm) {
        $p = Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        $ppat->givePermissionTo($p);
    }

    // Buyer view shows payment button and remaining balance (Rp 800.000.000)
    $this->actingAs($buyer)
        ->get(route('buyer.bought-properties.show', ['property' => $property->uuid]))
        ->assertOk()
        ->assertSee('Bayar Pelunasan (Escrow)')
        ->assertSee('800.000.000');

    // Seller view shows ready for settlement status
    $this->actingAs($seller)
        ->get(route('seller.my-properties.show', ['property' => $property->uuid]))
        ->assertOk()
        ->assertSee('Siap Dilunasi')
        ->assertSee('800.000.000');

    // PPAT view shows waiting for buyer to pay settlement, without payment form
    $this->actingAs($ppat)
        ->get(route('land-deed.client-properties.show', ['property' => $property->uuid]))
        ->assertOk()
        ->assertSee('800.000.000')
        ->assertDontSee('Bayar Pelunasan (Escrow)');

    // 2. When Stage 4 is completed (held_in_escrow)
    $scenario['transaction']->update([
        'state' => 'held_in_escrow',
        'settled_at' => now(),
    ]);

    // Buyer view shows confirmed
    $this->actingAs($buyer)
        ->get(route('buyer.bought-properties.show', ['property' => $property->uuid]))
        ->assertOk()
        ->assertSee('Pelunasan Terkonfirmasi');

    // Seller view shows confirmed
    $this->actingAs($seller)
        ->get(route('seller.my-properties.show', ['property' => $property->uuid]))
        ->assertOk()
        ->assertSee('Pelunasan Terkonfirmasi');

    // PPAT view shows confirmed
    $this->actingAs($ppat)
        ->get(route('land-deed.client-properties.show', ['property' => $property->uuid]))
        ->assertOk()
        ->assertSee('Pelunasan Terkonfirmasi');
});
