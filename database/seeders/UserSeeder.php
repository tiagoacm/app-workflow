<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'James',
            'email' => 'james@gmail.com',
            'role' => 'requester',
        ]);

        User::factory()->create([
            'name' => 'Adrian',
            'email' => 'adrian@gmail.com',
            'role' => 'requester',
        ]);

        User::factory()->create([
            'name' => 'Peter',
            'email' => 'peter@gmail.com',
            'role' => 'L1',
        ]);

        User::factory()->create([
            'name' => 'Willian',
            'email' => 'willian@gmail.com',
            'role' => 'L2',
        ]);

        User::factory()->create([
            'name' => 'Sophia',
            'email' => 'sophia@gmail.com',
            'role' => 'L3',
        ]);
    }
}
