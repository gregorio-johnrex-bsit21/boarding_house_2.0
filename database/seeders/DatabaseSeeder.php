<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 🌟 FIXED: Comment out or delete the default factory lines that create test@example.com
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // 🌟 FIXED: Explicitly call your UserSeeder so it builds your custom admin/tenant accounts
        $this->call([
            UserSeeder::class,
        ]);
    }
}