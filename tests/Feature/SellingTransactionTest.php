<?php

use App\Models\DpPaymentAttempt;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\TransactionLedgerEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $permission = Permission::firstOrCreate([
        'name' => 'transactions.view_own_tracker',
        'guard_name' => 'web',
    ]);
    Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web'])
        ->syncPermissions([$permission]);
});

function createSellingTransactionProperty(User $seller, array $attributes = []): Property
{
    return Property::query()->create(array_merge([
        'uuid' => (string) Str::uuid(),
        'seller_id' => $seller->id,
        'property_type' => 'bangunan',
        'type' => 'rumah',
        'category' => 'sale',
        'status' => 'reserved',
        'title' => 'Rumah Transaksi Penjualan',
        'description' => 'Properti untuk pengujian daftar transaksi penjualan.',
        'price' => 1000000000,
        'negotiable' => false,
        'province' => 'Jawa Barat',
        'city' => 'Bandung',
        'district' => 'Coblong',
        'postal_code' => '40132',
        'address' => 'Jl. Dago No. 100',
        'land_area_sqm' => 150,
        'building_area_sqm' => 120,
        'certificate_type' => 'SHM',
    ], $attributes));
}

function createSellingTransaction(
    User $seller,
    User $buyer,
    Property $property,
    string $state = 'draft',
): Transaction {
    return Transaction::query()->create([
        'uuid' => (string) Str::uuid(),
        'property_id' => $property->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $seller->id,
        'booking_fee_amount' => 10000000,
        'total_property_amount' => $property->price,
        'state' => $state,
    ]);
}

function recordSellingDownPayment(Transaction $transaction, string $amount = '100000000.00'): void
{
    $attempt = DpPaymentAttempt::query()->create([
        'uuid' => (string) Str::uuid(),
        'transaction_id' => $transaction->id,
        'payment_type' => DpPaymentAttempt::TYPE_DP,
        'reference_id' => 'mihom-dp-'.Str::uuid(),
        'xendit_payment_request_id' => 'pr-dp-'.Str::uuid(),
        'channel_code' => 'BNI',
        'amount' => $amount,
        'currency' => 'IDR',
        'status' => DpPaymentAttempt::STATUS_PAID,
        'request_payload' => [],
        'expires_at' => now()->addDay(),
        'paid_at' => now()->subDay(),
    ]);

    TransactionLedgerEntry::query()->create([
        'transaction_id' => $transaction->id,
        'dp_payment_attempt_id' => $attempt->id,
        'type' => TransactionLedgerEntry::TYPE_DP,
        'amount' => $attempt->amount,
        'currency' => 'IDR',
        'status' => TransactionLedgerEntry::STATUS_RESERVED,
        'reserved_at' => now()->subDay(),
    ]);
}

it('redirects guests away from the selling transaction page', function () {
    $this->get(route('seller.selling-transaction'))
        ->assertRedirect(route('login'));
});

it('lists only the transactions belonging to the logged in seller', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $otherSeller = User::factory()->create();
    $buyer = User::factory()->create();

    $ownProperty = createSellingTransactionProperty($seller, ['title' => 'Rumah Milik Penjual Utama']);
    createSellingTransaction($seller, $buyer, $ownProperty, 'dp_paid');

    $otherProperty = createSellingTransactionProperty($otherSeller, ['title' => 'Rumah Milik Penjual Lain']);
    createSellingTransaction($otherSeller, $buyer, $otherProperty, 'dp_paid');

    $this->actingAs($seller)
        ->get(route('seller.selling-transaction'))
        ->assertOk()
        ->assertSeeText('Rumah Milik Penjual Utama')
        ->assertDontSeeText('Rumah Milik Penjual Lain')
        ->assertViewHas('stats', fn (array $stats): bool => $stats['total'] === 1 && $stats['active'] === 1);
});

it('shows the down payment summary and the current transaction stage', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $buyer = User::factory()->create(['name' => 'Pembeli Transaksi']);
    $property = createSellingTransactionProperty($seller, ['title' => 'Rumah DP Dibayar']);
    $transaction = createSellingTransaction($seller, $buyer, $property, 'dp_paid');

    recordSellingDownPayment($transaction);

    $this->actingAs($seller)
        ->get(route('seller.selling-transaction'))
        ->assertOk()
        ->assertSeeText('Pembeli Transaksi')
        ->assertSeeText('Rp 1.000.000.000')
        ->assertSeeText('DP Rp 100.000.000')
        ->assertSeeText('Sisa Rp 900.000.000')
        ->assertSeeText('Tahap 3 dari 7')
        ->assertSeeText('Pengecekan legalitas BPN & validasi pajak')
        ->assertSeeText('DP dibayar, dana ditahan')
        ->assertViewHas('transactions', function ($transactions): bool {
            $transaction = $transactions->first();

            return (int) $transaction->confirmed_dp_amount === 100000000
                && (int) $transaction->remaining_amount === 900000000
                && (int) $transaction->current_stage === 3
                && (int) $transaction->current_stage_progress === 45;
        });
});

it('filters transactions by state and search term', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $buyer = User::factory()->create();

    $draftProperty = createSellingTransactionProperty($seller, ['title' => 'Rumah Masih Draft']);
    createSellingTransaction($seller, $buyer, $draftProperty, 'draft');

    $completedProperty = createSellingTransactionProperty($seller, ['title' => 'Rumah Sudah Selesai']);
    createSellingTransaction($seller, $buyer, $completedProperty, 'completed');

    $this->actingAs($seller)
        ->get(route('seller.selling-transaction', ['state' => 'completed']))
        ->assertOk()
        ->assertSeeText('Rumah Sudah Selesai')
        ->assertDontSeeText('Rumah Masih Draft');

    $this->actingAs($seller)
        ->get(route('seller.selling-transaction', ['search' => 'Draft']))
        ->assertOk()
        ->assertSeeText('Rumah Masih Draft')
        ->assertDontSeeText('Rumah Sudah Selesai');
});

it('falls back to default filters when invalid values are supplied', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $buyer = User::factory()->create(['name' => 'Pembeli Tetap Tampil']);
    $property = createSellingTransactionProperty($seller, ['title' => 'Rumah Tetap Tampil']);
    createSellingTransaction($seller, $buyer, $property, 'bpn_cleared');

    $this->actingAs($seller)
        ->get(route('seller.selling-transaction', ['state' => 'invalid', 'sort' => 'invalid']))
        ->assertOk()
        ->assertSeeText('Rumah Tetap Tampil')
        ->assertSee('value="newest" selected', false);
});

it('renders the empty state when the seller has no transactions', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');

    $this->actingAs($seller)
        ->get(route('seller.selling-transaction'))
        ->assertOk()
        ->assertSeeText('Belum ada transaksi penjualan')
        ->assertSeeText('Lihat permintaan survei');
});
