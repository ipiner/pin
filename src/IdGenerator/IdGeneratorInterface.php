<?php

declare(strict_types=1);

namespace Pin\IdGenerator;

/**
 * ID 生成器接口
 */
interface IdGeneratorInterface
{
    /**
     * 生成一个或多个 ID
     *
     * @param  int  $count  生成数量
     * @return int|string|list<int|string>
     */
    public function generate(int $count = 1);
}
