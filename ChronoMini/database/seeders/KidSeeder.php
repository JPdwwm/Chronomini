<?php

namespace Database\Seeders;

use App\Models\Kid;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KidSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Crée 1 enfant
        $kids = Kid::factory()->count(1)->create();

         // Récupère l'utilisateur avec l'id 2
        $users = User::where('id', 2)->get();

         // Associe l'enfant à l'utilisateur avec l'id 2
        foreach ($kids as $kid) {
            $kid->users()->attach($users->pluck('id')->toArray());
        }
    }
}

