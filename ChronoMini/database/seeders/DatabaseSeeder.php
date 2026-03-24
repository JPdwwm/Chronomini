<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Database\Seeders\ProductionUserSeeder;
use Database\Seeders\DevelopmentUserSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
        ]);

        if (app()->environment('production')) {
            $this->call(ProductionUserSeeder::class);
        } else {
            $this->call(DevelopmentUserSeeder::class);
            $this->call(KidSeeder::class);
        }

        $this->call([
            RecordSeeder::class
        ]);
    }
}