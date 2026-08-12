<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed in proper order: users first, then settings, then data
        $this->call([
            RoleSeeder::class,
            SettingsSeeder::class,
            CommitteeMemberSeeder::class,
            ComprehensiveDataSeeder::class,
        ]);
    }
}
