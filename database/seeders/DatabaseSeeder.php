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
        User::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Superadmin',
            'email' => 'superadmin@mihom.id',
            'password' => Hash::make('password'),
            'phone' => '997997997997'
        ]);
    }
}
