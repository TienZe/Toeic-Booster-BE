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
        // Seeds roles first
        $this->call(RoleSeeder::class);

        // Seeds admin user
        $this->call(AdminUserSeeder::class);

        // Seeds collection tags
        $this->call(CollectionTagSeeder::class);
    }
}
