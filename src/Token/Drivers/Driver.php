<?php

declare(strict_types=1);

namespace Pin\Token\Drivers;

use Pin\Token\Contracts\TokenDriver;
use Pin\Token\Exceptions\TokenExpiredException;
use Pin\Token\Token;

/**
 * Token Driver 抽象基类
 */
abstract class Driver implements TokenDriver
{
    /**
     * 校验 Token 是否已过期
     *
     * @throws TokenExpiredException
     */
    protected function validateExpired(Token $token): void
    {
        if ($this->isExpired($token)) {
            throw new TokenExpiredException($token);
        }
    }

    /**
     * 判断 Token 是否过期
     */
    protected function isExpired(Token $token): bool
    {
        $now = now()->getTimestamp();

        // JWT 标准过期时间
        if (isset($token->exp)) {
            return $token->exp < $now;
        }

        // 相对生命周期模式
        if (isset($token->expires)) {
            return $token->iat + $token->expires < $now;
        }

        return false;
    }
}
