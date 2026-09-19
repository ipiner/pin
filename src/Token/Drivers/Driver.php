<?php

declare(strict_types=1);

namespace Pin\Token\Drivers;

use Pin\Token\Contracts\TokenDriver;
use Pin\Token\Exceptions\TokenExpiredException;
use Pin\Token\Token;
use Pin\Token\TokenPayload;

/**
 * Token 驱动基类。
 */
abstract class Driver implements TokenDriver
{
    /**
     * 校验 Token 过期时间。
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
     * 判断 Token 是否过期。
     */
    protected function isExpired(Token $token): bool
    {
        if (isset($token->exp)) {
            return $token->exp < now()->getTimestamp();
        }

        if (isset($token->expires)) {
            return $token->iat + $token->expires < now()->getTimestamp();
        }

        return false;
    }

    /**
     * 补充 Token 过期时间。
     */
    protected function setExpiresAt(TokenPayload $payload, ?int $expires): void
    {
        if (! isset($payload->exp) && $expires) {
            $payload->exp = now()->getTimestamp() + $expires;
        }
    }
}
