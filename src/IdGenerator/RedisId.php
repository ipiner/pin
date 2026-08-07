<?php

declare(strict_types=1);

namespace Pin\IdGenerator;

use Closure;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

/**
 * 基于 Redis 自增生成全局唯一整数 ID
 */
class RedisId implements IdGeneratorInterface
{
    /**
     * @param  array{name: string, use_lock: bool}  $config  配置项
     */
    public function __construct(protected array $config)
    {
    }

    /**
     * 生成一个或多个 ID
     *
     * @param  int  $count  生成数量
     * @return int|int[] 单个 ID 或 ID 列表
     */
    public function generate(int $count = 1): array|int
    {
        $id = $this->lock(fn () => $this->increment($count));

        if ($count === 1) {
            return $id;
        }

        $ids = [];
        while ($count--) {
            $ids[] = $id - $count;
        }

        return $ids;
    }

    /**
     * Redis 原子递增
     */
    protected function increment(int $count): int
    {
        return $this->store()->increment(
            'uniqid:'.$this->config['name'],
            $count,
        );
    }

    /**
     * 使用分布式锁保护生成逻辑
     *
     * @template TResult
     *
     * @param  Closure(): TResult  $callback
     * @return TResult
     */
    protected function lock(Closure $callback): mixed
    {
        $name = $this->config['name'];

        return $this->config['use_lock']
            ? Cache::lock("redis-id-{$name}", 60, 'redis-id')->block(5, $callback)
            : $callback();
    }

    /**
     * 获取 Redis 缓存仓库
     */
    protected function store(): Repository
    {
        return Cache::store('redis');
    }
}
