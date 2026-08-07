<?php

declare(strict_types=1);

namespace Pin\Faker\Generators;

/**
 * 生成随机整数
 */
class IntegerGenerator extends Generator
{
    /**
     * 执行生成
     */
    public function fake(): int
    {
        return random_int(
            $this->rule->parameter(0, 1),
            $this->rule->parameter(1, 10000)
        );
    }
}
