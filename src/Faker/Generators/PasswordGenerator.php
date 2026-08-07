<?php

declare(strict_types=1);

namespace Pin\Faker\Generators;

use Pin\Support\Facades\Password;

/**
 * 生成密码
 */
class PasswordGenerator extends Generator
{
    /**
     * 执行生成
     *
     * 返回的是 `请求传输密码`，而不是原始明文密码。
     */
    public function fake(): string
    {
        return Password::encodeToRequest($this->rule->parameter(0, 'test@123'));
    }
}
