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
            'id'      => 'usdttrc20',
            'name'    => 'USDT TRC20',
            'network' => 'Tron Network',
            'fee'     => 'Low fees',
        ],
        [
            'id'      => 'usdtbsc',
            'name'    => 'USDT BEP20',
            'network' => 'BNB Smart Chain',
            'fee'     => 'Very low fees',
        ],
    ],
];
