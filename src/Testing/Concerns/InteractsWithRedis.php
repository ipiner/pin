<?php

declare(strict_types=1);

namespace Pin\Testing\Concerns;

use Illuminate\Support\Facades\Redis;

/**
 * Trait InteractsWithRedis
 *
 * 提供在测试环境中自动清理 Redis 数据的能力
 */
trait InteractsWithRedis
{
    /**
     * 初始化 Redis
     */
    protected function setUpInteractsWithRedis(): void
    {
        $this->cleanRedis();
    }

    /**
     * 清理所有指定 Redis 连接中的数据
     */
    protected function cleanRedis(): void
    {
        foreach ($this->getRedisConnections() as $name) {
            Redis::connection($name)->flushdb();
        }
    }

    /**
     * 获取需要清理的 Redis 连接列表
     *
     * @return array<string>
     */
    protected function getRedisConnections(): array
    {
        return ['default', 'cache'];
    }
}
