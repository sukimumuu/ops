<?php

use App\Models\Property;
use App\Models\PropertyPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'Seller', 'guard_name' => 'web']);
});

function createReviewProperty(string $status = 'pending_verification'): Property
{
    $seller = User::factory()->create();

    return Property::query()->create([
        'uuid' => (string) Str::uuid(),
        'seller_id' => $seller->id,
        'property_type' => 'bangunan',
        'type' => 'rumah',
        'category' => 'sale',
        'status' => $status,
        'title' => 'Rumah untuk ditinjau',
        'description' => 'Deskripsi properti uji.',
        'price' => 1250000000,
        'negotiable' => false,
        'province' => 'Jawa Barat',
        'city' => 'Bandung',
        'district' => 'Coblong',
        'address' => 'Jl. Merdeka No. 1',
        'land_area_sqm' => 120,
        'building_area_sqm' => 90,
        'certificate_type' => 'SHM',
    ]);
}

it('allows an admin to approve a pending property', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $property = createReviewProperty();

    $this->actingAs($admin)
        ->from(route('admin.master-data-property'))
        ->post(route('property-review.status', $property), ['status' => 'published'])
        ->assertRedirect(route('admin.master-data-property'))
        ->assertSessionHas('status', 'Properti berhasil disetujui.');

    $this->assertDatabaseHas('properties', [
        'id' => $property->id,
        'status' => 'published',
    ]);
});

it('allows a superadmin to reject a pending property', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');
    $property = createReviewProperty();

    $this->actingAs($superadmin)
        ->from(route('superadmin.master-data-property'))
        ->post(route('property-review.status', $property), ['status' => 'rejected'])
        ->assertRedirect(route('superadmin.master-data-property'))
        ->assertSessionHas('status', 'Properti berhasil ditolak.');

    $this->assertDatabaseHas('properties', [
        'id' => $property->id,
        'status' => 'rejected',
    ]);
});

it('does not change a property that has already left pending review', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $property = createReviewProperty('published');

    $this->actingAs($admin)
        ->from(route('admin.master-data-property'))
        ->post(route('property-review.status', $property), ['status' => 'rejected'])
        ->assertRedirect(route('admin.master-data-property'))
        ->assertSessionHasErrors('status');

    $this->assertDatabaseHas('properties', [
        'id' => $property->id,
        'status' => 'published',
    ]);
});

it('rejects invalid requested status values without changing the property', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $property = createReviewProperty();

    $this->actingAs($admin)
        ->from(route('admin.master-data-property'))
        ->post(route('property-review.status', $property), ['status' => 'archived'])
        ->assertRedirect(route('admin.master-data-property'))
        ->assertSessionHasErrors('status');

    $this->assertDatabaseHas('properties', [
        'id' => $property->id,
        'status' => 'pending_verification',
    ]);
});

it('requires an authenticated administrator to change property status', function () {
    $property = createReviewProperty();

    $this->post(route('property-review.status', $property), ['status' => 'published'])
        ->assertRedirect(route('login'));

    $seller = User::factory()->create();
    $seller->assignRole('Seller');

    $this->actingAs($seller)
        ->post(route('property-review.status', $property), ['status' => 'published'])
        ->assertForbidden();

    foreach ([route('admin.master-data-property'), route('superadmin.master-data-property')] as $url) {
        $this->get($url)->assertForbidden();
    }

    $this->assertDatabaseHas('properties', [
        'id' => $property->id,
        'status' => 'pending_verification',
    ]);
});

it('serves private certificate files only to authorized reviewers', function () {
    Storage::fake('local');

    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $property = createReviewProperty();
    $certificate = PropertyPhoto::query()->create([
        'property_id' => $property->id,
        'file_path' => 'properties/'.$property->id.'/certificates/certificate.pdf',
        'type' => 'certificate',
        'sort_order' => 0,
    ]);
    Storage::disk('local')->put($certificate->file_path, '%PDF-1.4 private certificate');

    $this->actingAs($admin)
        ->get(route('property-review.file', $certificate))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertHeader('X-Content-Type-Options', 'nosniff');

    $seller = User::factory()->create();
    $seller->assignRole('Seller');

    $this->actingAs($seller)
        ->get(route('property-review.file', $certificate))
        ->assertForbidden();
});

it('returns not found when a private property file is missing', function () {
    Storage::fake('local');

    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $property = createReviewProperty();
    $photo = PropertyPhoto::query()->create([
        'property_id' => $property->id,
        'file_path' => 'properties/'.$property->id.'/photos/missing.jpg',
        'type' => 'photo',
        'sort_order' => 0,
    ]);

    $this->actingAs($admin)
        ->get(route('property-review.file', $photo))
        ->assertNotFound();
});

it('shows working private document links and fallbacks in both reviewer views', function () {
    Storage::fake('local');

    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $property = createReviewProperty();
    $photo = PropertyPhoto::query()->create([
        'property_id' => $property->id,
        'file_path' => 'properties/'.$property->id.'/photos/home.jpg',
        'type' => 'photo',
        'sort_order' => 0,
    ]);
    $certificate = PropertyPhoto::query()->create([
        'property_id' => $property->id,
        'file_path' => 'properties/'.$property->id.'/certificates/certificate.pdf',
        'type' => 'certificate',
        'sort_order' => 0,
    ]);
    Storage::disk('local')->put($photo->file_path, 'photo contents');
    Storage::disk('local')->put($certificate->file_path, '%PDF-1.4 private certificate');

    $this->actingAs($admin);

    foreach ([route('admin.master-data-property'), route('superadmin.master-data-property')] as $url) {
        $this->get($url)
            ->assertOk()
            ->assertSee(route('property-review.file', $photo), false)
            ->assertSee(route('property-review.file', $certificate), false)
            ->assertSee('target="_blank"', false)
            ->assertSee('rel="noopener noreferrer"', false);
    }
});

it('renders missing image and certificate placeholders instead of broken links', function () {
    Storage::fake('local');

    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $property = createReviewProperty();
    $photo = PropertyPhoto::query()->create([
        'property_id' => $property->id,
        'file_path' => 'properties/'.$property->id.'/photos/missing.jpg',
        'type' => 'photo',
        'sort_order' => 0,
    ]);
    PropertyPhoto::query()->create([
        'property_id' => $property->id,
        'file_path' => 'properties/'.$property->id.'/certificates/missing.pdf',
        'type' => 'certificate',
        'sort_order' => 0,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.master-data-property'))
        ->assertOk()
        ->assertSeeText('Foto tidak tersedia')
        ->assertSeeText('Sertifikat tidak tersedia')
        ->assertDontSee(route('property-review.file', $photo), false);
});
