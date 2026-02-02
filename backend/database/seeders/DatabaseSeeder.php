<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin User
        User::factory()->create([
            'fullname' => 'Admin User',
            'email' => 'admin@gmail.com',
            'role' => 'admin',
            'password' => 'password123',
        ]);

        // Staff User
        User::factory()->create([
            'fullname' => 'Staff User',
            'email' => 'staff@gmail.com',
            'role' => 'staff',
            'password' => 'password123',
        ]);

        // Customer User
        User::factory()->create([
            'fullname' => 'Customer User',
            'email' => 'customer@gmail.com', // Using valid domain as per docs
            'role' => 'customer',
            'password' => 'password123',
            'contact_number' => '+639123456789', // Adding contact number as it might be required
        ]);
    }
}
