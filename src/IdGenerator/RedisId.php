<?php

declare(strict_types=1);

namespace Pin\IdGenerator;

use Closure;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Override;

/**
 * Redis 自增 ID 生成器
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
     * 生成一个或多个 ID。
     *
     * @param  int  $count  生成数量
     * @return int|list<int>
     */
    #[Override]
    public function generate(int $count = 1): array|int
    {
        if ($count < 1) {
            return [];
        }

        $id = $this->lock(fn () => $this->increment($count));

        if ($count === 1) {
            return $id;
        }

        return range($id - $count + 1, $id);
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
     * 按配置加锁执行。
     *
     * @template TResult
     *
     * @param  Closure(): TResult  $callback
     * @return TResult
     */
    protected function lock(Closure $callback): mixed
    {
        if (! $this->config['use_lock']) {
            return $callback();
        }

        return $this->store()->lock('redis-id-'.$this->config['name'], 60)->block(5, $callback);
    }

    /**
     * 获取 Redis 缓存仓库
     */
    protected function store(): Repository
    {
        return Cache::store('redis');
    }
}
