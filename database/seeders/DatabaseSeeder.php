<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Authorization\Database\Seeders\AuthorizationDatabaseSeeder;
use Modules\Content\Database\Seeders\ContentDatabaseSeeder;
use Modules\User\Database\Seeders\UserDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserDatabaseSeeder::class,
            AuthorizationDatabaseSeeder::class,
            ContentDatabaseSeeder::class,
        ]);
    }
}
