<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
            // First, create the admin user (without any verified_by reference)
            $adminUser = [
                'role_id' => 1, // Admin
                'first_name' => 'System',
                'middle_name' => '',
                'last_name' => 'Administrator',
                'email' => 'admin@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'), // Change later
                'contact_number' => '09123456789',
                'birth_date' => null,
                'sex' => null,
                'address' => null,
                'is_active' => true,
                'years_experience' => null,
                'qualifications' => null,
                'professional_experience' => null,
                'professional_id_path' => null,
                'verification_status' => 'pending',
                'rejection_reason' => null,
                'verified_at' => null,
                'verified_by' => null,
                'account_status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ];

            // Insert admin first
            DB::table('users')->updateOrInsert(
                ['email' => 'admin@example.com'],
                $adminUser
            );

            // Now create other users (can reference admin user ID 1)
            $users = [
                [
                    'role_id' => 2, // Nutritionist
                    'first_name' => 'Maria',
                    'middle_name' => 'L.',
                    'last_name' => 'Santos',
                    'email' => 'nutritionist@example.com',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password123'),
                    'contact_number' => '09111111111',
                    'birth_date' => '1990-05-10',
                    'sex' => 'Female',
                    'address' => 'Pacita 1, San Pedro, Laguna',
                    'is_active' => true,
                    'years_experience' => 8,
                    'qualifications' => 'BS Nutrition, Registered Nutritionist-Dietitian',
                    'professional_experience' => '8 years in public health nutrition, 2 years in private practice.',
                    'professional_id_path' => 'uploads/professional_ids/nutritionist1.jpg',
                    'verification_status' => 'verified',
                    'rejection_reason' => null,
                    'verified_at' => now(),
                    'verified_by' => 1,
                    'account_status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'role_id' => 2, // Nutritionist
                    'first_name' => 'Anna',
                    'middle_name' => 'R.',
                    'last_name' => 'Reyes',
                    'email' => 'nutritionist2@example.com',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password123'),
                    'contact_number' => '09333333333',
                    'birth_date' => '1988-03-15',
                    'sex' => 'Female',
                    'address' => 'Landayan, San Pedro, Laguna',
                    'is_active' => true,
                    'years_experience' => 5,
                    'qualifications' => 'BS Nutrition, Registered Nutritionist-Dietitian',
                    'professional_experience' => '5 years in community nutrition programs.',
                    'professional_id_path' => 'uploads/professional_ids/nutritionist2.jpg',
                    'verification_status' => 'verified',
                    'rejection_reason' => null,
                    'verified_at' => now(),
                    'verified_by' => 1,
                    'account_status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'role_id' => 2, // Nutritionist
                    'first_name' => 'Carlos',
                    'middle_name' => 'M.',
                    'last_name' => 'Villanueva',
                    'email' => 'nutritionist3@example.com',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password123'),
                    'contact_number' => '09444444444',
                    'birth_date' => '1992-11-22',
                    'sex' => 'Male',
                    'address' => 'Riverside, San Pedro, Laguna',
                    'is_active' => true,
                    'years_experience' => 3,
                    'qualifications' => 'BS Nutrition, Registered Nutritionist-Dietitian',
                    'professional_experience' => '3 years in pediatric nutrition.',
                    'professional_id_path' => 'uploads/professional_ids/nutritionist3.jpg',
                    'verification_status' => 'verified',
                    'rejection_reason' => null,
                    'verified_at' => now(),
                    'verified_by' => 1,
                    'account_status' => 'active',
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
                    'birth_date' => '1985-08-20',
                    'sex' => 'Male',
                    'address' => 'Bagong Silang, San Pedro, Laguna',
                    'is_active' => true,
                    'years_experience' => null,
                    'qualifications' => null,
                    'professional_experience' => null,
                    'professional_id_path' => null,
                    'verification_status' => 'pending',
                    'rejection_reason' => null,
                    'verified_at' => null,
                    'verified_by' => null,
                    'account_status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'role_id' => 3, // Parent
                    'first_name' => 'Rosa',
                    'middle_name' => 'T.',
                    'last_name' => 'Garcia',
                    'email' => 'parent2@example.com',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password123'),
                    'contact_number' => '09555555555',
                    'birth_date' => '1987-06-15',
                    'sex' => 'Female',
                    'address' => 'San Antonio, San Pedro, Laguna',
                    'is_active' => true,
                    'years_experience' => null,
                    'qualifications' => null,
                    'professional_experience' => null,
                    'professional_id_path' => null,
                    'verification_status' => 'pending',
                    'rejection_reason' => null,
                    'verified_at' => null,
                    'verified_by' => null,
                    'account_status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'role_id' => 3, // Parent
                    'first_name' => 'Pedro',
                    'middle_name' => 'S.',
                    'last_name' => 'Ramos',
                    'email' => 'parent3@example.com',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password123'),
                    'contact_number' => '09666666666',
                    'birth_date' => '1990-02-28',
                    'sex' => 'Male',
                    'address' => 'Calendola, San Pedro, Laguna',
                    'is_active' => true,
                    'years_experience' => null,
                    'qualifications' => null,
                    'professional_experience' => null,
                    'professional_id_path' => null,
                    'verification_status' => 'pending',
                    'rejection_reason' => null,
                    'verified_at' => null,
                    'verified_by' => null,
                    'account_status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'role_id' => 3, // Parent
                    'first_name' => 'Luisa',
                    'middle_name' => 'B.',
                    'last_name' => 'Mendoza',
                    'email' => 'parent4@example.com',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password123'),
                    'contact_number' => '09777777777',
                    'birth_date' => '1986-09-10',
                    'sex' => 'Female',
                    'address' => 'Chrysanthemum, San Pedro, Laguna',
                    'is_active' => true,
                    'years_experience' => null,
                    'qualifications' => null,
                    'professional_experience' => null,
                    'professional_id_path' => null,
                    'verification_status' => 'pending',
                    'rejection_reason' => null,
                    'verified_at' => null,
                    'verified_by' => null,
                    'account_status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'role_id' => 3, // Parent
                    'first_name' => 'Miguel',
                    'middle_name' => 'F.',
                    'last_name' => 'Torres',
                    'email' => 'parent5@example.com',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password123'),
                    'contact_number' => '09888888888',
                    'birth_date' => '1989-12-05',
                    'sex' => 'Male',
                    'address' => 'Estrella, San Pedro, Laguna',
                    'is_active' => true,
                    'years_experience' => null,
                    'qualifications' => null,
                    'professional_experience' => null,
                    'professional_id_path' => null,
                    'verification_status' => 'pending',
                    'rejection_reason' => null,
                    'verified_at' => null,
                    'verified_by' => null,
                    'account_status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
            ];

            // Insert other users (admin already exists, so verified_by = 1 is valid)
            foreach ($users as $user) {
                DB::table('users')->updateOrInsert(
                    ['email' => $user['email']],
                    $user
                );
            }
    }
}
