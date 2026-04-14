<?php

namespace Database\Seeders;

use App\Models\Dog;
use App\Models\Owner;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Settings
        $this->call(SettingsSeeder::class);

        // Staff user
        User::updateOrCreate(
            ['email' => 'joao@testecaudafeliz.com'],
            ['name' => 'João', 'password' => Hash::make('password'), 'role' => 'staff']
        );

        // Owner users
        $ricardo = User::updateOrCreate(
            ['email' => 'ricardo@testecaudafeliz.com'],
            ['name' => 'Ricardo', 'password' => Hash::make('password'), 'role' => 'owner']
        );

        User::updateOrCreate(
            ['email' => 'joana@testecaudafeliz.com'],
            ['name' => 'Joana', 'password' => Hash::make('password'), 'role' => 'owner']
        );

        // Owner Ricardo linked to user Ricardo
        $owner = Owner::updateOrCreate(
            ['phone' => '917375207'],
            ['name' => 'Ricardo', 'phone' => '917375207', 'user_id' => $ricardo->id]
        );

        // Dog
        Dog::updateOrCreate(
            ['name' => 'Loki', 'owner_id' => $owner->id],
            ['name' => 'Loki', 'breed' => 'Labrador + cocker', 'owner_id' => $owner->id]
        );
    }
}
