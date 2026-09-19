<?php

declare(strict_types=1);

namespace Pin\Auth;

use Pin\Support\Facades\Token;
use Pin\Token\TokenFactory;

/**
 * 认证 Token 服务入口。
 */
class Auth
{
    /**
     * 认证模块使用的 Token Driver 名称。
     */
    public const string TOKEN_DRIVER = 'auth-token';

    /**
     * 获取认证模块的 Token 工厂实例。
     */
    public static function token(): TokenFactory
    {
        return Token::driver(static::TOKEN_DRIVER);
    }
}
