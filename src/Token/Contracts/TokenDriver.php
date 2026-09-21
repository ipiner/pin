<?php

declare(strict_types=1);

namespace Pin\Token\Contracts;

use Pin\Token\Token;
use Pin\Token\TokenPayload;

/**
 * Token 驱动接口
 */
interface TokenDriver
{
    /**
     * 编码 Token
     *
     * @param  int|null  $expires  有效期（秒）
     */
    public function encode(TokenPayload $payload, ?int $expires = null): string;

    /**
     * 解码 Token
     */
    public function decode(string $encodedPayload): Token;
}
