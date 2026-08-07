<?php

declare(strict_types=1);

return [

    /**
     * 默认 Token 驱动
     *
     * 指定系统默认使用的 Token 驱动。
     */
    'default' => 'default',

    /**
     * Token 驱动配置
     *
     * 内置支持：
     * - aes：AES 加密 Token（无状态）
     * - jwt：JWT Token
     * - session：基于缓存的有状态 Token
     */
    'drivers' => [

        /**
         * 默认 AES Token 驱动
         *
         * 使用 AES 对 Token 进行加密。
         *
         * 适用于：
         * - 内部系统
         * - 简单认证
         * - 无需 JWT 标准协议场景
         */
        'default' => [
            'driver' => 'aes',
        ],

        /**
         * JWT Token 驱动
         *
         * 基于 JWT（JSON Web Token）实现。
         *
         * 适用于：
         * - API 鉴权
         * - 微服务
         * - 第三方系统对接
         */
        'jwt' => [

            'driver' => 'jwt',

            /**
             * JWT 签名密钥
             *
             * 用于 JWT 签名与校验。
             *
             * 默认读取：
             * config('app.key')
             */
            // 'key' => env('JWT_KEY'),

            /**
             * JWT 签名算法
             */
            'algo' => 'HS256',
        ],

        /**
         * Session Token 驱动
         *
         * 基于 Redis / Cache 的有状态 Token。
         *
         * 支持：
         * - 服务端主动失效
         * - 滑动过期
         * - 踢人下线
         * - 单端 / 多端登录控制
         */
        'session' => [

            'driver' => 'session',

            /**
             * Token 过期时间（秒）
             *
             * 默认：
             * - 7200 秒（2 小时）
             */
            'expires' => 7200,

            /**
             * Token 缓存 Key 前缀
             */
            'cache_prefix' => 'token:',

            /**
             * Token 自动续期阈值（秒）
             *
             * 当 Token 剩余时间小于该值时，自动刷新 TTL（滑动过期）。
             *
             * 默认：
             * - 600 秒（10 分钟）
             */
            'refresh_before' => 600,

            /**
             * Token 最大生命周期（秒）
             *
             * 即使用户持续活跃，Token 生命周期也不能超过该值。
             *
             * 默认：
             * - 86400 秒（24 小时）
             */
            'max_age' => 86400,
        ],
    ],
];
