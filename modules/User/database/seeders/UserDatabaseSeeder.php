<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\User\Models\User;

class UserDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->runOnDevelopment();
    }

    protected function runOnDevelopment(): void
    {
        if (!app()->environment('local')) {
            return;
        }

        User::factory()->create([
            'name'              => 'Admin',
            'email'             => 'admin@flownest.net',
            'username'          => 'admin',
            'password'          => Hash::make('@Admin123'),
            'email_verified_at' => now(),
            'remember_token'    => Str::random(10),
        ]);

        User::factory()->create([
            'name'              => 'Publisher',
            'email'             => 'publisher@flownest.net',
            'username'          => 'publisher',
            'password'          => Hash::make('@Publisher123'),
            'email_verified_at' => now(),
            'remember_token'    => Str::random(10),
        ]);

        User::factory()->create([
            'name'              => 'Editor',
            'email'             => 'editor@flownest.net',
            'username'          => 'editor',
            'password'          => Hash::make('@Editor123'),
            'email_verified_at' => now(),
            'remember_token'    => Str::random(10),
        ]);

        User::factory(5)->create([
            'password'          => Hash::make('@Guest123'),
            'email_verified_at' => now(),
            'remember_token'    => Str::random(10),
        ]);
    }
}
