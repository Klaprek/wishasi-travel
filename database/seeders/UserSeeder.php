<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@tour.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'owner@tour.com'],
            [
                'name' => 'Owner',
                'password' => bcrypt('owner123'),
                'role' => 'owner',
            ]
        );
    }
}
