<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
        'name' => 'Admin',
        'email' => 'admin@tour.com',
        'password' => bcrypt('admin123'),
        'role' => 'admin',
        ]);

        \App\Models\User::create([
            'name' => 'Owner',
            'email' => 'owner@tour.com',
            'password' => bcrypt('owner123'),
            'role' => 'owner',
        ]);
    }
}
