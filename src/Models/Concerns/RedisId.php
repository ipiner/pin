<?php

declare(strict_types=1);

namespace Pin\Models\Concerns;

use Pin\IdGenerator\IdGenerator;

/**
 * 为模型提供基于 Redis 自增序列的唯一 ID 生成功能
 */
trait RedisId
{
    use GeneratorId;

    /**
     * 生成新的唯一 ID
     */
    public function newUniqueId(): int
    {
        return IdGenerator::Redis->generate();
    }
}
