<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Request;
use App\Models\Approvals;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('one approver', function () {
    it('approve a request successfully', function () {

        $requester = User::factory()->create(['role' => 'requester']);
        $aprovador = User::factory()->create(['role' => 'L1']);

        $this->actingAs($requester);
        $response = $this->postJson('/api/requests', ['amount' => '5000.00']);
        $response->assertStatus(201);

        $approvals = Approvals::first();
        $this->actingAs($aprovador);
        $response = $this->postJson('/api/approvals/' . (int) $approvals->id   . '/approve');
        $response->assertStatus(200);
        $this->assertDatabaseHas('approvals', ['request_id' => $approvals->request_id, 'user_id' => $approvals->user_id, 'status' => 'approved']);
        $this->assertDatabaseHas('requests', ['user_id' => $requester->id, 'status' => 'approved']);
    });

    it('reject a request successful', function () {

        $requester = User::factory()->create(['role' => 'requester']);
        $aprovador = User::factory()->create(['role' => 'L1']);

        $this->actingAs($requester);
        $response = $this->postJson('/api/requests', ['amount' => '5000.00']);
        $response->assertStatus(201);

        $approvals = Approvals::first();
        $this->actingAs($aprovador);
        $response = $this->postJson('/api/approvals/' . (int) $approvals->id   . '/reject');
        $response->assertStatus(200);
        $this->assertDatabaseHas('approvals', ['request_id' => $approvals->request_id, 'user_id' => $approvals->user_id, 'status' => 'rejected']);
        $this->assertDatabaseHas('requests', ['user_id' => $requester->id, 'status' => 'rejected']);
    });
});


describe('three approvers', function () {
    it('approve a request successfully', function () {

        $requester = User::factory()->create(['role' => 'requester']);
        $approversL1 = User::factory()->create(['role' => 'L1']);
        $approversL2 = User::factory()->create(['role' => 'L2']);
        $approversL3 = User::factory()->create(['role' => 'L3']);

        $this->actingAs($requester);
        $response = $this->postJson('/api/requests', ['amount' => '60000.00']);
        $response->assertStatus(201);

        $approvals = Approvals::where('order', 1)->first();
        $this->actingAs($approversL1);
        $response = $this->postJson('/api/approvals/' . (int) $approvals->id   . '/approve');
        $response->assertStatus(200);
        $this->assertDatabaseHas('approvals', ['request_id' => $approvals->request_id, 'user_id' => $approvals->user_id, 'status' => 'approved']);
        $this->assertDatabaseHas('requests', ['user_id' => $requester->id, 'status' => 'pending']);

        $approvals = Approvals::where('order', 2)->first();
        $this->actingAs($approversL2);
        $response = $this->postJson('/api/approvals/' . (int) $approvals->id   . '/approve');
        $response->assertStatus(200);
        $this->assertDatabaseHas('approvals', ['request_id' => $approvals->request_id, 'user_id' => $approvals->user_id, 'status' => 'approved']);
        $this->assertDatabaseHas('requests', ['user_id' => $requester->id, 'status' => 'pending']);

        $approvals = Approvals::where('order', 3)->first();
        $this->actingAs($approversL3);
        $response = $this->postJson('/api/approvals/' . (int) $approvals->id   . '/approve');
        $response->assertStatus(200);
        $this->assertDatabaseHas('approvals', ['request_id' => $approvals->request_id, 'user_id' => $approvals->user_id, 'status' => 'approved']);
        $this->assertDatabaseHas('requests', ['user_id' => $requester->id, 'status' => 'approved']);

    });

    it('reject a request successful', function () {

        $requester = User::factory()->create(['role' => 'requester']);
        $approversL1 = User::factory()->create(['role' => 'L1']);
        $approversL2 = User::factory()->create(['role' => 'L2']);
        $approversL3 = User::factory()->create(['role' => 'L3']);

        $this->actingAs($requester);
        $response = $this->postJson('/api/requests', ['amount' => '60000.00']);
        $response->assertStatus(201);

        $approvals = Approvals::where('order', 1)->first();
        $this->actingAs($approversL1);
        $response = $this->postJson('/api/approvals/' . (int) $approvals->id   . '/approve');
        $response->assertStatus(200);
        $this->assertDatabaseHas('approvals', ['request_id' => $approvals->request_id, 'user_id' => $approvals->user_id, 'status' => 'approved']);
        $this->assertDatabaseHas('requests', ['user_id' => $requester->id, 'status' => 'pending']);

        $approvals = Approvals::where('order', 2)->first();
        $this->actingAs($approversL2);
        $response = $this->postJson('/api/approvals/' . (int) $approvals->id   . '/reject');
        $response->assertStatus(200);
        $this->assertDatabaseHas('approvals', ['request_id' => $approvals->request_id, 'user_id' => $approvals->user_id, 'status' => 'rejected']);
        $this->assertDatabaseHas('requests', ['user_id' => $requester->id, 'status' => 'rejected']);

        $approvals = Approvals::where('order', 3)->first();
        $this->actingAs($approversL2);
        $this->assertDatabaseHas('approvals', ['request_id' => $approvals->request_id, 'user_id' => $approvals->user_id, 'status' => 'canceled']);
        $this->assertDatabaseHas('requests', ['user_id' => $requester->id, 'status' => 'rejected']);

    });

    it('approve in wrong order', function () {

        $requester = User::factory()->create(['role' => 'requester']);
        $approversL1 = User::factory()->create(['role' => 'L1']);
        $approversL2 = User::factory()->create(['role' => 'L2']);
        $approversL3 = User::factory()->create(['role' => 'L3']);

        $this->actingAs($requester);
        $response = $this->postJson('/api/requests', ['amount' => '60000.00']);
        $response->assertStatus(201);

        $approvals = Approvals::where('order', 3)->first();
        $this->actingAs($approversL3);
        $response = $this->postJson('/api/approvals/' . (int) $approvals->id   . '/approve');
        $response->assertStatus(400);
        $this->assertDatabaseHas('approvals', ['request_id' => $approvals->request_id, 'user_id' => $approvals->user_id, 'status' => 'pending']);
        $this->assertDatabaseHas('requests', ['user_id' => $requester->id, 'status' => 'pending']);


    });

});