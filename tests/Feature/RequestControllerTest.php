<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'requester']);
    $this->actingAs($this->user);
});

it('creates a request and approvals correctly', function () {
    User::factory()->create(['role' => 'L1']);

    $response = $this->postJson('/api/requests', ['amount' => '5000.00']);
    $response->assertStatus(201);

    $this->assertDatabaseHas('requests', ['amount' => 5000, 'user_id' => $this->user->id]);
    $this->assertDatabaseHas('approvals', ['status' => 'pending']);
});