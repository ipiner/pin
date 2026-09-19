<?php

declare(strict_types=1);

namespace Pin\Route\Testing\Assertions;

use Pin\Support\Str;

/**
 * 验证错误断言
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
