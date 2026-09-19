<?php

declare(strict_types=1);

namespace Pin\Faker\Generators;

use Illuminate\Support\Arr;
use Override;

/**
 * 从枚举中随机返回一个值
 */
class EnumGenerator extends Generator
{
    /**
     * 生成数据
     */
    #[Override]
    public function fake(): mixed
    {
        return Arr::random($this->rule->parameter(0)::cases())->value;
    }
}
