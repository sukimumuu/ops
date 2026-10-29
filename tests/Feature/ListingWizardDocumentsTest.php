<?php

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/**
 * Minimal but complete Step 1 payload for listing submission.
 *
 * @return array<string, mixed>
 */
function listingStepOnePayload(): array
{
    return [
        'property_type' => 'bangunan',
        'building_type' => 'rumah',
        'transaction_type' => 'dijual',
        'title' => 'Rumah dengan dokumen legal',
        'description' => 'Deskripsi properti untuk pengujian dokumen.',
        'price' => 1000000000,
        'land_area_sqm' => 120,
        'building_area_sqm' => 90,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'floors' => 1,
        'condition' => 'siap_huni',
        'province' => 'Jawa Barat',
        'province_code' => '32',
        'city' => 'Bandung',
        'city_code' => '32.73',
        'district' => 'Coblong',
        'district_code' => '32.73.02',
        'address' => 'Jl. Merdeka No. 1',
    ];
}

it('accepts step one without a certificate type', function () {
    $this->actingAs(User::factory()->create())
        ->postJson(route('listing.step-one'), listingStepOnePayload())
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertSessionMissing('listing_wizard.step_1.certificate_type');
});

it('stores multiple certificates and optional documents in private staging storage', function () {
    Storage::fake('local');
    Storage::fake('public');

    $this->actingAs(User::factory()->create())
        ->postJson(route('listing.step-two'), [
            'photos' => [UploadedFile::fake()->image('house.jpg', 100, 100)],
            'jenis_sertifikat' => 'SHM',
            'certificate_document' => UploadedFile::fake()->create('sertifikat.pdf', 500, 'application/pdf'),
            'additional_certificates' => [
                [
                    'jenis_sertifikat' => 'AJB',
                    'certificate_document' => UploadedFile::fake()->image('ajb.jpg', 50, 50),
                ],
            ],
            'imb_document' => UploadedFile::fake()->create('imb.pdf', 500, 'application/pdf'),
            'pbb_document' => UploadedFile::fake()->image('pbb.png', 50, 50),
        ])
        ->assertOk()
        ->assertJsonPath('success', true);

    $stepTwo = session('listing_wizard.step_2');

    expect($stepTwo['certificates'])->toHaveCount(2)
        ->and($stepTwo['certificates'][0]['type'])->toBe('SHM')
        ->and($stepTwo['certificates'][1]['type'])->toBe('AJB')
        ->and($stepTwo['imb'])->not->toBeNull()
        ->and($stepTwo['pbb'])->not->toBeNull();

    Storage::disk('local')->assertExists($stepTwo['certificates'][0]['path']);
    Storage::disk('local')->assertExists($stepTwo['certificates'][1]['path']);
    Storage::disk('local')->assertExists($stepTwo['imb']);
    Storage::disk('local')->assertExists($stepTwo['pbb']);
});

it('allows the optional supporting documents to be omitted', function () {
    Storage::fake('local');
    Storage::fake('public');

    $this->actingAs(User::factory()->create())
        ->postJson(route('listing.step-two'), [
            'photos' => [UploadedFile::fake()->image('house.jpg', 100, 100)],
            'jenis_sertifikat' => 'SHGB',
            'certificate_document' => UploadedFile::fake()->image('sertifikat.png', 50, 50),
        ])
        ->assertOk();

    expect(session('listing_wizard.step_2.imb'))->toBeNull()
        ->and(session('listing_wizard.step_2.pbb'))->toBeNull();
});

it('rejects unsupported and oversized supporting documents', function () {
    Storage::fake('local');
    Storage::fake('public');

    $this->actingAs(User::factory()->create())
        ->postJson(route('listing.step-two'), [
            'photos' => [UploadedFile::fake()->image('house.jpg', 100, 100)],
            'jenis_sertifikat' => 'SHM',
            'certificate_document' => UploadedFile::fake()->image('sertifikat.png', 50, 50),
            'imb_document' => UploadedFile::fake()->create('setup.exe', 100, 'application/x-msdownload'),
        ])
        ->assertJsonValidationErrors(['imb_document']);

    $this->postJson(route('listing.step-two'), [
        'photos' => [UploadedFile::fake()->image('house.jpg', 100, 100)],
        'jenis_sertifikat' => 'SHM',
        'certificate_document' => UploadedFile::fake()->image('sertifikat.png', 50, 50),
        'pbb_document' => UploadedFile::fake()->create('pbb.pdf', 10241, 'application/pdf'),
    ])
        ->assertJsonValidationErrors(['pbb_document']);
});

it('associates secured certificates and supporting documents with the submitted property', function () {
    Storage::fake('local');
    Storage::fake('public');

    $this->actingAs(User::factory()->create())
        ->withSession(['listing_wizard.step_1' => listingStepOnePayload()])
        ->postJson(route('listing.step-two'), [
            'photos' => [UploadedFile::fake()->image('house.jpg', 100, 100)],
            'jenis_sertifikat' => 'SHM',
            'certificate_document' => UploadedFile::fake()->create('sertifikat.pdf', 500, 'application/pdf'),
            'imb_document' => UploadedFile::fake()->create('imb.pdf', 500, 'application/pdf'),
            'pbb_document' => UploadedFile::fake()->create('pbb.pdf', 500, 'application/pdf'),
        ])
        ->assertOk();

    $this->postJson(route('listing.submit'))
        ->assertOk()
        ->assertJsonPath('success', true);

    $property = Property::query()->firstOrFail();
    $certificate = $property->certificate()->firstOrFail();

    expect($property->certificate_type)->toBe('SHM')
        ->and($certificate->certificate_type)->toBe('SHM')
        ->and($property->supportingDocuments()->pluck('type')->all())
        ->toEqualCanonicalizing(['imb', 'pbb']);

    Storage::disk('local')->assertExists($certificate->file_path);

    $property->supportingDocuments->each(function ($document): void {
        expect($document->file_path)->toStartWith("properties/{$document->property_id}/documents/");
        Storage::disk('local')->assertExists($document->file_path);
    });
});
