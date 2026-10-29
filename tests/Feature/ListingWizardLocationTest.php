<?php

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

function listingLocationUser(): User
{
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::firstOrCreate([
        'name' => 'properties.create',
        'guard_name' => 'web',
    ]));

    return $user;
}

it('renders the cascading location fields and restores saved selections', function () {
    $this->actingAs(User::factory()->create())
        ->withSession([
            'listing_wizard.step_1' => [
                'province' => 'Banten',
                'province_code' => '36',
                'city' => 'Kota Tangerang Selatan',
                'city_code' => '36.74',
                'district' => 'Pondok Aren',
                'district_code' => '36.74.03',
            ],
        ])
        ->get(route('listing.create'))
        ->assertOk()
        ->assertSee('province-search')
        ->assertSee('city-search')
        ->assertSee('district-search')
        ->assertSee('Banten')
        ->assertSee('36.74.03');
});

it('returns wilayah data and caches province requests', function () {
    Http::preventStrayRequests();
    Http::fake([
        'wilayah.id/api/provinces.json' => Http::response([
            'data' => [['code' => '36', 'name' => 'Banten']],
        ]),
    ]);

    $this->actingAs(User::factory()->create())
        ->getJson(route('listing.regions.provinces'))
        ->assertOk()
        ->assertJsonPath('data.0.code', '36')
        ->assertJsonPath('data.0.name', 'Banten');

    $this->getJson(route('listing.regions.provinces'))->assertOk();

    Http::assertSentCount(1);
});

it('rejects a city code that does not belong to the selected province', function () {
    $response = $this->actingAs(listingLocationUser())
        ->postJson(route('listing.step-one'), [
            'property_type' => 'bangunan',
            'building_type' => 'rumah',
            'transaction_type' => 'dijual',
            'title' => 'Rumah dekat taman',
            'description' => 'Hunian nyaman dengan akses mudah.',
            'price' => 1250000000,
            'negotiable' => false,
            'land_area_sqm' => 120,
            'building_area_sqm' => 90,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'floors' => 1,
            'condition' => 'siap_huni',
            'certificate_type' => 'SHM',
            'province' => 'Banten',
            'province_code' => '36',
            'city' => 'Kota Administrasi Jakarta Selatan',
            'city_code' => '31.74',
            'district' => 'Cilandak',
            'district_code' => '31.74.06',
            'address' => 'Jl. Contoh No. 1',
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['city_code']);
});

it('accepts matching hierarchical region codes in step one', function () {
    $this->actingAs(listingLocationUser())
        ->postJson(route('listing.step-one'), [
            'property_type' => 'tanah',
            'land_type' => 'kavling',
            'transaction_type' => 'dijual',
            'title' => 'Tanah kavling di Pondok Aren',
            'description' => 'Tanah siap bangun di lokasi strategis.',
            'price' => 750000000,
            'negotiable' => false,
            'land_area_sqm' => 500,
            'land_contour' => 'datar',
            'road_access' => 'jalan_lingkungan',
            'certificate_type' => 'SHM',
            'province' => 'Banten',
            'province_code' => '36',
            'city' => 'Kota Tangerang Selatan',
            'city_code' => '36.74',
            'district' => 'Pondok Aren',
            'district_code' => '36.74.03',
            'address' => 'Jl. Contoh No. 1',
        ])
        ->assertOk()
        ->assertSessionHas('listing_wizard.step_1.province_code', '36')
        ->assertSessionHas('listing_wizard.step_1.city_code', '36.74')
        ->assertSessionHas('listing_wizard.step_1.district_code', '36.74.03');
});

it('prevents a role without listing permission from saving listing details', function () {
    $this->actingAs(User::factory()->create())
        ->postJson(route('listing.step-one'), [])
        ->assertForbidden();

    expect(session('listing_wizard.step_1'))->toBeNull();
});

it('persists selected region codes with the existing region names', function () {
    $seller = listingLocationUser();

    $stepOne = [
        'property_type' => 'bangunan',
        'building_type' => 'rumah',
        'transaction_type' => 'dijual',
        'title' => 'Rumah di Tangerang Selatan',
        'description' => 'Hunian nyaman dengan akses mudah.',
        'price' => 1250000000,
        'negotiable' => false,
        'land_area_sqm' => 120,
        'building_area_sqm' => 90,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'floors' => 1,
        'condition' => 'siap_huni',
        'certificate_type' => 'SHM',
        'province' => 'Banten',
        'province_code' => '36',
        'city' => 'Kota Tangerang Selatan',
        'city_code' => '36.74',
        'district' => 'Pondok Aren',
        'district_code' => '36.74.03',
        'address' => 'Jl. Contoh No. 1',
    ];

    $this->actingAs($seller)
        ->withSession([
            'listing_wizard.step_1' => $stepOne,
            'listing_wizard.step_2' => ['photos' => [], 'certificate' => null],
        ])
        ->postJson(route('listing.submit'))
        ->assertOk();

    $property = Property::query()->firstOrFail();

    expect($property->province)->toBe('Banten')
        ->and($property->province_code)->toBe('36')
        ->and($property->city)->toBe('Kota Tangerang Selatan')
        ->and($property->city_code)->toBe('36.74')
        ->and($property->district)->toBe('Pondok Aren')
        ->and($property->district_code)->toBe('36.74.03');
});

it('accepts a rent period when the transaction type is disewakan', function () {
    $this->actingAs(listingLocationUser())
        ->postJson(route('listing.step-one'), [
            'property_type' => 'bangunan',
            'building_type' => 'rumah',
            'transaction_type' => 'disewakan',
            'rent_period' => 'bulan',
            'title' => 'Rumah disewakan di BSD',
            'description' => 'Hunian nyaman siap sewa.',
            'price' => 15000000,
            'negotiable' => false,
            'land_area_sqm' => 120,
            'building_area_sqm' => 90,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'floors' => 1,
            'condition' => 'siap_huni',
            'certificate_type' => 'SHM',
            'province' => 'Banten',
            'province_code' => '36',
            'city' => 'Kota Tangerang Selatan',
            'city_code' => '36.74',
            'district' => 'Pondok Aren',
            'district_code' => '36.74.03',
            'address' => 'Jl. Contoh No. 1',
        ])
        ->assertOk();
});
