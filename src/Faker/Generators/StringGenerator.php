<?php

declare(strict_types=1);

namespace Pin\Faker\Generators;

use Illuminate\Support\Str;

/**
 * 生成随机字符串
 */
class StringGenerator extends Generator
{
    /**
     * 执行生成
     */
    public function fake()
    {
        return Str::random($this->rule->parameter(0, 16));
    }
}
