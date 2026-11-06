<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@bh.com'], // Unique identifier
            [
                'name' => 'System Admin',
                'password' => Hash::make('bh123'), // Always hash passwords!
                'email_verified_at' => now(),
            ]
        );
    }
}