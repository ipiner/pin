<?php

declare(strict_types=1);

namespace Pin\Token\Drivers;

use Pin\Token\Token;
use Pin\Token\TokenPayload;

/**
 * Aes Token Driver
 *
 * 使用 AES 对 Payload 进行加密生成 Token。
 */
class AesDriver extends Driver
{
    use AesHelper;

    /**
     * 解码 Token
     *
     * 流程：
     * 1. AES 解密
     * 2. JSON 解析
     * 3. 构建 Token 对象
     * 4. 校验是否过期
     *
     * @param  string  $encodedPayload  原始 Token
     */
    public function decode(string $encodedPayload): Token
    {
        return tap(
            $this->decrypt($encodedPayload),
            fn ($token) => $this->validateExpired($token),
        );
    }

    /**
     * 编码 Token
     *
     * 流程：
     * 1. 自动补充 exp
     * 2. Payload 转 JSON
     * 3. AES 加密
     *
     * @param  TokenPayload  $payload  Token Payload
     * @param  int|null  $expires  过期时间（秒）
     */
    public function encode(TokenPayload $payload, ?int $expires = null): string
    {
        // 自动设置过期时间
        if (! isset($payload->exp) && $expires) {
            $payload->exp = now()->getTimestamp() + $expires;
        }

        return $this->encrypt($payload);
    }
}
