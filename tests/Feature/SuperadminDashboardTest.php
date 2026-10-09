<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('renders the superadmin dashboard overview', function () {
    Role::firstOrCreate(['name' => 'Superadmin']);

    $user = User::factory()->create();
    $user->assignRole('Superadmin');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeText('Ringkasan platform')
        ->assertSeeText('Pengguna terdaftar')
        ->assertSeeText('Properti tayang')
        ->assertSeeText('Transaksi berjalan')
        ->assertSeeText('Transaksi terbaru');
});
