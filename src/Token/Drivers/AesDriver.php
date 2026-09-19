<?php

declare(strict_types=1);

namespace Pin\Token\Drivers;

use Override;
use Pin\Token\Token;
use Pin\Token\TokenPayload;

/**
 * AES Token 驱动。
 */
class AesDriver extends Driver
{
    use AesHelper;

    /**
     * 解码 Token。
     */
    #[Override]
    public function decode(string $encodedPayload): Token
    {
        $token = $this->decrypt($encodedPayload);
        $this->validateExpired($token);

        return $token;
    }

    /**
     * 编码 Token。
     *
     * @param  int|null  $expires  有效期（秒）
     */
    #[Override]
    public function encode(TokenPayload $payload, ?int $expires = null): string
    {
        $this->setExpiresAt($payload, $expires);

        return $this->encrypt($payload);
    }
}
