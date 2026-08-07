<?php

declare(strict_types=1);

namespace Pin\Token\Contracts;

use Pin\Token\Token;
use Pin\Token\TokenPayload;

/**
 * Token Driver 接口
 */
interface TokenDriver
{
    /**
     * 编码 Payload 为 Token 字符串
     *
     * @param  TokenPayload  $payload  Token 载荷
     * @param  int|null  $expires  过期时间（秒）
     * @return string 生成后的 Token 字符串
     */
    public function encode(TokenPayload $payload, ?int $expires = null): string;

    /**
     * 解码 Token
     *
     * @param  string  $encodedPayload  原始 Token 字符串
     */
    public function decode(string $encodedPayload): Token;
}
