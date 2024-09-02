<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);


it('user login', function () {

    $user = User::factory()->create([
        'name' => 'Test',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'role' => 'L1',
    ]);

    $response = $this->postJson('api/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(200);
    $this->assertAuthenticatedAs($user);
});
