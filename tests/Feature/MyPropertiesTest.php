<?php

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
});

function createMyPropertyForTest(User $seller, array $attributes = []): Property
{
    return Property::query()->create(array_merge([
        'uuid' => (string) Str::uuid(),
        'seller_id' => $seller->id,
        'property_type' => 'bangunan',
        'type' => 'rumah',
        'category' => 'sale',
        'status' => 'published',
        'title' => 'Properti Saya',
        'description' => 'Properti untuk pengujian halaman properti saya.',
        'price' => 1500000000,
        'negotiable' => false,
        'province' => 'Jawa Barat',
        'city' => 'Bandung',
        'district' => 'Coblong',
        'postal_code' => '40132',
        'address' => 'Jl. Merdeka No. 1',
        'land_area_sqm' => 120,
        'building_area_sqm' => 90,
        'rooms' => '3',
        'bathrooms' => '2',
        'certificate_type' => 'SHM',
    ], $attributes));
}

it('redirects guests to the login page', function () {
    $this->get(route('seller.my-properties'))
        ->assertRedirect(route('login'));
});

it('shows only properties owned by the authenticated seller', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');
    $otherSeller = User::factory()->create();
    $otherSeller->assignRole('User');

    createMyPropertyForTest($seller, ['title' => 'Rumah Milik Sendiri']);
    createMyPropertyForTest($otherSeller, ['title' => 'Rumah Milik Orang Lain']);

    $this->actingAs($seller)
        ->get(route('seller.my-properties'))
        ->assertSeeText('Rumah Milik Sendiri')
        ->assertDontSeeText('Rumah Milik Orang Lain');
});

it('searches properties by title and location', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');

    createMyPropertyForTest($seller, [
        'title' => 'Villa Dago Asri',
        'city' => 'Bandung',
        'district' => 'Coblong',
    ]);
    createMyPropertyForTest($seller, [
        'title' => 'Rumah Surabaya',
        'city' => 'Surabaya',
        'district' => 'Wonokromo',
    ]);

    $this->actingAs($seller)
        ->get(route('seller.my-properties', ['search' => 'Dago']))
        ->assertSeeText('Villa Dago Asri')
        ->assertDontSeeText('Rumah Surabaya');

    $this->actingAs($seller)
        ->get(route('seller.my-properties', ['search' => 'Surabaya']))
        ->assertSeeText('Rumah Surabaya')
        ->assertDontSeeText('Villa Dago Asri');
});

it('filters properties by status, category, and property type', function (array $query, string $visibleTitle, string $hiddenTitle) {
    $seller = User::factory()->create();
    $seller->assignRole('User');

    createMyPropertyForTest($seller, [
        'title' => 'Properti Terbit',
        'status' => 'published',
        'category' => 'sale',
        'property_type' => 'bangunan',
    ]);
    createMyPropertyForTest($seller, [
        'title' => 'Properti Draft',
        'status' => 'draft',
        'category' => 'rent',
        'property_type' => 'tanah',
    ]);

    $this->actingAs($seller)
        ->get(route('seller.my-properties', $query))
        ->assertSeeText($visibleTitle)
        ->assertDontSeeText($hiddenTitle);
})->with([
    'status' => [['status' => 'draft'], 'Properti Draft', 'Properti Terbit'],
    'category' => [['category' => 'sale'], 'Properti Terbit', 'Properti Draft'],
    'property type' => [['property_type' => 'tanah'], 'Properti Draft', 'Properti Terbit'],
]);

it('sorts properties by price', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');

    createMyPropertyForTest($seller, ['title' => 'Properti Murah', 'price' => 500000000]);
    createMyPropertyForTest($seller, ['title' => 'Properti Mahal', 'price' => 2500000000]);

    $this->actingAs($seller)
        ->get(route('seller.my-properties', ['sort' => 'price_asc']))
        ->assertSeeInOrder(['Properti Murah', 'Properti Mahal']);

    $this->actingAs($seller)
        ->get(route('seller.my-properties', ['sort' => 'price_desc']))
        ->assertSeeInOrder(['Properti Mahal', 'Properti Murah']);
});

it('paginates filtered properties and preserves the query string', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');

    foreach (range(1, 9) as $index) {
        createMyPropertyForTest($seller, [
            'title' => "Properti Halaman {$index}",
            'status' => 'draft',
        ]);
    }

    $this->actingAs($seller)
        ->get(route('seller.my-properties', ['search' => 'Halaman', 'status' => 'draft']))
        ->assertViewHas('properties', function ($properties): bool {
            return $properties->count() === 8
                && str_contains($properties->url(2), 'search=Halaman')
                && str_contains($properties->url(2), 'status=draft');
        });

    $this->actingAs($seller)
        ->get(route('seller.my-properties', ['search' => 'Halaman', 'status' => 'draft', 'page' => 2]))
        ->assertSeeText('Properti Halaman 1');
});

it('links every property card to the seller detail page', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');

    $published = createMyPropertyForTest($seller, ['title' => 'Properti Terbit', 'status' => 'published']);
    $draft = createMyPropertyForTest($seller, ['title' => 'Properti Draft', 'status' => 'draft']);

    $this->actingAs($seller)
        ->get(route('seller.my-properties'))
        ->assertSee(route('seller.my-properties.show', ['property' => $published->uuid]), false)
        ->assertSee(route('seller.my-properties.show', ['property' => $draft->uuid]), false)
        ->assertDontSee(route('property-details', ['uuid' => $published->uuid]), false);
});

it('falls back to default filters when invalid values are supplied', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');

    createMyPropertyForTest($seller, ['title' => 'Properti Tetap Tampil']);

    $this->actingAs($seller)
        ->get(route('seller.my-properties', [
            'status' => 'invalid',
            'category' => 'invalid',
            'property_type' => 'invalid',
            'sort' => 'invalid',
        ]))
        ->assertOk()
        ->assertSeeText('Properti Tetap Tampil')
        ->assertSee('value="newest" selected', false);
});

it('renders the empty state when the seller has no properties', function () {
    $seller = User::factory()->create();
    $seller->assignRole('User');

    $this->actingAs($seller)
        ->get(route('seller.my-properties'))
        ->assertSeeText('Belum ada properti')
        ->assertSeeText('Tambah Properti Baru');
});
