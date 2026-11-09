<?php

use App\Models\Appointment;
use App\Models\DpPaymentAttempt;
use App\Models\EscrowDisbursement;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\TransactionLedgerEntry;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleAndPermissionSeeder::class);
});

function createEscrowReleaseScenario(
    string $state = 'ajb_scheduled',
    bool $includeFinalPayment = true,
    bool $includeAjbEvidence = true,
): array {
    $seller = User::factory()->create(['name' => 'Penjual Tahap Enam']);
    $seller->assignRole('User');

    $buyer = User::factory()->create(['name' => 'Pembeli Tahap Enam']);
    $buyer->assignRole('User');

    $ppat = User::factory()->create(['name' => 'PPAT Tahap Enam']);
    $ppat->assignRole('PPAT');

    $property = Property::query()->create([
        'uuid' => (string) Str::uuid(),
        'seller_id' => $seller->id,
        'assigned_ppat_id' => $ppat->id,
        'property_type' => 'bangunan',
        'type' => 'rumah',
        'category' => 'sale',
        'status' => 'reserved',
        'title' => 'Rumah Pencairan Escrow',
        'description' => 'Properti untuk pengujian pencairan escrow.',
        'price' => '500000000.00',
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

    $ajbDocumentPath = $includeAjbEvidence ? 'ajb-documents/akta-release.pdf' : null;
    $appointment = Appointment::query()->create([
        'property_id' => $property->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'scheduled_at' => now()->subDays(5),
        'completed_at' => now()->subDays(4),
        'status' => Appointment::STATUS_COMPLETED,
        'buyer_intent' => Appointment::BUYER_INTENT_DEAL,
        'ppat_meeting_date' => now()->subDays(3)->toDateString(),
        'ppat_meeting_status' => Appointment::PPAT_MEETING_STATUS_CONFIRMED,
        'ajb_meeting_date' => now()->subDays(2)->toDateString(),
        'ajb_meeting_status' => Appointment::AJB_MEETING_STATUS_CONFIRMED,
        'ajb_document_path' => $ajbDocumentPath,
        'ajb_signed_at' => $includeAjbEvidence ? now()->subDay() : null,
    ]);

    $transaction = Transaction::query()->create([
        'uuid' => (string) Str::uuid(),
        'property_id' => $property->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'booking_fee_amount' => 50000000,
        'total_property_amount' => '500000000.00',
        'state' => $state,
        'ajb_document_path' => $ajbDocumentPath,
        'ajb_signed_at' => $includeAjbEvidence ? now()->subDay() : null,
    ]);

    $dpAttempt = createReleasePaymentAttempt($transaction, DpPaymentAttempt::TYPE_DP, '100000000.00');
    createReleaseLedgerEntry($transaction, $dpAttempt, TransactionLedgerEntry::TYPE_DP);

    if ($includeFinalPayment) {
        $finalAttempt = createReleasePaymentAttempt($transaction, DpPaymentAttempt::TYPE_FINAL, '400000000.00');
        createReleaseLedgerEntry($transaction, $finalAttempt, TransactionLedgerEntry::TYPE_FINAL);
    }

    return compact('seller', 'buyer', 'ppat', 'property', 'appointment', 'transaction');
}

function createReleasePaymentAttempt(Transaction $transaction, string $type, string $amount): DpPaymentAttempt
{
    return DpPaymentAttempt::query()->create([
        'uuid' => (string) Str::uuid(),
        'transaction_id' => $transaction->id,
        'payment_type' => $type,
        'reference_id' => 'mihom-'.$type.'-'.Str::uuid(),
        'xendit_payment_request_id' => 'pr-'.$type.'-'.Str::uuid(),
        'channel_code' => 'BNI',
        'virtual_account_number' => '880012345678',
        'amount' => $amount,
        'currency' => 'IDR',
        'status' => DpPaymentAttempt::STATUS_PAID,
        'request_payload' => [],
        'expires_at' => now()->addDay(),
        'paid_at' => now()->subDays(2),
    ]);
}

function createReleaseLedgerEntry(Transaction $transaction, DpPaymentAttempt $attempt, string $type): TransactionLedgerEntry
{
    return TransactionLedgerEntry::query()->create([
        'transaction_id' => $transaction->id,
        'dp_payment_attempt_id' => $attempt->id,
        'type' => $type,
        'amount' => $attempt->amount,
        'currency' => 'IDR',
        'status' => TransactionLedgerEntry::STATUS_RESERVED,
        'reserved_at' => now()->subDays(2),
    ]);
}

function createEscrowReleaseAdmin(string $role = 'Admin'): User
{
    $admin = User::factory()->create();
    $admin->assignRole($role);

    return $admin;
}

it('shows transaction history and actual escrow balances to Admin and Superadmin', function () {
    $scenario = createEscrowReleaseScenario();

    $this->actingAs(createEscrowReleaseAdmin())
        ->get(route('admin.transaction-and-escrow'))
        ->assertOk()
        ->assertSeeText('Rumah Pencairan Escrow')
        ->assertSeeText('Pembeli Tahap Enam')
        ->assertSeeText('Penjual Tahap Enam')
        ->assertSeeText('Tahap 2 · DP')
        ->assertSeeText('Tahap 4 · Pelunasan escrow')
        ->assertSeeText('Siap dicairkan')
        ->assertSee('500.000.000');

    $this->actingAs(createEscrowReleaseAdmin('Superadmin'))
        ->get(route('superadmin.transaction-and-escrow'))
        ->assertOk()
        ->assertSeeText($scenario['transaction']->uuid);
});

it('filters incoming payment history by type status and transaction search', function () {
    $scenario = createEscrowReleaseScenario();
    $dpAttempt = DpPaymentAttempt::query()
        ->where('transaction_id', $scenario['transaction']->id)
        ->where('payment_type', DpPaymentAttempt::TYPE_DP)
        ->firstOrFail();
    $finalAttempt = DpPaymentAttempt::query()
        ->where('transaction_id', $scenario['transaction']->id)
        ->where('payment_type', DpPaymentAttempt::TYPE_FINAL)
        ->firstOrFail();

    $this->actingAs(createEscrowReleaseAdmin())
        ->get(route('admin.transaction-and-escrow', [
            'search' => 'Rumah Pencairan Escrow',
            'payment_type' => DpPaymentAttempt::TYPE_DP,
            'payment_status' => DpPaymentAttempt::STATUS_PAID,
        ]))
        ->assertOk()
        ->assertSeeText($dpAttempt->reference_id)
        ->assertDontSeeText($finalAttempt->reference_id);
});

it('creates one pending disbursement for eligible escrow funds', function () {
    $scenario = createEscrowReleaseScenario();
    $admin = createEscrowReleaseAdmin();
    $url = route('escrow-disbursements.store', ['transaction' => $scenario['transaction']->uuid]);

    $this->actingAs($admin)
        ->from(route('admin.transaction-and-escrow'))
        ->post($url)
        ->assertRedirect(route('admin.transaction-and-escrow'))
        ->assertSessionHas('status');

    $disbursement = EscrowDisbursement::query()->firstOrFail();
    expect($disbursement->amount)->toBe('500000000.00')
        ->and($disbursement->recipient_user_id)->toBe($scenario['seller']->id)
        ->and($disbursement->recipient_name)->toBe('Penjual Tahap Enam')
        ->and($disbursement->initiated_by)->toBe($admin->id)
        ->and($disbursement->status)->toBe(EscrowDisbursement::STATUS_PENDING)
        ->and($scenario['transaction']->fresh()->state)->toBe('ajb_scheduled')
        ->and($scenario['transaction']->ledgerEntries()->where('status', TransactionLedgerEntry::STATUS_RESERVED)->count())->toBe(2);

    $this->from(route('admin.transaction-and-escrow'))
        ->post($url)
        ->assertRedirect(route('admin.transaction-and-escrow'))
        ->assertSessionHasErrors('escrow_release');

    expect(EscrowDisbursement::query()->count())->toBe(1);
});

it('blocks release when escrow payments or signed AJB evidence are incomplete', function () {
    $admin = createEscrowReleaseAdmin();
    $missingFinalPayment = createEscrowReleaseScenario(includeFinalPayment: false);
    $missingAjbEvidence = createEscrowReleaseScenario(includeAjbEvidence: false);
    $notAtStageSix = createEscrowReleaseScenario(state: 'held_in_escrow');
    $mismatchedPayment = createEscrowReleaseScenario();
    DpPaymentAttempt::query()
        ->where('transaction_id', $mismatchedPayment['transaction']->id)
        ->where('payment_type', DpPaymentAttempt::TYPE_FINAL)
        ->update(['amount' => '399000000.00']);

    foreach ([$missingFinalPayment, $missingAjbEvidence, $notAtStageSix, $mismatchedPayment] as $scenario) {
        $this->actingAs($admin)
            ->from(route('admin.transaction-and-escrow'))
            ->post(route('escrow-disbursements.store', ['transaction' => $scenario['transaction']->uuid]))
            ->assertRedirect(route('admin.transaction-and-escrow'))
            ->assertSessionHasErrors('escrow_release');
    }

    expect(EscrowDisbursement::query()->count())->toBe(0);
});

it('confirms a manual bank transfer and synchronizes Stage 6 across buyer seller and PPAT views', function () {
    $scenario = createEscrowReleaseScenario();
    $admin = createEscrowReleaseAdmin('Superadmin');
    $this->actingAs($admin)
        ->post(route('escrow-disbursements.store', ['transaction' => $scenario['transaction']->uuid]))
        ->assertRedirect();

    $disbursement = EscrowDisbursement::query()->firstOrFail();
    $confirmUrl = route('escrow-disbursements.confirm', ['escrowDisbursement' => $disbursement->uuid]);

    $this->from(route('superadmin.transaction-and-escrow'))
        ->post($confirmUrl)
        ->assertRedirect(route('superadmin.transaction-and-escrow'))
        ->assertSessionHasErrors('transfer_reference');

    $this->from(route('superadmin.transaction-and-escrow'))
        ->post($confirmUrl, ['transfer_reference' => 'TRF-BANK-20261109-001'])
        ->assertRedirect(route('superadmin.transaction-and-escrow'))
        ->assertSessionHas('status');

    $disbursement->refresh();
    expect($disbursement->status)->toBe(EscrowDisbursement::STATUS_RELEASED)
        ->and($disbursement->transfer_reference)->toBe('TRF-BANK-20261109-001')
        ->and($disbursement->confirmed_by)->toBe($admin->id)
        ->and($disbursement->confirmed_at)->not->toBeNull()
        ->and($scenario['transaction']->fresh()->state)->toBe('completed')
        ->and($scenario['transaction']->ledgerEntries()->where('status', TransactionLedgerEntry::STATUS_RELEASED)->count())->toBe(2)
        ->and($scenario['transaction']->logs()->where('to_state', 'completed')->exists())->toBeTrue();

    $this->from(route('superadmin.transaction-and-escrow'))
        ->post($confirmUrl, ['transfer_reference' => 'TRF-DUPLICATE'])
        ->assertRedirect(route('superadmin.transaction-and-escrow'))
        ->assertSessionHasErrors('escrow_release');

    $this->actingAs($scenario['buyer'])
        ->get(route('buyer.bought-properties.show', ['property' => $scenario['property']->uuid]))
        ->assertOk()
        ->assertSeeText('Dana telah dicairkan')
        ->assertSeeText('TRF-BANK-20261109-001');

    $this->actingAs($scenario['seller'])
        ->get(route('seller.my-properties.show', ['property' => $scenario['property']->uuid]))
        ->assertOk()
        ->assertSeeText('Dana telah dicairkan')
        ->assertSeeText('TRF-BANK-20261109-001');

    $this->actingAs($scenario['ppat'])
        ->get(route('land-deed.client-properties.show', ['property' => $scenario['property']->uuid]))
        ->assertOk()
        ->assertSeeText('Dana telah dicairkan')
        ->assertSeeText('TRF-BANK-20261109-001');
});

it('refuses escrow dashboard and release access to users outside Admin and Superadmin', function () {
    $scenario = createEscrowReleaseScenario();
    $ppat = $scenario['ppat'];

    $this->actingAs($ppat)
        ->get(route('admin.transaction-and-escrow'))
        ->assertForbidden();

    $this->actingAs($ppat)
        ->post(route('escrow-disbursements.store', ['transaction' => $scenario['transaction']->uuid]))
        ->assertForbidden();

    expect(EscrowDisbursement::query()->count())->toBe(0);
});
