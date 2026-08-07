<?php

use App\Models\User;
use Pin\Auth\Guard;
use Pin\Auth\UsersProvider;

return [

    /**
     * 认证守卫（Guards）
     */
    'guards' => [
        Guard::NAME => [
            'driver' => Guard::NAME,
            'provider' => UsersProvider::NAME,
            'token_key' => 'token',
        ],
    ],

    /**
     * 用户数据提供者（Providers）
     */
    'providers' => [
        UsersProvider::NAME => [
            'driver' => UsersProvider::NAME,
            'model' => env('AUTH_MODEL', User::class),
        ],
    ],
];
