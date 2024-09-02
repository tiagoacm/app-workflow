<?php

namespace App\Services;

use App\Models\User;

class ApprovalService
{
    public function getApprovers($amount)
    {
        $rules = config('approval_rules.rules');

        foreach ($rules as $rule) {
            if (is_null($rule['max_amount']) || $amount <= $rule['max_amount']) {
                return User::whereIn('role', $rule['approvers'])->get();
            }
        }

        return collect(); // Retorna uma coleção vazia se nenhuma regra for encontrada
    }
}