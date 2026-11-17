<?php

use App\Models\Property;
use App\Models\PropertyDraft;
use App\Models\PropertyPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

function listingDraftSeller(): User
{
    $seller = User::factory()->create();
    $seller->givePermissionTo(Permission::firstOrCreate([
        'name' => 'properties.create',
        'guard_name' => 'web',
    ]));

    return $seller;
}

it('autosaves partial listing data and restores it when the seller reopens the draft', function () {
    $seller = listingDraftSeller();

    $response = $this->actingAs($seller)
        ->postJson(route('listing.drafts.store'), [
            'current_step' => 1,
            'data' => [
                'property_type' => 'bangunan',
                'title' => 'Rumah yang belum selesai',
            ],
        ])
        ->assertOk()
        ->assertJsonPath('success', true);

    $draft = PropertyDraft::query()->where('uuid', $response->json('draft_uuid'))->firstOrFail();
    expect($draft->payload)->toMatchArray([
        'property_type' => 'bangunan',
        'title' => 'Rumah yang belum selesai',
    ]);

    $this->postJson(route('listing.drafts.store'), [
        'draft_uuid' => $draft->uuid,
        'current_step' => 1,
        'data' => [
            'property_type' => 'bangunan',
            'title' => 'Rumah yang hampir selesai',
        ],
    ])->assertOk();

    $this->assertDatabaseCount('property_drafts', 1);
    $this->get(route('listing.drafts.edit', ['draft' => $draft->uuid]))
        ->assertOk()
        ->assertViewHas('initialForm', fn (array $form): bool => $form['title'] === 'Rumah yang hampir selesai')
        ->assertViewHas('initialDraftUuid', $draft->uuid);
});

it('does not persist unexpected draft fields', function () {
    $this->actingAs(listingDraftSeller())
        ->postJson(route('listing.drafts.store'), [
            'current_step' => 1,
            'data' => [
                'title' => 'Draf',
                'seller_id' => 999,
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['data']);

    $this->assertDatabaseCount('property_drafts', 0);
});

it('keeps drafts private to their seller and lists them with a resume link', function () {
    $seller = listingDraftSeller();
    $otherSeller = listingDraftSeller();
    $draft = PropertyDraft::factory()->for($seller, 'seller')->create([
        'payload' => [
            'title' => 'Draf Rumah di Bandung',
            'city' => 'Bandung',
        ],
    ]);

    $this->actingAs($otherSeller)
        ->get(route('listing.drafts.edit', ['draft' => $draft->uuid]))
        ->assertNotFound();

    $this->postJson(route('listing.drafts.store'), [
        'draft_uuid' => $draft->uuid,
        'current_step' => 1,
        'data' => ['title' => 'Diubah orang lain'],
    ])->assertNotFound();

    $this->actingAs($seller)
        ->get(route('seller.my-properties'))
        ->assertOk()
        ->assertSeeText('Draf Rumah di Bandung')
        ->assertSee(route('listing.drafts.edit', ['draft' => $draft->uuid]), false);

    $this->actingAs($otherSeller)
        ->get(route('seller.my-properties'))
        ->assertDontSeeText('Draf Rumah di Bandung');
});

it('returns 422 upload errors when a seller submits a draft without required files', function () {
    $seller = listingDraftSeller();
    $draft = PropertyDraft::factory()->for($seller, 'seller')->create([
        'payload' => ['title' => 'Draf tanpa unggahan'],
        'uploads' => [],
    ]);

    $this->actingAs($seller)
        ->postJson(route('listing.submit'), ['draft_uuid' => $draft->uuid])
        ->assertUnprocessable()
        ->assertJsonPath('errors.photos.0', 'Minimal 1 foto properti wajib diunggah.')
        ->assertJsonPath('errors.certificate_document.0', 'Sertifikat properti wajib diunggah.');

    $this->assertDatabaseCount('properties', 0);
    $this->assertDatabaseCount('property_drafts', 1);
});

it('turns a completed draft into a submitted property and transfers its saved files', function () {
    Storage::fake('public');
    Storage::fake('local');
    $seller = listingDraftSeller();
    $draftUuid = (string) Str::uuid();
    $photoPath = "property-drafts/{$draftUuid}/photos/house.jpg";
    $certificatePath = "property-drafts/{$draftUuid}/certificates/certificate.png";
    Storage::disk('public')->put($photoPath, 'processed photo');
    Storage::disk('local')->put($certificatePath, 'certificate');

    $draft = PropertyDraft::factory()->for($seller, 'seller')->create([
        'uuid' => $draftUuid,
        'payload' => [
            'property_type' => 'bangunan',
            'building_type' => 'rumah',
            'transaction_type' => 'dijual',
            'title' => 'Rumah draf siap dikirim',
            'description' => 'Deskripsi rumah yang sudah dilengkapi.',
            'price' => 1250000000,
            'negotiable' => false,
            'land_area_sqm' => 120,
            'building_area_sqm' => 90,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'floors' => 1,
            'condition' => 'siap_huni',
            'certificate_type' => 'SHM',
            'province' => 'Jawa Barat',
            'province_code' => '32',
            'city' => 'Bandung',
            'city_code' => '32.73',
            'district' => 'Coblong',
            'district_code' => '32.73.01',
            'address' => 'Jl. Merdeka No. 1',
        ],
        'uploads' => [
            'photos' => [['path' => $photoPath, 'order' => 0, 'name' => 'house.jpg']],
            'certificates' => [['path' => $certificatePath, 'type' => 'SHM', 'order' => 0, 'name' => 'certificate.png']],
            'imb' => null,
            'pbb' => null,
        ],
        'current_step' => 3,
    ]);

    $this->actingAs($seller)
        ->postJson(route('listing.submit'), ['draft_uuid' => $draft->uuid])
        ->assertOk()
        ->assertJsonPath('success', true);

    $property = Property::query()->where('title', 'Rumah draf siap dikirim')->firstOrFail();
    $photo = PropertyPhoto::query()->where('property_id', $property->id)->where('type', 'photo')->firstOrFail();
    $certificate = PropertyPhoto::query()->where('property_id', $property->id)->where('type', 'certificate')->firstOrFail();

    expect($property->status)->toBe('pending_verification')
        ->and(PropertyDraft::query()->whereKey($draft->id)->exists())->toBeFalse()
        ->and($photo->file_path)->toBe("properties/{$property->id}/photos/house.jpg")
        ->and($certificate->file_path)->toBe("properties/{$property->id}/certificates/certificate.png");
    Storage::disk('public')->assertExists($photo->file_path);
    Storage::disk('local')->assertExists($certificate->file_path);
});
