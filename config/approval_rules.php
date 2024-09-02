<?php

return [
    'rules' => [
        [
            'max_amount' => 10000,
            'approvers' => ['L1'],
        ],
        [
            'max_amount' => 50000,
            'approvers' => ['L1', 'L2'],
        ],
        [
            'max_amount' => null, // null indica que não há limite superior
            'approvers' => ['L1', 'L2', 'L3'],
        ],
    ],
];