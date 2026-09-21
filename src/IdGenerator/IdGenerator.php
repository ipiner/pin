<?php

declare(strict_types=1);

namespace Pin\IdGenerator;

/**
 * 内置 ID 生成器
 */
enum IdGenerator: string
{
    /**
     * 时间戳 ID
     */
    case Timestamp = 'timestamp';

    /**
     * Redis 自增 ID
     */
    case Redis = 'redis';

    /**
     * Snowflake ID
     */
    case Snowflake = 'snowflake';

    /**
     * 生成一个或多个 ID
     *
     * @param  int  $count  生成数量
     * @return int|string|list<int|string>
     */
    public function generate(int $count = 1): array|int|string
    {
        return app($this->name())->generate($count);
    }

    /**
     * 容器绑定名称
     */
    public function name(): string
    {
        return 'pin.id.'.$this->value;
    }
}
