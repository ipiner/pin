<?php

declare(strict_types=1);

namespace Pin\Testing\Concerns;

use Illuminate\Support\Facades\Redis;

/**
 * 清理测试 Redis 数据。
 */
trait InteractsWithRedis
{
    /**
     * 清理测试前的数据。
     */
    protected function setUpInteractsWithRedis(): void
    {
        $this->cleanRedis();
    }

    /**
     * 清空指定连接。
     */
    protected function cleanRedis(): void
    {
        foreach ($this->getRedisConnections() as $name) {
            Redis::connection($name)->flushdb();
        }
    }

    /**
     * 获取待清理的连接。
     *
     * @return list<string>
     */
    protected function getRedisConnections(): array
    {
        return ['default', 'cache'];
    }
}
