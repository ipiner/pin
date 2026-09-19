<?php

declare(strict_types=1);

namespace Pin\Faker\Generators;

use Illuminate\Support\Arr;
use Override;

/**
 * 从候选值中随机返回一个值
 */
class InGenerator extends Generator
{
    /**
     * 生成数据
     */
    #[Override]
    public function fake(): mixed
    {
        $value = Arr::random($this->rule->parameters());

        if ($this->rules->has('integer')) {
            return (int) $value;
        }

        return $value;
    }
}
