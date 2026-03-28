<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin User',
                'email' => 'superadmin@example.com',
                'role' => User::ROLE_SUPER_ADMIN,
            ],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'role' => User::ROLE_ADMIN,
            ],
            [
                'name' => 'Sales Officer',
                'email' => 'sales@example.com',
                'role' => User::ROLE_SALES_OFFICER,
            ],
            [
                'name' => 'Procurement Officer',
                'email' => 'procurement@example.com',
                'role' => User::ROLE_PROCUREMENT_OFFICER,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'access_enabled' => true,
                    'access_expires_at' => null,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
