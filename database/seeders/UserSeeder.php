<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user only
        User::create([
            'username' => 'admin',
            'password' => Hash::make('password'),
            'full_name' => 'Administrator',
            'role' => 'Administrator',
            'status' => 'Active',
        ]);
    }
}
