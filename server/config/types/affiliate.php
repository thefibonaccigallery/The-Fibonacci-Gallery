<?php

return [
    'commission_rate' => 20, // 20% flat commission rate
    'network' => 'USDT (BEP20)',
    'accepted_networks' => [
        'USDT (BEP20)',
    ],
    'commission_statuses' => [
        'available',
        'paid_out',
    ],
    'payout_statuses' => [
        'requested',
        'approved',
        'rejected',
        'completed',
    ],
];
