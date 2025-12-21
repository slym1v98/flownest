<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Database\Seeders\AuthorizationDatabaseSeeder;
use Modules\User\Database\Seeders\UserDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->environment('local')) {
            $this->call([
                UserDatabaseSeeder::class,
                AuthorizationDatabaseSeeder::class,
            ]);
        }
    }
}
