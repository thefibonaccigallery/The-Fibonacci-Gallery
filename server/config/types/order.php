<?php

return [
    'statuses' => [
        'waiting',
        'confirming',
        'confirmed',
        'sending',
        'partially_paid',
        'finished',
        'failed',
        'refunded',
        'expired',
    ],
    'accepted_currencies' => [
        [
            'id'      => 'usdtbsc',
            'name'    => 'USDT BEP20',
            'network' => 'BNB Smart Chain',
            'fee'     => 'Very low fees',
        ],
    ],
];
