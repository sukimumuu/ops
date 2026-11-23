<?php

use App\Models\DpPaymentAttempt;
use App\Models\EscrowDisbursement;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function createDisbursementAccountSeller(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'name' => 'Penjual Rekening Pencairan',
    ], $attributes));
}

function createDisbursementAccountTransaction(
    User $seller,
    User $buyer,
    string $title = 'Rumah Rekening Pencairan',
    string $state = 'held_in_escrow',
): Transaction {
    $property = Property::query()->create([
        'uuid' => (string) Str::uuid(),
        'seller_id' => $seller->id,
        'property_type' => 'bangunan',
        'type' => 'rumah',
        'category' => 'sale',
        'status' => 'reserved',
        'title' => $title,
        'description' => 'Properti untuk pengujian rekening pencairan penjual.',
        'price' => 500000000,
        'negotiable' => false,
        'province' => 'Jawa Barat',
        'city' => 'Bandung',
        'district' => 'Coblong',
        'postal_code' => '40132',
        'address' => 'Jl. Dago No. 21',
        'land_area_sqm' => 120,
        'building_area_sqm' => 100,
        'certificate_type' => 'SHM',
    ]);

    return Transaction::query()->create([
        'uuid' => (string) Str::uuid(),
        'property_id' => $property->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'booking_fee_amount' => 100000000,
        'total_property_amount' => '500000000.00',
        'state' => $state,
    ]);
}

function createDisbursementAccountPaymentAttempt(
    Transaction $transaction,
    string $type,
    string $amount,
    string $status = DpPaymentAttempt::STATUS_PAID,
): DpPaymentAttempt {
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
        'status' => $status,
        'request_payload' => [],
        'expires_at' => now()->addDay(),
        'paid_at' => $status === DpPaymentAttempt::STATUS_PAID ? now()->subDay() : null,
    ]);
}

it('shows the disbursement account form and flags a missing account', function () {
    $seller = createDisbursementAccountSeller();

    $this->actingAs($seller)
        ->get(route('seller.disbursement-account'))
        ->assertOk()
        ->assertSee('name="bank_name"', false)
        ->assertSee('value="Mandiri"', false)
        ->assertSee('assets/bank-logos/BNI.svg', false)
        ->assertSeeText('BTN Syariah')
        ->assertSee('name="bank_account_number"', false)
        ->assertSee('name="bank_account_holder_name"', false)
        ->assertSeeText('Rekening belum lengkap')
        ->assertSeeText('Lengkapi rekening untuk membuka pencairan dana escrow')
        ->assertSeeText('Belum ada rekening tersimpan');
});

it('lets a seller save the disbursement account and normalizes the number', function () {
    $seller = createDisbursementAccountSeller();

    $this->actingAs($seller)
        ->put(route('seller.disbursement-account.update'), [
            'bank_name' => 'Mandiri',
            'bank_account_number' => '1370 0098 7654 3',
            'bank_account_holder_name' => 'Penjual Rekening Pencairan',
        ])
        ->assertRedirect(route('seller.disbursement-account'))
        ->assertSessionHas('status');

    $seller->refresh();

    expect($seller->bank_name)->toBe('Mandiri')
        ->and($seller->bank_account_number)->toBe('1370009876543')
        ->and($seller->bank_account_holder_name)->toBe('Penjual Rekening Pencairan')
        ->and($seller->bank_account_updated_at)->not->toBeNull()
        ->and($seller->hasDisbursementAccount())->toBeTrue();

    $this->actingAs($seller)
        ->get(route('seller.disbursement-account'))
        ->assertOk()
        ->assertSeeText('Rekening aktif')
        ->assertSeeText('1370009876543')
        ->assertSee('assets/bank-logos/Mandiri.svg', false);
});

it('rejects a bank that is not in the predefined list', function () {
    $seller = createDisbursementAccountSeller();

    $this->actingAs($seller)
        ->from(route('seller.disbursement-account'))
        ->put(route('seller.disbursement-account.update'), [
            'bank_name' => 'Bank BCA',
            'bank_account_number' => '1370009876543',
            'bank_account_holder_name' => 'Penjual Rekening Pencairan',
        ])
        ->assertRedirect(route('seller.disbursement-account'))
        ->assertSessionHasErrors('bank_name');

    expect($seller->fresh()->bank_name)->toBeNull();
});

it('requires every bank field before the account can be saved', function () {
    $seller = createDisbursementAccountSeller();

    $this->actingAs($seller)
        ->from(route('seller.disbursement-account'))
        ->put(route('seller.disbursement-account.update'), [
            'bank_name' => '',
            'bank_account_number' => 'ABC',
            'bank_account_holder_name' => '',
        ])
        ->assertRedirect(route('seller.disbursement-account'))
        ->assertSessionHasErrors(['bank_name', 'bank_account_number', 'bank_account_holder_name']);

    expect($seller->fresh()->hasDisbursementAccount())->toBeFalse();
});

it('lists DP pelunasan and disbursement stages for the seller history', function () {
    $seller = createDisbursementAccountSeller([
        'bank_name' => 'BRI',
        'bank_account_number' => '5271234567',
        'bank_account_holder_name' => 'Penjual Rekening Pencairan',
    ]);
    $buyer = User::factory()->create(['name' => 'Pembeli Rekening Pencairan']);
    $transaction = createDisbursementAccountTransaction($seller, $buyer);

    createDisbursementAccountPaymentAttempt($transaction, DpPaymentAttempt::TYPE_DP, '100000000.00');
    createDisbursementAccountPaymentAttempt(
        $transaction,
        DpPaymentAttempt::TYPE_FINAL,
        '400000000.00',
        DpPaymentAttempt::STATUS_PENDING,
    );

    EscrowDisbursement::query()->create([
        'uuid' => (string) Str::uuid(),
        'transaction_id' => $transaction->id,
        'recipient_user_id' => $seller->id,
        'recipient_name' => $seller->name,
        'amount' => '500000000.00',
        'currency' => 'IDR',
        'status' => EscrowDisbursement::STATUS_RELEASED,
        'initiated_by' => $seller->id,
        'transfer_reference' => 'TRF-REKENING-001',
        'confirmed_at' => now(),
    ]);

    $this->actingAs($seller)
        ->get(route('seller.disbursement-account'))
        ->assertOk()
        ->assertSeeText('Rumah Rekening Pencairan')
        ->assertSeeText('Pembeli Rekening Pencairan')
        ->assertSeeText('Down Payment (DP)')
        ->assertSeeText('Pelunasan')
        ->assertSeeText('Terverifikasi')
        ->assertSeeText('Menunggu pembayaran')
        ->assertSeeText('Dana telah ditransfer')
        ->assertSeeText('TRF-REKENING-001')
        ->assertSeeText('500.000.000');
});

it('only shows transactions belonging to the authenticated seller', function () {
    $seller = createDisbursementAccountSeller();
    $otherSeller = createDisbursementAccountSeller(['name' => 'Penjual Lain Rekening']);
    $buyer = User::factory()->create();

    createDisbursementAccountTransaction($seller, $buyer, 'Properti Milik Penjual Aktif');
    createDisbursementAccountTransaction($otherSeller, $buyer, 'Properti Milik Penjual Lain');

    $this->actingAs($seller)
        ->get(route('seller.disbursement-account'))
        ->assertOk()
        ->assertSeeText('Properti Milik Penjual Aktif')
        ->assertDontSeeText('Properti Milik Penjual Lain');
});
