<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DevelopmentUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    
    User::create([
        'first_name' => 'César',
        'last_name' => 'Vidal',
        'password' => Hash::make('Azerty88@'),
        'email' => 'admin@test.fr',
        'email_verified_at' => now(),
        'remember_token' => Str::random(10),
        'role_id' => 1,
        'token' => Str::random(40)
    ]);
    // Création d'un user parent de test
    User::create([
        'first_name' => 'Utilisateur',
        'last_name' => 'Parent',
        'password' => Hash::make('Azerty88@'),
        'email' => 'parent@jp.fr',
        'city' => 'Niort',
        'zip_code' => '79000',
        'email_verified_at' => now(),
        'remember_token' => Str::random(10),
        'role_id' => 2,
        'token' => Str::random(40)
    ]);

    }
}
