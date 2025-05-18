<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Waiver;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
        
        // Create regular staff users
        $users = User::factory(3)->create();
        
        // Create some waiver forms for each user
        $allUsers = User::all();
        
        foreach ($allUsers as $user) {
            Waiver::factory(5)->create([
                'user_id' => $user->id,
            ]);
        }
        
        // Create some unsigned waivers
        Waiver::factory(3)->create([
            'signed_at' => null,
            'signature' => null,
            'accepted_terms' => false,
            'accepted_aftercare' => false,
        ]);
    }
}
