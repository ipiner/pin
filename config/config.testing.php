<?php

return [
    'app' => [
        'env' => 'testing',
        'rate_limit' => [
            'enabled' => false,
        ],
    ],

    'database' => [
        'default' => 'testing',

        'redis' => [
            'default' => [
                'database' => 16,
            ],

            'cache' => [
                'database' => 16,
            ],
        ],
    ],

    'mail' => [
        'default' => 'array',
    ],

    'queue' => [
        'default' => 'sync',
    ],

    'session' => [
        'driver' => 'array',
    ],

];
