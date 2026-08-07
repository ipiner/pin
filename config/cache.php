<?php

return [

    /**
     * Token / Cache 存储驱动配置
     */
    'stores' => [

        /**
         * Redis Hash 存储
         */
        'redis-hash' => [
            'driver' => 'redis-hash',
            'store' => 'redis-hash',
            'connection' => 'cache',
            'ttl' => 604800,
        ],
    ],
];
