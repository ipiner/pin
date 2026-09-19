<?php

declare(strict_types=1);

namespace Pin\Faker\Generators;

use Override;
use Pin\Support\Facades\Password;

/**
 * 生成请求传输密码
 */
class PasswordGenerator extends Generator
{
    /**
     * 生成数据
     */
    #[Override]
    public function fake(): string
    {
        return Password::encodeToRequest($this->rule->parameter(0, 'test@123'));
    }
}
