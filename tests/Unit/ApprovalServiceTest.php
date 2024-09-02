<?php

namespace Tests\Unit;

use App\Services\ApprovalService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->approvalService = new ApprovalService();
});

describe('getApprovers', function () {
    it('returns correct approvers for amount up to 10.000', function () {
        User::factory()->create(['role' => 'L1']);
        User::factory()->create(['role' => 'L2']);
        User::factory()->create(['role' => 'L3']);

        $approvers = $this->approvalService->getApprovers(10000);

        expect($approvers)->toHaveCount(1);
        expect($approvers->pluck('role'))->toContain('L1');
    });

    it('returns correct approvers for amount between 10.001 and 50.000', function () {
        User::factory()->create(['role' => 'L1']);
        User::factory()->create(['role' => 'L2']);
        User::factory()->create(['role' => 'L3']);

        $approvers = $this->approvalService->getApprovers(10001);

        expect($approvers)->toHaveCount(2);
        expect($approvers->pluck('role'))->toContain('L1', 'L2');
    });

    it('returns correct approvers for amount above 50.000', function () {
        User::factory()->create(['role' => 'L1']);
        User::factory()->create(['role' => 'L2']);
        User::factory()->create(['role' => 'L3']);

        $approvers = $this->approvalService->getApprovers(50001);

        expect($approvers)->toHaveCount(3);
        expect($approvers->pluck('role'))->toContain('L1', 'L2', 'L3');
    });
});
