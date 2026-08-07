<?php

declare(strict_types=1);

namespace Pin\Faker\Generators;

use Illuminate\Support\Arr;

/**
 * 从候选值中随机返回一个值
 */
class InGenerator extends Generator
{
    /**
     * 执行生成
     */
    public function fake(): mixed
    {
        $value = Arr::random($this->rule->parameters());
        if ($this->rules->has('integer')) {
            return (int) $value;
        }

        return $value;
    }
}
