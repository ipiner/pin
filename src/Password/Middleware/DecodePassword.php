<?php

declare(strict_types=1);

namespace Pin\Password\Middleware;

use Pin\Http\Middleware\TransformsRequest;
use Pin\Support\Facades\Password;

/**
 * 请求密码字段解码中间件
 *
 * 自动对请求中的密码字段进行解码（如前端加密传输）
 */
class DecodePassword extends TransformsRequest
{
    /**
     * 需要解码的字段列表
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
     * 解密
     */
    protected function normalize(string $value): string
    {
        if ($plain = static::resolvePlainValue($value)) {
            return Password::encode($plain);
        }

        return Password::decodeFromRequest($value);
    }
}
