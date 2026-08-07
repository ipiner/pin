<?php

use Pin\Log\Config;

$isProduction = env('APP_ENV', 'production') === 'production';

return [

    /**
     * 日志通道配置
     */
    'channels' => [
        'app' => Config::single('app'),
        'api' => Config::single('api'),
        'sql' => [
            ...Config::single('sql'),
            /**
             * SQL 日志忽略规则
             *
             * 可用于忽略指定 SQL 或连接。
             */
            'ignores' => [],
        ],
    ],
];
