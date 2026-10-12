<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleAndPermissionSeeder::class);
        $superadmin = User::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Superadmin',
            'username' => 'superadmin',
            'email' => 'superadmin@mihom.id',
            'password' => Hash::make('password'),
            'phone' => '997997997997'
        ]);
        $superadmin->assignRole('Superadmin');

        $admin = User::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@mihom.id',
            'password' => Hash::make('password'),
            'phone' => '991991991991'
        ]);
        $admin->assignRole('Admin');

        $ppat = User::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'PPAT',
            'username' => 'ppat',
            'email' => 'ppat@mihom.id',
            'password' => Hash::make('password'),
            'phone' => '998998998998'
        ]);
        $ppat->assignRole('PPAT');

        $buyer = User::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Buyer',
            'username' => 'buyer',
            'email' => 'buyer@mihom.id',
            'password' => Hash::make('password'),
            'phone' => '996996996996'
        ]);
        $buyer->assignRole('Buyer');

        $seller = User::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Seller',
            'username' => 'seller',
            'email' => 'seller@mihom.id',
            'password' => Hash::make('password'),
            'phone' => '995995995995'
        ]);
        $seller->assignRole('Seller');
    }
}
