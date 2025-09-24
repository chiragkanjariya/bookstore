<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user if it doesn't exist
        User::firstOrCreate(
            ['email' => 'admin@bookstore.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@bookstore.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'phone' => '+1234567890',
                'address' => 'Admin Address, IPDC STORE',
                'email_verified_at' => now(),
            ]
        );

        // Create a sample regular user
        User::firstOrCreate(
            ['email' => 'chiragkanjariya0712@gmail.com'],
            [
                'name' => 'Sample User',
                'email' => 'chiragkanjariya0712@gmail.com',
                'password' => Hash::make('chirag@1997'),
                'role' => 'user',
                'phone' => '+0987654321',
                'address' => '123 Reader Street, Book City',
                'email_verified_at' => now(),
            ]
        );
    }
}