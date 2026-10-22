<?php

use App\Models\Property;
use App\Models\PropertyPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function createSavedPropertyForTest(User $seller, array $attributes = []): Property
{
    return Property::query()->create(array_merge([
        'uuid' => (string) Str::uuid(),
        'seller_id' => $seller->id,
        'property_type' => 'bangunan',
        'type' => 'rumah',
        'category' => 'sale',
        'status' => 'published',
        'title' => 'Rumah Tersimpan',
        'description' => 'Properti untuk pengujian halaman properti tersimpan.',
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

it('shows the empty state when the user has no saved properties', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('buyer.saved-properties'))
        ->assertOk()
        ->assertSee('Belum ada properti yang disimpan')
        ->assertSee('Cari Properti Sekarang')
        ->assertSee(route('properties'), false)
        ->assertDontSee('Anda memiliki')
        ->assertDontSee('Jelajahi properti lainnya');
});

it('renders the authenticated user saved properties with real data', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $seller = User::factory()->create();
    $property = createSavedPropertyForTest($seller, [
        'title' => 'Rumah Dago Asri',
        'category' => 'rent',
        'rent_period' => 'bulan',
        'price' => 7500000,
        'city' => 'Bandung',
        'district' => 'Coblong',
    ]);

    $path = "properties/{$property->id}/photos/cover.jpg";
    Storage::disk('public')->put($path, 'cover');
    PropertyPhoto::query()->create([
        'property_id' => $property->id,
        'file_path' => $path,
        'type' => 'photo',
        'sort_order' => 0,
    ]);

    $user->savedProperties()->attach($property->id);

    $this->actingAs($user)
        ->get(route('buyer.saved-properties'))
        ->assertOk()
        ->assertSee('Properti Tersimpan')
        ->assertSee('Anda memiliki 1 properti yang disimpan.')
        ->assertSee('Rumah Dago Asri')
        ->assertSee('Rp 7.500.000')
        ->assertSee('/ bulan')
        ->assertDontSee('/ tahun')
        ->assertSee('Disewakan')
        ->assertSee('Coblong, Bandung')
        ->assertSee('3 KT')
        ->assertSee('2 KM')
        ->assertSee('90 m²')
        ->assertSee('Jelajahi properti lainnya')
        ->assertSee(Storage::disk('public')->url($path), false);
});

it('sorts saved properties by the selected order', function () {
    $user = User::factory()->create();
    $seller = User::factory()->create();
    $older = createSavedPropertyForTest($seller, ['title' => 'Properti Lama']);
    $newer = createSavedPropertyForTest($seller, ['title' => 'Properti Baru']);

    DB::table('saved_properties')->insert([
        [
            'user_id' => $user->id,
            'property_id' => $older->id,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ],
        [
            'user_id' => $user->id,
            'property_id' => $newer->id,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $this->actingAs($user)
        ->get(route('buyer.saved-properties'))
        ->assertOk()
        ->assertSeeInOrder(['Properti Baru', 'Properti Lama']);

    $this->actingAs($user)
        ->get(route('buyer.saved-properties', ['sort' => 'oldest']))
        ->assertOk()
        ->assertSeeInOrder(['Properti Lama', 'Properti Baru'])
        ->assertSee('value="oldest" selected', false);
});

it('falls back to a safe sort order when an invalid value is supplied', function () {
    $user = User::factory()->create();
    $seller = User::factory()->create();
    $property = createSavedPropertyForTest($seller);

    $user->savedProperties()->attach($property->id);

    $this->actingAs($user)
        ->get(route('buyer.saved-properties', ['sort' => 'invalid']))
        ->assertOk()
        ->assertSee('value="newest" selected', false);
});

it('removes a property from the authenticated user saved collection', function () {
    $user = User::factory()->create();
    $seller = User::factory()->create();
    $property = createSavedPropertyForTest($seller);

    $user->savedProperties()->attach($property->id);

    $this->actingAs($user)
        ->from(route('buyer.saved-properties'))
        ->delete(route('buyer.saved-properties.destroy', ['uuid' => $property->uuid]))
        ->assertRedirect(route('buyer.saved-properties'))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('saved_properties', [
        'user_id' => $user->id,
        'property_id' => $property->id,
    ]);
});

it('only removes the authenticated user saved property', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $seller = User::factory()->create();
    $property = createSavedPropertyForTest($seller);

    $otherUser->savedProperties()->attach($property->id);

    $this->actingAs($user)
        ->delete(route('buyer.saved-properties.destroy', ['uuid' => $property->uuid]))
        ->assertRedirect();

    $this->assertDatabaseHas('saved_properties', [
        'user_id' => $otherUser->id,
        'property_id' => $property->id,
    ]);
});

it('stores a saved property through the save route', function () {
    $user = User::factory()->create();
    $seller = User::factory()->create();
    $property = createSavedPropertyForTest($seller);

    $this->actingAs($user)
        ->from(route('properties'))
        ->post(route('buyer.saved-properties.store', ['uuid' => $property->uuid]))
        ->assertRedirect(route('properties'));

    $this->assertDatabaseHas('saved_properties', [
        'user_id' => $user->id,
        'property_id' => $property->id,
    ]);
});

it('reflects the saved state on the property detail page', function () {
    $user = User::factory()->create();
    $seller = User::factory()->create();
    $property = createSavedPropertyForTest($seller);

    $this->actingAs($user)
        ->get(route('property-details', ['uuid' => $property->uuid]))
        ->assertOk()
        ->assertSee('aria-pressed="false"', false);

    $user->savedProperties()->attach($property->id);

    $this->actingAs($user)
        ->get(route('property-details', ['uuid' => $property->uuid]))
        ->assertOk()
        ->assertSee('aria-pressed="true"', false);
});

it('toggles a saved property through the JSON endpoint', function () {
    $user = User::factory()->create();
    $seller = User::factory()->create();
    $property = createSavedPropertyForTest($seller);

    $this->actingAs($user)
        ->postJson(route('buyer.saved-properties.toggle'), ['property_id' => $property->id])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'saved' => true,
            'property_id' => $property->id,
        ]);

    $this->assertDatabaseHas('saved_properties', [
        'user_id' => $user->id,
        'property_id' => $property->id,
    ]);
    expect($user->savedProperties()->count())->toBe(1);

    $this->actingAs($user)
        ->postJson(route('buyer.saved-properties.toggle'), ['property_id' => $property->id])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'saved' => false,
            'property_id' => $property->id,
        ]);

    $this->assertDatabaseMissing('saved_properties', [
        'user_id' => $user->id,
        'property_id' => $property->id,
    ]);
});

it('does not save unpublished properties but still allows existing saves to be removed', function () {
    $user = User::factory()->create();
    $seller = User::factory()->create();
    $property = createSavedPropertyForTest($seller, ['status' => 'archived']);

    $this->actingAs($user)
        ->postJson(route('buyer.saved-properties.toggle'), ['property_id' => $property->id])
        ->assertUnprocessable();

    $this->assertDatabaseMissing('saved_properties', [
        'user_id' => $user->id,
        'property_id' => $property->id,
    ]);

    $user->savedProperties()->attach($property->id);

    $this->actingAs($user)
        ->postJson(route('buyer.saved-properties.toggle'), ['property_id' => $property->id])
        ->assertOk()
        ->assertJson(['saved' => false]);
});

it('returns a JSON unauthorized response for guest toggle requests', function () {
    $seller = User::factory()->create();
    $property = createSavedPropertyForTest($seller);

    $this->postJson(route('buyer.saved-properties.toggle'), ['property_id' => $property->id])
        ->assertUnauthorized();
});

it('renders the correct initial heart state on property cards', function () {
    $user = User::factory()->create();
    $seller = User::factory()->create();
    $property = createSavedPropertyForTest($seller, ['title' => 'Rumah Kartu Favorit']);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('data-property-id="'.$property->id.'"', false)
        ->assertSee('data-saved="false"', false)
        ->assertSee(route('buyer.saved-properties.toggle'), false);

    $user->savedProperties()->attach($property->id);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('data-property-id="'.$property->id.'"', false)
        ->assertSee('data-saved="true"', false);

    $this->actingAs($user)
        ->get(route('properties'))
        ->assertOk()
        ->assertSee('data-property-id="'.$property->id.'"', false)
        ->assertSee('data-saved="true"', false);
});

it('requires authentication to view or change saved properties', function () {
    $seller = User::factory()->create();
    $property = createSavedPropertyForTest($seller);

    $this->get(route('buyer.saved-properties'))->assertRedirect(route('login'));

    $this->post(route('buyer.saved-properties.store', ['uuid' => $property->uuid]))
        ->assertRedirect(route('login'));
});
