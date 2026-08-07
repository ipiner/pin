<?php

declare(strict_types=1);

namespace Pin\Route\Testing\Assertions;

use Pin\Support\Str;

/**
 * 验证错误断言支持。
 *
 * 为 TestResponse 提供统一的验证失败断言能力，用于验证 Laravel Validation 异常返回的错误字段。
 *
 * 默认从 `data.errors` 中读取验证错误信息。
 */
trait AssertValidation
{
    /**
     * 断言请求验证失败
     *
     * @param  array<string>|string  $fields
     */
    public function assertInvalid(array|string $fields): static
    {
        $this->response->assertInvalid(
            errors: is_array($fields) ? $fields : Str::explode($fields),
            responseKey: 'data.errors',
        );

        return $this;
    }

    /**
     * 断言请求验证成功
     *
     * @param  array<string>|string  $fields
     */
    public function assertValid(array|string $fields): static
    {
        $this->response->assertValid(
            keys: is_array($fields) ? $fields : Str::explode($fields),
            responseKey: 'data.errors',
        );

        return $this;
    }
}
