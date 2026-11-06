<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Room;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Safe Admin User Account Setup
        User::updateOrCreate(
            ['email' => 'admin@ipk.com'], // Unique lookup key
            [
                'name' => 'System Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin', 
            ]
        );

        // 2. Safe Tenant User Account Setup
        $tenantUser = User::updateOrCreate(
            ['email' => 'tenant@ipk.com'], // Unique lookup key
            [
                'name' => 'tenant',
                'password' => Hash::make('password123'),
                'role' => 'client', 
            ]
        );

        // 3. Create or find a Test Room Assignment
        $room = Room::updateOrCreate(
            ['room_number' => 'ROOM 204-A'], // Unique lookup key
            [
                'room_type' => 'Executive Deluxe Solo',
                'status' => 'Occupied',
                'price' => 4500.00
            ]
        );

        // 4. Link the Tenant Profile cleanly without breaking
        Tenant::updateOrCreate(
            ['user_id' => $tenantUser->id], // Unique lookup key
            [
                'room_id' => $room->id,
                'name' => $tenantUser->name,
                'phone' => '0999-888-7777',
                'status' => 'Active',
            ]
        );
    }
}