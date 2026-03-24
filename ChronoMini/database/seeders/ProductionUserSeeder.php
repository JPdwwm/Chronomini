<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class ProductionUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $firstName = env('ADMIN_FIRST_NAME');
        $lastName = env('ADMIN_LAST_NAME');
        $password = env('ADMIN_PASSWORD');
        $email = env('ADMIN_EMAIL');
        
        if (!$firstName || !$lastName || !$password || !$email) {
            throw new \Exception('Variables d\'environnement pour l\'administrateur non définies.');
        }
        
        // Création de l'admin
        User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'password' => Hash::make($password),
            'email' => $email,
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'role_id' => 1,
            'token' => Str::random(40)
        ]);
    }
}
