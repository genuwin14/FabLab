<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin Account
        User::create([
            'fullname' => 'System Administrator',
            'email' => 'admin@gmail.com',
            'password' => 'password',
            'role' => 'admin',
            'contact_number' => '09123456789',
            'phone_verified' => true,
        ]);

        // Staff Account
        User::create([
            'fullname' => 'Fablab Staff One',
            'email' => 'staff@gmail.com',
            'password' => 'password',
            'role' => 'staff',
            'contact_number' => '09987654321',
            'phone_verified' => true,
        ]);

        // Sample Customer
        User::create([
            'fullname' => 'John Doe',
            'email' => 'customer@gmail.com',
            'password' => 'password',
            'role' => 'customer',
            'contact_number' => '09551234455',
            'phone_verified' => true,
            'address' => 'Sample Address, City',
            'degree' => 'BS IT',
            'year' => '4th Year',
            'section' => 'A',
            'gender' => 'Male',
        ]);
    }
}
