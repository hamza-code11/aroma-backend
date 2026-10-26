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
                'name' => 'Hamza',
                'email' => 'admin@example.com',
                'role' => 'admin',
            ],
            [
                'name' => 'Ali',
                'email' => 'ali@example.com',
                'role' => 'user',
            ],
            [
                'name' => 'Ahmed',
                'email' => 'ahmed@example.com',
                'role' => 'user',
            ],
            [
                'name' => 'Usman',
                'email' => 'usman@example.com',
                'role' => 'user',
            ],
            [
                'name' => 'Ayesha',
                'email' => 'ayesha@example.com',
                'role' => 'user',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'password' => Hash::make('Password@123'),
                ]
            );
        }
    }
}

