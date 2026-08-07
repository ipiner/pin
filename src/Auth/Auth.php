<?php

declare(strict_types=1);

namespace Pin\Auth;

use Pin\Support\Facades\Token;
use Pin\Token\TokenFactory;

/**
 * 认证服务入口。
 *
 * 提供认证模块的统一访问点，便于业务代码获取当前认证体系使用的
 * Token 服务实例。
 */
class Auth
{
    /**
     * 认证模块使用的 Token Driver 名称。
     */
    public const string TOKEN_DRIVER = 'auth-token';

    /**
     * 获取认证模块的 Token 工厂实例。
     *
     * 可用于创建、解析或注销认证 Token。
     */
    public static function token(): TokenFactory
    {
        return Token::driver(static::TOKEN_DRIVER);
    }
}
