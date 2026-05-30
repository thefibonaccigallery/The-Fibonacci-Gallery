<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user1 = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('12345678'),
            'country' => 'US',
            'uuid' => Str::uuid(),
        ]);
        $user1->assignRole('admin');

        $user2 = User::create([
            'name' => 'Super Admin User',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('12345678'),
            'country' => 'US',
            'uuid' => Str::uuid(),
        ]);
        $user2->assignRole('super_admin');

        // Create Test User
        $user3 = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('12345678'),
            'country' => 'US',
            'uuid' => Str::uuid(),
        ]);
        $user3->assignRole('user');
    }
}
