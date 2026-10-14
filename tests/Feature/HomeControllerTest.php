<?php

use App\Models\Property;
use App\Models\PropertyPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('renders public property photos on the landing, catalog, and detail pages', function () {
    Storage::fake('public');

    $seller = User::factory()->create();
    $uuid = (string) Str::uuid();
    $property = Property::query()->create([
        'uuid' => $uuid,
        'seller_id' => $seller->id,
        'property_type' => 'bangunan',
        'type' => 'rumah',
        'category' => 'sale',
        'status' => 'published',
        'title' => 'Rumah dengan foto publik',
        'description' => 'Foto properti telah diberi watermark.',
        'price' => 1250000000,
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
        'floors' => '1',
        'condition' => 'siap_huni',
        'certificate_type' => 'SHM',
    ]);
    $path = "properties/{$property->id}/photos/watermarked.jpg";
    Storage::disk('public')->put($path, 'watermarked image');
    PropertyPhoto::query()->create([
        'property_id' => $property->id,
        'file_path' => $path,
        'type' => 'photo',
        'sort_order' => 0,
    ]);
    $imageUrl = Storage::disk('public')->url($path);

    $this->get(route('home'))->assertSee($imageUrl, false);
    $this->get(route('properties'))->assertSee($imageUrl, false);
    $this->get(route('property-details', ['uuid' => $uuid]))->assertSee($imageUrl, false);
});

it('shows only approved properties on the landing page', function () {
    $seller = User::factory()->create();

    $createProperty = function (string $title, string $status) use ($seller): Property {
        return Property::query()->create([
            'uuid' => (string) Str::uuid(),
            'seller_id' => $seller->id,
            'property_type' => 'bangunan',
            'type' => 'rumah',
            'category' => 'sale',
            'status' => $status,
            'title' => $title,
            'description' => 'Hunian nyaman di lokasi strategis.',
            'price' => 1250000000,
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
            'floors' => '1',
            'condition' => 'siap_huni',
            'certificate_type' => 'SHM',
        ]);
    };

    $createProperty('Rumah Terverifikasi', 'published');
    $createProperty('Rumah Menunggu Verifikasi', 'pending_verification');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Rumah Terverifikasi')
        ->assertSee('Coblong, Bandung')
        ->assertDontSee('Rumah Menunggu Verifikasi');
});

it('shows an empty state when no properties are approved', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Belum ada properti tersedia saat ini.');
});

it('paginates only approved properties and preserves query parameters', function () {
    $seller = User::factory()->create();

    $createProperty = function (string $title, string $status) use ($seller): Property {
        return Property::query()->create([
            'uuid' => (string) Str::uuid(),
            'seller_id' => $seller->id,
            'property_type' => 'bangunan',
            'type' => 'rumah',
            'category' => 'sale',
            'status' => $status,
            'title' => $title,
            'description' => 'Hunian nyaman di lokasi strategis.',
            'price' => 1250000000,
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
            'floors' => '1',
            'condition' => 'siap_huni',
            'certificate_type' => 'SHM',
        ]);
    };

    foreach (range(1, 7) as $number) {
        $createProperty("Properti Disetujui {$number}", 'published');
    }

    $createProperty('Properti Belum Disetujui', 'pending_verification');

    $firstPage = $this->get(route('properties', ['search' => 'Jakarta', 'type' => 'rumah']))
        ->assertOk()
        ->assertSee('Properti Disetujui 7')
        ->assertSee('Properti Disetujui 2')
        ->assertDontSee('Properti Disetujui 1')
        ->assertDontSee('Properti Belum Disetujui');

    expect(html_entity_decode($firstPage->getContent()))
        ->toContain(route('properties', ['search' => 'Jakarta', 'type' => 'rumah', 'page' => 2]));

    $this->get(route('properties', ['search' => 'Jakarta', 'type' => 'rumah', 'page' => 2]))
        ->assertOk()
        ->assertSee('Properti Disetujui 1')
        ->assertDontSee('Properti Disetujui 2')
        ->assertDontSee('Properti Belum Disetujui');
});

it('shows an empty state when the property catalog has no approved listings', function () {
    $this->get(route('properties'))
        ->assertOk()
        ->assertSee('Belum ada properti terverifikasi saat ini.');
});

it('renders an approved property by its UUID', function () {
    $seller = User::factory()->create(['name' => 'Pemilik Detail']);
    $uuid = (string) Str::uuid();
    Property::query()->create([
        'uuid' => $uuid,
        'seller_id' => $seller->id,
        'property_type' => 'bangunan',
        'type' => 'rumah',
        'category' => 'sale',
        'status' => 'published',
        'title' => 'Rumah UUID Terverifikasi',
        'description' => 'Rumah dengan data detail dari database.',
        'price' => 1250000000,
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
        'floors' => '1',
        'condition' => 'siap_huni',
        'certificate_type' => 'SHM',
    ]);

    $this->get(route('property-details', ['uuid' => $uuid]))
        ->assertOk()
        ->assertSee('Rumah UUID Terverifikasi')
        ->assertSee('Pemilik Detail')
        ->assertSee('Coblong, Bandung');
});

it('returns 404 when a property detail UUID is malformed', function () {
    $this->get('/detail-properti/not-a-uuid')
        ->assertNotFound();
});

it('returns 404 when no property exists for a valid detail UUID', function () {
    $this->get(route('property-details', ['uuid' => (string) Str::uuid()]))
        ->assertNotFound();
});

it('returns 404 when a property detail UUID belongs to an unapproved property', function () {
    $seller = User::factory()->create();
    $uuid = (string) Str::uuid();
    Property::query()->create([
        'uuid' => $uuid,
        'seller_id' => $seller->id,
        'property_type' => 'bangunan',
        'type' => 'rumah',
        'category' => 'sale',
        'status' => 'pending_verification',
        'title' => 'Rumah Menunggu Persetujuan',
        'description' => 'Belum disetujui.',
        'price' => 1250000000,
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
        'floors' => '1',
        'condition' => 'siap_huni',
        'certificate_type' => 'SHM',
    ]);

    $this->get(route('property-details', ['uuid' => $uuid]))
        ->assertNotFound();
});
