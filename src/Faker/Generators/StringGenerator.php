<?php

declare(strict_types=1);

namespace Pin\Faker\Generators;

use Illuminate\Support\Str;
use Override;

/**
 * 生成随机字符串
 */
class StringGenerator extends Generator
{
    /**
     * 生成数据
     */
    #[Override]
    public function fake(): string
    {
        return Str::random((int) $this->rule->parameter(0, 16));
    }
}
