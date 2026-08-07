<?php

declare(strict_types=1);

namespace Pin\Faker\Generators;

use Illuminate\Support\Arr;

/**
 * 从枚举中随机返回一个值
 */
class EnumGenerator extends Generator
{
    /**
     * 执行生成
     */
    public function fake(): mixed
    {
        return Arr::random($this->rule->parameter(0)::cases())->value;
    }
}
