<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            [
                'role_id' => 1, // Admin
                'first_name' => 'System',
                'middle_name' => '',
                'last_name' => 'Administrator',
                'email' => 'admin@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'), // Change later
                'contact_number' => '09123456789',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => 2, // Nutritionist
                'first_name' => 'Maria',
                'middle_name' => 'L.',
                'last_name' => 'Santos',
                'email' => 'nutritionist@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'contact_number' => '09111111111',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'role_id' => 3, // Parent
                'first_name' => 'Juan',
                'middle_name' => 'D.',
                'last_name' => 'Cruz',
                'email' => 'parent@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'contact_number' => '09222222222',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
