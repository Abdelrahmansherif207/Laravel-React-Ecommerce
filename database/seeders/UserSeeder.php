<?php

namespace Database\Seeders;

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'User',
            'email' => 'user@gmail.com',
            'password' => Hash::make('dodger12345')
        ])->assignRole(RolesEnum::User);

        User::factory()->create([
            'name' => 'Vendor',
            'email' => 'vendor@gmail.com',
            'password' => Hash::make('dodger12345')
        ])->assignRole(RolesEnum::Vendor);

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('dodger12345')
        ])->assignRole(RolesEnum::Admin);
    }
}
