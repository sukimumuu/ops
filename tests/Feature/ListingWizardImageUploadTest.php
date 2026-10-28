<?php

use App\Models\Property;
use App\Models\PropertyPhoto;
use App\Models\User;
use App\Services\ImageWatermarkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Mockery;
use RuntimeException;

uses(RefreshDatabase::class);

it('saves a resized property photo with an embedded watermark to the public disk', function () {
    Storage::fake('local');
    Storage::fake('public');
    $this->actingAs(User::factory()->create())
        ->withSession([
            'listing_wizard.step_1' => [
                'property_type' => 'bangunan',
                'building_type' => 'rumah',
                'transaction_type' => 'dijual',
                'title' => 'Rumah dengan watermark',
                'description' => 'Deskripsi properti untuk pengujian.',
                'price' => 1000000000,
                'land_area_sqm' => 120,
                'building_area_sqm' => 90,
                'certificate_type' => 'SHM',
                'province' => 'Jawa Barat',
                'city' => 'Bandung',
                'district' => 'Coblong',
                'address' => 'Jl. Merdeka No. 1',
            ],
        ]);

    $response = $this->postJson(route('listing.step-two'), [
        'photos' => [UploadedFile::fake()->image('house.jpg', 3000, 1500)->size(2048)],
        'jenis_sertifikat' => 'SHM',
        'certificate_document' => UploadedFile::fake()->image('certificate.png', 100, 100),
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true);

    $photos = Storage::disk('public')->allFiles('properties/tmp');
    expect($photos)->toHaveCount(1);

    $path = $photos[0];
    $imagePath = Storage::disk('public')->path($path);
    $dimensions = getimagesize($imagePath);
    expect($dimensions[0])->toBe(2560);
    expect(session('listing_wizard.step_2.photos.0.path'))->toBe($path);

    $image = (new ImageManager(new Driver))->decodePath($imagePath);
    $watermarkIsVisible = false;

    for ($x = (int) ($image->width() * 0.3); $x < $image->width() * 0.7; $x += 10) {
        for ($y = (int) ($image->height() * 0.45); $y < $image->height() * 0.55; $y += 10) {
            $pixel = imagecolorat($image->core()->native(), $x, $y);
            $red = ($pixel >> 16) & 0xFF;
            $green = ($pixel >> 8) & 0xFF;
            $blue = $pixel & 0xFF;

            if ($red > 50 && $green > 50 && $blue > 50) {
                $watermarkIsVisible = true;
                break 2;
            }
        }
    }

    expect($watermarkIsVisible)->toBeTrue();

    $this->postJson(route('listing.submit'))
        ->assertOk()
        ->assertJsonPath('success', true);

    $property = Property::query()->where('title', 'Rumah dengan watermark')->firstOrFail();
    $photo = PropertyPhoto::query()->where('property_id', $property->id)->firstOrFail();
    expect($photo->file_path)->toBe("properties/{$property->id}/photos/".basename($path));
    Storage::disk('public')->assertExists($photo->file_path);
    Storage::disk('public')->assertMissing($path);
});

it('rejects an uploaded image larger than 2MB', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());

    $this->postJson(route('listing.step-two'), [
        'photos' => [UploadedFile::fake()->image('house.jpg', 100, 100)->size(2049)],
        'jenis_sertifikat' => 'SHM',
        'certificate_document' => UploadedFile::fake()->image('certificate.png', 100, 100),
    ])
        ->assertJsonValidationErrors(['photos.0'])
        ->assertJsonFragment(['Ukuran foto maksimal 2MB.']);
});

it('rejects executable content disguised as an image', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());

    $this->postJson(route('listing.step-two'), [
        'photos' => [UploadedFile::fake()->createWithContent('payload.jpg', '<?php echo "unsafe"; ?>')],
        'jenis_sertifikat' => 'SHM',
        'certificate_document' => UploadedFile::fake()->image('certificate.png', 100, 100),
    ])
        ->assertJsonValidationErrors(['photos.0']);
});

it('returns a friendly error and removes staged photos when image processing fails', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());

    $existingPath = 'properties/tmp/session/photos/processed.jpg';
    Storage::disk('public')->put($existingPath, 'processed');

    $imageService = Mockery::mock(ImageWatermarkService::class);
    $imageService->shouldReceive('process')
        ->once()
        ->andReturn($existingPath);
    $imageService->shouldReceive('process')
        ->once()
        ->andThrow(new RuntimeException('Internal image processing details'));
    $this->instance(ImageWatermarkService::class, $imageService);

    $this->postJson(route('listing.step-two'), [
        'photos' => [
            UploadedFile::fake()->image('house-one.jpg', 100, 100),
            UploadedFile::fake()->image('house-two.jpg', 100, 100),
        ],
        'jenis_sertifikat' => 'SHM',
        'certificate_document' => UploadedFile::fake()->image('certificate.png', 100, 100),
    ])
        ->assertStatus(500)
        ->assertJsonPath('message', 'Foto gagal diproses. Silakan coba unggah kembali.')
        ->assertJsonMissing(['message' => 'Internal image processing details']);

    Storage::disk('public')->assertMissing($existingPath);
});

it('rolls back property creation and restores staged photos when a stored photo cannot be moved', function () {
    Storage::fake('local');
    Storage::fake('public');
    $this->actingAs(User::factory()->create())
        ->withSession([
            'listing_wizard.step_1' => [
                'property_type' => 'bangunan',
                'building_type' => 'rumah',
                'transaction_type' => 'dijual',
                'title' => 'Rumah dengan foto staging',
                'description' => 'Deskripsi properti untuk pengujian.',
                'price' => 1000000000,
                'land_area_sqm' => 120,
                'building_area_sqm' => 90,
                'certificate_type' => 'SHM',
                'province' => 'Jawa Barat',
                'city' => 'Bandung',
                'district' => 'Coblong',
                'address' => 'Jl. Merdeka No. 1',
            ],
            'listing_wizard.step_2' => [
                'photos' => [
                    ['path' => 'properties/tmp/session/photos/first.jpg', 'order' => 0],
                    ['path' => 'properties/tmp/session/photos/missing.jpg', 'order' => 1],
                ],
            ],
        ]);

    Storage::disk('public')->put('properties/tmp/session/photos/first.jpg', 'processed photo');

    $this->postJson(route('listing.submit'))
        ->assertStatus(500)
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'Listing gagal disimpan. Silakan coba lagi.');

    $this->assertDatabaseCount('properties', 0);
    Storage::disk('public')->assertExists('properties/tmp/session/photos/first.jpg');
    expect(Storage::disk('public')->allFiles('properties'))->toBe([
        'properties/tmp/session/photos/first.jpg',
    ]);
});
