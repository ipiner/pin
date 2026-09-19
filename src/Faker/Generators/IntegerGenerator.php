<?php

declare(strict_types=1);

namespace Pin\Faker\Generators;

use Override;

/**
 * 生成随机整数
 */
class IntegerGenerator extends Generator
{
    /**
     * 生成数据
     */
    #[Override]
    public function fake(): int
    {
        return random_int(
            (int) $this->rule->parameter(0, 1),
            (int) $this->rule->parameter(1, 10000)
        );
    }
}
