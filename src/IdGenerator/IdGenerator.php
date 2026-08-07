<?php

declare(strict_types=1);

namespace Pin\IdGenerator;

/**
 * ID 生成策略枚举
 *
 *  内置生成策略：
 *  - Timestamp：基于时间戳的趋势递增 ID
 *  - Redis：基于 Redis INCR 的全局递增 ID
 *  - Snowflake：基于 Snowflake 算法的分布式 ID
 */
enum IdGenerator: string
{
    /**
     * 基于时间戳生成趋势递增 ID
     */
    case Timestamp = 'timestamp';

    /**
     * 基于 Redis INCR 生成全局递增 ID
     */
    case Redis = 'redis';

    /**
     * 基于 Snowflake 算法生成分布式 ID
     */
    case Snowflake = 'snowflake';

    /**
     * 使用当前生成策略生成 ID
     *
     * @param  int  $count  生成数量
     * @return int|string|array 单个 ID 或 ID 列表
     */
    public function generate(int $count = 1): array|int|string
    {
        return app($this->name())->generate($count);
    }

    /**
     * 生成器名称
     */
    public function name(): string
    {
        return 'pin.id.'.$this->value;
    }
}
