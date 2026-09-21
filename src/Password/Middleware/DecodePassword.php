<?php

declare(strict_types=1);

namespace Pin\Password\Middleware;

use Override;
use Pin\Http\Middleware\TransformsRequest;
use Pin\Support\Facades\Password;

/**
 * 请求密码字段解码
 */
class DecodePassword extends TransformsRequest
{
    /**
     * 待解码字段
     *
     * @var array<string>
     */
    protected array $fields = [
        'password',
        'current_password',
        'new_password',
        'password_confirmation',
    ];

    /**
     * 解码密码
     */
    #[Override]
    protected function normalize(string $value): string
    {
        $plain = static::resolvePlainValue($value);
        $encoded = $plain === null
            ? Password::decodeFromRequest($value)
            : Password::encode($plain);

        return Password::isEmpty($encoded) ? '' : $encoded;
    }
}
