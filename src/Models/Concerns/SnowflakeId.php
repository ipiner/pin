<?php

declare(strict_types=1);

namespace Pin\Models\Concerns;

use Pin\IdGenerator\IdGenerator;

/**
 * 为模型提供基于 Snowflake 算法的分布式唯一 ID 生成功能
 */
trait SnowflakeId
{
    use GeneratorId;

    /**
     * 生成新的唯一 ID
     */
    public function newUniqueId(): int
    {
        return (int) IdGenerator::Snowflake->generate();
    }
}
