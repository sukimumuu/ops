<?php

use App\Models\Appointment;
use App\Models\Property;
use App\Models\PropertyPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $reviewPermissions = [
        'properties.approve_listing',
        'properties.reject_listing',
        'documents.view_sensitive_pii',
        'appointments.view_all',
        'appointments.confirm_or_reject',
    ];

    foreach ($reviewPermissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web'])
        ->syncPermissions($reviewPermissions);
    Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web'])
        ->syncPermissions($reviewPermissions);
    Role::firstOrCreate(['name' => 'PPAT', 'guard_name' => 'web'])
        ->syncPermissions(['appointments.view_all']);
    Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
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

it('applies the selected approval permission to each review action', function () {
    $reviewerRole = Role::create(['name' => 'Listing approver', 'guard_name' => 'web']);
    $reviewerRole->givePermissionTo('properties.approve_listing');
    $reviewer = User::factory()->create();
    $reviewer->assignRole($reviewerRole);
    $property = createReviewProperty();

    $this->actingAs($reviewer)
        ->post(route('property-review.status', $property), ['status' => 'rejected'])
        ->assertForbidden();

    $this->assertDatabaseHas('properties', [
        'id' => $property->id,
        'status' => 'pending_verification',
    ]);

    $this->post(route('property-review.status', $property), ['status' => 'published'])
        ->assertRedirect();

    $this->assertDatabaseHas('properties', [
        'id' => $property->id,
        'status' => 'published',
    ]);
});

it('does not allow property access after review permissions are removed', function () {
    $adminRole = Role::findByName('Admin');
    $adminRole->syncPermissions(['documents.view_sensitive_pii']);
    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $this->actingAs($admin)
        ->get(route('admin.master-data-property'))
        ->assertForbidden();

    $this->get(route('superadmin.master-data-property'))
        ->assertForbidden();
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
    $seller->assignRole('User');

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
    $seller->assignRole('User');

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

it('serves watermarked public property photos through the reviewer file route', function () {
    Storage::fake('local');
    Storage::fake('public');

    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $property = createReviewProperty();
    $photo = PropertyPhoto::query()->create([
        'property_id' => $property->id,
        'file_path' => 'properties/'.$property->id.'/photos/watermarked.jpg',
        'type' => 'photo',
        'sort_order' => 0,
    ]);
    Storage::disk('public')->put($photo->file_path, 'watermarked image');

    $this->actingAs($admin)
        ->get(route('property-review.file', $photo))
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff');
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
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');

    foreach ([
        [$admin, route('admin.master-data-property')],
        [$superadmin, route('superadmin.master-data-property')],
    ] as [$reviewer, $url]) {
        $this->actingAs($reviewer);
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

it('returns PPAT search results in district priority with six users per page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $property = createReviewProperty();
    $localPpat = User::factory()->create([
        'name' => 'Local PPAT',
        'email' => 'local-ppat@example.test',
        'district' => ' coblong ',
    ]);
    $localPpat->assignRole('PPAT');

    $otherPpats = User::factory()->count(7)->sequence(fn ($sequence) => [
        'name' => 'Other PPAT '.($sequence->index + 1),
        'email' => 'other-ppat-'.($sequence->index + 1).'@example.test',
        'district' => 'Cicendo',
    ])->create();
    $otherPpats->each(fn (User $ppat) => $ppat->assignRole('PPAT'));

    $nonPpat = User::factory()->create([
        'name' => 'Coblong Contact',
        'district' => 'Coblong',
    ]);
    $nonPpat->assignRole('User');

    $this->actingAs($admin)
        ->getJson(route('property-review.ppats', $property))
        ->assertOk()
        ->assertJsonCount(6, 'data')
        ->assertJsonPath('data.0.id', $localPpat->id)
        ->assertJsonPath('current_page', 1)
        ->assertJsonPath('has_more', true)
        ->assertJsonMissing(['id' => $nonPpat->id]);

    $this->getJson(route('property-review.ppats', [$property, 'page' => 2]))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('current_page', 2)
        ->assertJsonPath('has_more', false);

    $this->getJson(route('property-review.ppats', [$property, 'search' => 'local-ppat@example.test']))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $localPpat->id)
        ->assertJsonPath('has_more', false);
});

it('assigns and replaces a PPAT for admins and superadmins without duplicate assignments', function () {
    $property = createReviewProperty();
    $firstPpat = User::factory()->create(['name' => 'First PPAT']);
    $firstPpat->assignRole('PPAT');
    $secondPpat = User::factory()->create(['name' => 'Second PPAT']);
    $secondPpat->assignRole('PPAT');

    foreach (['Admin', 'Superadmin'] as $role) {
        $reviewer = User::factory()->create();
        $reviewer->assignRole($role);

        $this->actingAs($reviewer)
            ->postJson(route('property-review.assign-ppat', $property), ['ppat_id' => $firstPpat->id])
            ->assertOk()
            ->assertJsonPath('assigned.id', $firstPpat->id)
            ->assertJsonPath('unchanged', false);

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'assigned_ppat_id' => $firstPpat->id,
        ]);
        $this->get($role === 'Admin' ? route('admin.master-data-property') : route('superadmin.master-data-property'))
            ->assertSee('First PPAT');
        $this->getJson(route('property-review.ppats', $property))
            ->assertOk()
            ->assertJsonMissing(['id' => $firstPpat->id]);

        $this->postJson(route('property-review.assign-ppat', $property), ['ppat_id' => $firstPpat->id])
            ->assertOk()
            ->assertJsonPath('unchanged', true);

        $this->postJson(route('property-review.assign-ppat', $property), ['ppat_id' => $secondPpat->id])
            ->assertOk()
            ->assertJsonPath('assigned.id', $secondPpat->id)
            ->assertJsonPath('unchanged', false);
    }

    $this->assertDatabaseHas('properties', [
        'id' => $property->id,
        'assigned_ppat_id' => $secondPpat->id,
    ]);
});

it('rejects non-PPAT assignment and prevents unauthorized users from assigning PPATs', function () {
    $property = createReviewProperty();
    $ppat = User::factory()->create();
    $ppat->assignRole('PPAT');
    $seller = User::factory()->create();
    $seller->assignRole('User');

    $this->postJson(route('property-review.assign-ppat', $property), ['ppat_id' => $ppat->id])
        ->assertUnauthorized();

    $this->actingAs($seller)
        ->postJson(route('property-review.assign-ppat', $property), ['ppat_id' => $ppat->id])
        ->assertForbidden();

    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $this->actingAs($admin)
        ->postJson(route('property-review.assign-ppat', $property), ['ppat_id' => $seller->id])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('ppat_id');

    $this->assertDatabaseHas('properties', [
        'id' => $property->id,
        'assigned_ppat_id' => null,
    ]);
});

it('shows only survey appointments for properties assigned to the signed-in PPAT', function () {
    $ppat = User::factory()->create();
    $ppat->assignRole('PPAT');
    $otherPpat = User::factory()->create();
    $otherPpat->assignRole('PPAT');
    $buyer = User::factory()->create(['name' => 'Assigned Survey Client']);
    $otherBuyer = User::factory()->create(['name' => 'Private Survey Client']);

    $assignedProperty = createReviewProperty();
    $assignedProperty->update(['assigned_ppat_id' => $ppat->id]);
    $privateProperty = createReviewProperty();
    $privateProperty->update(['assigned_ppat_id' => $otherPpat->id]);

    $assignedAppointment = Appointment::query()->create([
        'property_id' => $assignedProperty->id,
        'buyer_id' => $buyer->id,
        'seller_id' => $assignedProperty->seller_id,
        'scheduled_at' => '2026-10-19 10:00:00',
        'status' => 'confirmed',
    ]);
    Appointment::query()->create([
        'property_id' => $privateProperty->id,
        'buyer_id' => $otherBuyer->id,
        'seller_id' => $privateProperty->seller_id,
        'scheduled_at' => '2026-10-19 11:00:00',
        'status' => 'confirmed',
    ]);

    $this->actingAs($ppat)
        ->get(route('land-deed.index', ['month' => '2026-10']))
        ->assertOk()
        ->assertSee('Kalender jadwal survei')
        ->assertSee('Assigned Survey Client')
        ->assertDontSee('Private Survey Client')
        ->assertViewHas('appointments', fn ($appointments): bool => $appointments->modelKeys() === [$assignedAppointment->id]);
});

it('denies non-PPAT users access to the survey calendar', function () {
    $user = User::factory()->create();
    $user->assignRole('User');

    $this->actingAs($user)
        ->get(route('land-deed.index'))
        ->assertForbidden();
});
