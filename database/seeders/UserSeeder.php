<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\user;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        user::firstOrCreate(
            ['email' => 'admin@tour.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
            ]
        );

        user::firstOrCreate(
            ['email' => 'owner@tour.com'],
            [
                'name' => 'Owner',
                'password' => bcrypt('owner123'),
                'role' => 'owner',
            ]
        );
    }
}
