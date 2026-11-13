<?php

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Property;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $permissions = [
        'system.view_audit_logs',
        'properties.approve_listing',
        'properties.reject_listing',
        'documents.view_sensitive_pii',
        'appointments.request',
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web'])
        ->syncPermissions($permissions);
    Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web'])
        ->syncPermissions($permissions);
    Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
});

function createAuditedProperty(string $status = 'pending_verification'): Property
{
    $seller = User::factory()->create();

    return Property::query()->create([
        'uuid' => (string) Str::uuid(),
        'seller_id' => $seller->id,
        'property_type' => 'bangunan',
        'type' => 'rumah',
        'category' => 'sale',
        'status' => $status,
        'title' => 'Rumah Audit Log',
        'description' => 'Properti untuk pengujian audit log.',
        'price' => 900000000,
        'negotiable' => false,
        'province' => 'Jawa Barat',
        'city' => 'Bandung',
        'district' => 'Coblong',
        'address' => 'Jl. Audit No. 1',
        'land_area_sqm' => 100,
        'building_area_sqm' => 80,
        'certificate_type' => 'SHM',
    ]);
}

it('records a login event for authenticated users', function () {
    $user = User::factory()->create(['name' => 'Pengguna Masuk']);
    $user->assignRole('User');

    $this->post(route('login'), [
        'phone' => $user->phone,
        'password' => 'password',
    ])->assertRedirect('dashboard');

    $log = AuditLog::query()->where('event', AuditLog::EVENT_AUTH_LOGIN)->first();

    expect($log)->not->toBeNull()
        ->and($log->category)->toBe(AuditLog::CATEGORY_AUTHENTICATION)
        ->and($log->actor_id)->toBe($user->id)
        ->and($log->actor_name)->toBe('Pengguna Masuk')
        ->and($log->actor_role)->toBe('User')
        ->and($log->subject_label)->toBe('Pengguna Masuk');
});

it('records a logout event for authenticated users', function () {
    $user = User::factory()->create();
    $user->assignRole('User');

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect('/');

    $this->assertDatabaseHas('audit_logs', [
        'event' => AuditLog::EVENT_AUTH_LOGOUT,
        'category' => AuditLog::CATEGORY_AUTHENTICATION,
        'actor_id' => $user->id,
    ]);
});

it('records failed login attempts without persisting credentials', function () {
    $this->post(route('login'), [
        'phone' => '081234567890',
        'password' => 'rahasia-yang-salah',
    ])->assertSessionHasErrors('phone');

    $log = AuditLog::query()->where('event', AuditLog::EVENT_AUTH_FAILED)->first();

    expect($log)->not->toBeNull()
        ->and($log->category)->toBe(AuditLog::CATEGORY_AUTHENTICATION)
        ->and($log->actor_id)->toBeNull()
        ->and($log->properties['identifier'])->toBe('6281234567890')
        ->and(json_encode($log->properties))->not->toContain('rahasia-yang-salah');
});

it('records property approval and rejection by administrators', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $property = createAuditedProperty();

    $this->actingAs($admin)
        ->from(route('admin.master-data-property'))
        ->post(route('property-review.status', $property), ['status' => 'published'])
        ->assertRedirect(route('admin.master-data-property'));

    $approved = AuditLog::query()->where('event', AuditLog::EVENT_PROPERTY_APPROVED)->first();

    expect($approved)->not->toBeNull()
        ->and($approved->category)->toBe(AuditLog::CATEGORY_MODERATION)
        ->and($approved->actor_id)->toBe($admin->id)
        ->and($approved->subject_type)->toBe($property->getMorphClass())
        ->and($approved->subject_id)->toBe($property->id)
        ->and($approved->subject_label)->toBe('Rumah Audit Log')
        ->and($approved->properties['status'])->toBe([
            'old' => 'pending_verification',
            'new' => 'published',
        ]);

    $secondProperty = createAuditedProperty();

    $this->actingAs($admin)
        ->from(route('admin.master-data-property'))
        ->post(route('property-review.status', $secondProperty), ['status' => 'rejected'])
        ->assertRedirect(route('admin.master-data-property'));

    $this->assertDatabaseHas('audit_logs', [
        'event' => AuditLog::EVENT_PROPERTY_REJECTED,
        'category' => AuditLog::CATEGORY_MODERATION,
        'subject_id' => $secondProperty->id,
    ]);
});

it('records a scheduling event when a buyer books a survey', function () {
    $seller = User::factory()->create();
    $buyer = User::factory()->create(['name' => 'Pembeli Survei']);
    $buyer->assignRole('User');
    $buyer->givePermissionTo('appointments.request');
    $property = createAuditedProperty('published');
    $property->update(['seller_id' => $seller->id]);

    $this->actingAs($buyer)
        ->post(route('property.schedule-visit', $property->uuid), [
            'scheduled_at' => now()->addDay()->toDateString(),
            'scheduled_time' => Appointment::TIME_SLOTS[0],
            'notes' => 'Tolong siapkan dokumen.',
        ])
        ->assertRedirect();

    $appointment = Appointment::query()->where('property_id', $property->id)->firstOrFail();
    $log = AuditLog::query()->where('event', AuditLog::EVENT_APPOINTMENT_CREATED)->first();

    expect($log)->not->toBeNull()
        ->and($log->category)->toBe(AuditLog::CATEGORY_SCHEDULING)
        ->and($log->actor_id)->toBe($buyer->id)
        ->and($log->subject_type)->toBe($appointment->getMorphClass())
        ->and($log->subject_id)->toBe($appointment->id)
        ->and($log->subject_label)->toBe('Rumah Audit Log');
});

it('redacts sensitive attributes from audit metadata', function () {
    app(AuditLogger::class)->record(
        AuditLog::EVENT_USER_CREATED,
        'Uji redaksi metadata.',
        null,
        [
            'password' => 'rahasia',
            'password_confirmation' => 'rahasia',
            'remember_token' => 'token-merah',
            'nested' => ['token' => 'token-nested', 'visible' => 'aman'],
        ],
    );

    $log = AuditLog::query()->where('event', AuditLog::EVENT_USER_CREATED)->firstOrFail();

    expect($log->properties)->toBe(['nested' => ['visible' => 'aman']])
        ->and($log->category)->toBe(AuditLog::CATEGORY_ADMINISTRATION);
});

it('shows the audit trail to administrators without system events or technical metadata', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $logger = app(AuditLogger::class);

    $logger->record(AuditLog::EVENT_PROPERTY_APPROVED, 'Properti contoh disetujui.', null, ['status' => 'published']);
    $logger->record(AuditLog::EVENT_ROLE_PERMISSIONS_UPDATED, 'Perubahan konfigurasi internal.', null, [], null, AuditLog::CATEGORY_SYSTEM);

    $this->actingAs($admin)
        ->get(route('admin.audit-log'))
        ->assertOk()
        ->assertSeeText('Riwayat Aktivitas')
        ->assertSeeText('Properti contoh disetujui.')
        ->assertSeeText('Moderasi Properti')
        ->assertDontSeeText('Perubahan konfigurasi internal.')
        ->assertDontSeeText('IP:');

    $this->get(route('admin.audit-log', ['category' => AuditLog::CATEGORY_SYSTEM]))
        ->assertOk()
        ->assertDontSeeText('Perubahan konfigurasi internal.');
});

it('shows system events and request context to superadmins', function () {
    $superadmin = User::factory()->create();
    $superadmin->assignRole('Superadmin');
    $logger = app(AuditLogger::class);

    $logger->record(AuditLog::EVENT_ROLE_PERMISSIONS_UPDATED, 'Hak akses role Admin diperbarui.', null, [], null, AuditLog::CATEGORY_SYSTEM);

    $this->actingAs($superadmin)
        ->get(route('superadmin.audit-log'))
        ->assertOk()
        ->assertSeeText('Audit Log Sistem')
        ->assertSeeText('Hak akses role Admin diperbarui.')
        ->assertSeeText('Sistem')
        ->assertSeeText('IP:');
});

it('filters audit entries by category and search term', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $logger = app(AuditLogger::class);

    $logger->record(AuditLog::EVENT_APPOINTMENT_CREATED, 'Survei untuk properti Melati.', null, []);
    $logger->record(AuditLog::EVENT_TRANSACTION_DP_INITIATED, 'Pembayaran DP untuk properti Anggrek.', null, []);

    $this->actingAs($admin)
        ->get(route('admin.audit-log', ['category' => AuditLog::CATEGORY_SCHEDULING]))
        ->assertOk()
        ->assertSeeText('Survei untuk properti Melati.')
        ->assertDontSeeText('Pembayaran DP untuk properti Anggrek.');

    $this->get(route('admin.audit-log', ['search' => 'Anggrek']))
        ->assertOk()
        ->assertSeeText('Pembayaran DP untuk properti Anggrek.')
        ->assertDontSeeText('Survei untuk properti Melati.');
});

it('denies audit log access to users without the audit permission', function () {
    $user = User::factory()->create();
    $user->assignRole('User');

    $this->actingAs($user)
        ->get(route('admin.audit-log'))
        ->assertForbidden();

    $this->get(route('superadmin.audit-log'))
        ->assertForbidden();
});
