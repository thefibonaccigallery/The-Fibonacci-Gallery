<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'uuid' => Str::uuid(),
            'password' => '12345678',
            'country' => 'US',
            'role' => 'admin',
        ]);
        User::factory()->create([
            'name' => 'Super Admin User',
            'email' => 'superadmin@example.com',
            'password' => '12345678',
            'uuid' => Str::uuid(),
            'country' => 'US',
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '12345678',
            'uuid' => Str::uuid(),
            'country' => 'US',
            'role' => 'user',
        ]);
    }
}
